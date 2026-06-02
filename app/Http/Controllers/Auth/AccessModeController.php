<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AccessModeController extends Controller
{
    private const ERROR_INVALID_MODE = 'ACCESS-MODE-INVALID';

    public function show(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $user?->hasMultipleAccessModes()) {
            return redirect()->route($user?->dashboardRouteName() ?? 'login');
        }

        return view('auth.choose-access', [
            'user' => $user,
            'modes' => $this->availableModes($user),
        ]);
    }

    public function choose(Request $request): RedirectResponse
    {
        return $this->setMode($request, 'access_mode_selected');
    }

    public function switchMode(Request $request): RedirectResponse
    {
        return $this->setMode($request, 'access_mode_switched');
    }

    private function setMode(Request $request, string $event): RedirectResponse
    {
        $user = $request->user();
        $mode = (string) $request->input('mode');

        if (! $user || ! array_key_exists($mode, $this->availableModes($user))) {
            Log::error(self::ERROR_INVALID_MODE, [
                'actor_id' => $user?->id,
                'requested_mode' => $mode,
                'controller' => self::class,
                'status_code' => 403,
            ]);

            abort(403);
        }

        $request->session()->put('active_access_mode', $mode);

        Log::info($event, [
            'actor_id' => $user->id,
            'mode' => $mode,
            'admin_role' => $user->effectiveAdminRole(),
            'user_role' => $user->role,
            'controller' => self::class,
            'status_code' => 302,
        ]);

        return redirect()->route($user->dashboardRouteNameForMode($mode));
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function availableModes(User $user): array
    {
        $modes = [];

        if ($user->isOperationalUser()) {
            $modes['user'] = [
                'label' => $this->userModeLabel($user),
                'description' => 'Buat form, lihat draft, dan riwayat pekerjaan sendiri.',
                'icon' => 'bi-person-workspace',
            ];
        }

        if ($user->hasAdminPanelAccess()) {
            $modes['admin'] = [
                'label' => $user->isAdminApproval() ? 'Admin Monitoring' : 'Admin Panel',
                'description' => $user->isAdminApproval()
                    ? 'Pantau QC dan Commissioning sesuai akses Approval.'
                    : 'Kelola menu admin sesuai permission akun.',
                'icon' => 'bi-display',
            ];
        }

        return $modes;
    }

    private function userModeLabel(User $user): string
    {
        return match ($user->role) {
            'qc' => 'User Quality Control',
            'commissioning' => 'User Commissioning',
            'pgo' => 'User PGO',
            'approval' => 'User Approval',
            default => 'User',
        };
    }
}

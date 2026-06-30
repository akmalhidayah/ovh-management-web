<?php

namespace App\Http\Middleware;

use App\Support\AdminMenuPermissions;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminMenuPermission
{
    private const MENU_ROUTE_NAMES = [
        'dashboard.dashboard' => 'admin.dashboard',
        'dashboard.overview' => 'admin.overview',
        'planning.kalender-overhaul' => 'admin.kalender-overhaul',
        'planning.schedule' => 'admin.schedule',
        'qc_commissioning.qc' => 'admin.qc',
        'qc_commissioning.commissioning' => 'admin.commissioning',
        'qc_commissioning.template-qc' => 'admin.template-form-qc.index',
        'qc_commissioning.template-commissioning' => 'admin.template-form-commissioning.index',
        'procurement.barang' => 'admin.procurement.barang',
        'procurement.jasa' => 'admin.procurement.jasa',
        'procurement.capex' => 'admin.procurement.capex',
        'procurement.action-log' => 'admin.procurement.action-log',
        'procurement.minutes-of-meeting' => 'admin.procurement.minutes-of-meeting',
        'master_data.equipment' => 'admin.master-data',
        'master_data.unit-kerja' => 'admin.organization-sections.index',
        'userpanel.management' => 'admin.user-panel',
        'userpanel.role-permission' => 'admin.user-panel.role-permission',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $routeName = (string) $request->route()?->getName();
        $menuKey = $this->menuKeyForRoute($routeName);

        if ($menuKey && ! AdminMenuPermissions::canSee($user, $menuKey)) {
            if ($request->isMethodSafe() && $fallbackRoute = $this->firstAllowedRouteName($user)) {
                return redirect()
                    ->route($fallbackRoute)
                    ->with('warning', 'Akses menu tersebut dibatasi. Anda diarahkan ke menu yang tersedia.');
            }

            abort(403);
        }

        return $next($request);
    }

    private function firstAllowedRouteName($user): ?string
    {
        foreach (self::MENU_ROUTE_NAMES as $menuKey => $routeName) {
            if (AdminMenuPermissions::canSee($user, $menuKey)) {
                return $routeName;
            }
        }

        return null;
    }

    private function menuKeyForRoute(string $routeName): ?string
    {
        if ($routeName === 'admin.dashboard') {
            return 'dashboard.dashboard';
        }

        if ($routeName === 'admin.overview') {
            return 'dashboard.overview';
        }

        if ($routeName === 'admin.kalender-overhaul') {
            return 'planning.kalender-overhaul';
        }

        if ($routeName === 'admin.schedule') {
            return 'planning.schedule';
        }

        if ($routeName === 'admin.procurement' || str_starts_with($routeName, 'admin.procurement.')) {
            return match ($routeName) {
                'admin.procurement.jasa' => 'procurement.jasa',
                'admin.procurement.capex' => 'procurement.capex',
                'admin.procurement.action-log' => 'procurement.action-log',
                'admin.procurement.minutes-of-meeting' => 'procurement.minutes-of-meeting',
                default => 'procurement.barang',
            };
        }

        if (str_starts_with($routeName, 'admin.template-form-qc.')) {
            return 'qc_commissioning.template-qc';
        }

        if (str_starts_with($routeName, 'admin.template-form-commissioning.')) {
            return 'qc_commissioning.template-commissioning';
        }

        if (str_starts_with($routeName, 'admin.qc.submissions.') || $routeName === 'admin.qc') {
            return 'qc_commissioning.qc';
        }

        if (str_starts_with($routeName, 'admin.commissioning.submissions.') || $routeName === 'admin.commissioning') {
            return 'qc_commissioning.commissioning';
        }

        if (str_starts_with($routeName, 'admin.master-data')) {
            return 'master_data.equipment';
        }

        if (str_starts_with($routeName, 'admin.organization-sections')) {
            return 'master_data.unit-kerja';
        }

        if (str_starts_with($routeName, 'admin.user-panel.role-permission')) {
            return 'userpanel.role-permission';
        }

        if (str_starts_with($routeName, 'admin.user-panel')) {
            return 'userpanel.management';
        }

        return null;
    }
}

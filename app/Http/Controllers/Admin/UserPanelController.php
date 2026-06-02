<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommissioningFormSubmission;
use App\Models\MasterDataRecord;
use App\Models\QcFormSubmission;
use App\Models\User;
use App\Services\InspectionSubmissionDeletionService;
use App\Support\AdminMenuPermissions;
use App\Support\PublicRegistrationAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class UserPanelController extends Controller
{
    private const DEFAULT_PASSWORD = 'overhaul123';
    private const ERROR_STORE = 'ADMIN-USER-STORE-FAILED';
    private const ERROR_UPDATE = 'ADMIN-USER-UPDATE-FAILED';

    public function index(Request $request): View
    {
        $filters = [
            'usertype' => $request->query('usertype', 'all'),
            'role' => $request->query('role', 'all'),
            'search' => trim((string) $request->query('search')),
        ];

        $users = User::query()
            ->when($filters['usertype'] !== 'all', fn ($query) => $query->where('usertype', $filters['usertype']))
            ->when($filters['role'] !== 'all', function ($query) use ($filters) {
                $query->where(function ($query) use ($filters) {
                    $query->where('role', $filters['role'])
                        ->orWhere('secondary_role', $filters['role']);
                });
            })
            ->when($filters['search'] !== '', function ($query) use ($filters) {
                $search = $filters['search'];

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('role', 'like', "%{$search}%")
                        ->orWhere('secondary_role', 'like', "%{$search}%");
                });
            })
            ->orderByRaw("CASE WHEN usertype = 'admin' THEN 0 ELSE 1 END")
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.user-panel.index', [
            'users' => $users,
            'filters' => $filters,
            'usertypeOptions' => self::usertypeOptions(),
            'roleOptions' => self::roleOptions(),
            'secondaryRoleOptions' => self::secondaryRoleOptions(),
            'summary' => $this->summary(),
            'defaultPassword' => self::DEFAULT_PASSWORD,
            'publicRegistrationEnabled' => PublicRegistrationAccess::enabled(),
            'workAreaOptions' => $this->workAreaOptions(),
        ]);
    }

    public function toggleRegistration(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
        ]);

        PublicRegistrationAccess::setEnabled((bool) $validated['enabled']);

        $this->logStatus('public_registration_toggled', [
            'enabled' => (bool) $validated['enabled'],
            'status_code' => 200,
        ]);

        return back()->with(
            'success',
            (bool) $validated['enabled']
                ? 'Registrasi publik berhasil diaktifkan.'
                : 'Registrasi publik berhasil dinonaktifkan.'
        );
    }

    public function rolePermission(): View
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        return view('admin.user-panel.role-permission', [
            'adminRoles' => AdminMenuPermissions::adminRoles(),
            'configurableRoles' => AdminMenuPermissions::configurableRoles(),
            'menuOptions' => AdminMenuPermissions::menuOptions(),
            'permissions' => AdminMenuPermissions::permissions(),
        ]);
    }

    public function updateRolePermission(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        AdminMenuPermissions::setPermissions($request->input('permissions', []));

        return back()->with('success', 'Permission menu berhasil diperbarui.');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateUser($request);

        try {
            $user = User::create($validated + [
                'password' => Hash::make(self::DEFAULT_PASSWORD),
            ]);
        } catch (Throwable $exception) {
            $this->logError(self::ERROR_STORE, $exception, [
                'email' => $validated['email'] ?? null,
                'status_code' => 500,
            ]);

            return back()
                ->withInput()
                ->withErrors(['user' => 'User gagal dibuat. Kode error: '.self::ERROR_STORE]);
        }

        $this->logStatus('admin_user_created', [
            'target_user_id' => $user->id,
            'target_usertype' => $user->usertype,
            'target_role' => $user->role,
            'target_secondary_role' => $user->secondary_role,
            'status_code' => 201,
        ]);

        return redirect()
            ->route('admin.user-panel')
            ->with('success', "Akun {$user->name} berhasil dibuat. Password default: ".self::DEFAULT_PASSWORD);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $this->validateUser($request, $user);

        if ($request->user()?->is($user) && $validated['usertype'] !== 'admin') {
            $this->logStatus('admin_user_self_demotion_blocked', [
                'target_user_id' => $user->id,
                'status_code' => 403,
            ]);

            return back()->withErrors(['user' => 'Akun admin yang sedang login tidak bisa diubah menjadi user.']);
        }

        try {
            $user->update($validated);
        } catch (Throwable $exception) {
            $this->logError(self::ERROR_UPDATE, $exception, [
                'target_user_id' => $user->id,
                'status_code' => 500,
            ]);

            return back()
                ->withInput()
                ->withErrors(['user' => 'User gagal diperbarui. Kode error: '.self::ERROR_UPDATE]);
        }

        $this->logStatus('admin_user_updated', [
            'target_user_id' => $user->id,
            'target_usertype' => $user->usertype,
            'target_role' => $user->role,
            'target_secondary_role' => $user->secondary_role,
            'status_code' => 200,
        ]);

        return back()->with('success', "Data {$user->name} berhasil diperbarui.");
    }

    public function destroy(
        Request $request,
        User $user,
        InspectionSubmissionDeletionService $deletionService
    ): RedirectResponse
    {
        if ($request->user()?->is($user)) {
            $this->logStatus('admin_user_self_delete_blocked', [
                'target_user_id' => $user->id,
                'status_code' => 403,
            ]);

            return back()->withErrors(['user' => 'Akun admin yang sedang login tidak bisa dihapus.']);
        }

        $name = $user->name;

        try {
            $this->deleteUserDraftSubmissions($user, $deletionService);
            $user->delete();
        } catch (Throwable $exception) {
            $this->logError(self::ERROR_UPDATE, $exception, [
                'target_user_id' => $user->id,
                'status_code' => 500,
            ]);

            return back()->withErrors(['user' => 'User gagal dihapus. Kode error: '.self::ERROR_UPDATE]);
        }

        $this->logStatus('admin_user_deleted', [
            'target_user_id' => $user->id,
            'status_code' => 200,
        ]);

        return back()->with('success', "Akun {$name} berhasil dihapus.");
    }

    private function deleteUserDraftSubmissions(User $user, InspectionSubmissionDeletionService $deletionService): void
    {
        QcFormSubmission::query()
            ->where('user_id', $user->id)
            ->where('status', 'draft')
            ->get()
            ->each(fn (QcFormSubmission $submission) => $deletionService->deleteQcPermanently($submission));

        CommissioningFormSubmission::query()
            ->where('user_id', $user->id)
            ->where('status', 'draft')
            ->get()
            ->each(fn (CommissioningFormSubmission $submission) => $deletionService->deleteCommissioningPermanently($submission, auth()->user()));
    }

    private function validateUser(Request $request, ?User $user = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'usertype' => ['required', Rule::in(array_keys(self::usertypeOptions()))],
            'role' => ['required', Rule::in(array_keys(self::roleOptions()))],
            'secondary_role' => ['nullable', Rule::in(array_keys(self::secondaryRoleOptions()))],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'profile_areas' => ['nullable', 'array'],
            'profile_areas.*' => ['string', Rule::in($this->allWorkAreaOptions())],
        ], [
            'profile_photo.image' => 'Foto profil harus berupa gambar.',
            'profile_photo.mimes' => 'Foto profil hanya boleh format JPG atau PNG.',
            'profile_photo.max' => 'Ukuran foto profil maksimal 2 MB.',
        ]);

        if ($validated['usertype'] === 'admin' && ! array_key_exists($validated['role'], self::adminRoleOptions())) {
            $validated['role'] = AdminMenuPermissions::ROLE_ADMIN;
        } elseif ($validated['usertype'] === 'user' && ! array_key_exists($validated['role'], self::operationalRoleOptions())) {
            $validated['role'] = 'qc';
        }

        $validated['secondary_role'] = $validated['usertype'] === 'user'
            ? ($validated['secondary_role'] ?? null)
            : null;

        if (($validated['secondary_role'] ?? null) === $validated['role']) {
            $validated['secondary_role'] = null;
        }

        if (in_array($validated['role'], ['qc', 'commissioning'], true)) {
            $areas = collect($validated['profile_areas'] ?? [])
                ->filter()
                ->unique()
                ->values()
                ->all();

            $validated['profile_areas'] = $areas ?: null;
            $validated['profile_plants'] = $areas
                ? $this->plantsForAreas($validated['role'], $areas)
                : null;
        } else {
            $validated['profile_areas'] = null;
            $validated['profile_plants'] = null;
        }

        if ($request->hasFile('profile_photo')) {
            if ($user?->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            $validated['profile_photo_path'] = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        unset($validated['profile_photo']);

        return $validated;
    }

    private static function usertypeOptions(): array
    {
        return [
            'admin' => 'Admin',
            'user' => 'User',
        ];
    }

    private static function roleOptions(): array
    {
        return self::adminRoleOptions() + self::operationalRoleOptions();
    }

    private static function adminRoleOptions(): array
    {
        return AdminMenuPermissions::adminRoles();
    }

    private static function operationalRoleOptions(): array
    {
        return [
            'qc' => 'Quality Control',
            'commissioning' => 'Commissioning',
            'pgo' => 'PGO',
            'approval' => 'Approval',
        ];
    }

    private static function secondaryRoleOptions(): array
    {
        return [
            'qc' => 'Quality Control',
            'commissioning' => 'Commissioning',
            'pgo' => 'PGO',
            AdminMenuPermissions::ROLE_APPROVAL => 'Approval / Monitoring',
        ];
    }

    private function summary(): array
    {
        return [
            'total' => User::count(),
            'admin' => User::where('usertype', 'admin')->count(),
            'user' => User::where('usertype', 'user')->count(),
            'approval' => User::where('role', 'approval')
                ->orWhere('secondary_role', AdminMenuPermissions::ROLE_APPROVAL)
                ->count(),
        ];
    }

    private function workAreaOptions(): array
    {
        return [
            'qc' => $this->workAreaOptionsFor(MasterDataRecord::CATEGORY_QC),
            'commissioning' => $this->workAreaOptionsFor(MasterDataRecord::CATEGORY_COMMISSIONING),
        ];
    }

    private function allWorkAreaOptions(): array
    {
        return collect($this->workAreaOptions())
            ->flatten()
            ->unique()
            ->values()
            ->all();
    }

    private function workAreaOptionsFor(string $category): array
    {
        return MasterDataRecord::query()
            ->where('document_category', $category)
            ->where('status', 'active')
            ->whereNotNull('area')
            ->where('area', '<>', '')
            ->distinct()
            ->orderBy('area')
            ->pluck('area')
            ->all();
    }

    private function plantsForAreas(string $role, array $areas): array
    {
        $category = $role === 'qc'
            ? MasterDataRecord::CATEGORY_QC
            : MasterDataRecord::CATEGORY_COMMISSIONING;

        return MasterDataRecord::query()
            ->where('document_category', $category)
            ->where('status', 'active')
            ->whereIn('area', $areas)
            ->whereNotNull('plant')
            ->where('plant', '<>', '')
            ->distinct()
            ->orderBy('plant')
            ->pluck('plant')
            ->all();
    }

    private function logStatus(string $event, array $context = []): void
    {
        Log::info($event, $context + [
            'actor_id' => auth()->id(),
            'controller' => self::class,
        ]);
    }

    private function logError(string $code, Throwable $exception, array $context = []): void
    {
        Log::error($code, $context + [
            'actor_id' => auth()->id(),
            'controller' => self::class,
            'exception' => $exception::class,
            'message' => $exception->getMessage(),
        ]);
    }
}

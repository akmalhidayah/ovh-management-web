<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\ResetPasswordNotification;
use App\Support\AdminMenuPermissions;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'usertype',
        'role',
        'secondary_role',
        'profile_photo_path',
        'profile_plants',
        'profile_areas',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'profile_plants' => 'array',
            'profile_areas' => 'array',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->usertype === 'admin';
    }

    public function isSuperAdmin(): bool
    {
        return $this->isAdmin() && $this->role === AdminMenuPermissions::ROLE_SUPER_ADMIN;
    }

    public function hasAdminPanelAccess(): bool
    {
        return $this->isAdmin()
            || in_array($this->secondary_role, array_keys(AdminMenuPermissions::adminRoles()), true);
    }

    public function effectiveAdminRole(): ?string
    {
        if ($this->isAdmin()) {
            return $this->role;
        }

        return in_array($this->secondary_role, array_keys(AdminMenuPermissions::adminRoles()), true)
            ? $this->secondary_role
            : null;
    }

    public function isAdminApproval(): bool
    {
        return $this->effectiveAdminRole() === AdminMenuPermissions::ROLE_APPROVAL;
    }

    public function isOperationalUser(): bool
    {
        return $this->usertype === 'user';
    }

    public function hasMultipleAccessModes(): bool
    {
        return count($this->availableAccessModes()) > 1;
    }

    public function hasUserRole(string $role): bool
    {
        return $this->isOperationalUser() && in_array($role, $this->userRoles(), true);
    }

    /**
     * @return list<string>
     */
    public function userRoles(): array
    {
        if (! $this->isOperationalUser()) {
            return [];
        }

        $roles = [$this->role];

        if (in_array($this->secondary_role, ['qc', 'commissioning', 'pgo'], true)) {
            $roles[] = $this->secondary_role;
        }

        return array_values(array_unique(array_filter($roles)));
    }

    /**
     * @return list<string>
     */
    public function availableAccessModes(): array
    {
        $modes = collect($this->userRoles())
            ->map(fn (string $role): string => "user:{$role}")
            ->all();

        if ($this->hasAdminPanelAccess()) {
            $modes[] = 'admin';
        }

        return $modes;
    }

    public function dashboardRouteName(): string
    {
        if ($this->hasAdminPanelAccess() && session('active_access_mode') === 'admin') {
            return 'admin.dashboard';
        }

        if ($this->isAdmin()) {
            return 'admin.dashboard';
        }

        $role = $this->activeUserRole();

        return match ($role) {
            'qc' => 'user.qc.dashboard',
            'commissioning' => 'user.commissioning.dashboard',
            'pgo' => 'user.pgo.dashboard',
            'approval' => 'user.approval.dashboard',
            default => 'login',
        };
    }

    public function dashboardRouteNameForMode(string $mode): string
    {
        if ($mode === 'admin' && $this->hasAdminPanelAccess()) {
            return 'admin.dashboard';
        }

        $role = str_starts_with($mode, 'user:')
            ? str($mode)->after('user:')->toString()
            : $this->activeUserRole();

        if (! $this->hasUserRole($role)) {
            $role = $this->role;
        }

        return match ($role) {
            'qc' => 'user.qc.dashboard',
            'commissioning' => 'user.commissioning.dashboard',
            'pgo' => 'user.pgo.dashboard',
            'approval' => 'user.approval.dashboard',
            default => $this->isAdmin() ? 'admin.dashboard' : 'login',
        };
    }

    public function activeUserRole(): string
    {
        $role = (string) session('active_user_role');

        return $this->hasUserRole($role) ? $role : $this->role;
    }

    public function profilePhotoUrl(): ?string
    {
        if (! $this->profile_photo_path) {
            return null;
        }

        if (! Storage::disk('public')->exists($this->profile_photo_path)) {
            return null;
        }

        return route('profile.photo', [
            'user' => $this,
            'v' => $this->updated_at?->timestamp ?? time(),
        ]);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}

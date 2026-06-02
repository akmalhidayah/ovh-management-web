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
        'admin_role',
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
            || in_array($this->admin_role, array_keys(AdminMenuPermissions::adminRoles()), true);
    }

    public function effectiveAdminRole(): ?string
    {
        if ($this->isAdmin()) {
            return $this->role;
        }

        return $this->admin_role;
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
        return $this->isOperationalUser() && $this->hasAdminPanelAccess();
    }

    public function dashboardRouteName(): string
    {
        if ($this->hasAdminPanelAccess() && session('active_access_mode') === 'admin') {
            return 'admin.dashboard';
        }

        if ($this->isAdmin()) {
            return 'admin.dashboard';
        }

        return match ($this->role) {
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

        return match ($this->role) {
            'qc' => 'user.qc.dashboard',
            'commissioning' => 'user.commissioning.dashboard',
            'pgo' => 'user.pgo.dashboard',
            'approval' => 'user.approval.dashboard',
            default => $this->isAdmin() ? 'admin.dashboard' : 'login',
        };
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

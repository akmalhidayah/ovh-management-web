<?php

namespace App\Support;

use App\Models\AppSetting;
use App\Models\User;

class AdminMenuPermissions
{
    public const SETTING_KEY = 'admin_menu_permissions';
    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_APPROVAL = 'approval';

    public static function configurableRoles(): array
    {
        return [
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_APPROVAL => 'Approval',
        ];
    }

    public static function adminRoles(): array
    {
        return [
            self::ROLE_SUPER_ADMIN => 'Super Admin',
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_APPROVAL => 'Approval',
        ];
    }

    public static function menuOptions(): array
    {
        return [
            'dashboard' => [
                'label' => 'Dashboard',
                'children' => [
                    'dashboard.dashboard' => 'Dashboard',
                    'dashboard.overview' => 'Overview',
                ],
            ],
            'planning' => [
                'label' => 'Planning',
                'children' => [
                    'planning.kalender-overhaul' => 'Kalender Overhaul',
                    'planning.schedule' => 'Schedule',
                ],
            ],
            'qc_commissioning' => [
                'label' => 'QC & Commissioning',
                'children' => [
                    'qc_commissioning.qc' => 'Quality Control',
                    'qc_commissioning.commissioning' => 'Commissioning',
                    'qc_commissioning.template-qc' => 'Template Form QC',
                    'qc_commissioning.template-commissioning' => 'Template Form Commissioning',
                ],
            ],
            'procurement' => [
                'label' => 'Pengadaan',
                'children' => [
                    'procurement.barang' => 'Barang',
                    'procurement.jasa' => 'Jasa',
                    'procurement.capex' => 'Capex',
                    'procurement.action-log' => 'Action Log',
                    'procurement.minutes-of-meeting' => 'Minutes of Meeting',
                ],
            ],
            'master_data' => [
                'label' => 'Master Data',
                'children' => [
                    'master_data.equipment' => 'Equipment',
                    'master_data.unit-kerja' => 'Unit Kerja',
                ],
            ],
            'userpanel' => [
                'label' => 'Userpanel',
                'children' => [
                    'userpanel.management' => 'Manajemen User',
                    'userpanel.role-permission' => 'Role & Permission',
                ],
            ],
        ];
    }

    public static function allMenuKeys(): array
    {
        return collect(self::menuOptions())
            ->flatMap(fn (array $group) => array_keys($group['children']))
            ->values()
            ->all();
    }

    public static function defaultPermissions(): array
    {
        $all = self::allMenuKeys();

        return [
            self::ROLE_ADMIN => array_values(array_diff($all, ['userpanel.role-permission'])),
            self::ROLE_APPROVAL => [
                'dashboard.dashboard',
                'qc_commissioning.qc',
                'qc_commissioning.commissioning',
            ],
        ];
    }

    public static function permissions(): array
    {
        $stored = AppSetting::query()->where('key', self::SETTING_KEY)->value('value');
        $decoded = is_string($stored) && $stored !== '' ? json_decode($stored, true) : [];
        $decoded = is_array($decoded) ? $decoded : [];
        $defaults = self::defaultPermissions();
        $allowedKeys = self::allMenuKeys();

        foreach (self::configurableRoles() as $role => $label) {
            $values = is_array($decoded[$role] ?? null) ? $decoded[$role] : ($defaults[$role] ?? []);
            $decoded[$role] = collect($values)
                ->intersect($allowedKeys)
                ->values()
                ->all();
        }

        return array_intersect_key($decoded, self::configurableRoles());
    }

    public static function setPermissions(array $permissions): void
    {
        $allowedKeys = self::allMenuKeys();
        $normalized = [];

        foreach (self::configurableRoles() as $role => $label) {
            $normalized[$role] = collect($permissions[$role] ?? [])
                ->intersect($allowedKeys)
                ->values()
                ->all();
        }

        AppSetting::updateOrCreate(
            ['key' => self::SETTING_KEY],
            ['value' => json_encode($normalized)]
        );
    }

    public static function canSee(?User $user, string $menuKey): bool
    {
        if (! $user || ! $user->hasAdminPanelAccess()) {
            return false;
        }

        $role = $user->effectiveAdminRole();

        if ($role === self::ROLE_SUPER_ADMIN) {
            return true;
        }

        return in_array($menuKey, self::permissions()[$role] ?? [], true);
    }
}

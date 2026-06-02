@php
    $currentUser = auth()->user();
    $isAdminShell = request()->routeIs('admin.*') && (bool) $currentUser?->hasAdminPanelAccess();
    $role = $isAdminShell ? 'admin' : 'inspector';
    $brandRoleLabel = $role === 'admin'
        ? (\App\Support\AdminMenuPermissions::adminRoles()[$currentUser->effectiveAdminRole() ?? ''] ?? 'Admin')
        : strtoupper($role);
    $canSeeAdminMenu = fn (?string $key): bool => $role !== 'admin'
        || $key === null
        || \App\Support\AdminMenuPermissions::canSee($currentUser, $key);
    $groups = [
        [
            'key' => 'dashboard',
            'label' => 'Dashboard',
            'icon' => 'bi-speedometer2',
            'routes' => ["{$role}.dashboard", "{$role}.overview"],
            'items' => [
                ['key' => 'dashboard.dashboard', 'label' => 'Dashboard', 'route' => "{$role}.dashboard"],
                ['key' => 'dashboard.overview', 'label' => 'Overview', 'route' => "{$role}.overview"],
            ],
        ],
        [
            'key' => 'planning',
            'label' => 'Planning',
            'icon' => 'bi-calendar3',
            'routes' => ["{$role}.kalender-overhaul", "{$role}.schedule"],
            'items' => [
                ['key' => 'planning.kalender-overhaul', 'label' => 'Kalender Overhaul', 'route' => "{$role}.kalender-overhaul"],
                ['key' => 'planning.schedule', 'label' => 'Schedule', 'route' => "{$role}.schedule"],
            ],
        ],
        [
            'key' => 'qc_commissioning',
            'label' => 'QC & Commissioning',
            'icon' => 'bi-shield-check',
            'routes' => array_filter([
                "{$role}.qc",
                "{$role}.commissioning",
                $role === 'admin' ? 'admin.qc.submissions.*' : null,
                $role === 'admin' ? 'admin.commissioning.submissions.*' : null,
                $role === 'admin' ? 'admin.template-form-qc.*' : null,
                $role === 'admin' ? 'admin.template-form-commissioning.*' : null,
            ]),
            'items' => [
                ...($role === 'admin' ? [
                    ['key' => 'qc_commissioning.qc', 'label' => 'Quality Control', 'route' => 'admin.qc', 'active' => ['admin.qc', 'admin.qc.submissions.*']],
                    ['key' => 'qc_commissioning.commissioning', 'label' => 'Commissioning', 'route' => 'admin.commissioning', 'active' => ['admin.commissioning', 'admin.commissioning.submissions.*']],
                    ['key' => 'qc_commissioning.template-qc', 'label' => 'Template Form QC', 'route' => 'admin.template-form-qc.index', 'active' => 'admin.template-form-qc.*'],
                    ['key' => 'qc_commissioning.template-commissioning', 'label' => 'Template Form Commissioning', 'route' => 'admin.template-form-commissioning.index', 'active' => 'admin.template-form-commissioning.*'],
                ] : [
                    ['label' => 'QC', 'route' => "{$role}.qc"],
                    ['label' => 'Commissioning', 'route' => "{$role}.commissioning"],
                ]),
            ],
        ],
        ['key' => 'procurement.index', 'label' => 'Procurement', 'icon' => 'bi-cart-check', 'route' => "{$role}.procurement"],
        [
            'key' => 'asset_records',
            'label' => 'Asset & Records',
            'icon' => 'bi-archive',
            'routes' => ["{$role}.equipment", "{$role}.mom", "{$role}.dokumen"],
            'items' => [
                ['key' => 'asset_records.equipment', 'label' => 'Equipment', 'route' => "{$role}.equipment"],
                ['key' => 'asset_records.mom', 'label' => 'MoM', 'route' => "{$role}.mom"],
                ['key' => 'asset_records.dokumen', 'label' => 'Dokumen', 'route' => "{$role}.dokumen"],
            ],
        ],
    ];

    if ($role === 'admin') {
        $groups[] = ['section' => 'Lainnya'];
        $groups[] = [
            'key' => 'master_data',
            'label' => 'Master Data',
            'icon' => 'bi-database-gear',
            'routes' => ['admin.master-data*', 'admin.organization-sections*'],
            'items' => [
                ['key' => 'master_data.equipment', 'label' => 'Equipment', 'route' => 'admin.master-data', 'active' => 'admin.master-data*'],
                ['key' => 'master_data.unit-kerja', 'label' => 'Unit Kerja', 'route' => 'admin.organization-sections.index', 'active' => 'admin.organization-sections*'],
            ],
        ];
        $groups[] = [
            'key' => 'userpanel',
            'label' => 'Userpanel',
            'icon' => 'bi-people',
            'routes' => ['admin.user-panel*'],
            'items' => [
                ['key' => 'userpanel.management', 'label' => 'Manajemen User', 'route' => 'admin.user-panel', 'active' => 'admin.user-panel'],
                ['key' => 'userpanel.role-permission', 'label' => 'Role & Permission', 'route' => 'admin.user-panel.role-permission', 'active' => 'admin.user-panel.role-permission'],
            ],
        ];
    }

    $groups = collect($groups)
        ->map(function (array $item) use ($canSeeAdminMenu) {
            if (isset($item['items'])) {
                $item['items'] = collect($item['items'])
                    ->filter(fn (array $child) => $canSeeAdminMenu($child['key'] ?? null))
                    ->values()
                    ->all();

                return $item['items'] === [] ? null : $item;
            }

            if (isset($item['section'])) {
                return $item;
            }

            return $canSeeAdminMenu($item['key'] ?? null) ? $item : null;
        })
        ->filter()
        ->values();

    $groups = $groups
        ->reject(function (array $item, int $index) use ($groups) {
            if (! isset($item['section'])) {
                return false;
            }

            $next = $groups->get($index + 1);

            return ! $next || isset($next['section']);
        })
        ->values()
        ->all();
@endphp

<aside class="ovh-sidebar" id="ovhSidebar">
    <div class="sidebar-brand">
        <div class="brand-logo-group">
            <img src="{{ asset('assets/images/logo/logo-st2.png') }}" alt="Semen Tonasa" class="brand-logo">
        </div>
        <div class="brand-copy">
            <div class="brand-title">Overhaul PT. Semen Tonasa</div>
            <div class="brand-subtitle">{{ strtoupper($brandRoleLabel) }}</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        @foreach ($groups as $index => $item)
            @php
                $isSection = isset($item['section']);
                $isGroup = isset($item['items']);
                $isActive = $isSection ? false : ($isGroup ? request()->routeIs(...$item['routes']) : request()->routeIs($item['active'] ?? $item['route']));
                $groupId = 'sidebarGroup'.$index;
            @endphp

            @if ($isSection)
                <div class="sidebar-section-label">{{ $item['section'] }}</div>
            @elseif ($isGroup)
                <div class="sidebar-group {{ $isActive ? 'open' : '' }}" data-sidebar-group>
                    <button class="sidebar-link sidebar-group-toggle {{ $isActive ? 'active' : '' }}" type="button" aria-expanded="{{ $isActive ? 'true' : 'false' }}" aria-controls="{{ $groupId }}">
                        <i class="bi {{ $item['icon'] }}"></i>
                        <span>{{ $item['label'] }}</span>
                        <i class="bi bi-chevron-down group-chevron"></i>
                    </button>
                    <div class="sidebar-submenu" id="{{ $groupId }}">
                        @foreach ($item['items'] as $child)
                            @php
                                $href = route($child['route']).(isset($child['hash']) ? '#'.$child['hash'] : '');
                                $activeRoutes = (array) ($child['active'] ?? $child['route']);
                            @endphp
                            <a href="{{ $href }}" class="sidebar-sublink {{ request()->routeIs(...$activeRoutes) && ! isset($child['hash']) ? 'active' : '' }}">
                                {{ $child['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @else
                <a href="{{ route($item['route']) }}" class="sidebar-link {{ $isActive ? 'active' : '' }}">
                    <i class="bi {{ $item['icon'] }}"></i>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endif
        @endforeach
    </nav>
</aside>

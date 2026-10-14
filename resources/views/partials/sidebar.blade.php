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
            'key' => 'dashboard.dashboard',
            'label' => 'Dashboard',
            'icon' => 'bi-speedometer2',
            'route' => "{$role}.dashboard",
        ],
        [
            'key' => 'planning.kalender-overhaul',
            'label' => 'Kalender Overhaul',
            'icon' => 'bi-calendar3',
            'route' => "{$role}.kalender-overhaul",
        ],
        [
            'key' => 'planning.schedule',
            'label' => 'Rencana Kerja',
            'icon' => 'bi-clipboard-check',
            'routes' => ["{$role}.schedule"],
            'items' => [
                ['key' => 'planning.schedule', 'label' => 'Rencana Kerja Barang', 'route' => "{$role}.schedule", 'query' => ['work_type' => 'barang']],
                ['key' => 'planning.schedule', 'label' => 'Rencana Kerja Jasa', 'route' => "{$role}.schedule", 'query' => ['work_type' => 'jasa']],
                ['key' => 'planning.schedule', 'label' => 'Rencana Kerja Capex', 'route' => "{$role}.schedule", 'query' => ['work_type' => 'capex']],
            ],
        ],
        [
            'key' => 'procurement',
            'label' => 'Pengadaan',
            'icon' => 'bi-cart-check',
            'routes' => $role === 'admin'
                ? ['admin.procurement', 'admin.procurement.barang', 'admin.procurement.jasa', 'admin.procurement.capex']
                : ["{$role}.procurement"],
            'items' => [
                ...($role === 'admin' ? [
                    ['key' => 'procurement.barang', 'label' => 'Barang', 'route' => 'admin.procurement.barang'],
                    ['key' => 'procurement.jasa', 'label' => 'Jasa', 'route' => 'admin.procurement.jasa'],
                    ['key' => 'procurement.capex', 'label' => 'Capex', 'route' => 'admin.procurement.capex'],
                ] : [
                    ['key' => 'procurement.barang', 'label' => 'Pengadaan', 'route' => "{$role}.procurement"],
                ]),
            ],
        ],
        ...($role === 'admin' ? [
            [
                'key' => 'dashboard.overview',
                'label' => 'Progress Overhaul',
                'icon' => 'bi-clipboard-data',
                'route' => 'admin.overview',
            ],
            [
                'key' => 'procurement.action-log',
                'label' => 'Action Log',
                'icon' => 'bi-card-checklist',
                'route' => 'admin.procurement.action-log',
            ],
            [
                'key' => 'procurement.minutes-of-meeting',
                'label' => 'MOM',
                'icon' => 'bi-clipboard2-check',
                'route' => 'admin.procurement.minutes-of-meeting',
            ],
        ] : []),
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
        ...($role === 'admin' ? [
            [
                'label' => 'MAC',
                'icon' => 'bi-tools',
                'route' => 'admin.equipment',
            ],
        ] : []),
    ];

    if ($role === 'admin') {
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
        $groups[] = [
            'key' => 'master_data',
            'label' => 'Master Data',
            'icon' => 'bi-database-gear',
            'routes' => ['admin.master-data*', 'admin.organization-sections*', 'admin.woms-organization*'],
            'items' => [
                ['key' => 'master_data.equipment', 'label' => 'Equipment', 'route' => 'admin.master-data', 'active' => 'admin.master-data*'],
                ['key' => 'master_data.unit-kerja', 'label' => 'Unit Kerja', 'route' => 'admin.organization-sections.index', 'active' => 'admin.organization-sections*'],
                ['key' => 'master_data.woms-organization', 'label' => 'Struktur Organisasi WOMS', 'route' => 'admin.woms-organization.index', 'active' => 'admin.woms-organization*'],
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
                                $childQuery = $child['query'] ?? [];
                                $href = route($child['route'], $childQuery).(isset($child['hash']) ? '#'.$child['hash'] : '');
                                $activeRoutes = (array) ($child['active'] ?? $child['route']);
                                $expectedWorkType = $childQuery['work_type'] ?? null;
                                $currentWorkType = request()->query('work_type', 'barang');
                                $isChildActive = request()->routeIs(...$activeRoutes)
                                    && ($expectedWorkType === null || $currentWorkType === $expectedWorkType);
                            @endphp
                            <a href="{{ $href }}" class="sidebar-sublink {{ $isChildActive && ! isset($child['hash']) ? 'active' : '' }}">
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

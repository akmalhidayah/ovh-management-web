@php
    $role = (auth()->user()->usertype ?? 'inspector') === 'admin' ? 'admin' : 'inspector';
    $groups = [
        [
            'label' => 'Dashboard',
            'icon' => 'bi-speedometer2',
            'routes' => ["{$role}.dashboard", "{$role}.overview"],
            'items' => [
                ['label' => 'Dashboard', 'route' => "{$role}.dashboard"],
                ['label' => 'Overview', 'route' => "{$role}.overview"],
            ],
        ],
        [
            'label' => 'Planning',
            'icon' => 'bi-calendar3',
            'routes' => ["{$role}.kalender-overhaul", "{$role}.schedule"],
            'items' => [
                ['label' => 'Kalender Overhaul', 'route' => "{$role}.kalender-overhaul"],
                ['label' => 'Schedule', 'route' => "{$role}.schedule"],
            ],
        ],
        [
            'label' => 'Inspection & Commissioning',
            'icon' => 'bi-shield-check',
            'routes' => array_filter([
                "{$role}.qc",
                "{$role}.commissioning",
                $role === 'admin' ? 'admin.qc.submissions.*' : null,
                $role === 'admin' ? 'admin.template-form-qc.*' : null,
                $role === 'admin' ? 'admin.template-form-commissioning.*' : null,
            ]),
            'items' => [
                ...($role === 'admin' ? [
                    ['label' => 'QC & Commissioning', 'route' => 'admin.qc', 'active' => 'admin.qc'],
                    ['label' => 'Template Form QC', 'route' => 'admin.template-form-qc.index', 'active' => 'admin.template-form-qc.*'],
                    ['label' => 'Template Form Commissioning', 'route' => 'admin.template-form-commissioning.index', 'active' => 'admin.template-form-commissioning.*'],
                ] : [
                    ['label' => 'QC', 'route' => "{$role}.qc"],
                    ['label' => 'Commissioning', 'route' => "{$role}.commissioning"],
                ]),
            ],
        ],
        ['label' => 'Procurement', 'icon' => 'bi-cart-check', 'route' => "{$role}.procurement"],
        [
            'label' => 'Asset & Records',
            'icon' => 'bi-archive',
            'routes' => ["{$role}.equipment", "{$role}.mom", "{$role}.dokumen"],
            'items' => [
                ['label' => 'Equipment', 'route' => "{$role}.equipment"],
                ['label' => 'MoM', 'route' => "{$role}.mom"],
                ['label' => 'Dokumen', 'route' => "{$role}.dokumen"],
            ],
        ],
    ];

    if ($role === 'admin') {
        $groups[] = [
            'label' => 'Master Data',
            'icon' => 'bi-database-gear',
            'route' => 'admin.master-data',
            'active' => 'admin.master-data*',
        ];
    }
@endphp

<aside class="ovh-sidebar" id="ovhSidebar">
    <div class="sidebar-brand">
        <div class="brand-logo-group">
            <img src="{{ asset('assets/images/logo/logo-st2.png') }}" alt="Semen Tonasa" class="brand-logo">
        </div>
        <div class="brand-copy">
            <div class="brand-title">Overhaull PT. Semen Tonasa</div>
            <div class="brand-subtitle">{{ strtoupper($role) }}</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        @foreach ($groups as $index => $item)
            @php
                $isGroup = isset($item['items']);
                $isActive = $isGroup ? request()->routeIs(...$item['routes']) : request()->routeIs($item['active'] ?? $item['route']);
                $groupId = 'sidebarGroup'.$index;
            @endphp

            @if ($isGroup)
                <div class="sidebar-group {{ $isActive ? 'open' : '' }}" data-sidebar-group>
                    <button class="sidebar-link sidebar-group-toggle {{ $isActive ? 'active' : '' }}" type="button" aria-expanded="{{ $isActive ? 'true' : 'false' }}" aria-controls="{{ $groupId }}">
                        <i class="bi {{ $item['icon'] }}"></i>
                        <span>{{ $item['label'] }}</span>
                        <i class="bi bi-chevron-down group-chevron"></i>
                    </button>
                    <div class="sidebar-submenu" id="{{ $groupId }}">
                        @foreach ($item['items'] as $child)
                            @php($href = route($child['route']).(isset($child['hash']) ? '#'.$child['hash'] : ''))
                            <a href="{{ $href }}" class="sidebar-sublink {{ request()->routeIs($child['active'] ?? $child['route']) && ! isset($child['hash']) ? 'active' : '' }}">
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

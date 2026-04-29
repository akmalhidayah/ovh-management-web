@php
    $role = auth()->user()->usertype ?? 'user';
    $menu = [
        ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => "{$role}.dashboard"],
        ['label' => 'Overview', 'icon' => 'bi-grid-1x2', 'route' => "{$role}.overview"],
        ['label' => 'Procurement', 'icon' => 'bi-cart-check', 'route' => "{$role}.procurement"],
        ['label' => 'Kalender Overhaul', 'icon' => 'bi-calendar3', 'route' => "{$role}.kalender-overhaul"],
        ['label' => 'Schedule', 'icon' => 'bi-diagram-3', 'route' => "{$role}.schedule"],
        ['label' => 'Commissioning', 'icon' => 'bi-tools', 'route' => "{$role}.commissioning"],
        ['label' => 'QC', 'icon' => 'bi-shield-check', 'route' => "{$role}.qc"],
        ['label' => 'Equipment', 'icon' => 'bi-cpu', 'route' => "{$role}.equipment"],
        ['label' => 'MoM', 'icon' => 'bi-journal-text', 'route' => "{$role}.mom"],
        ['label' => 'Dokument', 'icon' => 'bi-folder2-open', 'route' => "{$role}.dokument"],
    ];

    if ($role === 'admin') {
        $menu[] = ['label' => 'Master Data', 'icon' => 'bi-database-gear', 'route' => 'admin.master-data'];
    }
@endphp

<aside class="ovh-sidebar" id="ovhSidebar">
    <div class="sidebar-brand">
        <img src="{{ asset('assets/images/logo/logo-ovh.svg') }}" alt="OVH" class="brand-logo">
        <div>
            <div class="brand-title">OVH Management</div>
            <div class="brand-subtitle">{{ strtoupper($role) }}</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        @foreach ($menu as $item)
            <a href="{{ route($item['route']) }}" class="sidebar-link {{ request()->routeIs($item['route']) ? 'active' : '' }}">
                <i class="bi {{ $item['icon'] }}"></i>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>
</aside>

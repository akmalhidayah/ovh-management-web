@php
    $requestedProcurementServiceTab = request()->query('tab', 'dashboard');
    $procurementServiceTabs = [
        'dashboard' => 'Dashboard',
        'finish' => 'Good Issue/Finish',
        'ready' => 'Ready',
        'procurement' => 'Procurement',
        'pending' => 'Pending',
        'cancel' => 'Cancel',
        'all' => 'All Item',
    ];
    $procurementServiceTab = is_string($requestedProcurementServiceTab) && array_key_exists($requestedProcurementServiceTab, $procurementServiceTabs)
        ? $requestedProcurementServiceTab
        : 'dashboard';
@endphp

<div class="procurement-goods procurement-service" data-procurement-goods>
    <nav class="procurement-goods-tabs" aria-label="Navigasi Pengadaan Jasa">
        @foreach ($procurementServiceTabs as $tabKey => $tabLabel)
            <a
                href="{{ route('admin.procurement.jasa', ['tab' => $tabKey]) }}"
                class="procurement-goods-tab {{ $procurementServiceTab === $tabKey ? 'is-active' : '' }}"
                @if ($procurementServiceTab === $tabKey) aria-current="page" @endif
            >{{ $tabLabel }}</a>
        @endforeach
    </nav>

    <div class="procurement-goods-content">
        @if ($procurementServiceTab === 'dashboard')
            @include('modules.procurement-barang.dashboard', [
                'procurementBudgetTitle' => 'Budget Overhaul',
                'procurementContextLabel' => 'jasa',
            ])
        @else
            @include('modules.procurement-jasa.list', ['listMode' => $procurementServiceTab])
        @endif
    </div>
</div>

@include('modules.procurement-barang.assets')
@include('modules.procurement-jasa.assets')

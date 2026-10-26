@php
    $requestedScheduleCapexTab = request()->query('tab', 'dashboard');
    $scheduleCapexTabs = [
        'dashboard' => 'Dashboard',
        'lanjut' => 'Lanjut',
        'pending' => 'Pending',
        'batal' => 'Batal',
        'semua' => 'Semua',
    ];
    $scheduleCapexTab = is_string($requestedScheduleCapexTab) && array_key_exists($requestedScheduleCapexTab, $scheduleCapexTabs)
        ? $requestedScheduleCapexTab
        : 'dashboard';
@endphp

<div class="schedule-barang-tabs-shell" style="--schedule-tab-count: {{ count($scheduleCapexTabs) }};">
    <nav class="schedule-barang-tabs" aria-label="Navigasi Rencana Kerja Capex">
        @foreach ($scheduleCapexTabs as $tabKey => $tabLabel)
            <a
                href="{{ request()->url() }}?{{ http_build_query(['work_type' => 'capex', 'tab' => $tabKey]) }}"
                class="schedule-barang-tab {{ $scheduleCapexTab === $tabKey ? 'is-active' : '' }}"
                @if ($scheduleCapexTab === $tabKey) aria-current="page" @endif
            >{{ $tabLabel }}</a>
        @endforeach
    </nav>

    <div class="schedule-barang-tab-content">
        @include("modules.schedule-capex.{$scheduleCapexTab}")
    </div>
</div>

@include('modules.schedule-shared.tab-assets')

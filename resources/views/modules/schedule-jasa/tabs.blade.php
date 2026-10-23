@php
    $requestedScheduleJasaTab = request()->query('tab', 'dashboard');
    $scheduleJasaTabs = [
        'dashboard' => 'Dashboard',
        'lanjut' => 'Lanjut',
        'internal' => 'Dikerjakan Intern',
        'batal' => 'Batal',
        'semua' => 'Semua',
    ];
    $scheduleJasaTab = is_string($requestedScheduleJasaTab) && array_key_exists($requestedScheduleJasaTab, $scheduleJasaTabs)
        ? $requestedScheduleJasaTab
        : 'dashboard';
@endphp

<div class="schedule-barang-tabs-shell" style="--schedule-tab-count: {{ count($scheduleJasaTabs) }};">
    <nav class="schedule-barang-tabs" aria-label="Navigasi Rencana Kerja Jasa">
        @foreach ($scheduleJasaTabs as $tabKey => $tabLabel)
            <a
                href="{{ request()->url() }}?{{ http_build_query(['work_type' => 'jasa', 'tab' => $tabKey]) }}"
                class="schedule-barang-tab {{ $scheduleJasaTab === $tabKey ? 'is-active' : '' }}"
                @if ($scheduleJasaTab === $tabKey) aria-current="page" @endif
            >{{ $tabLabel }}</a>
        @endforeach
    </nav>

    <div class="schedule-barang-tab-content">
        @include("modules.schedule-jasa.{$scheduleJasaTab}")
    </div>
</div>

@include('modules.schedule-shared.tab-assets')

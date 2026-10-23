@php
    $requestedScheduleBarangTab = request()->query('tab', 'dashboard');
    $scheduleBarangTabs = [
        'dashboard' => 'Dashboard',
        'lanjut' => 'Lanjut',
        'cancel' => 'Cancel',
        'semua' => 'Semua',
    ];
    $scheduleBarangTab = is_string($requestedScheduleBarangTab) && array_key_exists($requestedScheduleBarangTab, $scheduleBarangTabs)
        ? $requestedScheduleBarangTab
        : 'dashboard';
@endphp

<div class="schedule-barang-tabs-shell" style="--schedule-tab-count: {{ count($scheduleBarangTabs) }};">
    <nav class="schedule-barang-tabs" aria-label="Navigasi Rencana Kerja Barang">
        @foreach ($scheduleBarangTabs as $tabKey => $tabLabel)
            <a
                href="{{ request()->url() }}?{{ http_build_query(['work_type' => 'barang', 'tab' => $tabKey]) }}"
                class="schedule-barang-tab {{ $scheduleBarangTab === $tabKey ? 'is-active' : '' }}"
                @if ($scheduleBarangTab === $tabKey) aria-current="page" @endif
            >{{ $tabLabel }}</a>
        @endforeach
    </nav>

    <div class="schedule-barang-tab-content">
        @include("modules.schedule-barang.{$scheduleBarangTab}")
    </div>
</div>

@include('modules.schedule-shared.tab-assets')

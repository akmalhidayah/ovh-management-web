@php
    $requestedScheduleBarangTab = request()->query('tab', 'dashboard');
    $scheduleBarangTabs = [
        'dashboard' => 'Dashboard',
        'lanjut' => 'Lanjut',
        'internal' => 'Dikerjakan Intern',
        'batal' => 'Batal',
        'semua' => 'Semua',
    ];
    $scheduleBarangTab = array_key_exists($requestedScheduleBarangTab, $scheduleBarangTabs)
        ? $requestedScheduleBarangTab
        : 'dashboard';
@endphp

<div class="schedule-barang-tabs-shell">
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

@push('styles')
    <style>
        .schedule-barang-tabs-shell {
            display: grid;
            gap: 22px;
            min-width: 0;
        }

        .schedule-barang-tabs {
            min-height: 48px;
            padding: 7px;
            display: flex;
            align-items: center;
            gap: 8px;
            overflow-x: auto;
            border: 1px solid #d7e0e8;
            border-radius: 8px;
            background: #fff;
            scrollbar-width: thin;
        }

        .schedule-barang-tab {
            min-width: 116px;
            min-height: 34px;
            padding: 0 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            border-radius: 6px;
            color: #1f2937;
            font-size: 12px;
            font-weight: 600;
            line-height: 1;
            text-decoration: none;
            white-space: nowrap;
            transition: background-color .18s ease, color .18s ease;
        }

        .schedule-barang-tab:hover {
            background: #edf3f8;
            color: #173f6d;
        }

        .schedule-barang-tab.is-active {
            background: #1e4b80;
            color: #fff;
            box-shadow: 0 2px 5px rgba(30, 75, 128, .2);
        }

        .schedule-barang-tab:focus-visible {
            outline: 3px solid rgba(30, 75, 128, .22);
            outline-offset: 2px;
        }

        .schedule-barang-tab-content {
            min-width: 0;
        }

        .schedule-barang-empty-sheet {
            min-height: 560px;
            border: 1px solid #dce4ec;
            border-radius: 9px;
            background: #fff;
        }

        @media (max-width: 575.98px) {
            .schedule-barang-tabs-shell { gap: 16px; }
            .schedule-barang-tabs { border-radius: 7px; }
            .schedule-barang-tab { min-width: 105px; padding: 0 13px; }
            .schedule-barang-empty-sheet { min-height: 420px; }
        }
    </style>
@endpush

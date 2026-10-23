@push('styles')
    <style>
        .schedule-barang-tabs-shell {
            display: grid;
            gap: 22px;
            min-width: 0;
        }

        .schedule-barang-tabs {
            min-height: 48px;
            width: 100%;
            padding: 6px;
            display: grid;
            grid-template-columns: repeat(var(--schedule-tab-count, 5), minmax(0, 1fr));
            align-items: center;
            gap: 5px;
            border: 1px solid #d7e0e8;
            border-radius: 8px;
            background: #f8fafc;
        }

        .schedule-barang-tab {
            width: 100%;
            min-height: 36px;
            padding: 0 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            color: #475569;
            font-size: 12px;
            font-weight: 700;
            line-height: 1;
            text-decoration: none;
            white-space: nowrap;
            transition: background-color .18s ease, color .18s ease;
        }

        .schedule-barang-tab:hover {
            background: #eaf1f7;
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
            .schedule-barang-tabs {
                display: flex;
                overflow-x: auto;
                border-radius: 7px;
                scrollbar-width: thin;
            }
            .schedule-barang-tab { width: auto; min-width: 132px; padding: 0 13px; flex: 0 0 auto; }
            .schedule-barang-empty-sheet { min-height: 420px; }
        }
    </style>
@endpush

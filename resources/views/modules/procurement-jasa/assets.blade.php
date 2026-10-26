@push('styles')
    <style>
        .procurement-service-filter {
            grid-template-columns: 110px 140px minmax(180px, 1fr) minmax(170px, .8fr) auto;
            gap: 12px;
        }

        .procurement-service-export-actions {
            min-height: 38px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 7px;
        }

        .procurement-service-export-actions button {
            min-height: 34px;
            padding: 0 11px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border: 0;
            border-radius: 7px;
            color: #fff;
            font: inherit;
            font-size: 9px;
            font-weight: 800;
            white-space: nowrap;
            box-shadow: 0 2px 5px rgba(15, 23, 42, .12);
        }

        .procurement-service-export-actions .is-excel { background: #3f865e; }
        .procurement-service-export-actions .is-pdf { background: #e65059; }
        .procurement-service-export-actions button:hover { filter: brightness(.95); }
        .procurement-service-export-actions button:focus-visible,
        .procurement-service-actions button:focus-visible { outline: 3px solid rgba(35, 104, 185, .2); outline-offset: 1px; }

        .procurement-service-table table { min-width: 1540px; table-layout: fixed; }
        .procurement-service-table th:first-child,
        .procurement-service-table td:first-child { width: 82px; text-align: left; }
        .procurement-service-table th:nth-child(2) { width: 150px; }
        .procurement-service-table th:nth-child(3) { width: 105px; }
        .procurement-service-table th:nth-child(4) { width: 220px; }
        .procurement-service-table th:nth-child(5) { width: 105px; }
        .procurement-service-table th:nth-child(6) { width: 150px; }
        .procurement-service-table th:nth-child(7) { width: 155px; }
        .procurement-service-table th:nth-child(8) { width: 100px; }
        .procurement-service-table th:nth-child(9) { width: 92px; }
        .procurement-service-table th:nth-child(10) { width: 88px; }
        .procurement-service-table th:nth-child(11) { width: 118px; }
        .procurement-service-table th:nth-child(12),
        .procurement-service-table th:nth-child(13) { width: 58px; text-align: center; }
        .procurement-service-table td { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .procurement-service-table td:nth-child(12),
        .procurement-service-table td:nth-child(13) { text-align: center; }

        .procurement-service-notification { color: #6683a5; font-size: 8px; font-weight: 750; text-decoration: none; }
        .procurement-service-notification:hover { color: var(--pg-blue); text-decoration: underline; }

        .procurement-service-progress {
            display: grid;
            grid-template-columns: minmax(36px, 1fr) 28px;
            align-items: center;
            gap: 6px;
        }

        .procurement-service-progress i {
            position: relative;
            height: 4px;
            overflow: hidden;
            border-radius: 999px;
            background: #e6ebf1;
        }

        .procurement-service-progress i::after {
            content: '';
            position: absolute;
            inset: 0 auto 0 0;
            width: var(--service-progress, 0%);
            border-radius: inherit;
            background: var(--service-progress-color, #4aa36f);
        }

        .procurement-service-progress i[data-progress="0"] { --service-progress: 0%; }
        .procurement-service-progress i[data-progress="17"] { --service-progress: 17%; --service-progress-color: #2d70d6; }
        .procurement-service-progress i[data-progress="22"] { --service-progress: 22%; --service-progress-color: #2d70d6; }
        .procurement-service-progress i[data-progress="45"] { --service-progress: 45%; --service-progress-color: #ef9808; }
        .procurement-service-progress i[data-progress="65"] { --service-progress: 65%; --service-progress-color: #34898a; }
        .procurement-service-progress i[data-progress="75"] { --service-progress: 75%; --service-progress-color: #34898a; }
        .procurement-service-progress i[data-progress="100"] { --service-progress: 100%; }
        .procurement-service-progress strong { color: #28527f; font-size: 8px; text-align: right; }

        .procurement-service-actions { display: inline-flex; align-items: center; justify-content: center; gap: 2px; }
        .procurement-service-actions button {
            width: 22px;
            height: 22px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid transparent;
            border-radius: 5px;
            background: transparent;
            font-size: 9px;
        }

        .procurement-service-actions button:hover { border-color: #d9e2eb; background: #f8fafc; }
        .procurement-service-actions .is-view { color: #2563c2; }
        .procurement-service-actions .is-edit { color: #8b5bd6; }
        .procurement-service-actions .is-document { color: #e65362; }
        .procurement-service-actions .is-note { color: #72849a; }

        @media (max-width: 1199.98px) {
            .procurement-service-filter { grid-template-columns: 110px 140px repeat(2, minmax(180px, 1fr)); }
            .procurement-service-export-actions { grid-column: 1 / -1; justify-content: flex-end; }
        }

        @media (max-width: 767.98px) {
            .procurement-service-filter { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .procurement-service-filter .procurement-goods-search { grid-column: auto; }
        }

        @media (max-width: 575.98px) {
            .procurement-service-filter { grid-template-columns: minmax(0, 1fr); }
            .procurement-service-export-actions { grid-column: auto; justify-content: stretch; }
            .procurement-service-export-actions button { flex: 1 1 0; }
        }
    </style>
@endpush

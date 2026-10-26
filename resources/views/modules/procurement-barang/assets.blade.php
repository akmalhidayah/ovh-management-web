@push('styles')
    <style>
        .procurement-goods {
            --pg-navy: #1d4679;
            --pg-blue: #2368b9;
            --pg-teal: #337a79;
            --pg-green: #4d9d75;
            --pg-red: #e24b55;
            --pg-amber: #ef9808;
            display: grid;
            gap: 16px;
            min-width: 0;
            color: #1d3149;
        }

        .procurement-goods-tabs {
            width: 100%;
            min-height: 42px;
            padding: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
            overflow-x: auto;
            border: 1px solid #d4dee8;
            border-radius: 10px;
            background: rgba(255, 255, 255, .9);
            box-shadow: 0 2px 8px rgba(15, 23, 42, .04);
            scrollbar-width: thin;
        }

        .procurement-goods-tab {
            min-width: max-content;
            min-height: 32px;
            padding: 0 14px;
            flex: 1 0 auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 7px;
            color: #334155;
            font-size: 11px;
            font-weight: 700;
            line-height: 1;
            text-decoration: none;
            white-space: nowrap;
            transition: background-color .18s ease, color .18s ease, box-shadow .18s ease;
        }

        .procurement-goods-tab:hover { background: #edf3f8; color: var(--pg-navy); }
        .procurement-goods-tab.is-active { background: var(--pg-navy); color: #fff; box-shadow: 0 3px 8px rgba(29, 70, 121, .2); }
        .procurement-goods-tab:focus-visible { outline: 3px solid rgba(35, 104, 185, .2); outline-offset: 1px; }
        .procurement-goods-content { min-width: 0; }

        .procurement-goods-filter {
            margin-bottom: 14px;
            display: grid;
            grid-template-columns: 130px 150px;
            align-items: end;
            gap: 18px;
        }

        .procurement-goods-filter.is-list { grid-template-columns: 130px 150px minmax(260px, 420px); }
        .procurement-goods-filter label { display: grid; gap: 5px; min-width: 0; }
        .procurement-goods-filter label > span { color: #263a51; font-size: 11px; font-weight: 700; }
        .procurement-goods-filter select,
        .procurement-goods-search > div {
            width: 100%;
            min-height: 38px;
            border: 1px solid #dce5ee;
            border-radius: 8px;
            background: #fff;
            color: #26364d;
            font: inherit;
            font-size: 12px;
        }

        .procurement-goods-filter select { padding: 0 30px 0 10px; }
        .procurement-goods-search > div { padding: 0 11px; display: flex; align-items: center; gap: 8px; }
        .procurement-goods-search i { color: #94a3b8; }
        .procurement-goods-search input { width: 100%; min-width: 0; border: 0; outline: 0; background: transparent; font: inherit; }
        .procurement-goods-filter select:focus,
        .procurement-goods-search > div:focus-within { border-color: #6d91b7; box-shadow: 0 0 0 3px rgba(35, 104, 185, .1); outline: 0; }

        .procurement-goods-dashboard-title {
            margin: 2px 0 10px;
            color: #174d84;
            font-size: 18px;
            font-weight: 850;
            letter-spacing: .015em;
            text-transform: uppercase;
        }

        .procurement-goods-budget { display: grid; grid-template-columns: minmax(0, 1fr) 210px; gap: 12px; }
        .procurement-goods-budget-main { display: grid; gap: 10px; min-width: 0; }
        .procurement-goods-budget-headline { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
        .procurement-goods-metric,
        .procurement-goods-mini-metric,
        .procurement-goods-owner,
        .procurement-goods-owner-status,
        .procurement-goods-process-card,
        .procurement-goods-panel,
        .procurement-goods-realization {
            border: 1px solid #d6e0e9;
            border-radius: 11px;
            background: #fff;
            box-shadow: 0 2px 5px rgba(15, 23, 42, .065);
        }

        .procurement-goods-metric { min-height: 88px; padding: 14px 20px; display: flex; align-items: center; gap: 13px; }
        .procurement-goods-metric-icon,
        .procurement-goods-owner > span {
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 9px;
            background: linear-gradient(145deg, #285d98, var(--pg-navy));
            color: #fff;
            font-size: 20px;
        }

        .procurement-goods-metric small,
        .procurement-goods-mini-metric small,
        .procurement-goods-owner small,
        .procurement-goods-owner-status small { display: block; color: #37516e; font-size: 10px; font-weight: 800; text-transform: uppercase; }
        .procurement-goods-metric strong { display: block; margin-top: 2px; color: #174d84; font-size: clamp(24px, 2vw, 31px); font-weight: 850; line-height: 1; }

        .procurement-goods-flow-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 8px; }
        .procurement-goods-mini-metric { min-height: 76px; padding: 11px; display: flex; align-items: center; gap: 9px; }
        .procurement-goods-mini-metric > span { width: 36px; height: 36px; flex: 0 0 36px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; color: #fff; font-size: 17px; }
        .procurement-goods-mini-metric.is-orange > span { background: #f29b38; }
        .procurement-goods-mini-metric.is-green > span { background: #52c71e; }
        .procurement-goods-mini-metric.is-purple > span { background: #8058ed; }
        .procurement-goods-mini-metric.is-red > span { background: #e54c58; }
        .procurement-goods-mini-metric small { font-size: 8px; color: #536273; }
        .procurement-goods-mini-metric strong { display: block; margin-top: 2px; color: #101827; font-size: 16px; line-height: 1; white-space: nowrap; }

        .procurement-goods-realization { position: relative; min-height: 174px; padding: 18px; display: grid; align-content: center; color: #43966d; border-color: #66bc94; background: linear-gradient(145deg, #f0fcf7, #e8f9f1); }
        .procurement-goods-realization > small { font-size: 11px; font-weight: 800; line-height: 1.35; text-transform: uppercase; }
        .procurement-goods-realization > strong { margin-top: 13px; font-size: 54px; font-weight: 850; letter-spacing: -.05em; line-height: .9; }
        .procurement-goods-realization > strong span { margin-left: 3px; font-size: .62em; }
        .procurement-goods-realization p { margin: 10px 0 0; color: #397b5d; font-size: 9px; font-weight: 700; }
        .procurement-goods-realization-icon { position: absolute; top: 13px; right: 13px; width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; background: var(--pg-green); color: #fff; font-size: 20px; }

        .procurement-goods-owner-grid { margin-top: 10px; display: grid; grid-template-columns: .9fr .9fr 1.1fr; gap: 10px; }
        .procurement-goods-owner { min-height: 76px; padding: 13px 16px; display: flex; align-items: center; gap: 11px; }
        .procurement-goods-owner strong,
        .procurement-goods-owner-status strong { display: block; margin-top: 2px; color: #174d84; font-size: 22px; line-height: 1; }
        .procurement-goods-owner-status { min-height: 76px; padding: 12px 16px; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); align-items: center; gap: 12px; }
        .procurement-goods-owner-status > div { display: flex; align-items: center; gap: 9px; min-width: 0; }
        .procurement-goods-owner-status > div > i { width: 34px; height: 34px; flex: 0 0 34px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; color: #fff; }
        .procurement-goods-owner-status .is-cancel > i { background: #e04435; }
        .procurement-goods-owner-status .is-pending > i { background: #ef9808; }
        .procurement-goods-owner-status strong { font-size: 16px; white-space: nowrap; }

        .procurement-goods-process { margin-top: 18px; display: grid; gap: 9px; }
        .procurement-goods-process h2 { margin: 0; color: #174d84; font-size: 18px; font-weight: 850; text-transform: uppercase; }
        .procurement-goods-process h2 small { color: #1f2937; font-size: 9px; }
        .procurement-goods-process-grid { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 7px; }
        .procurement-goods-process-card { min-height: 64px; padding: 9px; display: flex; align-items: center; gap: 8px; }
        .procurement-goods-process-card > span { width: 34px; height: 34px; flex: 0 0 34px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; background: var(--pg-navy); color: #fff; font-size: 16px; }
        .procurement-goods-process-card.is-red > span { background: var(--pg-red); }
        .procurement-goods-process-card.is-amber > span { background: var(--pg-amber); }
        .procurement-goods-process-card strong { color: #111827; font-size: 21px; line-height: 1; }
        .procurement-goods-process-card small { margin-left: 3px; color: #111827; font-size: 8px; font-weight: 800; }
        .procurement-goods-process-card p { margin: 3px 0 0; color: #536273; font-size: 8px; line-height: 1; }

        .procurement-goods-dual-grid { margin-top: 12px; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        .procurement-goods-panel { overflow: hidden; min-width: 0; }
        .procurement-goods-panel > h3 { margin: 0; padding: 7px 14px; background: var(--pg-navy); color: #fff; font-size: 10px; font-weight: 800; text-align: center; text-transform: uppercase; }
        .procurement-goods-panel.is-teal > h3 { background: var(--pg-teal); }
        .procurement-goods-panel > a { display: block; padding: 0 14px 10px; color: #df2f37; font-size: 9px; font-weight: 800; text-align: right; text-decoration: none; }

        .procurement-goods-plant-progress { padding: 10px; display: grid; grid-template-columns: 110px 1fr; gap: 8px; }
        .procurement-goods-ring,
        .procurement-goods-status-ring { display: grid; place-items: center; border-radius: 50%; background: conic-gradient(var(--ring-color, var(--pg-navy)) 0 var(--ring-progress, 21%), #e6ebf1 var(--ring-progress, 21%) 100%); }
        .procurement-goods-ring { width: 96px; height: 96px; margin: auto; }
        .procurement-goods-ring::before,
        .procurement-goods-status-ring::before { content: ''; grid-area: 1 / 1; border-radius: 50%; background: #fff; }
        .procurement-goods-ring::before { width: 68px; height: 68px; }
        .procurement-goods-ring > div,
        .procurement-goods-status-ring > div { z-index: 1; display: grid; grid-area: 1 / 1; text-align: center; }
        .procurement-goods-ring strong { font-size: 20px; line-height: 1; }
        .procurement-goods-ring span { margin-top: 3px; color: #6b7d91; font-size: 7px; text-transform: uppercase; }
        .procurement-goods-ring.is-5 { --ring-color: #3b827f; --ring-progress: 5%; }
        .procurement-goods-ring.is-21 { --ring-progress: 21%; }
        .procurement-goods-plant-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 6px; }
        .procurement-goods-plant-grid > div { min-height: 44px; padding: 7px 9px; display: flex; align-items: flex-end; justify-content: space-between; border: 1px solid #d9e2eb; border-radius: 8px; background: #fbfdff; }
        .procurement-goods-plant-grid small { color: #607286; font-size: 7px; font-weight: 800; text-transform: uppercase; }
        .procurement-goods-plant-grid strong { color: #16283f; font-size: 17px; line-height: 1; }
        .procurement-goods-panel.is-teal .procurement-goods-plant-grid strong { color: #2d7271; }

        .procurement-goods-status-chart { min-height: 190px; padding: 14px 18px; display: grid; grid-template-columns: minmax(130px, .8fr) minmax(180px, 1.2fr); align-items: center; gap: 20px; }
        .procurement-goods-status-ring { width: 142px; height: 142px; margin: auto; }
        .procurement-goods-status-ring::before { width: 84px; height: 84px; }
        .procurement-goods-status-ring strong { color: #142439; font-size: 25px; line-height: 1; }
        .procurement-goods-status-ring span { margin-top: 3px; color: #738296; font-size: 7px; text-transform: uppercase; }
        .procurement-goods-status-ring.is-procurement { background: conic-gradient(#174d62 0 78.9%, #e34d59 78.9% 89.5%, #4aaab4 89.5% 96%, #efb23a 96% 100%); }
        .procurement-goods-status-ring.is-order { background: conic-gradient(#286d6b 0 40.9%, #4c9995 40.9% 62.3%, #65c2bc 62.3% 83.4%, #8edbd4 83.4% 92.3%, #c2eeea 92.3% 100%); }
        .procurement-goods-status-chart ul { margin: 0; padding: 0; display: grid; gap: 8px; list-style: none; }
        .procurement-goods-status-chart li { display: grid; grid-template-columns: 7px 1fr auto; align-items: center; gap: 7px; color: #53667c; font-size: 8px; }
        .procurement-goods-status-chart li i { width: 7px; height: 7px; border-radius: 50%; background: #174d62; }
        .procurement-goods-status-chart li i.is-cancel { background: #e34d59; }
        .procurement-goods-status-chart li i.is-po { background: #4aaab4; }
        .procurement-goods-status-chart li i.is-ready { background: #efb23a; }
        .procurement-goods-status-chart li i.is-order-cancel { background: #4c9995; }
        .procurement-goods-status-chart li i.is-purchase { background: #65c2bc; }
        .procurement-goods-status-chart li i.is-shipping { background: #8edbd4; }
        .procurement-goods-status-chart li strong { color: #24445b; font-size: 8px; }

        .procurement-goods-request-list { padding: 14px 16px 8px; display: grid; gap: 10px; }
        .procurement-goods-request-list > div { display: grid; grid-template-columns: 70px 1fr 36px; align-items: center; gap: 8px; font-size: 9px; }
        .procurement-goods-request-list span { color: #3d526a; font-weight: 700; }
        .procurement-goods-request-list strong { color: #1b4d87; font-size: 9px; text-align: right; }
        .procurement-goods-panel.is-teal .procurement-goods-request-list strong { color: #2d7271; }
        .procurement-goods-progress { height: 14px; overflow: hidden; border-radius: 5px; background: #edf1f5; box-shadow: inset 0 1px 2px rgba(15, 23, 42, .08); }
        .procurement-goods-progress i { display: block; width: 0; height: 100%; border-radius: inherit; background: linear-gradient(90deg, #143f74, #236ac4); }
        .procurement-goods-panel.is-teal .procurement-goods-progress i { background: linear-gradient(90deg, #286b69, #479997); }
        .procurement-goods-progress[data-progress="5"] i { width: 5%; }
        .procurement-goods-progress[data-progress="10"] i { width: 10%; }
        .procurement-goods-progress[data-progress="15"] i { width: 15%; }
        .procurement-goods-progress[data-progress="20"] i { width: 20%; }
        .procurement-goods-progress[data-progress="25"] i { width: 25%; }
        .procurement-goods-progress[data-progress="46"] i { width: 46%; }
        .procurement-goods-progress[data-progress="58"] i { width: 58%; }
        .procurement-goods-progress[data-progress="80"] i { width: 80%; }

        .procurement-goods-table-card { min-width: 0; overflow: hidden; border: 1px solid #d6e0e9; border-radius: 10px; background: #fff; box-shadow: 0 2px 5px rgba(15, 23, 42, .055); }
        .procurement-goods-table-scroll { overflow-x: auto; }
        .procurement-goods-table-card table { width: 100%; min-width: 1420px; border-collapse: collapse; color: #2b4058; font-size: 9px; }
        .procurement-goods-table-card th { padding: 9px 7px; border-bottom: 1px solid #dfe6ee; background: #f4f7fa; color: #536b86; font-size: 7px; font-weight: 850; letter-spacing: .025em; text-align: left; text-transform: uppercase; white-space: nowrap; }
        .procurement-goods-table-card td { padding: 10px 7px; border-bottom: 1px solid #edf1f5; vertical-align: middle; }
        .procurement-goods-table-card tbody tr:nth-child(even) { background: #fbfdff; }
        .procurement-goods-table-card tbody tr:hover { background: #f2f7fb; }
        .procurement-goods-table-card th:first-child,
        .procurement-goods-table-card td:first-child { width: 36px; text-align: center; }
        .procurement-goods-table-card th:nth-child(4) { width: 180px; }
        .procurement-goods-table-card th:nth-child(6) { width: 290px; }
        .procurement-goods-table-card td:nth-child(9),
        .procurement-goods-table-card td:nth-child(10) { text-align: center; }
        .procurement-goods-status { min-width: 82px; padding: 5px 8px; display: inline-flex; align-items: center; justify-content: center; gap: 5px; border-radius: 999px; color: #fff; font-size: 7px; font-weight: 800; white-space: nowrap; }
        .procurement-goods-status i { font-size: 5px; }
        .procurement-goods-status.is-navy { background: #173f70; }
        .procurement-goods-status.is-green { background: #4aa36f; }
        .procurement-goods-status.is-teal { background: #3e9a8d; }
        .procurement-goods-status.is-blue { background: #4297bd; }
        .procurement-goods-status.is-purple { background: #8058ed; }
        .procurement-goods-status.is-amber { background: #ef9808; }
        .procurement-goods-status.is-red { background: #e34b55; }
        .procurement-goods-action { width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid transparent; border-radius: 6px; background: transparent; font-size: 11px; }
        .procurement-goods-action.is-edit { color: #386fd0; }
        .procurement-goods-action.is-note { color: #7890a7; }
        .procurement-goods-action:hover { border-color: #d6e0e9; background: #fff; }
        [data-procurement-row][hidden] { display: none; }
        .procurement-goods-empty { min-height: 180px; display: grid; place-items: center; align-content: center; gap: 8px; color: #718399; }
        .procurement-goods-empty i { font-size: 25px; }
        .procurement-goods-empty[hidden] { display: none; }
        .procurement-goods-table-footer { min-height: 46px; padding: 9px 12px; display: flex; align-items: center; justify-content: space-between; gap: 12px; color: #6b7f95; font-size: 9px; }
        [data-procurement-pagination] { display: flex; gap: 5px; }
        .procurement-goods-page { min-width: 28px; height: 28px; padding: 0 8px; border: 1px solid #d6e0e9; border-radius: 6px; background: #fff; color: #52677f; font: inherit; font-size: 10px; font-weight: 800; }
        .procurement-goods-page.is-active { border-color: var(--pg-navy); background: var(--pg-navy); color: #fff; }
        .procurement-goods-page:disabled { opacity: .4; cursor: not-allowed; }

        @media (max-width: 1199.98px) {
            .procurement-goods-budget { grid-template-columns: minmax(0, 1fr) 190px; }
            .procurement-goods-process-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .procurement-goods-owner-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .procurement-goods-owner-status { grid-column: 1 / -1; }
        }

        @media (max-width: 991.98px) {
            .procurement-goods-budget,
            .procurement-goods-dual-grid { grid-template-columns: minmax(0, 1fr); }
            .procurement-goods-realization { min-height: 120px; }
            .procurement-goods-flow-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .procurement-goods-filter.is-list { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .procurement-goods-search { grid-column: 1 / -1; }
        }

        @media (max-width: 575.98px) {
            .procurement-goods { gap: 13px; }
            .procurement-goods-tab { padding: 0 12px; }
            .procurement-goods-filter,
            .procurement-goods-filter.is-list,
            .procurement-goods-budget-headline,
            .procurement-goods-flow-grid,
            .procurement-goods-owner-grid,
            .procurement-goods-owner-status,
            .procurement-goods-process-grid { grid-template-columns: minmax(0, 1fr); }
            .procurement-goods-search,
            .procurement-goods-owner-status { grid-column: auto; }
            .procurement-goods-metric { min-height: 76px; padding: 12px 15px; }
            .procurement-goods-process-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .procurement-goods-plant-progress { grid-template-columns: minmax(0, 1fr); }
            .procurement-goods-status-chart { grid-template-columns: minmax(0, 1fr); }
            .procurement-goods-table-footer { align-items: flex-start; flex-direction: column; }
            [data-procurement-pagination] { flex-wrap: wrap; }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-procurement-table]').forEach((tableRoot) => {
                const rows = Array.from(tableRoot.querySelectorAll('[data-procurement-row]'));
                const moduleRoot = tableRoot.closest('[data-procurement-goods]');
                const search = moduleRoot?.querySelector('[data-procurement-search]');
                const specification = moduleRoot?.querySelector('[data-procurement-specification]');
                const plant = moduleRoot?.querySelector('[data-procurement-plant]');
                const summary = tableRoot.querySelector('[data-procurement-summary]');
                const pagination = tableRoot.querySelector('[data-procurement-pagination]');
                const empty = tableRoot.querySelector('[data-procurement-empty]');
                const pageSize = 8;
                let currentPage = 1;

                const pageButton = (label, page, options = {}) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'procurement-goods-page';
                    button.textContent = label;
                    button.disabled = options.disabled === true;
                    if (options.active) {
                        button.classList.add('is-active');
                        button.setAttribute('aria-current', 'page');
                    }
                    button.addEventListener('click', () => {
                        currentPage = page;
                        render();
                    });
                    return button;
                };

                const matchingRows = () => {
                    const query = (search?.value || '').trim().toLowerCase();
                    const specificationQuery = (specification?.value || '').trim().toLowerCase();
                    const selectedPlant = plant?.value || 'all';
                    return rows.filter((row) => {
                        const matchesSearch = query === '' || (row.dataset.search || '').includes(query);
                        const matchesSpecification = specificationQuery === '' || (row.dataset.specification || '').includes(specificationQuery);
                        const matchesPlant = selectedPlant === 'all' || row.dataset.plant === selectedPlant;
                        return matchesSearch && matchesSpecification && matchesPlant;
                    });
                };

                const render = () => {
                    const matched = matchingRows();
                    const pageCount = Math.max(1, Math.ceil(matched.length / pageSize));
                    currentPage = Math.min(Math.max(currentPage, 1), pageCount);
                    const start = (currentPage - 1) * pageSize;
                    const end = Math.min(start + pageSize, matched.length);
                    const visible = new Set(matched.slice(start, end));

                    rows.forEach((row) => { row.hidden = !visible.has(row); });
                    if (empty) empty.hidden = matched.length !== 0;
                    if (summary) {
                        summary.textContent = matched.length === 0
                            ? 'Tidak ada data'
                            : `Menampilkan ${start + 1} - ${end} dari ${matched.length} data`;
                    }

                    if (!pagination) return;
                    pagination.replaceChildren();
                    pagination.append(pageButton('‹', currentPage - 1, { disabled: currentPage === 1 }));
                    for (let page = 1; page <= pageCount; page += 1) {
                        pagination.append(pageButton(String(page), page, { active: page === currentPage }));
                    }
                    pagination.append(pageButton('›', currentPage + 1, { disabled: currentPage === pageCount }));
                };

                search?.addEventListener('input', () => { currentPage = 1; render(); });
                specification?.addEventListener('input', () => { currentPage = 1; render(); });
                plant?.addEventListener('change', () => { currentPage = 1; render(); });
                render();
            });
        });
    </script>
@endpush

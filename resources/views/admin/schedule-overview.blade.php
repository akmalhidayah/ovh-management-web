@php
    $unitKerjaBarang = [
        'PEL. MESIN KCM 4/5',
        'OPERASI RCC 4/5',
        'PEM. MESIN FM 4/5',
        'PEM. MESIN RM 4/5',
        'OPERASI FM 4/5',
        'OPERASI KILN 4',
        'PEM. LISTRIK PABRIK 4',
        'PEM. INSTRUMEN 4',
        'OPERASI CRUSHER 5',
        'PEM. MESIN KILN 5',
        'OPERASI COAL MILL 5',
        'PEM. UTILITAS 5',
        'OPERASI PACKING 4',
        'PEM. MESIN CRUSHER 4',
        'OPERASI RAW MILL 5',
        'PEM. LISTRIK PABRIK 5',
        'OPERASI FINISH MILL 5',
        'PEM. INSTRUMEN 5',
        'OPERASI KILN 2/3',
        'PEM. MESIN PABRIK 2',
        'OPERASI CRUSHER 3',
        'PEM. LISTRIK PABRIK 3',
        'OPERASI PACKING 5',
        'PEM. UTILITAS 2/3',
    ];
    $unitKerjaJasa = [
        'OVERHAUL KILN 4/5',
        'INSPEKSI ROTATING 4',
        'REPAIR FINISH MILL 4',
        'ALIGNMENT RAW MILL 4',
        'SERVICE ID FAN 5',
        'NDT KILN SHELL 5',
        'REPAIR CRUSHER 5',
        'KALIBRASI INSTRUMEN 4',
        'HOT SPLICING CONVEYOR 5',
        'SERVICE GEARBOX 4',
        'CLEANING PREHEATER 5',
        'REPAIR COOLER PLATE 4',
        'INSPEKSI STRUKTUR SILO',
        'LOAD TEST CRANE 4',
        'BALANCING FAN 5',
        'SERVICE GENERATOR 4',
        'KALIBRASI PACKER 5',
        'REPAIR DUST COLLECTOR',
        'OVERHAUL KILN 2/3',
        'NDT TRUNNION MILL 2',
        'SERVICE COMPRESSOR 3',
        'REPAIR BUCKET ELEVATOR',
        'INSPEKSI POWER HOUSE',
        'CLEANING DUCTING 2/3',
    ];
    $plants = ['Tonasa 4', 'Tonasa 4', 'Tonasa 5', 'Tonasa 4', 'Tonasa 5', 'Tonasa 5', 'Tonasa 4', 'Tonasa 4', 'Tonasa 5', 'Tonasa 5', 'Tonasa 5', 'Tonasa 4', 'Tonasa 4', 'Tonasa 4', 'Tonasa 5', 'Tonasa 5', 'Tonasa 5', 'Tonasa 4', 'Tonasa 2', 'Tonasa 2', 'Tonasa 3', 'Tonasa 3', 'Tonasa 5', 'Tonasa 3'];

    $makeOverviewRows = function (array $units, int $budgetOffset) use ($plants): array {
        $rows = [];
        $years = [...array_fill(0, 24, 2026), ...array_fill(0, 6, 2025), ...array_fill(0, 6, 2027)];

        foreach ($years as $index => $year) {
            $unitIndex = $index % count($units);
            $pagu = 180000000 + (($unitIndex * 137000000 + $budgetOffset) % 2550000000);
            $isOverBudget = $year === 2026 ? $unitIndex < 16 : $index % 2 === 0;
            $factor = $isOverBudget
                ? 1.04 + (($unitIndex % 5) * 0.035)
                : 0.72 + (($unitIndex % 4) * 0.055);

            $rows[] = [
                'year' => $year,
                'plant' => $plants[$unitIndex],
                'unit' => $units[$unitIndex],
                'pagu' => $pagu,
                'ajuan' => (int) round($pagu * $factor),
            ];
        }

        return $rows;
    };

    $overviewData = [
        'barang' => $makeOverviewRows($unitKerjaBarang, 70000000),
        'jasa' => $makeOverviewRows($unitKerjaJasa, 145000000),
    ];
@endphp

@extends('layouts.dashboard')
@section('title', 'Overview Rencana Kerja')
@section('page_title', 'Overview Rencana Kerja')
@section('breadcrumb_parent', 'Rencana Kerja')

@push('styles')
    <style>
        .work-overview {
            --overview-navy: #344f70;
            --overview-green: #48a56a;
            --overview-green-soft: #e7f6ec;
            --overview-red: #bd3434;
            --overview-red-soft: #fdeaea;
            display: grid;
            gap: 1rem;
            min-width: 0;
        }
        .work-overview-summary {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1.05fr);
            gap: 1rem;
        }
        .work-overview-summary-stack { display: grid; gap: 1rem; }
        .work-overview-summary-card,
        .work-overview-filter,
        .work-overview-section {
            border: 1px solid #d5dce5;
            border-radius: .8rem;
            background: #fff;
            box-shadow: 0 2px 3px rgba(15, 23, 42, .06);
        }
        .work-overview-summary-card {
            display: flex;
            min-height: 82px;
            flex-direction: column;
            justify-content: center;
            padding: 1.1rem 1.35rem;
            border-color: #dde3ea;
            box-shadow: 0 6px 18px rgba(25, 42, 65, .045);
        }
        .work-overview-summary-card.is-primary { min-height: 180px; }
        .work-overview-summary-label {
            margin-bottom: .6rem;
            color: #657186;
            font-size: .8rem;
            font-weight: 600;
        }
        .work-overview-summary-card.is-compact .work-overview-summary-label { font-size: .76rem; }
        .work-overview-summary-value {
            color: #172033;
            font-size: clamp(1.8rem, 3.4vw, 2.75rem);
            font-weight: 800;
            letter-spacing: -.04em;
            line-height: 1;
        }
        .work-overview-summary-card.is-compact .work-overview-summary-value {
            font-size: clamp(1.6rem, 2.6vw, 2.2rem);
        }
        .work-overview-summary-card.is-compact {
            padding: .9rem 1.2rem;
        }
        .work-overview-filter {
            display: grid;
            grid-template-columns: minmax(120px, 160px) minmax(150px, 190px) minmax(220px, 1fr);
            gap: 1.1rem;
            padding: 1rem 1.25rem;
            box-shadow: none;
        }
        .work-overview-field { min-width: 0; }
        .work-overview-field label {
            display: block;
            margin-bottom: .35rem;
            color: #26364c;
            font-size: .72rem;
            font-weight: 800;
        }
        .work-overview-field .form-select,
        .work-overview-field .form-control {
            min-height: 40px;
            border-color: #d8e0ea;
            border-radius: .5rem;
            color: #34445b;
            font-size: .8rem;
            box-shadow: none;
        }
        .work-overview-search { position: relative; }
        .work-overview-search i {
            position: absolute;
            top: 50%;
            left: .8rem;
            color: #94a3b8;
            transform: translateY(-50%);
            pointer-events: none;
        }
        .work-overview-search .form-control { padding-left: 2.25rem; }
        .work-overview-section { min-width: 0; overflow: hidden; }
        .work-overview-section-title {
            margin: 0;
            padding: 1rem 1.2rem .8rem;
            color: #303030;
            font-size: .95rem;
            font-weight: 800;
        }
        .work-overview-section-grid {
            display: grid;
            grid-template-columns: minmax(0, 3fr) minmax(220px, .85fr);
            min-width: 0;
        }
        .work-overview-table-panel { min-width: 0; padding: 0 1.2rem 1rem; }
        .work-overview-table-scroll {
            max-width: 100%;
            overflow-x: auto;
            border-radius: .2rem;
            scrollbar-width: thin;
        }
        .work-overview-table {
            min-width: 820px;
            margin: 0;
            border-collapse: separate;
            border-spacing: 0;
        }
        .work-overview-table thead th {
            padding: .7rem .65rem;
            border: 0;
            background: var(--overview-navy);
            color: #fff;
            font-size: .65rem;
            font-weight: 800;
            letter-spacing: .015em;
            text-align: center;
            text-transform: uppercase;
            vertical-align: middle;
            white-space: nowrap;
        }
        .work-overview-table thead th:nth-child(2) { text-align: left; }
        .work-overview-table tbody td {
            padding: .7rem .65rem;
            border: 0;
            border-bottom: 1px solid #edf1f5;
            color: #29364a;
            font-size: .72rem;
            vertical-align: middle;
        }
        .work-overview-table tbody tr:nth-child(even) td { background: #fafbfd; }
        .work-overview-table tbody tr:hover td { background: #f2f6fa; }
        .work-overview-table td:first-child { width: 48px; text-align: center; }
        .work-overview-table td:nth-child(2) { min-width: 185px; font-weight: 700; }
        .work-overview-table td:nth-child(3) { min-width: 92px; text-align: center; }
        .work-overview-table td:nth-child(4),
        .work-overview-table td:nth-child(5) { min-width: 135px; text-align: right; white-space: nowrap; }
        .work-overview-table td:last-child { min-width: 165px; text-align: center; }
        .work-overview-budget-status {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .3rem;
            min-width: 132px;
            padding: .35rem .55rem;
            border-radius: 999px;
            font-size: .62rem;
            font-weight: 800;
            white-space: nowrap;
        }
        .work-overview-budget-status.is-safe { background: var(--overview-green-soft); color: #27834b; }
        .work-overview-budget-status.is-over { background: var(--overview-red-soft); color: #ae2e2e; }
        .work-overview-budget-status i { font-size: .72rem; }
        .work-overview-empty td {
            padding: 2.4rem 1rem !important;
            color: #7a8798 !important;
            text-align: center !important;
        }
        .work-overview-empty i { display: block; margin-bottom: .35rem; font-size: 1.35rem; }
        .work-overview-table-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            min-height: 50px;
            padding-top: .85rem;
            color: #66758a;
            font-size: .68rem;
        }
        .work-overview-table-controls,
        .work-overview-page-size,
        .work-overview-pagination {
            display: flex;
            align-items: center;
            gap: .45rem;
        }
        .work-overview-page-size select {
            width: 58px;
            min-height: 30px;
            border-color: #d6deea;
            border-radius: .35rem;
            font-size: .7rem;
        }
        .work-overview-pagination { flex-wrap: wrap; }
        .work-overview-page-button {
            min-width: 30px;
            height: 30px;
            border: 1px solid #d6deea;
            border-radius: .35rem;
            padding: 0 .55rem;
            background: #fff;
            color: #58677a;
            font-size: .68rem;
            font-weight: 700;
        }
        .work-overview-page-button:hover:not(:disabled) { border-color: var(--overview-navy); color: var(--overview-navy); }
        .work-overview-page-button.is-active { border-color: var(--overview-navy); background: var(--overview-navy); color: #fff; }
        .work-overview-page-button:disabled { cursor: not-allowed; opacity: .4; }
        .work-overview-chart-panel {
            display: flex;
            min-width: 0;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            padding: 1rem 1.2rem 1.4rem;
            border-left: 1px solid #edf1f5;
        }
        .work-overview-donut {
            --over-percent: 66.666%;
            position: relative;
            width: clamp(150px, 14vw, 190px);
            aspect-ratio: 1;
            border-radius: 50%;
            background: conic-gradient(var(--overview-red) 0 var(--over-percent), var(--overview-green) var(--over-percent) 100%);
            box-shadow: inset 0 0 0 1px rgba(15, 23, 42, .04);
        }
        .work-overview-donut::after {
            position: absolute;
            inset: 23%;
            border-radius: 50%;
            background: #fff;
            content: '';
        }
        .work-overview-donut.is-empty { background: #e4e9ef; }
        .work-overview-donut-center {
            position: absolute;
            inset: 25%;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #273449;
            text-align: center;
        }
        .work-overview-donut-center strong { font-size: 1.85rem; line-height: 1; }
        .work-overview-donut-center span { margin-top: .25rem; font-size: .7rem; }
        .work-overview-legend {
            display: grid;
            width: 100%;
            grid-template-columns: 1fr 1fr;
            gap: .7rem;
        }
        .work-overview-legend-item {
            display: grid;
            grid-template-columns: 10px minmax(0, 1fr) auto;
            align-items: center;
            gap: .4rem;
            color: #3f4b5c;
            font-size: .7rem;
            line-height: 1.15;
        }
        .work-overview-legend-dot { width: 10px; height: 10px; border-radius: 50%; }
        .work-overview-legend-dot.is-over { background: var(--overview-red); }
        .work-overview-legend-dot.is-safe { background: var(--overview-green); }
        .work-overview-legend-item strong { font-size: .82rem; }

        @media (max-width: 991.98px) {
            .work-overview-section-grid { grid-template-columns: 1fr; }
            .work-overview-chart-panel { border-top: 1px solid #edf1f5; border-left: 0; }
            .work-overview-donut { width: 180px; }
            .work-overview-legend { width: min(360px, 100%); }
        }
        @media (max-width: 767.98px) {
            .work-overview-summary { grid-template-columns: 1fr; }
            .work-overview-summary-card.is-primary { min-height: 130px; }
            .work-overview-filter { grid-template-columns: 1fr; gap: .8rem; }
            .work-overview-table-footer { align-items: flex-start; flex-direction: column; }
            .work-overview-table-controls { width: 100%; align-items: flex-start; flex-direction: column; }
            .work-overview-pagination { width: 100%; }
        }
        @media (max-width: 575.98px) {
            .work-overview-summary-value { font-size: 2rem; }
            .work-overview-section-title { padding-inline: .9rem; }
            .work-overview-table-panel { padding-inline: .9rem; }
            .work-overview-chart-panel { padding-inline: .9rem; }
            .work-overview-page-button { min-width: 28px; padding-inline: .42rem; }
        }
    </style>
@endpush

@section('content')
    <div class="work-overview" data-work-overview>
        <section class="work-overview-summary" aria-label="Ringkasan anggaran rencana kerja">
            <article class="work-overview-summary-card is-primary">
                <span class="work-overview-summary-label">Total Pagu Anggaran</span>
                <strong class="work-overview-summary-value">Rp 12,8 M</strong>
            </article>
            <div class="work-overview-summary-stack">
                <article class="work-overview-summary-card is-compact">
                    <span class="work-overview-summary-label">Total Ajuan</span>
                    <strong class="work-overview-summary-value">Rp 5 M</strong>
                </article>
                <article class="work-overview-summary-card is-compact">
                    <span class="work-overview-summary-label">Perbandingan Pagu dan Ajuan</span>
                    <strong class="work-overview-summary-value">56%</strong>
                </article>
            </div>
        </section>

        <section class="work-overview-filter" aria-label="Filter overview rencana kerja">
            <div class="work-overview-field">
                <label for="workOverviewYear">Tahun</label>
                <select class="form-select" id="workOverviewYear" data-overview-year>
                    <option value="2025">2025</option>
                    <option value="2026" selected>2026</option>
                    <option value="2027">2027</option>
                </select>
            </div>
            <div class="work-overview-field">
                <label for="workOverviewPlant">Plant</label>
                <select class="form-select" id="workOverviewPlant" data-overview-plant>
                    <option value="">Semua</option>
                    <option value="Tonasa 2">Tonasa 2</option>
                    <option value="Tonasa 3">Tonasa 3</option>
                    <option value="Tonasa 4">Tonasa 4</option>
                    <option value="Tonasa 5">Tonasa 5</option>
                </select>
            </div>
            <div class="work-overview-field">
                <label for="workOverviewSearch">Cari Unit Kerja</label>
                <div class="work-overview-search">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <input type="search" class="form-control" id="workOverviewSearch" placeholder="Search Unit Kerja..." autocomplete="off" data-overview-search>
                </div>
            </div>
        </section>

        @foreach (['barang' => 'Barang', 'jasa' => 'Jasa'] as $type => $label)
            <section class="work-overview-section" data-overview-section="{{ $type }}">
                <h2 class="work-overview-section-title">Rencana Kerja {{ $label }}</h2>
                <div class="work-overview-section-grid">
                    <div class="work-overview-table-panel">
                        <div class="work-overview-table-scroll">
                            <table class="table work-overview-table">
                                <thead>
                                    <tr>
                                        <th scope="col">No</th>
                                        <th scope="col">Unit Kerja</th>
                                        <th scope="col">Plant</th>
                                        <th scope="col">Pagu Anggaran</th>
                                        <th scope="col">Ajuan Anggaran</th>
                                        <th scope="col">Status Anggaran</th>
                                    </tr>
                                </thead>
                                <tbody data-overview-table-body></tbody>
                            </table>
                        </div>
                        <div class="work-overview-table-footer">
                            <span data-overview-table-summary aria-live="polite"></span>
                            <div class="work-overview-table-controls">
                                <label class="work-overview-page-size">
                                    <select class="form-select" data-overview-page-size aria-label="Jumlah data Rencana Kerja {{ $label }} per halaman">
                                        <option value="5" selected>5</option>
                                        <option value="10">10</option>
                                        <option value="15">15</option>
                                    </select>
                                    <span>data per halaman</span>
                                </label>
                                <nav class="work-overview-pagination" data-overview-pagination aria-label="Pagination Rencana Kerja {{ $label }}"></nav>
                            </div>
                        </div>
                    </div>
                    <aside class="work-overview-chart-panel" aria-label="Ringkasan status anggaran Rencana Kerja {{ $label }}">
                        <div class="work-overview-donut" data-overview-donut role="img">
                            <div class="work-overview-donut-center">
                                <strong data-overview-total>0</strong>
                                <span>Total Seksi</span>
                            </div>
                        </div>
                        <div class="work-overview-legend">
                            <div class="work-overview-legend-item">
                                <span class="work-overview-legend-dot is-over"></span>
                                <span>Melebihi<br>Pagu</span>
                                <strong data-overview-over-count>0</strong>
                            </div>
                            <div class="work-overview-legend-item">
                                <span class="work-overview-legend-dot is-safe"></span>
                                <span>Sesuai<br>Pagu</span>
                                <strong data-overview-safe-count>0</strong>
                            </div>
                        </div>
                    </aside>
                </div>
            </section>
        @endforeach
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const root = document.querySelector('[data-work-overview]');
            if (!root) return;

            const datasets = @json($overviewData);
            const filters = {
                year: root.querySelector('[data-overview-year]'),
                plant: root.querySelector('[data-overview-plant]'),
                search: root.querySelector('[data-overview-search]'),
            };
            const tableStates = {
                barang: {page: 1, perPage: 5},
                jasa: {page: 1, perPage: 5},
            };
            const rupiah = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0,
            });

            const escapeHtml = (value) => String(value)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');

            const filteredRows = (type) => {
                const year = Number(filters.year?.value || 2026);
                const plant = filters.plant?.value || '';
                const query = (filters.search?.value || '').trim().toLocaleLowerCase('id-ID');

                return (datasets[type] || []).filter((row) => (
                    Number(row.year) === year
                    && (!plant || row.plant === plant)
                    && (!query || row.unit.toLocaleLowerCase('id-ID').includes(query))
                ));
            };

            const pageButton = (label, page, currentPage, disabled = false) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'work-overview-page-button';
                button.textContent = label;
                button.dataset.page = String(page);
                button.disabled = disabled;

                if (page === currentPage && /^\d+$/.test(label)) {
                    button.classList.add('is-active');
                    button.setAttribute('aria-current', 'page');
                }

                return button;
            };

            const renderSection = (section) => {
                const type = section.dataset.overviewSection;
                const state = tableStates[type];
                const rows = filteredRows(type);
                const totalPages = Math.max(1, Math.ceil(rows.length / state.perPage));
                state.page = Math.min(state.page, totalPages);
                const start = (state.page - 1) * state.perPage;
                const visibleRows = rows.slice(start, start + state.perPage);
                const body = section.querySelector('[data-overview-table-body]');

                if (visibleRows.length === 0) {
                    body.innerHTML = `
                        <tr class="work-overview-empty">
                            <td colspan="6"><i class="bi bi-inbox"></i>Data rencana kerja tidak ditemukan.</td>
                        </tr>`;
                } else {
                    body.innerHTML = visibleRows.map((row, index) => {
                        const ratio = row.pagu > 0 ? (row.ajuan / row.pagu) * 100 : 0;
                        const percentage = Math.round(ratio * 10) / 10;
                        const isOver = row.ajuan > row.pagu;
                        const statusClass = isOver ? 'is-over' : 'is-safe';
                        const icon = isOver ? 'bi-arrow-up-circle-fill' : 'bi-check-circle-fill';
                        const status = isOver ? 'Di atas Pagu' : 'Sesuai Pagu';

                        return `
                            <tr>
                                <td>${start + index + 1}</td>
                                <td>${escapeHtml(row.unit)}</td>
                                <td>${escapeHtml(row.plant)}</td>
                                <td>${escapeHtml(rupiah.format(row.pagu))}</td>
                                <td>${escapeHtml(rupiah.format(row.ajuan))}</td>
                                <td>
                                    <span class="work-overview-budget-status ${statusClass}">
                                        <i class="bi ${icon}" aria-hidden="true"></i>
                                        ${status} ${percentage}%
                                    </span>
                                </td>
                            </tr>`;
                    }).join('');
                }

                const end = Math.min(start + state.perPage, rows.length);
                section.querySelector('[data-overview-table-summary]').textContent = rows.length === 0
                    ? 'Menampilkan 0 data'
                    : `Menampilkan ${start + 1} - ${end} dari ${rows.length} data`;

                const pagination = section.querySelector('[data-overview-pagination]');
                pagination.replaceChildren();
                pagination.appendChild(pageButton('Previous', Math.max(1, state.page - 1), state.page, state.page === 1 || rows.length === 0));
                for (let page = 1; page <= totalPages; page += 1) {
                    pagination.appendChild(pageButton(String(page), page, state.page, rows.length === 0));
                }
                pagination.appendChild(pageButton('Next', Math.min(totalPages, state.page + 1), state.page, state.page === totalPages || rows.length === 0));

                const overCount = rows.filter((row) => row.ajuan > row.pagu).length;
                const safeCount = rows.length - overCount;
                const overPercent = rows.length > 0 ? (overCount / rows.length) * 100 : 0;
                const donut = section.querySelector('[data-overview-donut]');
                donut.style.setProperty('--over-percent', `${overPercent}%`);
                donut.classList.toggle('is-empty', rows.length === 0);
                donut.setAttribute('aria-label', `${rows.length} total seksi, ${overCount} melebihi pagu, ${safeCount} sesuai pagu`);
                section.querySelector('[data-overview-total]').textContent = String(rows.length);
                section.querySelector('[data-overview-over-count]').textContent = String(overCount);
                section.querySelector('[data-overview-safe-count]').textContent = String(safeCount);
            };

            const sections = Array.from(root.querySelectorAll('[data-overview-section]'));
            const renderAll = (resetPages = false) => {
                if (resetPages) {
                    Object.values(tableStates).forEach((state) => { state.page = 1; });
                }
                sections.forEach(renderSection);
            };

            [filters.year, filters.plant].forEach((filter) => filter?.addEventListener('change', () => renderAll(true)));
            filters.search?.addEventListener('input', () => renderAll(true));

            sections.forEach((section) => {
                const type = section.dataset.overviewSection;
                section.querySelector('[data-overview-page-size]')?.addEventListener('change', (event) => {
                    tableStates[type].perPage = Math.max(1, Number(event.target.value) || 5);
                    tableStates[type].page = 1;
                    renderSection(section);
                });
                section.querySelector('[data-overview-pagination]')?.addEventListener('click', (event) => {
                    const button = event.target.closest('[data-page]');
                    if (!button || button.disabled) return;
                    tableStates[type].page = Number(button.dataset.page) || 1;
                    renderSection(section);
                });
            });

            renderAll();
        })();
    </script>
@endpush

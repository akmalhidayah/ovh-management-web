@php
    $scheduleBarangRows = [
        ['year' => 2026, 'plant' => 'Plant 5', 'unit' => 'Kiln Utama (BTA 86 Meter)', 'pic' => 'Auliya Sahara', 'qty' => '1 Set', 'category' => 'OEM', 'bom' => true, 'note' => '-'],
        ['year' => 2026, 'plant' => 'Plant 4', 'unit' => 'Raw Mill 4 (Tyre Kiln Pondasi)', 'pic' => 'Budi Santoso', 'qty' => '2 Pcs', 'category' => 'FABRIKASI', 'bom' => true, 'note' => '-'],
        ['year' => 2026, 'plant' => 'Plant 3', 'unit' => 'Finish Mill 3 (Wear Segment Roller)', 'pic' => 'Siti Aminah', 'qty' => '4 Set', 'category' => 'CASTING', 'bom' => true, 'note' => '-'],
        ['year' => 2026, 'plant' => 'Plant 2', 'unit' => 'Coal Mill (Liner Diaphragma)', 'pic' => 'Auliya Sahara', 'qty' => '1 Unit', 'category' => 'OEM', 'bom' => true, 'note' => '-'],
        ['year' => 2026, 'plant' => 'Plant 5', 'unit' => 'Crusher Plant (Pinion Gear Main)', 'pic' => 'Rahmat Hidayat', 'qty' => '1 Set', 'category' => 'FABRIKASI', 'bom' => true, 'note' => '-'],
        ['year' => 2026, 'plant' => 'Plant 4', 'unit' => 'Finish Mill 4 (Grinding Roller)', 'pic' => 'Nurul Fadilah', 'qty' => '3 Pcs', 'category' => 'CASTING', 'bom' => true, 'note' => 'Drawing disetujui'],
        ['year' => 2026, 'plant' => 'Plant 2', 'unit' => 'Packer 2 (Rotary Feeder)', 'pic' => 'Andi Pratama', 'qty' => '2 Unit', 'category' => 'OEM', 'bom' => true, 'note' => '-'],
        ['year' => 2026, 'plant' => 'Plant 3', 'unit' => 'Raw Mill 3 (Damper Assembly)', 'pic' => 'Dewi Lestari', 'qty' => '5 Pcs', 'category' => 'FABRIKASI', 'bom' => true, 'note' => '-'],
        ['year' => 2026, 'plant' => 'Plant 5', 'unit' => 'Coal Mill 5 (Grinding Table)', 'pic' => 'Rahmat Hidayat', 'qty' => '1 Set', 'category' => 'CASTING', 'bom' => true, 'note' => '-'],
        ['year' => 2026, 'plant' => 'Plant 4', 'unit' => 'Kiln 4 (Support Roller)', 'pic' => 'Budi Santoso', 'qty' => '2 Set', 'category' => 'OEM', 'bom' => true, 'note' => '-'],
        ['year' => 2025, 'plant' => 'Plant 2', 'unit' => 'Crusher 2 (Drive Shaft)', 'pic' => 'Andi Pratama', 'qty' => '1 Set', 'category' => 'FABRIKASI', 'bom' => true, 'note' => '-'],
        ['year' => 2027, 'plant' => 'Plant 5', 'unit' => 'Raw Mill 5 (Separator Blade)', 'pic' => 'Nurul Fadilah', 'qty' => '8 Pcs', 'category' => 'CASTING', 'bom' => true, 'note' => '-'],
    ];
@endphp

<div class="schedule-barang-index" data-schedule-barang-index>
    <section class="schedule-barang-summary" aria-label="Ringkasan anggaran rencana kerja barang">
        <article class="schedule-barang-summary-card schedule-barang-summary-card--primary">
            <p>Total Pagu Anggaran</p>
            <strong>Rp 12,8 M</strong>
        </article>

        <div class="schedule-barang-summary-side">
            <article class="schedule-barang-summary-card">
                <p>Total Ajuan</p>
                <strong>Rp 5 M</strong>
            </article>
            <article class="schedule-barang-summary-card">
                <p>Perbandingan Pagu dan Ajuan</p>
                <strong>56%</strong>
            </article>
        </div>
    </section>

    <section class="schedule-barang-filter" aria-label="Filter rencana kerja barang">
        <div class="schedule-barang-field">
            <label for="schedule-barang-year">Tahun</label>
            <select id="schedule-barang-year" data-barang-year>
                <option value="2025">2025</option>
                <option value="2026" selected>2026</option>
                <option value="2027">2027</option>
            </select>
        </div>

        <div class="schedule-barang-field">
            <label for="schedule-barang-plant">Plant</label>
            <select id="schedule-barang-plant" data-barang-plant>
                <option value="">Semua</option>
                <option value="Plant 2">Plant 2</option>
                <option value="Plant 3">Plant 3</option>
                <option value="Plant 4">Plant 4</option>
                <option value="Plant 5">Plant 5</option>
            </select>
        </div>

        <div class="schedule-barang-field schedule-barang-field--search">
            <label for="schedule-barang-search">Cari Unit Kerja/PIC</label>
            <div class="schedule-barang-search-control">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input id="schedule-barang-search" type="search" placeholder="Search Unit Kerja/PIC..." data-barang-search>
            </div>
        </div>

        <button class="schedule-barang-update" type="button" data-barang-apply-filter>
            <i class="bi bi-plus-lg" aria-hidden="true"></i>
            <span>Update Data</span>
        </button>
    </section>

    <section class="schedule-barang-table-card" aria-label="Daftar rencana kerja barang">
        <div class="schedule-barang-table-scroll">
            <table class="schedule-barang-table">
                <thead>
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Plant</th>
                        <th scope="col">Unit Kerja</th>
                        <th scope="col">PIC ST</th>
                        <th scope="col">Qty yang Disetujui<br>Tim OVH</th>
                        <th scope="col">Kategori (OEM;<br>Fabrikasi; Casting)</th>
                        <th scope="col">Status BOM</th>
                        <th scope="col">Ket</th>
                    </tr>
                </thead>
                <tbody data-barang-table-body></tbody>
            </table>
        </div>

        <div class="schedule-barang-empty" data-barang-empty hidden>
            <i class="bi bi-search" aria-hidden="true"></i>
            <strong>Data tidak ditemukan</strong>
            <span>Coba ubah tahun, plant, atau kata kunci pencarian.</span>
        </div>

        <div class="schedule-barang-table-footer">
            <span data-barang-table-summary aria-live="polite"></span>
            <button class="schedule-barang-detail" type="button" data-barang-detail-toggle>
                Lihat Detail
                <i class="bi bi-chevron-right" aria-hidden="true"></i>
            </button>
        </div>
    </section>
</div>

@push('styles')
    <style>
        .schedule-barang-index {
            --barang-navy: #334f70;
            --barang-border: #d8e0e9;
            --barang-muted: #64748b;
            display: grid;
            gap: 18px;
            min-width: 0;
        }

        .schedule-barang-summary {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(340px, 1.04fr);
            gap: 18px;
        }

        .schedule-barang-summary-side {
            display: grid;
            gap: 12px;
        }

        .schedule-barang-summary-card,
        .schedule-barang-filter,
        .schedule-barang-table-card {
            background: #fff;
            border: 1px solid var(--barang-border);
            border-radius: 12px;
            box-shadow: 0 2px 3px rgba(15, 23, 42, .13);
        }

        .schedule-barang-summary-card {
            min-height: 92px;
            padding: 18px 24px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .schedule-barang-summary-card--primary {
            min-height: 196px;
        }

        .schedule-barang-summary-card p {
            margin: 0 0 8px;
            color: #595959;
            font-size: 14px;
            font-weight: 600;
        }

        .schedule-barang-summary-card strong {
            color: #080808;
            font-size: clamp(30px, 3vw, 44px);
            font-weight: 800;
            letter-spacing: -.04em;
            line-height: 1;
        }

        .schedule-barang-summary-side .schedule-barang-summary-card strong {
            font-size: clamp(27px, 2.4vw, 36px);
        }

        .schedule-barang-summary-side .schedule-barang-summary-card:last-child p {
            font-size: 11px;
        }

        .schedule-barang-filter {
            display: grid;
            grid-template-columns: 130px 160px minmax(220px, 1fr) auto;
            gap: 24px;
            align-items: end;
            padding: 17px 28px;
        }

        .schedule-barang-field {
            display: grid;
            gap: 7px;
            min-width: 0;
        }

        .schedule-barang-field label {
            color: #111827;
            font-size: 12px;
            font-weight: 700;
        }

        .schedule-barang-field select,
        .schedule-barang-search-control {
            width: 100%;
            min-height: 40px;
            background: #f7f9fc;
            border: 1px solid #e5eaf0;
            border-radius: 6px;
            color: #26364d;
            font: inherit;
            font-size: 13px;
            outline: none;
        }

        .schedule-barang-field select {
            padding: 0 34px 0 12px;
        }

        .schedule-barang-search-control {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0 12px;
        }

        .schedule-barang-search-control i {
            color: #9aa7b7;
        }

        .schedule-barang-search-control input {
            min-width: 0;
            width: 100%;
            padding: 0;
            background: transparent;
            border: 0;
            color: #26364d;
            font: inherit;
            font-size: 13px;
            outline: none;
        }

        .schedule-barang-search-control input::placeholder {
            color: #aab4c0;
        }

        .schedule-barang-field select:focus,
        .schedule-barang-search-control:focus-within {
            border-color: #315f99;
            box-shadow: 0 0 0 3px rgba(49, 95, 153, .12);
        }

        .schedule-barang-update {
            min-height: 42px;
            padding: 0 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border: 0;
            border-radius: 7px;
            background: #254c7f;
            color: #fff;
            font: inherit;
            font-size: 14px;
            font-weight: 600;
            white-space: nowrap;
            transition: background-color .18s ease, transform .18s ease;
        }

        .schedule-barang-update:hover {
            background: #183a67;
        }

        .schedule-barang-update:active {
            transform: translateY(1px);
        }

        .schedule-barang-update:focus-visible,
        .schedule-barang-detail:focus-visible {
            outline: 3px solid rgba(37, 76, 127, .25);
            outline-offset: 2px;
        }

        .schedule-barang-table-card {
            overflow: hidden;
        }

        .schedule-barang-table-scroll {
            width: 100%;
            overflow-x: auto;
        }

        .schedule-barang-table {
            width: 100%;
            min-width: 1080px;
            table-layout: fixed;
            border-collapse: collapse;
            color: #26364d;
            font-size: 13px;
        }

        .schedule-barang-table th {
            padding: 13px 10px;
            background: var(--barang-navy);
            border-right: 1px solid rgba(255, 255, 255, .13);
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .025em;
            line-height: 1.25;
            text-align: center;
            text-transform: uppercase;
            vertical-align: middle;
        }

        .schedule-barang-table th:nth-child(1) { width: 5%; }
        .schedule-barang-table th:nth-child(2) { width: 9%; }
        .schedule-barang-table th:nth-child(3) { width: 26%; text-align: left; }
        .schedule-barang-table th:nth-child(4) { width: 15%; text-align: left; }
        .schedule-barang-table th:nth-child(5) { width: 15%; }
        .schedule-barang-table th:nth-child(6) { width: 15%; }
        .schedule-barang-table th:nth-child(7) { width: 10%; }
        .schedule-barang-table th:nth-child(8) { width: 5%; }

        .schedule-barang-table td {
            height: 58px;
            padding: 12px 12px;
            background: #fff;
            border-right: 1px solid #e3e8ee;
            border-bottom: 1px solid #e3e8ee;
            vertical-align: middle;
        }

        .schedule-barang-table tbody tr:nth-child(even) td {
            background: #f8fafc;
        }

        .schedule-barang-table td:nth-child(1),
        .schedule-barang-table td:nth-child(2),
        .schedule-barang-table td:nth-child(5),
        .schedule-barang-table td:nth-child(6),
        .schedule-barang-table td:nth-child(7),
        .schedule-barang-table td:nth-child(8) {
            text-align: center;
        }

        .schedule-barang-table td:nth-child(2),
        .schedule-barang-table td:nth-child(3),
        .schedule-barang-table td:nth-child(5) {
            font-weight: 600;
        }

        .schedule-barang-category {
            min-width: 78px;
            padding: 5px 9px;
            display: inline-flex;
            justify-content: center;
            border-radius: 5px;
            font-size: 10px;
            font-weight: 700;
            line-height: 1;
        }

        .schedule-barang-category--oem {
            background: #e7f0ff;
            border: 1px solid #c7dcff;
            color: #245aa7;
        }

        .schedule-barang-category--fabrikasi {
            background: #fff1cf;
            border: 1px solid #f5d477;
            color: #965500;
        }

        .schedule-barang-category--casting {
            background: #f0e7ff;
            border: 1px solid #d9c4ff;
            color: #7142b1;
        }

        .schedule-barang-bom {
            color: #2f9b65;
            font-size: 18px;
            font-weight: 700;
        }

        .schedule-barang-note {
            color: var(--barang-muted);
        }

        .schedule-barang-empty {
            min-height: 210px;
            padding: 36px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 7px;
            color: var(--barang-muted);
            text-align: center;
        }

        .schedule-barang-empty[hidden] {
            display: none;
        }

        .schedule-barang-empty i {
            margin-bottom: 5px;
            color: #94a3b8;
            font-size: 28px;
        }

        .schedule-barang-empty strong {
            color: #334155;
        }

        .schedule-barang-table-footer {
            min-height: 52px;
            padding: 12px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            color: var(--barang-muted);
            font-size: 12px;
        }

        .schedule-barang-detail {
            padding: 4px 0;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border: 0;
            background: transparent;
            color: #e23d3d;
            font: inherit;
            font-size: 12px;
            font-weight: 700;
        }

        .schedule-barang-detail:hover {
            color: #b91c1c;
            text-decoration: underline;
        }

        .schedule-barang-detail[hidden] {
            display: none;
        }

        .schedule-barang-detail.is-expanded i {
            transform: rotate(90deg);
        }

        @media (max-width: 991.98px) {
            .schedule-barang-summary {
                grid-template-columns: minmax(0, 1fr) minmax(300px, .9fr);
            }

            .schedule-barang-filter {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .schedule-barang-field--search {
                grid-column: 1 / 2;
            }

            .schedule-barang-update {
                width: 100%;
            }
        }

        @media (max-width: 767.98px) {
            .schedule-barang-index {
                gap: 14px;
            }

            .schedule-barang-summary {
                grid-template-columns: minmax(0, 1fr);
                gap: 12px;
            }

            .schedule-barang-summary-card--primary {
                min-height: 132px;
            }

            .schedule-barang-summary-card {
                padding: 18px;
            }

            .schedule-barang-filter {
                grid-template-columns: minmax(0, 1fr);
                gap: 14px;
                padding: 18px;
            }

            .schedule-barang-field--search {
                grid-column: auto;
            }

            .schedule-barang-table-footer {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        (() => {
            const root = document.querySelector('[data-schedule-barang-index]');
            if (!root) return;

            const allRows = @json($scheduleBarangRows);
            const initialLimit = 5;
            const yearInput = root.querySelector('[data-barang-year]');
            const plantInput = root.querySelector('[data-barang-plant]');
            const searchInput = root.querySelector('[data-barang-search]');
            const applyButton = root.querySelector('[data-barang-apply-filter]');
            const tableBody = root.querySelector('[data-barang-table-body]');
            const tableScroll = root.querySelector('.schedule-barang-table-scroll');
            const emptyState = root.querySelector('[data-barang-empty]');
            const summary = root.querySelector('[data-barang-table-summary]');
            const detailButton = root.querySelector('[data-barang-detail-toggle]');
            let filteredRows = [];
            let expanded = false;

            const escapeHtml = (value) => String(value)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');

            const categoryClass = (category) => {
                const normalized = String(category).toLowerCase();
                return ['oem', 'fabrikasi', 'casting'].includes(normalized) ? normalized : 'oem';
            };

            const render = () => {
                const visibleRows = expanded ? filteredRows : filteredRows.slice(0, initialLimit);
                tableBody.innerHTML = visibleRows.map((row, index) => `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${escapeHtml(row.plant)}</td>
                        <td>${escapeHtml(row.unit)}</td>
                        <td>${escapeHtml(row.pic)}</td>
                        <td>${escapeHtml(row.qty)}</td>
                        <td>
                            <span class="schedule-barang-category schedule-barang-category--${categoryClass(row.category)}">
                                ${escapeHtml(row.category)}
                            </span>
                        </td>
                        <td>
                            <span class="schedule-barang-bom" title="BOM tersedia" aria-label="BOM tersedia">
                                <i class="bi bi-check-lg" aria-hidden="true"></i>
                            </span>
                        </td>
                        <td class="schedule-barang-note">${escapeHtml(row.note || '-')}</td>
                    </tr>
                `).join('');

                const hasRows = filteredRows.length > 0;
                tableScroll.hidden = !hasRows;
                emptyState.hidden = hasRows;
                summary.textContent = hasRows
                    ? `Menampilkan ${visibleRows.length} dari ${filteredRows.length} data`
                    : '0 data ditampilkan';

                detailButton.hidden = filteredRows.length <= initialLimit;
                detailButton.classList.toggle('is-expanded', expanded);
                detailButton.firstChild.textContent = expanded ? 'Tutup Detail ' : 'Lihat Detail ';
                detailButton.setAttribute('aria-expanded', String(expanded));
            };

            const applyFilters = () => {
                const selectedYear = Number(yearInput.value);
                const selectedPlant = plantInput.value;
                const keyword = searchInput.value.trim().toLocaleLowerCase('id-ID');

                filteredRows = allRows.filter((row) => {
                    const matchesYear = row.year === selectedYear;
                    const matchesPlant = !selectedPlant || row.plant === selectedPlant;
                    const searchableText = `${row.unit} ${row.pic}`.toLocaleLowerCase('id-ID');
                    return matchesYear && matchesPlant && (!keyword || searchableText.includes(keyword));
                });

                expanded = false;
                render();
            };

            applyButton.addEventListener('click', applyFilters);
            searchInput.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter') return;
                event.preventDefault();
                applyFilters();
            });
            detailButton.addEventListener('click', () => {
                expanded = !expanded;
                render();
            });

            applyFilters();
        })();
    </script>
@endpush

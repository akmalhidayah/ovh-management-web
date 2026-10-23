<div class="schedule-barang-budget" data-schedule-barang-budget>
    <section class="schedule-barang-budget-filter" aria-label="Filter rencana kerja barang">
        <div class="schedule-barang-budget-field">
            <label for="schedule-barang-budget-year">Tahun</label>
            <select id="schedule-barang-budget-year">
                <option value="2025">2025</option>
                <option value="2026" selected>2026</option>
                <option value="2027">2027</option>
            </select>
        </div>

        <div class="schedule-barang-budget-field">
            <label for="schedule-barang-budget-plant">Plant</label>
            <select id="schedule-barang-budget-plant">
                <option value="Tonasa 2">Tonasa 2</option>
                <option value="Tonasa 3">Tonasa 3</option>
                <option value="Tonasa 4">Tonasa 4</option>
                <option value="Tonasa 5" selected>Tonasa 5</option>
            </select>
        </div>

        <div class="schedule-barang-budget-field schedule-barang-budget-field--search">
            <label for="schedule-barang-budget-search">Filter</label>
            <div class="schedule-barang-budget-search">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input id="schedule-barang-budget-search" type="search" placeholder="Cari unit kerja/status ajuan/pekerjaan...">
            </div>
        </div>

        <button type="button" class="schedule-barang-budget-add">
            <i class="bi bi-plus-lg" aria-hidden="true"></i>
            <span>Tambah Ajuan</span>
        </button>
    </section>

    <section class="schedule-barang-budget-overview" aria-labelledby="schedule-barang-budget-title">
        <h2 id="schedule-barang-budget-title">Pagu &amp; Ajuan Anggaran</h2>

        <div class="schedule-barang-budget-grid">
            <div class="schedule-barang-budget-column">
                <article class="schedule-barang-budget-card schedule-barang-budget-card--large">
                    <span class="schedule-barang-budget-icon" aria-hidden="true"><i class="bi bi-bar-chart-line-fill"></i></span>
                    <div><p>Total Pagu</p><strong>Rp 12,8 M</strong></div>
                </article>

                <article class="schedule-barang-budget-card">
                    <span class="schedule-barang-budget-icon" aria-hidden="true"><i class="bi bi-bar-chart-line-fill"></i></span>
                    <div><p>Total Ajuan Anggaran</p><strong>Rp 5 M</strong></div>
                </article>
            </div>

            <div class="schedule-barang-budget-column">
                <article class="schedule-barang-budget-ratio">
                    <strong>96<span>%</span></strong>
                    <div class="schedule-barang-budget-ratio-label"><p>Pagu</p><p>vs Ajuan</p></div>
                    <span class="schedule-barang-budget-ratio-icon" aria-hidden="true"><i class="bi bi-graph-up-arrow"></i></span>
                </article>

                <div class="schedule-barang-budget-split">
                    <article class="schedule-barang-budget-card">
                        <span class="schedule-barang-budget-icon" aria-hidden="true"><i class="bi bi-bar-chart-line-fill"></i></span>
                        <div><p>Total Ajuan Lanjut</p><strong>30 <small>Item</small></strong></div>
                    </article>

                    <article class="schedule-barang-budget-card">
                        <span class="schedule-barang-budget-icon" aria-hidden="true"><i class="bi bi-bar-chart-line-fill"></i></span>
                        <div><p>Total Ajuan Cancel</p><strong>15 <small>Item</small></strong></div>
                    </article>
                </div>
            </div>
        </div>
    </section>
</div>

@push('styles')
    <style>
        .schedule-barang-budget {
            --budget-navy: #1e4b80;
            --budget-text: #16497e;
            --budget-green: #459d72;
            display: grid;
            gap: 34px;
            min-width: 0;
            padding: 2px 0 22px;
        }

        .schedule-barang-budget-filter {
            display: grid;
            grid-template-columns: 150px 170px minmax(280px, 1fr) auto;
            align-items: end;
            gap: 42px;
        }

        .schedule-barang-budget-field {
            display: grid;
            gap: 8px;
            min-width: 0;
        }

        .schedule-barang-budget-field label {
            margin: 0;
            color: #1e293b;
            font-size: 12px;
            font-weight: 700;
        }

        .schedule-barang-budget-field select,
        .schedule-barang-budget-search {
            width: 100%;
            min-height: 42px;
            border: 1px solid #e4ebf2;
            border-radius: 7px;
            background: #f8fafc;
            color: #26364d;
            font: inherit;
            font-size: 13px;
            outline: none;
        }

        .schedule-barang-budget-field select {
            padding: 0 36px 0 12px;
        }

        .schedule-barang-budget-search {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 0 13px;
        }

        .schedule-barang-budget-search i {
            color: #a2adba;
            font-size: 13px;
        }

        .schedule-barang-budget-search input {
            width: 100%;
            min-width: 0;
            border: 0;
            background: transparent;
            color: #26364d;
            font: inherit;
            font-size: 13px;
            outline: none;
        }

        .schedule-barang-budget-search input::placeholder {
            color: #a3adba;
        }

        .schedule-barang-budget-field select:focus,
        .schedule-barang-budget-search:focus-within {
            border-color: #4b78aa;
            box-shadow: 0 0 0 3px rgba(30, 75, 128, .11);
        }

        .schedule-barang-budget-add {
            min-height: 43px;
            padding: 0 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border: 0;
            border-radius: 7px;
            background: var(--budget-navy);
            color: #fff;
            font: inherit;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
            box-shadow: 0 4px 10px rgba(30, 75, 128, .12);
            transition: background-color .18s ease, transform .18s ease;
        }

        .schedule-barang-budget-add:hover { background: #143b68; }
        .schedule-barang-budget-add:active { transform: translateY(1px); }
        .schedule-barang-budget-add:focus-visible { outline: 3px solid rgba(30, 75, 128, .22); outline-offset: 2px; }

        .schedule-barang-budget-overview {
            display: grid;
            gap: 18px;
        }

        .schedule-barang-budget-overview h2 {
            margin: 0;
            color: var(--budget-text);
            font-size: 23px;
            font-weight: 800;
            letter-spacing: .02em;
            text-transform: uppercase;
        }

        .schedule-barang-budget-grid {
            display: grid;
            grid-template-columns: minmax(300px, .92fr) minmax(420px, 1.2fr);
            gap: 16px;
        }

        .schedule-barang-budget-column {
            display: grid;
            grid-template-rows: minmax(170px, 1fr) auto;
            gap: 14px;
            min-width: 0;
        }

        .schedule-barang-budget-card,
        .schedule-barang-budget-ratio {
            border: 1px solid #bcc9d6;
            border-radius: 13px;
            background: #fff;
            box-shadow: 0 3px 3px rgba(15, 23, 42, .16);
        }

        .schedule-barang-budget-card {
            min-height: 106px;
            padding: 22px 28px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .schedule-barang-budget-card--large {
            min-height: 170px;
            padding: 32px 44px;
        }

        .schedule-barang-budget-icon {
            width: 52px;
            height: 52px;
            flex: 0 0 52px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 5px;
            background: var(--budget-navy);
            color: #fff;
            font-size: 27px;
        }

        .schedule-barang-budget-card p {
            margin: 0 0 6px;
            color: #16528f;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: .025em;
            line-height: 1.25;
            text-transform: uppercase;
        }

        .schedule-barang-budget-card strong {
            display: block;
            color: var(--budget-text);
            font-size: clamp(26px, 2.35vw, 38px);
            font-weight: 800;
            letter-spacing: -.035em;
            line-height: 1.05;
        }

        .schedule-barang-budget-card strong small {
            font-size: .48em;
            font-weight: 800;
            letter-spacing: 0;
        }

        .schedule-barang-budget-ratio {
            position: relative;
            min-height: 170px;
            padding: 26px 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 56px;
            overflow: hidden;
            border-color: #29976d;
            background: #effcf7;
            color: var(--budget-green);
        }

        .schedule-barang-budget-ratio > strong {
            font-size: clamp(62px, 7vw, 92px);
            font-weight: 800;
            letter-spacing: -.055em;
            line-height: .9;
        }

        .schedule-barang-budget-ratio > strong span {
            margin-left: 5px;
            font-size: .72em;
        }

        .schedule-barang-budget-ratio-label { padding-right: 50px; }
        .schedule-barang-budget-ratio-label p {
            margin: 0;
            font-size: 17px;
            font-weight: 800;
            line-height: 1.35;
            text-transform: uppercase;
        }

        .schedule-barang-budget-ratio-icon {
            position: absolute;
            top: 18px;
            right: 20px;
            width: 54px;
            height: 54px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 7px;
            background: var(--budget-green);
            color: #fff;
            font-size: 29px;
        }

        .schedule-barang-budget-split {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }

        .schedule-barang-budget-split .schedule-barang-budget-card { padding: 22px 18px; }
        .schedule-barang-budget-split .schedule-barang-budget-icon { width: 50px; height: 50px; flex-basis: 50px; }
        .schedule-barang-budget-split .schedule-barang-budget-card p { font-size: 11px; }
        .schedule-barang-budget-split .schedule-barang-budget-card strong { font-size: clamp(25px, 2.1vw, 34px); }

        @media (max-width: 1199.98px) {
            .schedule-barang-budget-filter {
                grid-template-columns: 130px 150px minmax(220px, 1fr) auto;
                gap: 18px;
            }

            .schedule-barang-budget-grid {
                grid-template-columns: minmax(280px, .82fr) minmax(390px, 1.18fr);
            }

            .schedule-barang-budget-ratio { gap: 30px; }
        }

        @media (max-width: 991.98px) {
            .schedule-barang-budget-filter { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .schedule-barang-budget-field--search { grid-column: 1 / -1; }
            .schedule-barang-budget-add { grid-column: 2; justify-self: end; }
            .schedule-barang-budget-grid { grid-template-columns: minmax(0, 1fr); }
        }

        @media (max-width: 575.98px) {
            .schedule-barang-budget { gap: 26px; }
            .schedule-barang-budget-filter,
            .schedule-barang-budget-split { grid-template-columns: minmax(0, 1fr); }
            .schedule-barang-budget-field--search,
            .schedule-barang-budget-add { grid-column: auto; }
            .schedule-barang-budget-add { width: 100%; }
            .schedule-barang-budget-overview h2 { font-size: 19px; }
            .schedule-barang-budget-column { grid-template-rows: auto; }
            .schedule-barang-budget-card,
            .schedule-barang-budget-card--large { min-height: 112px; padding: 22px; }
            .schedule-barang-budget-ratio { min-height: 150px; padding: 24px 18px; justify-content: flex-start; gap: 22px; }
            .schedule-barang-budget-ratio > strong { font-size: 62px; }
            .schedule-barang-budget-ratio-label { padding-right: 42px; }
            .schedule-barang-budget-ratio-label p { font-size: 14px; }
            .schedule-barang-budget-ratio-icon { width: 44px; height: 44px; top: 14px; right: 14px; font-size: 23px; }
        }
    </style>
@endpush

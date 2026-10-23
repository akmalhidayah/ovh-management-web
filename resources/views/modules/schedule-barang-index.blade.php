@php
    $scheduleBarangPlanRows = [
        ['unit' => 'Pemeliharaan Kiln & Cooler', 'plant' => 'Tonasa 4', 'pagu' => 'Rp 3.500.000.000', 'ajuan' => 'Rp 3.250.000.000', 'status' => 'Di bawah 100% (92,8%)', 'tone' => 'safe'],
        ['unit' => 'Re-bricking & Refractory RKC', 'plant' => 'Tonasa 5', 'pagu' => 'Rp 2.800.000.000', 'ajuan' => 'Rp 2.750.000.000', 'status' => 'Di bawah 100% (98,2%)', 'tone' => 'safe'],
        ['unit' => 'Alignment & Balancing Turbin', 'plant' => 'Power Plant', 'pagu' => 'Rp 1.500.000.000', 'ajuan' => 'Rp 1.120.000.000', 'status' => 'Di bawah 100% (74,6%)', 'tone' => 'safe'],
        ['unit' => 'Fabrikasi Dust Collector', 'plant' => 'Tonasa 4', 'pagu' => 'Rp 1.200.000.000', 'ajuan' => 'Rp 1.450.000.000', 'status' => 'Di atas 100% (120,8%)', 'tone' => 'danger'],
        ['unit' => 'Inspeksi NDT & Vibrasi Finish Mill', 'plant' => 'Tonasa 2/3', 'pagu' => 'Rp 800.000.000', 'ajuan' => 'Rp 910.000.000', 'status' => 'Di atas 100% (113,7%)', 'tone' => 'danger'],
        ['unit' => 'Overhaul Gearbox Raw Mill', 'plant' => 'Tonasa 5', 'pagu' => 'Rp 2.150.000.000', 'ajuan' => 'Rp 2.050.000.000', 'status' => 'Di bawah 100% (95,3%)', 'tone' => 'safe'],
        ['unit' => 'Repair Bucket Elevator', 'plant' => 'Tonasa 4', 'pagu' => 'Rp 975.000.000', 'ajuan' => 'Rp 1.025.000.000', 'status' => 'Di atas 100% (105,1%)', 'tone' => 'danger'],
        ['unit' => 'Penggantian Liner Coal Mill', 'plant' => 'Tonasa 3', 'pagu' => 'Rp 1.300.000.000', 'ajuan' => 'Rp 1.175.000.000', 'status' => 'Di bawah 100% (90,3%)', 'tone' => 'safe'],
        ['unit' => 'Major Service ID Fan', 'plant' => 'Tonasa 2', 'pagu' => 'Rp 1.050.000.000', 'ajuan' => 'Rp 995.000.000', 'status' => 'Di bawah 100% (94,7%)', 'tone' => 'safe'],
        ['unit' => 'Rekondisi Hydraulic System', 'plant' => 'Tonasa 5', 'pagu' => 'Rp 1.525.000.000', 'ajuan' => 'Rp 1.575.000.000', 'status' => 'Di atas 100% (103,2%)', 'tone' => 'danger'],
    ];

    $scheduleBarangRemarks = [
        ['text' => 'Evaluasi spesifikasi Bearing Ball 6205 C3 SKF Tonasa 4, status BOM OK dan disetujui untuk pembelian Q3 2026.', 'detail' => 'Spesifikasi teknis dan jumlah kebutuhan telah sesuai hasil evaluasi Tim Overhaul.', 'status' => 'OK'],
        ['text' => 'Audit pembatalan item material Oil Seal Tonasa 5 (Cancel) sesuai operasi RKC akibat efisiensi anggaran.', 'detail' => 'Ajuan dibatalkan dan tidak diperhitungkan pada total ajuan lanjut.', 'status' => 'Cancel'],
        ['text' => 'Verifikasi dokumen COA & Technical Drawing Power Plant Seksi Turbin disetujui 100% lengkap.', 'detail' => 'Seluruh dokumen pendukung telah diterima dan tervalidasi.', 'status' => 'OK'],
        ['text' => 'Pembatalan pengadaan Coupling Finish Mill Tonasa 2/3 karena stok existing di gudang masih mencukupi.', 'detail' => 'Kebutuhan akan dievaluasi kembali pada periode overhaul berikutnya.', 'status' => 'Cancel'],
        ['text' => 'Finalisasi pendaftaran garansi pabrikan 12 bulan dan jadwal pengiriman item Conveyor Belt Tonasa 4.', 'detail' => 'Vendor telah memberikan konfirmasi garansi dan estimasi waktu pengiriman.', 'status' => 'OK'],
        ['text' => 'Penyesuaian jumlah kebutuhan refractory brick Kiln Tonasa 5 berdasarkan hasil inspeksi aktual.', 'detail' => 'Jumlah akhir telah diselaraskan dengan hasil pengukuran lapangan.', 'status' => 'OK'],
        ['text' => 'Ajuan penggantian motor feeder Tonasa 3 dibatalkan karena unit existing masih layak operasi.', 'detail' => 'Hasil pengecekan menunjukkan parameter operasi masih dalam batas aman.', 'status' => 'Cancel'],
        ['text' => 'Dokumen penawaran vendor gearbox Raw Mill telah lengkap dan siap masuk evaluasi komersial.', 'detail' => 'Klarifikasi teknis terakhir telah ditutup oleh PIC dan vendor.', 'status' => 'OK'],
        ['text' => 'Koreksi spesifikasi material liner Coal Mill mengikuti rekomendasi engineering terbaru.', 'detail' => 'Revisi material sudah tercatat pada dokumen teknis pengadaan.', 'status' => 'OK'],
        ['text' => 'Pembatalan ajuan spare coupling Power Plant karena terdapat duplikasi permintaan antar unit kerja.', 'detail' => 'Satu ajuan dipertahankan dan ajuan duplikat dibatalkan.', 'status' => 'Cancel'],
        ['text' => 'Konfirmasi jadwal inspeksi bersama vendor untuk pekerjaan NDT Finish Mill Tonasa 4.', 'detail' => 'Jadwal inspeksi disepakati pada minggu kedua masa overhaul.', 'status' => 'OK'],
        ['text' => 'Review ulang kebutuhan bearing ID Fan Tonasa 2 telah selesai tanpa perubahan nilai ajuan.', 'detail' => 'Nilai dan jumlah pengadaan tetap sesuai proposal awal.', 'status' => 'OK'],
    ];
@endphp

<div class="schedule-barang-budget" data-schedule-barang-budget>
    <section class="schedule-barang-budget-filter" aria-label="Search rencana kerja barang">
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
            <label for="schedule-barang-budget-search">Search</label>
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

    <section class="schedule-barang-plants" aria-labelledby="schedule-barang-plants-title">
        <div class="schedule-barang-section-heading">
            <h2 id="schedule-barang-plants-title">Ajuan Plant</h2>
            <div class="schedule-barang-section-actions">
                <span class="schedule-barang-total-chip">Total Ajuan = <strong>Rp 16,8 M</strong></span>
                <a href="#schedule-barang-report" class="schedule-barang-detail-link">Lihat Laporan Detail <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            </div>
        </div>

        <div class="schedule-barang-plant-grid">
            @foreach ([
                ['Tonasa 2', 'Rp 1,8 M', '2%', 'orange'],
                ['Tonasa 4', 'Rp 6,1 M', '40%', 'green'],
                ['Tonasa 3', 'Rp 2,4 M', '3%', 'purple'],
                ['Tonasa 5', 'Rp 6,5 M', '42%', 'red'],
            ] as [$plant, $value, $progress, $tone])
                <article class="schedule-barang-plant-card">
                    <div class="schedule-barang-plant-card-top">
                        <div class="schedule-barang-plant-name">
                            <span>{{ $plant }}</span>
                            <span class="schedule-barang-plant-icon is-{{ $tone }}" aria-hidden="true"><i class="bi bi-building-fill-gear"></i></span>
                        </div>
                        <span class="schedule-barang-plant-progress"><i class="bi bi-caret-down-fill" aria-hidden="true"></i> {{ $progress }}</span>
                    </div>
                    <strong>{{ $value }}</strong>
                </article>
            @endforeach
        </div>
    </section>

    <section class="schedule-barang-report-grid" id="schedule-barang-report">
        <article class="schedule-barang-report-card" data-paginated-list data-page-size="5">
            <h2>Rencana Kerja Jasa</h2>
            <div class="schedule-barang-report-scroll">
                <table class="schedule-barang-report-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Unit Kerja Jasa</th>
                            <th>Plant</th>
                            <th>Pagu Anggaran</th>
                            <th>Ajuan Anggaran</th>
                            <th>Status Anggaran</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($scheduleBarangPlanRows as $index => $row)
                            <tr data-paginated-row>
                                <td>{{ $index + 1 }}</td>
                                <td><strong>{{ $row['unit'] }}</strong></td>
                                <td>{{ $row['plant'] }}</td>
                                <td><strong>{{ $row['pagu'] }}</strong></td>
                                <td class="{{ $row['tone'] === 'danger' ? 'is-over-budget' : '' }}"><strong>{{ $row['ajuan'] }}</strong></td>
                                <td><span class="schedule-barang-budget-status is-{{ $row['tone'] }}"><i class="bi {{ $row['tone'] === 'safe' ? 'bi-arrow-down' : 'bi-arrow-up' }}" aria-hidden="true"></i>{{ $row['status'] }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="schedule-barang-list-footer">
                <span data-pagination-summary aria-live="polite"></span>
                <div class="schedule-barang-list-controls">
                    <label><select data-pagination-size aria-label="Jumlah data per halaman"><option value="5" selected>5</option><option value="10">10</option></select><span>data per halaman</span></label>
                    <nav data-pagination-nav aria-label="Pagination rencana kerja jasa"></nav>
                </div>
            </div>
        </article>

        <aside class="schedule-barang-distribution" aria-labelledby="schedule-barang-distribution-title">
            <h2 id="schedule-barang-distribution-title">Distribusi Pagu</h2>
            <div class="schedule-barang-donut" role="img" aria-label="35 plant melebihi pagu dan 17 plant sesuai pagu">
                <div><strong>52</strong><span>Total Plant</span></div>
            </div>
            <div class="schedule-barang-distribution-legend">
                <div class="is-danger"><span><i></i>Melebihi Pagu</span><strong>35 <small>Plant</small></strong><small>67,3% dari total plant</small></div>
                <div class="is-safe"><span><i></i>Sesuai Pagu</span><strong>17 <small>Plant</small></strong><small>32,7% dari total plant</small></div>
            </div>
        </aside>
    </section>

    <section class="schedule-barang-remarks" data-paginated-list data-page-size="5" aria-labelledby="schedule-barang-remarks-title">
        <h2 id="schedule-barang-remarks-title">Remarks</h2>
        <div class="schedule-barang-remarks-scroll">
            <table class="schedule-barang-remarks-table">
                <thead><tr><th>No</th><th>Informasi / Catatan</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                    @foreach ($scheduleBarangRemarks as $index => $remark)
                        <tr data-paginated-row>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <p>{{ $remark['text'] }}</p>
                                <details class="schedule-barang-read-more"><summary>Read More +</summary><p>{{ $remark['detail'] }}</p></details>
                            </td>
                            <td><span class="schedule-barang-remark-status is-{{ strtolower($remark['status']) }}"><i class="bi {{ $remark['status'] === 'OK' ? 'bi-check' : 'bi-x' }}" aria-hidden="true"></i>{{ $remark['status'] }}</span></td>
                            <td>
                                <div class="schedule-barang-remark-actions" aria-label="Aksi remark {{ $index + 1 }}">
                                    <span class="is-document" title="Lihat dokumen" aria-label="Lihat dokumen"><i class="bi bi-file-earmark-text-fill" aria-hidden="true"></i></span>
                                    <span class="is-edit" title="Edit remark" aria-label="Edit remark"><i class="bi bi-pencil-fill" aria-hidden="true"></i></span>
                                    <span class="is-status {{ $remark['status'] === 'OK' ? 'is-ok' : 'is-pending' }}" title="{{ $remark['status'] === 'OK' ? 'Status selesai' : 'Status belum selesai' }}" aria-label="{{ $remark['status'] === 'OK' ? 'Status selesai' : 'Status belum selesai' }}"><i class="bi {{ $remark['status'] === 'OK' ? 'bi-check-circle-fill' : 'bi-dash-circle' }}" aria-hidden="true"></i></span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="schedule-barang-list-footer">
            <span data-pagination-summary aria-live="polite"></span>
            <nav data-pagination-nav aria-label="Pagination remarks"></nav>
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

        .schedule-barang-plants {
            display: grid;
            gap: 16px;
        }

        .schedule-barang-section-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
        }

        .schedule-barang-section-heading h2,
        .schedule-barang-report-card > h2,
        .schedule-barang-distribution > h2,
        .schedule-barang-remarks > h2 {
            margin: 0;
            color: var(--budget-text);
            font-size: 20px;
            font-weight: 800;
            letter-spacing: .025em;
            text-transform: uppercase;
        }

        .schedule-barang-section-actions {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .schedule-barang-total-chip,
        .schedule-barang-detail-link {
            min-height: 38px;
            padding: 0 15px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 7px;
            font-size: 12px;
            text-decoration: none;
            white-space: nowrap;
        }

        .schedule-barang-total-chip {
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #335273;
            box-shadow: 0 2px 4px rgba(15, 23, 42, .08);
        }

        .schedule-barang-detail-link {
            gap: 9px;
            background: var(--budget-navy);
            color: #fff;
            box-shadow: 0 3px 5px rgba(30, 75, 128, .2);
        }

        .schedule-barang-detail-link:hover { background: #143b68; color: #fff; }

        .schedule-barang-plant-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .schedule-barang-plant-card {
            min-height: 142px;
            padding: 24px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border: 1px solid #d2dae3;
            border-radius: 11px;
            background: #fff;
            box-shadow: 0 3px 3px rgba(15, 23, 42, .13);
        }

        .schedule-barang-plant-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
        }

        .schedule-barang-plant-name {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--budget-text);
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .schedule-barang-plant-icon {
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            color: #fff;
            font-size: 23px;
        }

        .schedule-barang-plant-icon.is-orange { background: #f39a3d; }
        .schedule-barang-plant-icon.is-green { background: #54c81e; }
        .schedule-barang-plant-icon.is-purple { background: #7d5cff; }
        .schedule-barang-plant-icon.is-red { background: #eb4e55; }

        .schedule-barang-plant-progress {
            min-width: 56px;
            padding: 3px 8px;
            border-radius: 5px;
            background: var(--budget-navy);
            color: #fff;
            font-size: 12px;
            font-weight: 800;
            line-height: 1;
            text-align: center;
        }

        .schedule-barang-plant-card > strong {
            color: var(--budget-text);
            font-size: clamp(30px, 3vw, 42px);
            font-weight: 800;
            letter-spacing: -.03em;
            line-height: 1;
        }

        .schedule-barang-report-grid {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(300px, 1fr);
            gap: 18px;
            align-items: stretch;
            scroll-margin-top: 22px;
        }

        .schedule-barang-report-card,
        .schedule-barang-distribution,
        .schedule-barang-remarks {
            min-width: 0;
            border: 1px solid #d8e0e8;
            border-radius: 9px;
            background: #fff;
            box-shadow: 0 2px 4px rgba(15, 23, 42, .06);
        }

        .schedule-barang-report-card,
        .schedule-barang-distribution {
            padding: 20px;
        }

        .schedule-barang-report-card > h2,
        .schedule-barang-distribution > h2,
        .schedule-barang-remarks > h2 {
            font-size: 16px;
        }

        .schedule-barang-report-scroll,
        .schedule-barang-remarks-scroll {
            margin-top: 16px;
            overflow-x: auto;
        }

        .schedule-barang-report-scroll {
            border: 1px solid #e1e7ee;
            border-radius: 9px;
        }

        .schedule-barang-report-table,
        .schedule-barang-remarks-table {
            width: 100%;
            border-collapse: collapse;
            color: #2b4058;
        }

        .schedule-barang-report-table {
            min-width: 760px;
            font-size: 10px;
        }

        .schedule-barang-report-table th,
        .schedule-barang-remarks-table th {
            background: #f3f6fa;
            color: #57708c;
            font-size: 9px;
            font-weight: 800;
            letter-spacing: .035em;
            text-align: left;
            text-transform: uppercase;
        }

        .schedule-barang-report-table th,
        .schedule-barang-report-table td {
            padding: 11px 10px;
            border-bottom: 1px solid #e6ebf1;
            vertical-align: middle;
        }

        .schedule-barang-report-table th:first-child,
        .schedule-barang-report-table td:first-child {
            width: 38px;
            text-align: center;
        }

        .schedule-barang-report-table th:nth-child(2) { width: 30%; }
        .schedule-barang-report-table td:nth-child(4),
        .schedule-barang-report-table td:nth-child(5) { white-space: nowrap; }
        .schedule-barang-report-table td.is-over-budget { color: #e33343; }

        .schedule-barang-budget-status {
            padding: 5px 8px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            border-radius: 999px;
            font-size: 8px;
            font-weight: 800;
            white-space: nowrap;
        }

        .schedule-barang-budget-status.is-safe { background: #e7fbf1; color: #159062; }
        .schedule-barang-budget-status.is-danger { background: #fff0f1; color: #df3043; }

        .schedule-barang-list-footer {
            min-height: 46px;
            padding-top: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            color: #6a7d92;
            font-size: 10px;
        }

        .schedule-barang-list-controls,
        .schedule-barang-list-controls label,
        [data-pagination-nav] {
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .schedule-barang-list-controls select {
            height: 28px;
            padding: 0 7px;
            border: 1px solid #d7e0e9;
            border-radius: 5px;
            background: #fff;
            color: #52677f;
            font: inherit;
        }

        .schedule-barang-page-button {
            min-width: 28px;
            height: 28px;
            padding: 0 8px;
            border: 1px solid #d7e0e9;
            border-radius: 6px;
            background: #fff;
            color: #52677f;
            font: inherit;
            font-size: 10px;
            font-weight: 800;
        }

        .schedule-barang-page-button.is-active { border-color: var(--budget-navy); background: var(--budget-navy); color: #fff; }
        .schedule-barang-page-button:disabled { cursor: not-allowed; opacity: .4; }
        [data-paginated-row][hidden] { display: none; }

        .schedule-barang-distribution {
            display: flex;
            flex-direction: column;
        }

        .schedule-barang-donut {
            width: 190px;
            height: 190px;
            margin: 24px auto 18px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: conic-gradient(#b72e24 0 67.3%, #4b9f5b 67.3% 100%);
        }

        .schedule-barang-donut::before {
            width: 112px;
            height: 112px;
            border-radius: 50%;
            background: #fff;
            content: '';
            grid-area: 1 / 1;
        }

        .schedule-barang-donut > div {
            z-index: 1;
            display: grid;
            grid-area: 1 / 1;
            text-align: center;
        }

        .schedule-barang-donut strong { color: #1d2d42; font-size: 39px; line-height: 1; }
        .schedule-barang-donut span { margin-top: 4px; color: #67788d; font-size: 9px; font-weight: 800; text-transform: uppercase; }

        .schedule-barang-distribution-legend {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .schedule-barang-distribution-legend > div {
            padding: 12px;
            display: grid;
            gap: 7px;
            border: 1px solid #e3e8ee;
            border-radius: 9px;
        }

        .schedule-barang-distribution-legend > div > span {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #66778b;
            font-size: 8px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .schedule-barang-distribution-legend i { width: 10px; height: 10px; border-radius: 50%; }
        .schedule-barang-distribution-legend .is-danger i { background: #b72e24; }
        .schedule-barang-distribution-legend .is-safe i { background: #4b9f5b; }
        .schedule-barang-distribution-legend strong { color: #16283d; font-size: 20px; }
        .schedule-barang-distribution-legend strong small { font-size: 10px; }
        .schedule-barang-distribution-legend > div > small { margin: 0 -12px -12px; padding: 8px 10px; border-radius: 0 0 9px 9px; font-size: 9px; font-weight: 700; text-align: center; }
        .schedule-barang-distribution-legend .is-danger > small { background: #fff0f0; color: #c52b2b; }
        .schedule-barang-distribution-legend .is-safe > small { background: #ebfaef; color: #278246; }

        .schedule-barang-remarks {
            overflow: hidden;
        }

        .schedule-barang-remarks > h2 {
            padding: 18px 20px;
            border-bottom: 1px solid #e1e7ee;
        }

        .schedule-barang-remarks-scroll {
            margin-top: 0;
        }

        .schedule-barang-remarks-table {
            min-width: 900px;
            font-size: 11px;
        }

        .schedule-barang-remarks-table th,
        .schedule-barang-remarks-table td {
            padding: 12px 16px;
            border-bottom: 1px solid #e7ecf1;
            vertical-align: middle;
        }

        .schedule-barang-remarks-table th:first-child,
        .schedule-barang-remarks-table td:first-child { width: 60px; text-align: center; }
        .schedule-barang-remarks-table th:nth-child(3),
        .schedule-barang-remarks-table td:nth-child(3) { width: 130px; text-align: center; }
        .schedule-barang-remarks-table th:last-child,
        .schedule-barang-remarks-table td:last-child { width: 120px; text-align: center; }
        .schedule-barang-remarks-table td > p { margin: 0; color: #24364c; font-weight: 600; }

        .schedule-barang-read-more { margin-top: 3px; }
        .schedule-barang-read-more summary { cursor: pointer; list-style: none; color: #1762b3; font-size: 9px; font-weight: 800; }
        .schedule-barang-read-more summary::-webkit-details-marker { display: none; }
        .schedule-barang-read-more[open] summary { margin-bottom: 4px; }
        .schedule-barang-read-more p { margin: 0; color: #6b7f95; font-size: 10px; font-weight: 400; }

        .schedule-barang-remark-status {
            min-width: 55px;
            padding: 5px 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            border-radius: 999px;
            font-size: 9px;
            font-weight: 800;
        }

        .schedule-barang-remark-status.is-ok { border: 1px solid #72deb3; background: #ecfff7; color: #159563; }
        .schedule-barang-remark-status.is-cancel { border: 1px solid #f1a4ad; background: #fff3f4; color: #dc3143; }

        .schedule-barang-remark-actions {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .schedule-barang-remark-actions > span {
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid transparent;
            border-radius: 6px;
            font-size: 12px;
        }

        .schedule-barang-remark-actions .is-document {
            border-color: #c9d9ea;
            background: #f3f7fb;
            color: #315f8f;
        }

        .schedule-barang-remark-actions .is-edit {
            border-color: #f0d9a8;
            background: #fff9ec;
            color: #b87a12;
        }

        .schedule-barang-remark-actions .is-status.is-ok {
            border-color: #b7e5cf;
            background: #effaf5;
            color: #249967;
        }

        .schedule-barang-remark-actions .is-status.is-pending {
            border-color: #d6dee7;
            background: #f7f9fb;
            color: #718197;
        }

        .schedule-barang-remarks > .schedule-barang-list-footer {
            padding: 12px 18px;
        }

        @media (max-width: 1199.98px) {
            .schedule-barang-budget-filter {
                grid-template-columns: 130px 150px minmax(220px, 1fr) auto;
                gap: 18px;
            }

            .schedule-barang-budget-grid {
                grid-template-columns: minmax(280px, .82fr) minmax(390px, 1.18fr);
            }

            .schedule-barang-budget-ratio { gap: 30px; }
            .schedule-barang-report-grid { grid-template-columns: minmax(0, 1fr); }
            .schedule-barang-distribution { min-height: 360px; }
        }

        @media (max-width: 991.98px) {
            .schedule-barang-budget-filter { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .schedule-barang-budget-field--search { grid-column: 1 / -1; }
            .schedule-barang-budget-add { grid-column: 2; justify-self: end; }
            .schedule-barang-budget-grid { grid-template-columns: minmax(0, 1fr); }
            .schedule-barang-section-heading { align-items: flex-start; }
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
            .schedule-barang-section-heading,
            .schedule-barang-section-actions,
            .schedule-barang-list-footer,
            .schedule-barang-list-controls { align-items: stretch; flex-direction: column; }
            .schedule-barang-section-actions { width: 100%; }
            .schedule-barang-total-chip,
            .schedule-barang-detail-link { width: 100%; }
            .schedule-barang-plant-grid { grid-template-columns: minmax(0, 1fr); }
            .schedule-barang-plant-card { min-height: 130px; padding: 20px; }
            .schedule-barang-report-card,
            .schedule-barang-distribution { padding: 16px; }
            .schedule-barang-distribution { min-height: 0; }
            .schedule-barang-donut { width: 166px; height: 166px; }
            .schedule-barang-donut::before { width: 98px; height: 98px; }
            .schedule-barang-distribution-legend { grid-template-columns: minmax(0, 1fr); }
            .schedule-barang-list-controls label { justify-content: space-between; }
            [data-pagination-nav] { flex-wrap: wrap; }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-schedule-barang-budget] [data-paginated-list]').forEach((list) => {
                const rows = Array.from(list.querySelectorAll('[data-paginated-row]'));
                const summary = list.querySelector('[data-pagination-summary]');
                const navigation = list.querySelector('[data-pagination-nav]');
                const sizeSelect = list.querySelector('[data-pagination-size]');
                let pageSize = Number(sizeSelect?.value || list.dataset.pageSize || 5);
                let currentPage = 1;

                const createButton = (label, page, options = {}) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'schedule-barang-page-button';
                    button.textContent = label;
                    button.disabled = options.disabled === true;

                    if (options.active === true) {
                        button.classList.add('is-active');
                        button.setAttribute('aria-current', 'page');
                    }

                    button.addEventListener('click', () => {
                        currentPage = page;
                        render();
                    });

                    return button;
                };

                const render = () => {
                    const pageCount = Math.max(1, Math.ceil(rows.length / pageSize));
                    currentPage = Math.min(Math.max(currentPage, 1), pageCount);
                    const start = (currentPage - 1) * pageSize;
                    const end = Math.min(start + pageSize, rows.length);

                    rows.forEach((row, index) => {
                        row.hidden = index < start || index >= end;
                    });

                    if (summary) {
                        summary.textContent = rows.length === 0
                            ? 'Tidak ada data'
                            : `Menampilkan ${start + 1} - ${end} dari ${rows.length} data`;
                    }

                    if (!navigation) {
                        return;
                    }

                    navigation.replaceChildren();
                    navigation.append(createButton('‹', currentPage - 1, {
                        disabled: currentPage === 1,
                    }));

                    for (let page = 1; page <= pageCount; page += 1) {
                        navigation.append(createButton(String(page), page, {
                            active: page === currentPage,
                        }));
                    }

                    navigation.append(createButton('›', currentPage + 1, {
                        disabled: currentPage === pageCount,
                    }));
                };

                sizeSelect?.addEventListener('change', () => {
                    pageSize = Number(sizeSelect.value || 5);
                    currentPage = 1;
                    render();
                });

                render();
            });
        });
    </script>
@endpush

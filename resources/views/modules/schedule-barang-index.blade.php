@php
    $scheduleBarangPlanRows = [
        ['unit' => 'PEM. MESIN KCM 4/5', 'plant' => 'Tonasa 4', 'pagu' => '2.700.000.000,00', 'ajuan' => '2.554.923.037,00', 'status' => 'Di bawah 100% (94,6%)', 'tone' => 'safe'],
        ['unit' => 'OPERASI RKC 4/5', 'plant' => 'Tonasa 4', 'pagu' => '50.000.000,00', 'ajuan' => '31.057.500,00', 'status' => 'Di bawah 100% (62,1%)', 'tone' => 'safe'],
        ['unit' => 'PEM. MESIN FM 4/5', 'plant' => 'Tonasa 4', 'pagu' => '2.200.000.000,00', 'ajuan' => '51.684.100,00', 'status' => 'Di bawah 100% (2,3%)', 'tone' => 'safe'],
        ['unit' => 'PEM. MESIN RM 4/5', 'plant' => 'Tonasa 4', 'pagu' => '2.350.000.000,00', 'ajuan' => '2.845.986.000,00', 'status' => 'Di atas 100% (121,1%)', 'tone' => 'danger'],
        ['unit' => 'OPERASI FM 4/5', 'plant' => 'Tonasa 4', 'pagu' => '50.000.000,00', 'ajuan' => '168.707.171,00', 'status' => 'Di atas 100% (337,4%)', 'tone' => 'danger'],
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
                    <div><p>Total Ajuan Anggaran</p><strong>Rp 11,8 M</strong></div>
                </article>
            </div>

            <div class="schedule-barang-budget-column">
                <article class="schedule-barang-budget-ratio">
                    <strong>92,2<span>%</span></strong>
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
                <span class="schedule-barang-total-chip">Total Ajuan = <strong>Rp 11,8 M</strong></span>
                <a href="#schedule-barang-report" class="schedule-barang-detail-link">Lihat Laporan Detail <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            </div>
        </div>

        <div class="schedule-barang-plant-grid">
            @foreach ([
                ['Tonasa 2', 'Rp 0,9 M', '2%', 'orange'],
                ['Tonasa 4', 'Rp 4,5 M', '40%', 'green'],
                ['Tonasa 3', 'Rp 1,2 M', '3%', 'purple'],
                ['Tonasa 5', 'Rp 5,2 M', '42%', 'red'],
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
            <h2>Rencana Kerja Barang</h2>
            <div class="schedule-barang-report-scroll">
                <table class="schedule-barang-report-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Unit Kerja</th>
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
                    <nav data-pagination-nav aria-label="Pagination rencana kerja barang"></nav>
                </div>
            </div>
        </article>

        <aside class="schedule-barang-distribution" aria-labelledby="schedule-barang-distribution-title">
            <h2 id="schedule-barang-distribution-title">Distribusi Pagu</h2>
            <div class="schedule-barang-donut" role="img" aria-label="24 seksi: 16 seksi melebihi pagu dan 8 seksi sesuai pagu">
                <div><strong>24</strong><span>Total Seksi</span></div>
            </div>
            <div class="schedule-barang-distribution-legend">
                <div class="is-danger"><span><i></i>Melebihi Pagu</span><strong>16 <small>Seksi</small></strong><small>66,7% dari total seksi</small></div>
                <div class="is-safe"><span><i></i>Sesuai Pagu</span><strong>8 <small>Seksi</small></strong><small>33,3% dari total seksi</small></div>
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

@include('modules.schedule-shared.dashboard-assets')

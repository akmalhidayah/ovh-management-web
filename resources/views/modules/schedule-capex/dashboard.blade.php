@php
    $scheduleCapexPlanRows = [
        ['unit' => 'SEKSI PEMEL. MESIN FM 4/5', 'plant' => 'Tonasa 5', 'pagu' => '7.804.504.000', 'ajuan' => '7.804.504.000', 'status' => 'Di bawah 100% (100,0%)', 'tone' => 'safe'],
        ['unit' => 'SEKSI PEMEL. RM MACHINE MAINT.', 'plant' => 'Tonasa 5', 'pagu' => '7.500.000.000', 'ajuan' => '8.318.045.040', 'status' => 'Di atas 100% (110,9%)', 'tone' => 'danger'],
        ['unit' => 'SEKSI PEMEL. KILN & CM ELECTRIC', 'plant' => 'Tonasa 4', 'pagu' => '3.000.000.000', 'ajuan' => '2.405.000.000', 'status' => 'Di bawah 100% (80,2%)', 'tone' => 'safe'],
        ['unit' => 'SEKSI OPERASI FM 5', 'plant' => 'Tonasa 5', 'pagu' => '250.000.000', 'ajuan' => '221.000.000', 'status' => 'Di bawah 100% (88,4%)', 'tone' => 'safe'],
        ['unit' => 'SEKSI OPERASI RKC 5', 'plant' => 'Tonasa 5', 'pagu' => '1.600.000.000', 'ajuan' => '1.834.608.720', 'status' => 'Di atas 100% (114,7%)', 'tone' => 'danger'],
        ['unit' => 'SEKSI PEMEL. CRUSHER 2/3', 'plant' => 'Tonasa 2', 'pagu' => '1.250.000.000', 'ajuan' => '1.180.000.000', 'status' => 'Di bawah 100% (94,4%)', 'tone' => 'safe'],
        ['unit' => 'SEKSI POWER DISTRIBUTION', 'plant' => 'Power Plant', 'pagu' => '2.100.000.000', 'ajuan' => '2.315.000.000', 'status' => 'Di atas 100% (110,2%)', 'tone' => 'danger'],
        ['unit' => 'SEKSI PACKING PLANT', 'plant' => 'Tonasa 4', 'pagu' => '980.000.000', 'ajuan' => '875.000.000', 'status' => 'Di bawah 100% (89,3%)', 'tone' => 'safe'],
        ['unit' => 'SEKSI PEMEL. COAL MILL', 'plant' => 'Tonasa 3', 'pagu' => '1.450.000.000', 'ajuan' => '1.525.000.000', 'status' => 'Di atas 100% (105,2%)', 'tone' => 'danger'],
        ['unit' => 'SEKSI ELECTRICAL MAINT.', 'plant' => 'Tonasa 5', 'pagu' => '3.250.000.000', 'ajuan' => '3.010.000.000', 'status' => 'Di bawah 100% (92,6%)', 'tone' => 'safe'],
        ['unit' => 'SEKSI INSTRUMENTATION', 'plant' => 'Tonasa 4', 'pagu' => '1.875.000.000', 'ajuan' => '1.760.000.000', 'status' => 'Di bawah 100% (93,9%)', 'tone' => 'safe'],
        ['unit' => 'SEKSI UTILITAS PABRIK', 'plant' => 'Tonasa 2', 'pagu' => '725.000.000', 'ajuan' => '780.000.000', 'status' => 'Di atas 100% (107,6%)', 'tone' => 'danger'],
        ['unit' => 'SEKSI PEMEL. RAW MILL', 'plant' => 'Tonasa 3', 'pagu' => '2.650.000.000', 'ajuan' => '2.405.000.000', 'status' => 'Di bawah 100% (90,8%)', 'tone' => 'safe'],
        ['unit' => 'SEKSI MECHANICAL WORKSHOP', 'plant' => 'Tonasa 5', 'pagu' => '860.000.000', 'ajuan' => '945.000.000', 'status' => 'Di atas 100% (109,9%)', 'tone' => 'danger'],
        ['unit' => 'SEKSI QUALITY ASSURANCE', 'plant' => 'Tonasa 4', 'pagu' => '640.000.000', 'ajuan' => '590.000.000', 'status' => 'Di bawah 100% (92,2%)', 'tone' => 'safe'],
        ['unit' => 'SEKSI PORT FACILITY', 'plant' => 'Tonasa 2', 'pagu' => '1.930.000.000', 'ajuan' => '2.115.000.000', 'status' => 'Di atas 100% (109,6%)', 'tone' => 'danger'],
        ['unit' => 'SEKSI CIVIL MAINTENANCE', 'plant' => 'Tonasa 3', 'pagu' => '1.320.000.000', 'ajuan' => '1.205.000.000', 'status' => 'Di bawah 100% (91,3%)', 'tone' => 'safe'],
        ['unit' => 'SEKSI AUTOMATION SYSTEM', 'plant' => 'Tonasa 5', 'pagu' => '2.450.000.000', 'ajuan' => '2.590.000.000', 'status' => 'Di atas 100% (105,7%)', 'tone' => 'danger'],
    ];

    $scheduleCapexRemarks = [
        ['text' => 'Evaluasi spesifikasi Bearing Ball 6205 C3 SKF Tonasa 4, status BOM OK disetujui untuk pemakaian Q3 2026.', 'detail' => 'Spesifikasi teknis dan jumlah kebutuhan telah sesuai hasil evaluasi Tim Overhaul.', 'status' => 'OK'],
        ['text' => 'Audit pembatalan item material Oil Seal Tonasa 5 (Cancel) seksi operasi RKC 5 akibat efisiensi anggaran.', 'detail' => 'Ajuan dibatalkan dan tidak diperhitungkan pada total ajuan lanjut.', 'status' => 'Cancel'],
        ['text' => 'Verifikasi dokumen COA & Technical Drawing Power Plant Seksi Turbin disetujui 100% lengkap.', 'detail' => 'Seluruh dokumen pendukung telah diterima dan tervalidasi.', 'status' => 'OK'],
        ['text' => 'Pembatalan pengadaan Coupling Finish Mill Tonasa 2/3 karena stok existing di gudang masih mencukupi.', 'detail' => 'Kebutuhan akan dievaluasi kembali pada periode overhaul berikutnya.', 'status' => 'Cancel'],
        ['text' => 'Finalisasi pendaftaran garansi pabrikan 12 bulan dan jadwal pengiriman item Conveyor Belt Tonasa 4.', 'detail' => 'Vendor telah memberikan konfirmasi garansi dan estimasi waktu pengiriman.', 'status' => 'OK'],
        ['text' => 'Penyesuaian nilai investasi upgrade sistem burner berdasarkan hasil evaluasi teknis terbaru.', 'detail' => 'Nilai estimasi diperbarui tanpa mengubah ruang lingkup utama pekerjaan.', 'status' => 'OK'],
        ['text' => 'Ajuan modernisasi panel kontrol ditahan sementara hingga kajian risiko operasional selesai.', 'detail' => 'Tim engineering sedang menyelesaikan analisis risiko dan rencana mitigasi.', 'status' => 'Cancel'],
        ['text' => 'Dokumen business case revitalisasi Finish Mill telah lengkap dan siap masuk evaluasi komersial.', 'detail' => 'Klarifikasi teknis terakhir telah ditutup oleh PIC dan calon vendor.', 'status' => 'OK'],
        ['text' => 'Koreksi spesifikasi sistem monitoring mengikuti rekomendasi engineering terbaru.', 'detail' => 'Revisi spesifikasi sudah tercatat pada dokumen teknis CAPEX.', 'status' => 'OK'],
        ['text' => 'Pembatalan ajuan perangkat pendukung karena terdapat duplikasi kebutuhan antar unit kerja.', 'detail' => 'Satu ajuan dipertahankan dan ajuan duplikat dibatalkan.', 'status' => 'Cancel'],
        ['text' => 'Konfirmasi jadwal inspeksi bersama vendor untuk pekerjaan upgrade Power House.', 'detail' => 'Jadwal inspeksi disepakati pada minggu kedua masa overhaul.', 'status' => 'OK'],
        ['text' => 'Review ulang kebutuhan investasi Tonasa 2 selesai tanpa perubahan nilai ajuan.', 'detail' => 'Nilai dan ruang lingkup tetap sesuai proposal awal.', 'status' => 'OK'],
    ];
@endphp

<div class="schedule-barang-budget" data-schedule-barang-budget>
    <section class="schedule-barang-budget-filter" aria-label="Search rencana kerja Capex">
        <div class="schedule-barang-budget-field">
            <label for="schedule-capex-budget-year">Tahun</label>
            <select id="schedule-capex-budget-year">
                <option value="2025">2025</option>
                <option value="2026" selected>2026</option>
                <option value="2027">2027</option>
            </select>
        </div>

        <div class="schedule-barang-budget-field">
            <label for="schedule-capex-budget-plant">Plant</label>
            <select id="schedule-capex-budget-plant">
                <option value="Tonasa 2">Tonasa 2</option>
                <option value="Tonasa 3">Tonasa 3</option>
                <option value="Tonasa 4">Tonasa 4</option>
                <option value="Tonasa 5" selected>Tonasa 5</option>
            </select>
        </div>

        <div class="schedule-barang-budget-field schedule-barang-budget-field--search">
            <label for="schedule-capex-budget-search">Search</label>
            <div class="schedule-barang-budget-search">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input id="schedule-capex-budget-search" type="search" placeholder="Cari unit kerja/status ajuan/pekerjaan...">
            </div>
        </div>

        <button type="button" class="schedule-barang-budget-add">
            <i class="bi bi-plus-lg" aria-hidden="true"></i>
            <span>Tambah Ajuan</span>
        </button>
    </section>

    <section class="schedule-barang-budget-overview" aria-labelledby="schedule-capex-budget-title">
        <h2 id="schedule-capex-budget-title">Pagu &amp; Ajuan Anggaran</h2>

        <div class="schedule-barang-budget-grid">
            <div class="schedule-barang-budget-column">
                <article class="schedule-barang-budget-card schedule-barang-budget-card--large">
                    <span class="schedule-barang-budget-icon" aria-hidden="true"><i class="bi bi-bar-chart-line-fill"></i></span>
                    <div><p>Total Pagu</p><strong>Rp 15,8 M</strong></div>
                </article>

                <article class="schedule-barang-budget-card">
                    <span class="schedule-barang-budget-icon" aria-hidden="true"><i class="bi bi-bar-chart-line-fill"></i></span>
                    <div><p>Total Ajuan Anggaran</p><strong>Rp 8 M</strong></div>
                </article>
            </div>

            <div class="schedule-barang-budget-column">
                <article class="schedule-barang-budget-ratio">
                    <strong>56<span>%</span></strong>
                    <div class="schedule-barang-budget-ratio-label"><p>Pagu</p><p>vs Ajuan</p></div>
                    <span class="schedule-barang-budget-ratio-icon" aria-hidden="true"><i class="bi bi-graph-up-arrow"></i></span>
                </article>

                <div class="schedule-barang-budget-split">
                    <article class="schedule-barang-budget-card">
                        <span class="schedule-barang-budget-icon" aria-hidden="true"><i class="bi bi-bar-chart-line-fill"></i></span>
                        <div><p>Total Ajuan Lanjut</p><strong class="is-warning-value">30 <small>Item</small></strong></div>
                    </article>

                    <article class="schedule-barang-budget-card">
                        <span class="schedule-barang-budget-icon" aria-hidden="true"><i class="bi bi-bar-chart-line-fill"></i></span>
                        <div><p>Total Ajuan Cancel</p><strong class="is-success-value">15 <small>Item</small></strong></div>
                    </article>
                </div>
            </div>
        </div>
    </section>

    <section class="schedule-barang-plants" aria-labelledby="schedule-capex-plants-title">
        <div class="schedule-barang-section-heading">
            <h2 id="schedule-capex-plants-title">Ajuan Plant</h2>
            <div class="schedule-barang-section-actions">
                <span class="schedule-barang-total-chip">Total Ajuan = <strong>Rp 16,8 M</strong></span>
                <a href="#schedule-capex-report" class="schedule-barang-detail-link">Lihat Laporan Detail <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
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

    <section class="schedule-barang-report-grid" id="schedule-capex-report">
        <article class="schedule-barang-report-card" data-paginated-list data-page-size="5">
            <h2>Rencana Kerja Capex</h2>
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
                        @foreach ($scheduleCapexPlanRows as $index => $row)
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
                    <nav data-pagination-nav aria-label="Pagination rencana kerja Capex"></nav>
                </div>
            </div>
        </article>

        <aside class="schedule-barang-distribution" aria-labelledby="schedule-capex-distribution-title">
            <h2 id="schedule-capex-distribution-title">Distribusi Pagu</h2>
            <div class="schedule-barang-donut is-capex" role="img" aria-label="28 plant: 19 plant melebihi pagu dan 9 plant sesuai pagu">
                <div><strong>28</strong><span>Total Plant</span></div>
            </div>
            <div class="schedule-barang-distribution-legend">
                <div class="is-danger"><span><i></i>Melebihi Pagu</span><strong>19 <small>Plant</small></strong><small>67,9% dari total plant</small></div>
                <div class="is-safe"><span><i></i>Sesuai Pagu</span><strong>9 <small>Plant</small></strong><small>32,1% dari total plant</small></div>
            </div>
        </aside>
    </section>

    <section class="schedule-barang-remarks" data-paginated-list data-page-size="5" aria-labelledby="schedule-capex-remarks-title">
        <h2 id="schedule-capex-remarks-title">Remarks</h2>
        <div class="schedule-barang-remarks-scroll">
            <table class="schedule-barang-remarks-table">
                <thead><tr><th>No</th><th>Informasi / Catatan</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                    @foreach ($scheduleCapexRemarks as $index => $remark)
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
            <div class="schedule-capex-remarks-footer-actions">
                <nav data-pagination-nav aria-label="Pagination remarks Capex"></nav>
                <span class="schedule-capex-database-status"><i class="bi bi-cloud-check-fill" aria-hidden="true"></i> Database Status Terhubung Realtime</span>
            </div>
        </div>
    </section>
</div>

@include('modules.schedule-shared.dashboard-assets')

@php
    $requestedWorkType = $workType ?? 'barang';
    $workType = in_array($requestedWorkType, ['barang', 'jasa', 'capex'], true) ? $requestedWorkType : 'barang';
    $tableTitle = match ($workType) {
        'jasa' => 'Daftar Rencana Kerja Jasa',
        'capex' => 'Daftar Rencana Kerja Capex',
        default => 'Daftar Rencana Kerja Barang',
    };
    $searchPlaceholder = match ($workType) {
        'jasa' => 'Cari plant, lingkup jasa, PIC...',
        'capex' => 'Cari plant, program capex, PIC...',
        default => 'Cari plant, unit kerja, PIC...',
    };
    $barangRows = [
        ['Plant 5', 'Kiln Utama (BTA 86 Meter)', 'Auliya Sahara', '1 Set', 'OEM', true, '-'],
        ['Plant 4', 'Raw Mill 4 (Tyre Kiln Pondasi)', 'Budi Santoso', '2 Pcs', 'Fabrikasi', true, '-'],
        ['Plant 4', 'Finish Mill 3', 'Siti Rahma', '4 Pcs', 'Casting', false, 'Menunggu final drawing'],
        ['Plant 5', 'Coal Mill 5', 'Rahmat Hidayat', '1 Unit', 'OEM', true, '-'],
        ['Plant 2', 'Crusher 2', 'Andi Pratama', '3 Set', 'Fabrikasi', true, '-'],
        ['Plant 3', 'Kiln Feed 3', 'Nur Aisyah', '6 Pcs', 'Casting', false, 'Review material'],
        ['Plant 4', 'Clinker Cooler 4', 'Fajar Akbar', '2 Unit', 'OEM', true, '-'],
        ['Plant 5', 'Cement Mill 5', 'Dewi Lestari', '5 Pcs', 'Fabrikasi', true, '-'],
        ['Plant 2', 'Raw Mill 2', 'Irfan Maulana', '1 Set', 'OEM', false, 'Revisi spesifikasi'],
        ['Plant 3', 'Finish Mill 2', 'Rizal Fahmi', '8 Pcs', 'Casting', true, '-'],
        ['Plant 4', 'Bucket Elevator 4', 'Maya Putri', '2 Set', 'Fabrikasi', true, '-'],
        ['Plant 5', 'Preheater Fan 5', 'Arman Yusuf', '1 Unit', 'OEM', false, 'Menunggu quotation'],
        ['Plant 2', 'Belt Conveyor 2', 'Nadia Amelia', '12 Pcs', 'Fabrikasi', true, '-'],
        ['Plant 3', 'Separator 3', 'Agus Salim', '1 Set', 'OEM', true, '-'],
        ['Plant 4', 'Dust Collector 4', 'Wahyu Ramadhan', '10 Pcs', 'Casting', false, 'Sampling material'],
        ['Plant 5', 'ID Fan 5', 'Fitriani', '2 Unit', 'OEM', true, '-'],
        ['Plant 2', 'Hammer Crusher 2', 'Muhammad Ilham', '16 Pcs', 'Casting', true, '-'],
        ['Plant 3', 'Rotary Feeder 3', 'Sri Wahyuni', '3 Set', 'Fabrikasi', false, 'Konfirmasi dimensi'],
        ['Plant 4', 'Hydraulic System 4', 'Reza Kurniawan', '1 Set', 'OEM', true, '-'],
        ['Plant 5', 'Chain Conveyor 5', 'Intan Permata', '9 Pcs', 'Fabrikasi', true, '-'],
        ['Plant 2', 'Packhouse 2', 'Dimas Saputra', '4 Unit', 'OEM', false, 'Verifikasi vendor'],
        ['Plant 3', 'Silo Discharge 3', 'Yuni Kartika', '7 Pcs', 'Casting', true, '-'],
        ['Plant 4', 'Air Slide 4', 'Akbar Firmansyah', '5 Set', 'Fabrikasi', true, '-'],
    ];
    $jasaRows = [
        ['Plant 5', 'Kiln Utama', 'Auliya Sahara', 'Refractory kiln shutdown', 'PT Teknik Prima', '30 Hari', 'Siap', '-'],
        ['Plant 4', 'Finish Mill 4', 'Budi Santoso', 'Alignment girth gear', 'PT Mitra Rotasi', '18 Hari', 'Siap', '-'],
        ['Plant 3', 'Raw Mill 3', 'Siti Rahma', 'Overhaul gearbox utama', 'PT Gearindo', '21 Hari', 'Proses', 'Finalisasi metode kerja'],
        ['Plant 2', 'Crusher 2', 'Rahmat Hidayat', 'Major inspection crusher', 'PT Rekayasa Mesin', '14 Hari', 'Siap', '-'],
        ['Plant 5', 'Coal Mill 5', 'Andi Pratama', 'Balancing dynamic fan', 'PT Presisi Teknik', '10 Hari', 'Proses', 'Menunggu jadwal vendor'],
        ['Plant 4', 'Clinker Cooler 4', 'Nur Aisyah', 'Penggantian cooler plate', 'PT Karya Industri', '20 Hari', 'Siap', '-'],
        ['Plant 3', 'Preheater 3', 'Fajar Akbar', 'Cleaning cyclone dan ducting', 'PT Bersih Plant', '12 Hari', 'Siap', '-'],
        ['Plant 5', 'Cement Mill 5', 'Dewi Lestari', 'NDT shell dan trunnion', 'PT Uji Material', '8 Hari', 'Proses', 'Review penawaran'],
        ['Plant 2', 'Packing Plant 2', 'Irfan Maulana', 'Kalibrasi packer', 'PT Kalibrasi Nusantara', '7 Hari', 'Siap', '-'],
        ['Plant 4', 'ID Fan 4', 'Rizal Fahmi', 'Repair impeller fan', 'PT Fabrikasi Jaya', '16 Hari', 'Siap', '-'],
        ['Plant 3', 'Belt Conveyor 3', 'Maya Putri', 'Hot splicing belt conveyor', 'PT Conveyor Mandiri', '6 Hari', 'Proses', 'Konfirmasi material belt'],
        ['Plant 5', 'Silo 5', 'Arman Yusuf', 'Inspeksi struktur silo', 'PT Struktur Cipta', '9 Hari', 'Siap', '-'],
        ['Plant 2', 'Workshop', 'Nadia Amelia', 'Load test overhead crane', 'PT Angkat Aman', '5 Hari', 'Siap', '-'],
        ['Plant 4', 'Power House', 'Agus Salim', 'Major service generator', 'PT Daya Teknik', '11 Hari', 'Proses', 'Menunggu spare part'],
        ['Plant 3', 'Dust Collector 3', 'Wahyu Ramadhan', 'Performance test bag filter', 'PT Emisi Bersih', '4 Hari', 'Siap', '-'],
    ];
    $capexRows = [
        ['Plant 5', 'Upgrade sistem burner kiln', 'Auliya Sahara', 'Rp 8,50 M', '2027', 'Strategis', 'Disetujui', '-'],
        ['Plant 4', 'Revitalisasi Finish Mill 4', 'Budi Santoso', 'Rp 12,75 M', '2027', 'Strategis', 'Review', 'Validasi business case'],
        ['Plant 3', 'Penggantian main drive Raw Mill', 'Siti Rahma', 'Rp 6,20 M', '2027', 'Replacement', 'Disetujui', '-'],
        ['Plant 2', 'Modernisasi Crusher 2', 'Rahmat Hidayat', 'Rp 4,80 M', '2027', 'Improvement', 'Review', 'Revisi estimasi biaya'],
        ['Plant 5', 'Instalasi online vibration monitoring', 'Andi Pratama', 'Rp 2,35 M', '2027', 'Digitalisasi', 'Disetujui', '-'],
        ['Plant 4', 'Upgrade dedusting system', 'Nur Aisyah', 'Rp 5,10 M', '2027', 'Lingkungan', 'Disetujui', '-'],
        ['Plant 3', 'Penggantian refractory preheater', 'Fajar Akbar', 'Rp 3,65 M', '2027', 'Replacement', 'Review', 'Finalisasi spesifikasi'],
        ['Plant 5', 'Optimasi separator Cement Mill', 'Dewi Lestari', 'Rp 7,40 M', '2028', 'Improvement', 'Disetujui', '-'],
        ['Plant 2', 'Automasi Packing Plant', 'Irfan Maulana', 'Rp 9,25 M', '2028', 'Digitalisasi', 'Review', 'Kajian integrasi sistem'],
        ['Plant 4', 'Pengadaan high efficiency ID Fan', 'Rizal Fahmi', 'Rp 4,95 M', '2027', 'Efisiensi', 'Disetujui', '-'],
        ['Plant 3', 'Perkuatan struktur conveyor', 'Maya Putri', 'Rp 1,85 M', '2027', 'Reliability', 'Review', 'Menunggu hasil inspeksi'],
        ['Plant 5', 'Ekspansi kapasitas silo', 'Arman Yusuf', 'Rp 15,30 M', '2028', 'Strategis', 'Disetujui', '-'],
        ['Plant 2', 'Revitalisasi workshop maintenance', 'Nadia Amelia', 'Rp 3,10 M', '2028', 'Improvement', 'Review', 'Penyusunan desain awal'],
        ['Plant 4', 'Upgrade proteksi Power House', 'Agus Salim', 'Rp 2,70 M', '2027', 'Reliability', 'Disetujui', '-'],
        ['Plant 3', 'Continuous emission monitoring', 'Wahyu Ramadhan', 'Rp 5,85 M', '2027', 'Lingkungan', 'Review', 'Evaluasi vendor'],
    ];
    $ganttRows = match ($workType) {
        'jasa' => [
            ['Refractory Kiln Tonasa 5', '30 Hari', 0, 100, 'blue'],
            ['Alignment Finish Mill Tonasa 4', '18 Hari', 12, 72, 'green'],
            ['Overhaul Raw Mill Tonasa 3', '21 Hari', 30, 62, 'orange'],
            ['Inspection Crusher Tonasa 2', '14 Hari', 48, 46, 'slate'],
        ],
        'capex' => [
            ['Upgrade Burner Kiln Tonasa 5', '30 Hari', 0, 100, 'blue'],
            ['Revitalisasi Finish Mill Tonasa 4', '25 Hari', 8, 86, 'green'],
            ['Main Drive Raw Mill Tonasa 3', '20 Hari', 28, 65, 'orange'],
            ['Modernisasi Crusher Tonasa 2', '16 Hari', 46, 52, 'slate'],
        ],
        default => [
            ['Tonasa 5 (Kiln, RM, CM & Crusher)', '30 Hari', 0, 100, 'blue'],
            ['Tonasa 4 (Finish Mill 420)', '25 Hari', 8, 86, 'green'],
            ['Tonasa 4 (Kiln, RM, CM) & FM 419', '20 Hari', 28, 65, 'orange'],
            ['Tonasa 2 & 3 (Finish Mill 2, 3 & Crusher)', '16 Hari', 46, 52, 'slate'],
        ],
    };
@endphp

<div class="row g-3 mb-4">
    <div class="col-12 col-xl-5">
        <div class="content-card h-100 work-plan-chart-card">
            <div class="work-plan-chart-heading">
                <h2>Kurva S - Progress</h2>
            </div>
            <div class="chart-wrap work-plan-curve"><canvas data-chart="line"></canvas></div>
        </div>
    </div>
    <div class="col-12 col-xl-7">
        <div class="content-card h-100 work-plan-chart-card">
            <div class="work-plan-chart-heading">
                <h2>Gantt Chart - Progress</h2>
                <span>Jadwal Overhaul - 2027</span>
            </div>
            <div class="work-plan-gantt" role="img" aria-label="Gantt chart jadwal overhaul {{ $workTypeLabel ?? $tableTitle }}">
                @foreach ($ganttRows as [$label, $duration, $start, $length, $color])
                    <div class="work-plan-gantt-row">
                        <span class="work-plan-gantt-label">{{ $label }}</span>
                        <span class="work-plan-gantt-track">
                            <span class="work-plan-gantt-bar is-{{ $color }}" style="--bar-start: {{ $start }}%; --bar-length: {{ $length }}%;"></span>
                        </span>
                        <strong class="work-plan-gantt-duration is-{{ $color }}">{{ $duration }}</strong>
                    </div>
                @endforeach
                <div class="work-plan-gantt-axis" aria-hidden="true">
                    <span>Hari 1</span><span>Hari 8</span><span>Hari 15</span><span>Hari 22</span><span>Hari 30</span>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="content-card work-plan-table-card" data-work-plan-table data-per-page="10">
    <div class="work-plan-table-toolbar">
        <div>
            <h2>{{ $tableTitle }}</h2>
        </div>
        <label class="work-plan-search">
            <i class="bi bi-search"></i>
            <input type="search" class="form-control" placeholder="{{ $searchPlaceholder }}" data-work-plan-search>
        </label>
    </div>

    <div class="table-responsive">
        <table class="table align-middle work-plan-table">
            @if ($workType === 'barang')
                <thead>
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Plant</th>
                        <th scope="col">Unit Kerja</th>
                        <th scope="col">PIC ST</th>
                        <th scope="col">Qty yang Disetujui<br>Tim OVH</th>
                        <th scope="col">Kategori<br>(OEM; Fabrikasi, Casting)</th>
                        <th scope="col">Status BOM</th>
                        <th scope="col">Ket</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($barangRows as $index => [$plant, $unitKerja, $pic, $quantity, $category, $bomReady, $note])
                        <tr data-work-plan-row>
                            <td class="work-plan-number">{{ $index + 1 }}</td>
                            <td><strong>{{ $plant }}</strong></td>
                            <td>{{ $unitKerja }}</td>
                            <td>{{ $pic }}</td>
                            <td class="text-center"><strong>{{ $quantity }}</strong></td>
                            <td class="text-center">
                                <span class="work-plan-category is-{{ strtolower($category) }}">{{ strtoupper($category) }}</span>
                            </td>
                            <td class="text-center">
                                @if ($bomReady)
                                    <span class="work-plan-bom is-ready" title="BOM tersedia"><i class="bi bi-check-lg"></i><span>Siap</span></span>
                                @else
                                    <span class="work-plan-bom is-pending" title="BOM belum tersedia"><i class="bi bi-clock"></i><span>Proses</span></span>
                                @endif
                            </td>
                            <td class="work-plan-note">{{ $note }}</td>
                        </tr>
                    @endforeach
                </tbody>
            @elseif ($workType === 'jasa')
                <thead>
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Plant</th>
                        <th scope="col">Unit Kerja</th>
                        <th scope="col">PIC ST</th>
                        <th scope="col">Lingkup Jasa</th>
                        <th scope="col">Vendor</th>
                        <th scope="col">Durasi</th>
                        <th scope="col">Status</th>
                        <th scope="col">Ket</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($jasaRows as $index => [$plant, $unitKerja, $pic, $scope, $vendor, $duration, $status, $note])
                        <tr data-work-plan-row>
                            <td class="work-plan-number">{{ $index + 1 }}</td>
                            <td><strong>{{ $plant }}</strong></td>
                            <td>{{ $unitKerja }}</td>
                            <td>{{ $pic }}</td>
                            <td>{{ $scope }}</td>
                            <td>{{ $vendor }}</td>
                            <td class="text-center"><strong>{{ $duration }}</strong></td>
                            <td class="text-center">
                                <span class="work-plan-bom {{ $status === 'Siap' ? 'is-ready' : 'is-pending' }}">
                                    <i class="bi {{ $status === 'Siap' ? 'bi-check-lg' : 'bi-clock' }}"></i><span>{{ $status }}</span>
                                </span>
                            </td>
                            <td class="work-plan-note">{{ $note }}</td>
                        </tr>
                    @endforeach
                </tbody>
            @else
                <thead>
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Plant</th>
                        <th scope="col">Program Capex</th>
                        <th scope="col">PIC ST</th>
                        <th scope="col">Anggaran</th>
                        <th scope="col">Periode</th>
                        <th scope="col">Kategori</th>
                        <th scope="col">Status</th>
                        <th scope="col">Ket</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($capexRows as $index => [$plant, $program, $pic, $budget, $period, $category, $status, $note])
                        <tr data-work-plan-row>
                            <td class="work-plan-number">{{ $index + 1 }}</td>
                            <td><strong>{{ $plant }}</strong></td>
                            <td>{{ $program }}</td>
                            <td>{{ $pic }}</td>
                            <td class="text-end"><strong>{{ $budget }}</strong></td>
                            <td class="text-center">{{ $period }}</td>
                            <td class="text-center"><span class="work-plan-category">{{ $category }}</span></td>
                            <td class="text-center">
                                <span class="work-plan-bom {{ $status === 'Disetujui' ? 'is-ready' : 'is-pending' }}">
                                    <i class="bi {{ $status === 'Disetujui' ? 'bi-check-lg' : 'bi-clock' }}"></i><span>{{ $status }}</span>
                                </span>
                            </td>
                            <td class="work-plan-note">{{ $note }}</td>
                        </tr>
                    @endforeach
                </tbody>
            @endif
        </table>
    </div>

    <div class="work-plan-empty" data-work-plan-empty hidden>
        <i class="bi bi-search"></i>
        <span>Data yang dicari tidak ditemukan.</span>
    </div>

    <div class="work-plan-pagination-bar">
        <p data-work-plan-summary aria-live="polite"></p>
        <nav class="work-plan-pagination" data-work-plan-pagination aria-label="Pagination {{ $tableTitle }}"></nav>
    </div>
</section>

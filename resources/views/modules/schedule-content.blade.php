@php
    $isBarang = ($workType ?? 'barang') === 'barang';
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
@endphp

@if (! $isBarang)
    <x-page-header :title="$workTypeLabel ?? 'Rencana Kerja'" subtitle="Kurva S, gantt chart, dan action log sementara untuk rancangan frontend.">
        <button class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Tambah Rencana Kerja</button>
    </x-page-header>
@endif

<div class="row g-3 mb-4">
    <div class="col-12 col-xl-5">
        <div class="content-card h-100 {{ $isBarang ? 'work-plan-chart-card' : '' }}">
            @if ($isBarang)
                <div class="work-plan-chart-heading">
                    <h2>Kurva S - Progress</h2>
                </div>
            @else
                <div class="card-heading"><h2>Kurva S</h2><span class="text-muted">Plan vs Actual</span></div>
            @endif
            <div class="chart-wrap {{ $isBarang ? 'work-plan-curve' : '' }}"><canvas data-chart="line"></canvas></div>
        </div>
    </div>
    <div class="col-12 col-xl-7">
        <div class="content-card h-100 {{ $isBarang ? 'work-plan-chart-card' : '' }}">
            @if ($isBarang)
                <div class="work-plan-chart-heading">
                    <h2>Gantt Chart - Progress</h2>
                    <span>Jadwal Overhaul - 2027</span>
                </div>
                <div class="work-plan-gantt" role="img" aria-label="Gantt chart jadwal overhaul Rencana Kerja Barang">
                    @foreach ([
                        ['Tonasa 5 (Kiln, RM, CM & Crusher)', '30 Hari', 0, 100, 'blue'],
                        ['Tonasa 4 (Finish Mill 420)', '25 Hari', 8, 86, 'green'],
                        ['Tonasa 4 (Kiln, RM, CM) & FM 419', '20 Hari', 28, 65, 'orange'],
                        ['Tonasa 2 & 3 (Finish Mill 2, 3 & Crusher)', '16 Hari', 46, 52, 'slate'],
                    ] as [$label, $duration, $start, $length, $color])
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
            @else
                <div class="card-heading"><h2>Gantt Chart</h2><span class="text-muted">Placeholder</span></div>
                <div class="placeholder-panel gantt-placeholder">
                    <img src="{{ asset('assets/images/placeholders/gantt-placeholder.svg') }}" alt="Gantt placeholder">
                </div>
            @endif
        </div>
    </div>
</div>

@if ($isBarang)
    <section class="content-card work-plan-table-card" data-work-plan-table data-per-page="10">
        <div class="work-plan-table-toolbar">
            <div>
                <h2>Daftar Rencana Kerja Barang</h2>
            </div>
            <label class="work-plan-search">
                <i class="bi bi-search"></i>
                <input type="search" class="form-control" placeholder="Cari plant, unit kerja, PIC..." data-work-plan-search>
            </label>
        </div>

        <div class="table-responsive">
            <table class="table align-middle work-plan-table">
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
            </table>
        </div>

        <div class="work-plan-empty" data-work-plan-empty hidden>
            <i class="bi bi-search"></i>
            <span>Data yang dicari tidak ditemukan.</span>
        </div>

        <div class="work-plan-pagination-bar">
            <p data-work-plan-summary aria-live="polite"></p>
            <nav class="work-plan-pagination" data-work-plan-pagination aria-label="Pagination Rencana Kerja Barang"></nav>
        </div>
    </section>
@else
    <div class="content-card">
        <div class="table-responsive">
            <table class="table align-middle ovh-table">
                <thead><tr><th>No</th><th>Action Item</th><th>PIC</th><th>Due Date</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    @foreach ([['Finalisasi scope pekerjaan', 'Andi', '2026-05-10', 'Proses'], ['Review spare critical', 'Sinta', '2026-05-14', 'Open'], ['Close punch list area boiler', 'Rudi', '2026-05-21', 'Terlambat']] as $i => $row)
                        <tr><td>{{ $i + 1 }}</td><td>{{ $row[0] }}</td><td>{{ $row[1] }}</td><td>{{ $row[2] }}</td><td><x-status-badge :status="$row[3]" /></td><td><button class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></button></td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

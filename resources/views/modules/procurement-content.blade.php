@php
    $section = $section ?? 'barang';
    $plantOptions = collect($plantOptions ?? []);
    $yearOptions = collect($yearOptions ?? [(string) now()->year])->prepend((string) ($currentYear ?? now()->year))->unique()->values();
    $selectedYear = (string) request('year', $currentYear ?? now()->year);
    $selectedPlant = (string) request('plant', 'all');
    $sections = [
        'barang' => ['label' => 'Barang', 'route' => 'admin.procurement.barang', 'icon' => 'bi-box-seam'],
        'jasa' => ['label' => 'Jasa', 'route' => 'admin.procurement.jasa', 'icon' => 'bi-person-gear'],
        'capex' => ['label' => 'Capex', 'route' => 'admin.procurement.capex', 'icon' => 'bi-building-gear'],
        'action-log' => ['label' => 'Action Log', 'route' => 'admin.procurement.action-log', 'icon' => 'bi-list-check'],
        'minutes-of-meeting' => ['label' => 'Minutes of Meeting', 'route' => 'admin.procurement.minutes-of-meeting', 'icon' => 'bi-journal-text'],
    ];

    $section = array_key_exists($section, $sections) ? $section : 'barang';
    $active = $sections[$section];

    $datasets = [
        'barang' => [
            'title' => 'Pengadaan Barang',
            'subtitle' => 'Monitoring dummy kebutuhan material, spare part, dan consumable overhaul.',
            'button' => 'Tambah Barang',
            'columns' => ['No', 'PR', 'Item', 'Plant', 'Area', 'Vendor', 'Status', 'Progress', 'Target'],
            'rows' => [
                ['PR-OVH-2601', 'Bearing Kiln Support Roller', 'Tonasa 4', 'Kiln', 'PT Mekar Teknik', 'PO Draft', 58, '08 Jul 2026'],
                ['PR-OVH-2602', 'Refractory Brick SK-34', 'Tonasa 4', 'Burner', 'CV Mitra Api', 'Evaluasi', 42, '10 Jul 2026'],
                ['PR-OVH-2603', 'Filter Bag Coal Mill', 'Tonasa 5', 'Coal Mill', 'PT Prima Filter', 'Delivery', 86, '04 Jul 2026'],
                ['PR-OVH-2604', 'Hydraulic Hose Set', 'Tonasa 4', 'Raw Mill', 'PT Hidro Nusantara', 'Selesai', 100, '28 Jun 2026'],
            ],
        ],
        'jasa' => [
            'title' => 'Pengadaan Jasa',
            'subtitle' => 'Monitoring dummy paket kerja jasa, kontraktor, dan layanan teknis.',
            'button' => 'Tambah Jasa',
            'columns' => ['No', 'Kode', 'Pekerjaan', 'Plant', 'PIC', 'Vendor', 'Status', 'Progress', 'Target'],
            'rows' => [
                ['JS-OVH-2601', 'Alignment Main Drive', 'Tonasa 4', 'Rudi', 'PT Presisi Rotasi', 'Negosiasi', 48, '06 Jul 2026'],
                ['JS-OVH-2602', 'NDT Shell Kiln', 'Tonasa 4', 'Andi', 'PT Inspeksi Prima', 'Ready On Site', 72, '03 Jul 2026'],
                ['JS-OVH-2603', 'Cleaning ESP Area', 'Tonasa 5', 'Sinta', 'CV Karya Bersih', 'Selesai', 100, '26 Jun 2026'],
                ['JS-OVH-2604', 'Scaffolding Raw Mill', 'Tonasa 4', 'Fajar', 'PT Mandiri Akses', 'Dokumen', 35, '07 Jul 2026'],
            ],
        ],
        'capex' => [
            'title' => 'Capex',
            'subtitle' => 'Monitoring dummy kebutuhan capex, estimasi nilai, dan kesiapan approval.',
            'button' => 'Tambah Capex',
            'columns' => ['No', 'Nomor', 'Paket Capex', 'Plant', 'Nilai Estimasi', 'Owner', 'Status', 'Progress', 'Target'],
            'rows' => [
                ['CX-OVH-2601', 'Upgrade MCC Panel Cooler', 'Tonasa 4', 'Rp 1.8 M', 'Electrical', 'Review Teknis', 40, '15 Jul 2026'],
                ['CX-OVH-2602', 'Online Vibration Monitoring', 'Tonasa 5', 'Rp 950 Jt', 'Reliability', 'Approval', 66, '12 Jul 2026'],
                ['CX-OVH-2603', 'Dust Collector Improvement', 'Tonasa 4', 'Rp 2.4 M', 'Process', 'Budget Check', 28, '20 Jul 2026'],
                ['CX-OVH-2604', 'Kiln Scanner Replacement', 'Tonasa 4', 'Rp 1.2 M', 'Instrumentation', 'Selesai', 100, '25 Jun 2026'],
            ],
        ],
        'action-log' => [
            'title' => 'Action Log',
            'subtitle' => 'Tracking dummy tindak lanjut pengadaan dan koordinasi antar fungsi.',
            'button' => 'Tambah Action',
            'columns' => ['No', 'Tanggal', 'Action Item', 'Kategori', 'PIC', 'Due Date', 'Status', 'Progress', 'Catatan'],
            'rows' => [
                ['30 Jun 2026', 'Finalisasi spesifikasi refractory brick', 'Barang', 'Procurement', '02 Jul 2026', 'Open', 35, 'Menunggu revisi teknis'],
                ['29 Jun 2026', 'Konfirmasi mobilisasi NDT shell kiln', 'Jasa', 'QC', '01 Jul 2026', 'On Track', 70, 'Vendor standby H-1'],
                ['28 Jun 2026', 'Validasi nilai capex MCC panel', 'Capex', 'Finance', '05 Jul 2026', 'Review', 45, 'Perlu breakdown BOQ'],
                ['27 Jun 2026', 'Follow up delivery filter bag', 'Barang', 'Warehouse', '30 Jun 2026', 'Selesai', 100, 'Sudah masuk gudang'],
            ],
        ],
        'minutes-of-meeting' => [
            'title' => 'Minutes of Meeting',
            'subtitle' => 'Dokumentasi dummy rapat pengadaan dan keputusan tindak lanjut.',
            'button' => 'Tambah MoM',
            'columns' => ['No', 'Tanggal', 'Agenda', 'Fungsi', 'Peserta', 'Keputusan', 'Status', 'Progress', 'Dokumen'],
            'rows' => [
                ['30 Jun 2026', 'Weekly Procurement Readiness', 'Pengadaan', '12 Orang', 'Prioritas jasa NDT dan refractory', 'Open', 55, 'MOM-OVH-001.pdf'],
                ['26 Jun 2026', 'Capex Review Meeting', 'Capex', '8 Orang', 'MCC panel lanjut approval', 'Review', 65, 'MOM-OVH-002.pdf'],
                ['24 Jun 2026', 'Vendor Clarification', 'Jasa', '6 Orang', 'Scaffolding butuh dokumen tambahan', 'Open', 40, 'MOM-OVH-003.pdf'],
                ['21 Jun 2026', 'Material Delivery Sync', 'Barang', '9 Orang', 'Filter bag dikirim bertahap', 'Selesai', 100, 'MOM-OVH-004.pdf'],
            ],
        ],
    ];

    $data = $datasets[$section];
    $barangCostCards = [
        ['label' => 'Plan', 'value' => '87,6 M', 'tone' => 'blue', 'icon' => 'bi-clipboard-data'],
        ['label' => 'Proses', 'value' => '77,3 M', 'tone' => 'amber', 'icon' => 'bi-arrow-repeat'],
        ['label' => 'PO', 'value' => '75,0 M', 'tone' => 'olive', 'icon' => 'bi-file-earmark-check'],
        ['label' => 'Ready Gudang', 'value' => '0,94 M', 'tone' => 'green-dark', 'icon' => 'bi-box-seam'],
        ['label' => 'Goods Issue', 'value' => '71,7 M', 'tone' => 'green', 'icon' => 'bi-truck'],
    ];
    $barangProcessCards = [
        ['label' => 'Total Item', 'value' => '1250', 'tone' => 'blue', 'icon' => 'bi-collection'],
        ['label' => 'Cancel', 'value' => '563', 'tone' => 'red', 'icon' => 'bi-x-circle'],
        ['label' => 'Proses Pengadaan', 'value' => '11', 'tone' => 'blue', 'icon' => 'bi-cart-check'],
        ['label' => 'PO/Delivery', 'value' => '25', 'tone' => 'green-dark', 'icon' => 'bi-file-earmark-arrow-down'],
        ['label' => 'Ready Gudang', 'value' => '20', 'tone' => 'green-light', 'icon' => 'bi-box2-heart'],
        ['label' => 'Good Issue', 'value' => '631', 'tone' => 'green', 'icon' => 'bi-check2-square'],
    ];
    $summary = [
        ['label' => 'Total Item', 'value' => count($data['rows']), 'icon' => 'bi-collection', 'tone' => 'primary'],
        ['label' => 'Open / Review', 'value' => collect($data['rows'])->filter(fn ($row) => ! in_array($row[5], ['Selesai'], true) && ! in_array($row[6], ['Selesai'], true))->count(), 'icon' => 'bi-hourglass-split', 'tone' => 'warning'],
        ['label' => 'Selesai', 'value' => collect($data['rows'])->filter(fn ($row) => in_array('Selesai', $row, true))->count(), 'icon' => 'bi-check2-circle', 'tone' => 'success'],
        ['label' => 'Progress Rata-rata', 'value' => round(collect($data['rows'])->avg(fn ($row) => (int) $row[6])).'%', 'icon' => 'bi-graph-up-arrow', 'tone' => 'info'],
    ];
@endphp

<x-page-header :title="$data['title']" :subtitle="$data['subtitle']"></x-page-header>

<form method="GET" action="{{ route($active['route']) }}">
    <x-filter-card>
        <div class="col-12 col-xl-5">
            <label class="form-label">Search</label>
            <input type="search" class="form-control" name="search" value="{{ request('search') }}" placeholder="Cari nomor, item, vendor, atau PIC">
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <label class="form-label">Plant</label>
            <select class="form-select" name="plant">
                <option value="all" @selected($selectedPlant === 'all')>Semua Plant</option>
                @foreach ($plantOptions as $plant)
                    <option value="{{ $plant }}" @selected($selectedPlant === (string) $plant)>{{ $plant }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-6 col-xl-2">
            <label class="form-label">Tahun</label>
            <select class="form-select" name="year">
                @foreach ($yearOptions as $year)
                    <option value="{{ $year }}" @selected($selectedYear === (string) $year)>{{ $year }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-6 col-xl-2 d-grid">
            <button class="btn btn-primary procurement-update-btn" type="button">
                <i class="bi bi-arrow-repeat"></i>
                <span>Update Progress</span>
            </button>
        </div>
    </x-filter-card>
</form>

@if ($section === 'barang')
    <section class="procurement-overview" aria-label="Ringkasan pengadaan barang">
        <div class="procurement-kpi-group">
            <h2>Cost Overhaul</h2>
            <div class="procurement-kpi-grid procurement-kpi-grid-cost">
                @foreach ($barangCostCards as $item)
                    <div class="procurement-kpi-card is-{{ $item['tone'] }}">
                        <div class="procurement-kpi-icon"><i class="bi {{ $item['icon'] }}"></i></div>
                        <div class="procurement-kpi-body">
                            <span>{{ $item['label'] }}</span>
                            <strong>{{ $item['value'] }}</strong>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="procurement-overview-lower">
            <div class="procurement-kpi-group">
                <h2>Proses Pengadaan</h2>
                <div class="procurement-kpi-grid procurement-kpi-grid-process">
                    @foreach ($barangProcessCards as $item)
                        <div class="procurement-kpi-card is-{{ $item['tone'] }}">
                            <div class="procurement-kpi-icon"><i class="bi {{ $item['icon'] }}"></i></div>
                            <div class="procurement-kpi-body">
                                <span>{{ $item['label'] }}</span>
                                <strong>{{ $item['value'] }}</strong>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="procurement-kpi-group procurement-budget-group">
                <h2>Pemanfaatan Budget</h2>
                <div class="procurement-budget-card">
                    <span>Budget Utilization</span>
                    <strong>96%</strong>
                    <p>Rp 84,1 M dari plan sudah termanfaatkan</p>
                    <div class="procurement-budget-bar">
                        <span style="width: 96%"></span>
                    </div>
                </div>
            </div>
        </div>
    </section>
@else
    <div class="row g-3 mb-3">
        @foreach ($summary as $item)
            <div class="col-12 col-sm-6 col-xl-3">
                <x-stat-card :title="$item['label']" :value="$item['value']" :icon="$item['icon']" :tone="$item['tone']" subtitle="Data dummy" />
            </div>
        @endforeach
    </div>
@endif

<div class="content-card">
    <div class="card-heading">
        <h2>{{ $active['label'] }}</h2>
        <span class="text-muted small">Dummy data - belum terhubung database</span>
    </div>
    <div class="table-responsive">
        <table class="table align-middle ovh-table procurement-table">
            <thead>
                <tr>
                    @foreach ($data['columns'] as $column)
                        <th>{{ $column }}</th>
                    @endforeach
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data['rows'] as $index => $row)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        @foreach ($row as $cellIndex => $cell)
                            @if ($cellIndex === 5)
                                <td><x-status-badge :status="$cell" /></td>
                            @elseif ($cellIndex === 6)
                                <td>
                                    <div class="progress ovh-progress">
                                        <div class="progress-bar" style="width: {{ $cell }}%">{{ $cell }}%</div>
                                    </div>
                                </td>
                            @else
                                <td>{{ $cell }}</td>
                            @endif
                        @endforeach
                        <td>
                            <div class="d-flex gap-1">
                                <button class="btn btn-sm btn-outline-secondary" title="Lihat"><i class="bi bi-eye"></i></button>
                                <button class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil-square"></i></button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

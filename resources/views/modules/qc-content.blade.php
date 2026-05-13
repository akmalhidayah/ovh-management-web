@php
    $statusLabels = $statusLabels ?? [];
    $summary = $summary ?? ['total' => 0, 'submitted' => 0, 'approved' => 0, 'revision' => 0];
    $charts = $charts ?? [
        'overall' => ['labels' => [], 'data' => []],
        'qc' => ['labels' => [], 'data' => []],
        'commissioning' => ['labels' => [], 'data' => []],
    ];
    $filterOptions = $filterOptions ?? ['years' => collect(), 'plants' => collect(), 'areas' => collect()];
    $filters = $filters ?? ['type' => 'all', 'status' => 'all', 'year' => 'all', 'plant' => 'all', 'area' => 'all', 'search' => ''];
    $statusClasses = [
        'draft' => 'text-bg-secondary',
        'submitted' => 'text-bg-info',
        'approved' => 'text-bg-success',
        'revision' => 'text-bg-warning',
    ];
    $typeTabs = [
        'all' => ['label' => 'Semua', 'icon' => 'bi-grid-3x3-gap'],
        'qc' => ['label' => 'QC', 'icon' => 'bi-shield-check'],
        'commissioning' => ['label' => 'Commissioning', 'icon' => 'bi-tools'],
    ];
@endphp

<div class="page-header">
    <div>
        <h1>Inspection & Commissioning</h1>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-md-6 col-xl-3"><x-stat-card title="Total Submission" :value="$summary['total']" icon="bi-shield-check" tone="primary" /></div>
    <div class="col-12 col-md-6 col-xl-3"><x-stat-card title="Menunggu Review" :value="$summary['submitted']" icon="bi-hourglass-split" tone="info" /></div>
    <div class="col-12 col-md-6 col-xl-3"><x-stat-card title="Disetujui" :value="$summary['approved']" icon="bi-check2-circle" tone="success" /></div>
    <div class="col-12 col-md-6 col-xl-3"><x-stat-card title="Perlu Revisi" :value="$summary['revision']" icon="bi-exclamation-triangle" tone="warning" /></div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-lg-4">
        <div class="content-card inspection-chart-card">
            <div class="card-heading mb-2">
                <span class="badge text-bg-primary">Semua</span>
            </div>
            <div class="inspection-chart-wrap">
                <canvas data-admin-area-chart='@json($charts['overall'])'></canvas>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="content-card inspection-chart-card">
            <div class="card-heading mb-2">
                <span class="badge text-bg-info">QC</span>
            </div>
            <div class="inspection-chart-wrap">
                <canvas data-admin-area-chart='@json($charts['qc'])'></canvas>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="content-card inspection-chart-card">
            <div class="card-heading mb-2">
                <span class="badge text-bg-success">Commissioning</span>
            </div>
            <div class="inspection-chart-wrap">
                <canvas data-admin-area-chart='@json($charts['commissioning'])'></canvas>
            </div>
        </div>
    </div>
</div>

<div class="template-toolbar">
    <div class="template-tabs">
        @foreach ($typeTabs as $type => $tab)
            <a href="{{ route('admin.qc', array_merge(request()->except('page'), ['type' => $type])) }}"
                class="template-tab {{ $filters['type'] === $type ? 'active' : '' }}">
                <i class="bi {{ $tab['icon'] }} me-1"></i>{{ $tab['label'] }}
            </a>
        @endforeach
    </div>
</div>

<form method="GET" action="{{ route('admin.qc') }}">
    <input type="hidden" name="type" value="{{ $filters['type'] }}">
    <x-filter-card>
        <div class="col-12 col-md-6 col-xl-2">
            <label class="form-label">Tahun</label>
            <select class="form-select" name="year">
                <option value="all">Semua Tahun</option>
                @foreach ($filterOptions['years'] as $year)
                    <option value="{{ $year }}" @selected($filters['year'] == $year)>{{ $year }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-6 col-xl-2">
            <label class="form-label">Plant</label>
            <select class="form-select" name="plant">
                <option value="all">Semua Plant</option>
                @foreach ($filterOptions['plants'] as $plant)
                    <option value="{{ $plant }}" @selected($filters['plant'] == $plant)>{{ $plant }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-6 col-xl-2">
            <label class="form-label">Area</label>
            <select class="form-select" name="area">
                <option value="all">Semua Area</option>
                @foreach ($filterOptions['areas'] as $area)
                    <option value="{{ $area }}" @selected($filters['area'] == $area)>{{ $area }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-6 col-xl-2">
            <label class="form-label">Status</label>
            <select class="form-select" name="status">
                <option value="all">Semua Status</option>
                @foreach ($statusLabels as $value => $label)
                    <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <label class="form-label">Cari</label>
            <div class="d-flex gap-2">
                <input type="search" class="form-control" name="search" value="{{ $filters['search'] }}" placeholder="No form / equipment">
                <button class="btn btn-primary" type="submit"><i class="bi bi-funnel"></i></button>
            </div>
        </div>
    </x-filter-card>
</form>

<div class="content-card">
    <div class="table-responsive">
        <table class="table align-middle ovh-table template-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Jenis</th>
                    <th>Tahun</th>
                    <th>Plant</th>
                    <th>Area</th>
                    <th>Equipment</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($submissions as $submission)
                    <tr>
                        <td>{{ $submissions->firstItem() + $loop->index }}</td>
                        <td>
                            <span class="badge {{ $submission->type === 'qc' ? 'text-bg-info' : 'text-bg-success' }}">
                                {{ $submission->type_label }}
                            </span>
                        </td>
                        <td>{{ $submission->year ?: '-' }}</td>
                        <td>{{ $submission->plant ?: '-' }}</td>
                        <td>{{ $submission->area ?: '-' }}</td>
                        <td>
                            <div class="fw-semibold">{{ $submission->equipment ?: '-' }}</div>
                            <div class="template-meta">
                                <span>{{ $submission->form_number ?: '-' }}</span>
                                <span>{{ $submission->submitted_at?->format('d M Y H:i') ?: '-' }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="badge {{ $statusClasses[$submission->status] ?? 'text-bg-secondary' }}">
                                {{ $statusLabels[$submission->status] ?? $submission->status }}
                            </span>
                        </td>
                        <td class="text-end">
                            @if ($submission->pdf_route)
                                <a href="{{ $submission->pdf_route }}" target="_blank" class="btn btn-sm btn-primary">
                                    <i class="bi bi-filetype-pdf me-1"></i>PDF
                                </a>
                            @else
                                <span class="text-muted small">Draft user</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Belum ada submission untuk filter ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $submissions->links() }}
    </div>
</div>

@push('scripts')
    <script>
        document.querySelectorAll('[data-admin-area-chart]').forEach(function (canvas) {
            if (!window.Chart) {
                return;
            }

            const chartData = JSON.parse(canvas.dataset.adminAreaChart || '{"labels":[],"data":[]}');
            new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Persentase per area',
                        data: chartData.data,
                        backgroundColor: '#1d4ed8',
                        borderRadius: 5,
                        maxBarThickness: 30,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const index = context.dataIndex;
                                    const meta = chartData.meta?.[index] || {};
                                    const count = chartData.counts?.[index] ?? meta.count ?? 0;
                                    const plants = (meta.plants || []).join(', ') || '-';
                                    const years = (meta.years || []).join(', ') || '-';

                                    return [
                                        `Persentase: ${context.parsed.y}%`,
                                        `Jumlah: ${count} dari ${chartData.total || 0} data`,
                                        `Plant: ${plants}`,
                                        `Tahun: ${years}`,
                                    ];
                                },
                            },
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                precision: 0,
                                callback: function (value) {
                                    return value + '%';
                                },
                            },
                            grid: { color: '#eef2f7' },
                        },
                        x: {
                            grid: { display: false },
                            ticks: { maxRotation: 0, autoSkip: true },
                        },
                    },
                },
            });
        });
    </script>
@endpush

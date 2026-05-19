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
    $pageHeading = $pageTitle ?? 'QC & Commissioning';
    $filterRoute = $filters['type'] === 'commissioning' ? 'admin.commissioning' : 'admin.qc';
    $visibleCharts = match ($filters['type']) {
        'qc' => [
            ['key' => 'qc', 'label' => 'Quality Control', 'badge' => 'text-bg-info'],
        ],
        'commissioning' => [
            ['key' => 'commissioning', 'label' => 'Commissioning', 'badge' => 'text-bg-success'],
        ],
        default => [
            ['key' => 'overall', 'label' => 'Semua', 'badge' => 'text-bg-primary'],
            ['key' => 'qc', 'label' => 'QC', 'badge' => 'text-bg-info'],
            ['key' => 'commissioning', 'label' => 'Commissioning', 'badge' => 'text-bg-success'],
        ],
    };
@endphp

<div class="page-header">
    <div>
        <h1>{{ $pageHeading }}</h1>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-md-6 col-xl-3"><x-stat-card title="Total Submission" :value="$summary['total']" icon="bi-shield-check" tone="primary" /></div>
    <div class="col-12 col-md-6 col-xl-3"><x-stat-card title="Menunggu Review" :value="$summary['submitted']" icon="bi-hourglass-split" tone="info" /></div>
    <div class="col-12 col-md-6 col-xl-3"><x-stat-card title="Disetujui" :value="$summary['approved']" icon="bi-check2-circle" tone="success" /></div>
    <div class="col-12 col-md-6 col-xl-3"><x-stat-card title="Perlu Revisi" :value="$summary['revision']" icon="bi-exclamation-triangle" tone="warning" /></div>
</div>

<div class="row g-3 mb-4">
    @foreach ($visibleCharts as $chart)
        <div class="col-12 {{ count($visibleCharts) === 1 ? '' : 'col-lg-4' }}">
            <div class="content-card inspection-chart-card">
                <div class="card-heading mb-2">
                    <span class="badge {{ $chart['badge'] }}">{{ $chart['label'] }}</span>
                </div>
                <div class="inspection-chart-wrap">
                    <canvas data-admin-area-chart='@json($charts[$chart['key']])'></canvas>
                </div>
            </div>
        </div>
    @endforeach
</div>

<form method="GET" action="{{ route($filterRoute) }}">
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
        <table class="table align-middle ovh-table admin-submission-table">
            <colgroup>
                <col class="admin-submission-col-no">
                <col class="admin-submission-col-type">
                <col class="admin-submission-col-year">
                <col class="admin-submission-col-plant">
                <col class="admin-submission-col-area">
                <col>
                <col class="admin-submission-col-status">
                <col class="admin-submission-col-action">
            </colgroup>
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
                            <div class="admin-submission-equipment">{{ $submission->equipment ?: '-' }}</div>
                            <div class="admin-submission-meta">
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
                            <div class="d-inline-flex align-items-center gap-2">
                                @if ($submission->model->approvalFlow)
                                    <button type="button" class="btn btn-sm btn-outline-primary admin-inspection-icon-btn" data-bs-toggle="modal" data-bs-target="#adminApprovalProgressModal{{ $submission->type }}{{ $submission->model->id }}" title="Detail Approval" aria-label="Detail Approval">
                                        <i class="bi bi-list-check"></i>
                                    </button>
                                @endif

                                @if ($submission->pdf_route)
                                    <a href="{{ $submission->pdf_route }}" target="_blank" class="btn btn-sm btn-primary admin-inspection-icon-btn" title="PDF" aria-label="PDF">
                                        <i class="bi bi-filetype-pdf"></i>
                                    </a>
                                @else
                                    <span class="text-muted small">Draft user</span>
                                @endif
                            </div>
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

@foreach ($submissions as $submission)
    @php
        $activeApprovalStep = $submission->model->approvalFlow?->steps->firstWhere('status', 'active');
        $adminCopyApprovalLinkUrl = $submission->status === 'pending_approval' && $activeApprovalStep
            ? ($submission->type === 'qc'
                ? route('admin.qc.submissions.approval-link', $submission->model)
                : route('admin.commissioning.submissions.approval-link', $submission->model))
            : null;
    @endphp
    @include('approvals._progress', [
        'submission' => $submission->model,
        'modalId' => 'adminApprovalProgressModal'.$submission->type.$submission->model->id,
        'copyApprovalLinkUrl' => $adminCopyApprovalLinkUrl,
    ])
@endforeach

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

@push('styles')
    <style>
        .admin-submission-table {
            min-width: 760px;
            margin-bottom: 0;
            font-size: .82rem;
        }

        .admin-submission-table thead th {
            padding: .72rem .7rem;
            font-size: .7rem;
            letter-spacing: 0;
        }

        .admin-submission-table tbody td {
            padding: .72rem .7rem;
            border-bottom-color: #e8edf4;
        }

        .admin-submission-col-no { width: 42px; }
        .admin-submission-col-type { width: 112px; }
        .admin-submission-col-year { width: 62px; }
        .admin-submission-col-plant { width: 104px; }
        .admin-submission-col-area { width: 112px; }
        .admin-submission-col-status { width: 138px; }
        .admin-submission-col-action { width: 88px; }

        .admin-submission-table .badge {
            padding: .36rem .56rem;
            border-radius: .48rem;
            font-size: .68rem;
            font-weight: 750;
            letter-spacing: 0;
        }

        .admin-submission-equipment {
            color: #172033;
            font-size: .89rem;
            font-weight: 750;
            line-height: 1.2;
        }

        .admin-submission-meta {
            display: flex;
            flex-wrap: wrap;
            gap: .2rem .55rem;
            margin-top: .22rem;
            color: #64748b;
            font-size: .72rem;
            line-height: 1.35;
        }

        .admin-submission-table td:not(:nth-child(6)) {
            white-space: nowrap;
        }

        .admin-inspection-icon-btn {
            width: 1.95rem;
            height: 1.95rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }

        @media (max-width: 1199.98px) {
            .admin-submission-table {
                min-width: 720px;
            }

            .admin-submission-col-plant { width: 96px; }
            .admin-submission-col-area { width: 96px; }
            .admin-submission-col-status { width: 126px; }
        }
    </style>
@endpush

@include('approvals._copy-link-script')

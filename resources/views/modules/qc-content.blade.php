@php
    $statusLabels = $statusLabels ?? [];
    $inspectionMetrics = $inspectionMetrics ?? ($qcMetrics ?? null);
    $filterOptions = $filterOptions ?? ['years' => collect(), 'plants' => collect()];
    $filters = $filters ?? ['type' => 'all', 'year' => 'all', 'plant' => 'all', 'search' => ''];
    $statusClasses = [
        'draft' => 'text-bg-secondary',
        'submitted' => 'text-bg-info',
        'pending_approval' => 'text-bg-info',
        'approved' => 'text-bg-success',
        'revision' => 'text-bg-warning',
        'revision_required' => 'text-bg-warning',
        'rejected' => 'text-bg-danger',
        'cancelled' => 'text-bg-secondary',
    ];
    $filterRoute = $filters['type'] === 'commissioning' ? 'admin.commissioning' : 'admin.qc';
    $formatPercentage = fn ($value) => rtrim(rtrim(number_format((float) $value, 1, ',', '.'), '0'), ',');
    $metricsLabel = $inspectionMetrics['label'] ?? ($filters['type'] === 'commissioning' ? 'Commissioning' : 'QC');
@endphp

<form method="GET" action="{{ route($filterRoute) }}">
    <x-filter-card>
        <div class="col-12 col-md-6 col-xl-3">
            <label class="form-label">Tahun</label>
            <select class="form-select" name="year">
                <option value="all">Semua Tahun</option>
                @foreach ($filterOptions['years'] as $year)
                    <option value="{{ $year }}" @selected($filters['year'] == $year)>{{ $year }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <label class="form-label">Plant</label>
            <select class="form-select" name="plant">
                <option value="all">Semua Plant</option>
                @foreach ($filterOptions['plants'] as $plant)
                    <option value="{{ $plant }}" @selected($filters['plant'] == $plant)>{{ $plant }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-xl-6">
            <label class="form-label">Cari</label>
            <div class="d-flex gap-2">
                <input type="search" class="form-control" name="search" value="{{ $filters['search'] }}" placeholder="No form / equipment">
                <button class="btn btn-primary" type="submit"><i class="bi bi-funnel"></i></button>
            </div>
        </div>
    </x-filter-card>
</form>

@if ($inspectionMetrics)
    <div class="qc-progress-dashboard">
        <div class="qc-progress-card qc-progress-card-total">
            <div>
                <div class="qc-progress-card-title">Equipment</div>
                <div class="qc-progress-card-value">{{ number_format($inspectionMetrics['cards']['total'], 0, ',', '.') }}</div>
            </div>
            <i class="bi bi-gear-wide-connected"></i>
        </div>
        <div class="qc-progress-card qc-progress-card-process">
            <div>
                <div class="qc-progress-card-title">Equipment Process</div>
                <div class="qc-progress-card-value">{{ number_format($inspectionMetrics['cards']['process'], 0, ',', '.') }}</div>
            </div>
            <i class="bi bi-clipboard2-check"></i>
        </div>
        <div class="qc-progress-card qc-progress-card-ongoing">
            <div>
                <div class="qc-progress-card-title">Equipment On Going</div>
                <div class="qc-progress-card-value">{{ number_format($inspectionMetrics['cards']['ongoing'], 0, ',', '.') }}</div>
            </div>
            <i class="bi bi-pencil-square"></i>
        </div>
        <div class="qc-progress-card qc-progress-card-percentage">
            <div>
                <div class="qc-progress-card-title">Persentase</div>
                <div class="qc-progress-card-value">{{ $formatPercentage($inspectionMetrics['cards']['percentage']) }}%</div>
            </div>
            <i class="bi bi-graph-up-arrow"></i>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-6">
            <div class="content-card qc-area-detail-card">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle qc-area-progress-table">
                        <thead>
                            <tr>
                                <th>Area</th>
                                <th>Equipment</th>
                                <th>Ongoing Equipment Testing</th>
                                <th>Equipment Process</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($inspectionMetrics['areaRows'] as $row)
                                <tr>
                                    <td>{{ $row['area'] }}</td>
                                    <td>{{ number_format($row['equipment'], 0, ',', '.') }}</td>
                                    <td>{{ number_format($row['ongoing'], 0, ',', '.') }}</td>
                                    <td>{{ number_format($row['process'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Belum ada master data equipment {{ $metricsLabel }} untuk filter ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($inspectionMetrics['areaRows']->isNotEmpty())
                            <tfoot>
                                <tr>
                                    <th>Total</th>
                                    <th>{{ number_format($inspectionMetrics['areaRows']->sum('equipment'), 0, ',', '.') }}</th>
                                    <th>{{ number_format($inspectionMetrics['areaRows']->sum('ongoing'), 0, ',', '.') }}</th>
                                    <th>{{ number_format($inspectionMetrics['areaRows']->sum('process'), 0, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-6">
            <div class="content-card qc-area-chart-card">
                <div class="qc-area-chart-wrap">
                    <canvas data-qc-area-progress-chart='@json($inspectionMetrics['chart'])'></canvas>
                </div>
            </div>
        </div>
    </div>
@endif

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

@if ($inspectionMetrics)
    @push('scripts')
        <script>
            document.querySelectorAll('[data-qc-area-progress-chart]').forEach(function (canvas) {
                if (!window.Chart) {
                    return;
                }

                const chartData = JSON.parse(canvas.dataset.qcAreaProgressChart || '{"labels":[],"data":[]}');
                new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            label: 'Progress',
                            data: chartData.data,
                            backgroundColor: '#3b82f6',
                            borderRadius: 4,
                            maxBarThickness: 42,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                align: 'start',
                                labels: {
                                    boxWidth: 28,
                                    boxHeight: 14,
                                    color: '#334155',
                                    font: { size: 13 },
                                },
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        return `Progress: ${context.parsed.y}%`;
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
                                grid: { color: '#d9dee8' },
                            },
                            x: {
                                grid: { display: false },
                                ticks: {
                                    color: '#334155',
                                    maxRotation: 35,
                                    minRotation: 35,
                                },
                            },
                        },
                    },
                });
            });
        </script>
    @endpush
@endif

@push('styles')
    <style>
        .qc-progress-dashboard {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1rem;
            margin: 1.25rem 0 1rem;
        }

        .qc-progress-card {
            position: relative;
            min-height: 118px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            overflow: hidden;
            padding: 1rem 1.2rem;
            border: 1px solid;
            border-radius: .55rem;
            background: #ffffff;
            box-shadow: 0 .25rem .7rem rgba(15, 23, 42, .12);
        }

        .qc-progress-card-title {
            max-width: 11rem;
            color: #3343a5;
            font-size: 1rem;
            font-weight: 800;
            line-height: 1.1;
            text-transform: uppercase;
        }

        .qc-progress-card-value {
            margin-top: .7rem;
            color: #3343a5;
            font-size: clamp(2rem, 3.1vw, 3rem);
            font-weight: 500;
            line-height: 1;
            text-align: right;
        }

        .qc-progress-card > i {
            flex: 0 0 auto;
            color: currentColor;
            font-size: 4rem;
            line-height: 1;
            opacity: .42;
        }

        .qc-progress-card-total {
            border-color: #3343a5;
            color: #3343a5;
        }

        .qc-progress-card-process {
            border-color: #2f8c3c;
            color: #2f8c3c;
        }

        .qc-progress-card-process .qc-progress-card-title,
        .qc-progress-card-process .qc-progress-card-value {
            color: #2f8c3c;
        }

        .qc-progress-card-ongoing {
            border-color: #b91c1c;
            color: #b91c1c;
        }

        .qc-progress-card-ongoing .qc-progress-card-title,
        .qc-progress-card-ongoing .qc-progress-card-value {
            color: #b91c1c;
        }

        .qc-progress-card-percentage {
            border-color: #94105d;
            color: #94105d;
        }

        .qc-progress-card-percentage .qc-progress-card-title,
        .qc-progress-card-percentage .qc-progress-card-value {
            color: #94105d;
        }

        .qc-area-detail-card,
        .qc-area-chart-card {
            height: 100%;
        }

        .qc-area-chart-card {
            background: #f3f4f6;
        }

        .qc-area-chart-wrap {
            height: 255px;
        }

        .qc-area-progress-table {
            width: 100%;
            min-width: 0;
            table-layout: fixed;
            margin-bottom: 0;
            color: #111827;
            font-size: .8rem;
        }

        .qc-area-progress-table th {
            background: #3f78ca;
            color: #ffffff;
            font-size: .7rem;
            font-weight: 800;
            letter-spacing: 0;
            text-transform: uppercase;
            line-height: 1.15;
            text-align: center;
            vertical-align: middle;
            white-space: normal;
            overflow-wrap: anywhere;
        }

        .qc-area-progress-table td,
        .qc-area-progress-table th {
            padding: .28rem .38rem;
            border-color: #1f2937;
        }

        .qc-area-progress-table th:nth-child(1),
        .qc-area-progress-table td:nth-child(1) {
            width: 24%;
        }

        .qc-area-progress-table th:nth-child(2),
        .qc-area-progress-table td:nth-child(2) {
            width: 17%;
        }

        .qc-area-progress-table th:nth-child(3),
        .qc-area-progress-table td:nth-child(3) {
            width: 32%;
        }

        .qc-area-progress-table th:nth-child(4),
        .qc-area-progress-table td:nth-child(4) {
            width: 27%;
        }

        .qc-area-progress-table td:nth-child(n+2),
        .qc-area-progress-table tfoot th:nth-child(n+2) {
            text-align: center;
        }

        .qc-area-progress-table tfoot th {
            background: #bdd7ee;
            color: #111827;
        }

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
            .qc-progress-dashboard {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .admin-submission-table {
                min-width: 720px;
            }

            .admin-submission-col-plant { width: 96px; }
            .admin-submission-col-area { width: 96px; }
            .admin-submission-col-status { width: 126px; }
        }

        @media (max-width: 575.98px) {
            .qc-progress-dashboard {
                grid-template-columns: 1fr;
            }

            .qc-progress-card {
                min-height: 104px;
            }

            .qc-progress-card > i {
                font-size: 3rem;
            }
        }
    </style>
@endpush

@include('approvals._copy-link-script')

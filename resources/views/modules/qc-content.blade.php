@php
    $statusLabels = $statusLabels ?? [];
    $inspectionMetrics = $inspectionMetrics ?? ($qcMetrics ?? null);
    $filterOptions = $filterOptions ?? ['years' => collect(), 'plants' => collect(), 'areas' => collect(), 'approvalProgress' => collect()];
    $filters = $filters ?? ['type' => 'all', 'year' => 'all', 'plant' => 'all', 'area' => 'all', 'work_status' => 'all', 'approval_progress' => 'all', 'sort' => 'default', 'search' => ''];
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
    $supportsRemarks = in_array(($filters['type'] ?? null), ['qc', 'commissioning'], true);
    $isAdminApproval = auth()->user()?->isAdminApproval();
    $canDeleteInspectionSubmission = ! $isAdminApproval;
    $canUpdateInspectionStatus = ! $isAdminApproval;
@endphp

<form method="GET" action="{{ route($filterRoute) }}">
    <x-filter-card>
        <input type="hidden" name="search" value="{{ $filters['search'] }}">
        <input type="hidden" name="sort" value="{{ $filters['sort'] ?? 'default' }}">
        <input type="hidden" name="work_status" value="{{ $filters['work_status'] ?? 'all' }}">
        <input type="hidden" name="approval_progress" value="{{ $filters['approval_progress'] ?? 'all' }}">
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
        <div class="col-12 col-md-6 col-xl-3">
            <label class="form-label">Area</label>
            <select class="form-select" name="area">
                <option value="all">Semua Area</option>
                @foreach ($filterOptions['areas'] as $area)
                    <option value="{{ $area }}" @selected(($filters['area'] ?? 'all') == $area)>{{ $area }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <label class="form-label">&nbsp;</label>
            <button class="btn btn-primary w-100" type="submit">
                <i class="bi bi-funnel"></i>
                Filter
            </button>
        </div>
    </x-filter-card>
</form>

@if ($inspectionMetrics)
    <div class="qc-progress-dashboard @if ($supportsRemarks) has-remarks-metric @endif">
        <div class="qc-progress-card qc-progress-card-total">
            <div class="qc-progress-card-icon"><i class="bi bi-database"></i></div>
            <div class="min-w-0">
                <div class="qc-progress-card-title">Equipment</div>
                <div class="qc-progress-card-value" data-count-value="{{ $inspectionMetrics['cards']['total'] }}" data-count-decimals="0">{{ number_format($inspectionMetrics['cards']['total'], 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="qc-progress-card qc-progress-card-process">
            <div class="qc-progress-card-icon"><i class="bi bi-check2-circle"></i></div>
            <div class="min-w-0">
                <div class="qc-progress-card-title">Equipment Complete</div>
                <div class="qc-progress-card-value" data-count-value="{{ $inspectionMetrics['cards']['process'] }}" data-count-decimals="0">{{ number_format($inspectionMetrics['cards']['process'], 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="qc-progress-card qc-progress-card-ongoing">
            <div class="qc-progress-card-icon"><i class="bi bi-hourglass-split"></i></div>
            <div class="min-w-0">
                <div class="qc-progress-card-title">Equipment On Going</div>
                <div class="qc-progress-card-value" data-count-value="{{ $inspectionMetrics['cards']['ongoing'] }}" data-count-decimals="0">{{ number_format($inspectionMetrics['cards']['ongoing'], 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="qc-progress-card qc-progress-card-percentage">
            <div class="qc-progress-card-icon"><i class="bi bi-percent"></i></div>
            <div class="min-w-0">
                <div class="qc-progress-card-title">Persentase</div>
                <div class="qc-progress-card-value" data-count-value="{{ $inspectionMetrics['cards']['percentage'] }}" data-count-decimals="1" data-count-trim="true" data-count-suffix="%">{{ $formatPercentage($inspectionMetrics['cards']['percentage']) }}%</div>
            </div>
        </div>
        @if ($supportsRemarks)
            <div class="qc-progress-card qc-progress-card-remarks">
                <div class="qc-progress-card-icon"><i class="bi bi-chat-left-text"></i></div>
                <div class="min-w-0">
                    <div class="qc-progress-card-title">Form Dengan Remarks</div>
                    <div class="qc-progress-card-value" data-count-value="{{ $inspectionMetrics['remarkForms'] ?? 0 }}" data-count-decimals="0">{{ number_format($inspectionMetrics['remarkForms'] ?? 0, 0, ',', '.') }}</div>
                </div>
            </div>
        @endif
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
                                <th>Equipment Complete</th>
                                <th>Ongoing Equipment Testing</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($inspectionMetrics['areaRows'] as $row)
                                <tr>
                                    <td>{{ $row['area'] }}</td>
                                    <td>{{ number_format($row['equipment'], 0, ',', '.') }}</td>
                                    <td>{{ number_format($row['process'], 0, ',', '.') }}</td>
                                    <td>{{ number_format($row['ongoing'], 0, ',', '.') }}</td>
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
                                    <th>{{ number_format($inspectionMetrics['areaRows']->sum('process'), 0, ',', '.') }}</th>
                                    <th>{{ number_format($inspectionMetrics['areaRows']->sum('ongoing'), 0, ',', '.') }}</th>
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

<form method="GET" action="{{ route($filterRoute) }}" class="admin-table-filter-form">
    <x-filter-card>
        <input type="hidden" name="year" value="{{ $filters['year'] }}">
        <input type="hidden" name="plant" value="{{ $filters['plant'] }}">
        <input type="hidden" name="area" value="{{ $filters['area'] ?? 'all' }}">
        <div class="col-12 col-md-6 col-xl-2">
            <label class="form-label">Status Tabel</label>
            <select class="form-select" name="work_status">
                <option value="all" @selected(($filters['work_status'] ?? 'all') === 'all')>Semua Status</option>
                <option value="close" @selected(($filters['work_status'] ?? 'all') === 'close')>Close</option>
                <option value="ongoing" @selected(($filters['work_status'] ?? 'all') === 'ongoing')>On Going</option>
            </select>
        </div>
        <div class="col-12 col-md-6 col-xl-2">
            <label class="form-label">Approval Tabel</label>
            <select class="form-select" name="approval_progress">
                <option value="all" @selected(($filters['approval_progress'] ?? 'all') === 'all')>Semua Approval</option>
                <option value="none" @selected(($filters['approval_progress'] ?? 'all') === 'none')>Belum submit</option>
                @foreach ($filterOptions['approvalProgress'] as $approvalProgress)
                    <option value="{{ $approvalProgress }}" @selected(($filters['approval_progress'] ?? 'all') === $approvalProgress)>TTD {{ $approvalProgress }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-6 col-xl-2">
            <label class="form-label">Urutkan Tabel</label>
            <select class="form-select" name="sort">
                <option value="default" @selected(($filters['sort'] ?? 'default') === 'default')>Default</option>
                <option value="name_asc" @selected(($filters['sort'] ?? 'default') === 'name_asc')>Nama A-Z</option>
                <option value="name_desc" @selected(($filters['sort'] ?? 'default') === 'name_desc')>Nama Z-A</option>
                <option value="area_asc" @selected(($filters['sort'] ?? 'default') === 'area_asc')>Area A-Z</option>
                <option value="area_desc" @selected(($filters['sort'] ?? 'default') === 'area_desc')>Area Z-A</option>
            </select>
        </div>
        <div class="col-12 col-xl-6">
            <label class="form-label">Cari Tabel</label>
            <div class="d-flex gap-2">
                <input type="search" class="form-control" name="search" value="{{ $filters['search'] }}" placeholder="Form / equipment / area">
                <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
            </div>
        </div>
    </x-filter-card>
</form>

<div class="content-card admin-table-card">
    <div class="table-responsive">
        <table class="table align-middle ovh-table admin-submission-table has-location-column">
            <colgroup>
                <col class="admin-submission-col-no">
                <col class="admin-submission-col-type">
                <col class="admin-submission-col-year">
                <col class="admin-submission-col-location">
                <col>
                <col class="admin-submission-col-status">
                <col class="admin-submission-col-action">
            </colgroup>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Jenis</th>
                    <th>Tahun</th>
                    <th>Lokasi</th>
                    <th>Equipment</th>
                    <th>Approval</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($submissions as $submission)
                    <tr>
                        <td>{{ $submissions->firstItem() + $loop->index }}</td>
                        <td>
                            <div class="admin-submission-actor">
                                <span class="admin-submission-avatar" aria-hidden="true">
                                    @if (! empty($submission->user_photo_url))
                                        <img src="{{ $submission->user_photo_url }}" alt="">
                                    @else
                                        {{ $submission->user_initials ?? 'U' }}
                                    @endif
                                </span>
                                <span class="admin-submission-actor-text">
                                    <span class="badge {{ $submission->type === 'qc' ? 'text-bg-info' : 'text-bg-success' }}">
                                        {{ $submission->type_label }}
                                    </span>
                                    <span class="admin-submission-actor-name">{{ $submission->user_name ?: 'Belum ada submitter' }}</span>
                                </span>
                            </div>
                        </td>
                        <td>{{ $submission->year ?: '-' }}</td>
                        <td>
                            <div class="admin-submission-location">{{ $submission->plant ?: '-' }}</div>
                            <div class="admin-submission-location-meta">{{ $submission->area ?: '-' }}</div>
                        </td>
                        <td>
                            <div class="admin-submission-equipment-row">
                                <div class="admin-submission-equipment">{{ $submission->equipment ?: '-' }}</div>
                                @if (($submission->inspection_status_update_url ?? null) && $canUpdateInspectionStatus)
                                    <select class="admin-work-status-select @if (($submission->work_status ?? null) === 'close') admin-work-status-close @elseif (($submission->work_status ?? null) === 'ongoing') admin-work-status-ongoing @endif"
                                            data-inspection-status-select
                                            data-update-url="{{ $submission->inspection_status_update_url }}"
                                            data-has-digital-form="{{ $submission->form_number ? 'true' : 'false' }}"
                                            aria-label="Status equipment">
                                        <option value="" @selected(! in_array($submission->work_status ?? null, ['close', 'ongoing'], true))>Pilih Status</option>
                                        <option value="close" @selected(($submission->work_status ?? null) === 'close')>Close</option>
                                        <option value="ongoing" @selected(($submission->work_status ?? null) === 'ongoing')>On Going</option>
                                    </select>
                                @elseif (($submission->work_status ?? null) === 'close')
                                    <span class="admin-work-status-badge admin-work-status-close">Close</span>
                                @elseif (($submission->work_status ?? null) === 'ongoing')
                                    <span class="admin-work-status-badge admin-work-status-ongoing">On Going</span>
                                @endif
                            </div>
                            <div class="admin-submission-meta">
                                @if ($submission->form_number)
                                    <span>{{ $submission->form_number }}</span>
                                    <span>{{ $submission->submitted_at?->format('d M Y H:i') ?: '-' }}</span>
                                @else
                                    <span>{{ $submission->functional_location ?? '-' }}</span>
                                    <span>{{ $submission->equipment_no ?? 'ID equipment belum ada' }}</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            @php
                                $approvalFlow = $submission->model?->approvalFlow;
                                $approvalSteps = $approvalFlow?->steps ?? collect();
                                $activeApprovalStep = $approvalSteps->firstWhere('status', 'active');
                                $approvedApprovalSteps = (int) ($submission->approval_approved_count ?? $approvalSteps->where('status', 'approved')->count());
                                $approvalTotalSteps = (int) ($submission->approval_total_count ?? $approvalSteps->count());
                                $approvalModalId = $submission->model ? 'adminApprovalProgressModal'.$submission->type.$submission->model->id : null;
                                $approvalStatusLabel = $statusLabels[$submission->status] ?? $submission->status;
                                $activeApprovalStepLabel = $activeApprovalStep?->label;
                                $approvalProgressClass = $submission->approval_progress_class ?? 'admin-approval-progress-none';
                                $areaOwnerSourceLabel = trim((string) data_get($submission->model, 'approval_data.unit_kerja.label', ''));

                                if ($areaOwnerSourceLabel === '') {
                                    $areaOwnerSourceLabel = trim((string) data_get($submission->model, 'approval_data.approved_by_unit_kerja.label', ''));
                                }

                                if ($areaOwnerSourceLabel === '') {
                                    $areaOwnerSourceLabel = trim((string) data_get($submission->model, 'header_data.unit_kerja', ''));
                                }

                                if ($areaOwnerSourceLabel === '') {
                                    $areaOwnerSourceLabel = trim((string) data_get($submission->model, 'general_info.unit_kerja', ''));
                                }

                                if (
                                    $activeApprovalStepLabel
                                    && $areaOwnerSourceLabel !== ''
                                    && (
                                        \App\Support\AreaOwnerLabel::isPlaceholder(trim((string) $activeApprovalStepLabel))
                                        || Str::upper(trim((string) $activeApprovalStepLabel)) === Str::upper($areaOwnerSourceLabel)
                                    )
                                ) {
                                    $activeApprovalStepLabel = \App\Support\AreaOwnerLabel::approvalLabel($areaOwnerSourceLabel);
                                }

                                $approvalStatusTitle = $activeApprovalStep
                                    ? 'Aktif: '.$activeApprovalStepLabel
                                    : ($approvalFlow ? $approvalStatusLabel : null);
                            @endphp

                            @if ($submission->status && $submission->status !== 'draft' && $approvalFlow && $approvalModalId)
                                <button
                                    type="button"
                                    class="admin-approval-status-trigger {{ $activeApprovalStep ? 'is-active' : 'is-'.$submission->status }} {{ $approvalProgressClass }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#{{ $approvalModalId }}"
                                    title="Detail Approval: {{ $approvalStatusTitle }}"
                                    aria-label="Detail Approval {{ $approvalStatusTitle }}"
                                >
                                    <span class="admin-approval-status-main">
                                        {{ $activeApprovalStep ? 'Aktif: '.$activeApprovalStepLabel : $approvalStatusLabel }}
                                    </span>
                                    @if ($approvalTotalSteps > 0)
                                        <span class="admin-approval-status-meta">TTD {{ $approvedApprovalSteps }}/{{ $approvalTotalSteps }}</span>
                                    @endif
                                </button>
                            @elseif ($submission->status && $submission->status !== 'draft')
                                <span class="admin-approval-status-trigger is-{{ $submission->status }} {{ $approvalProgressClass }}">
                                    <span class="admin-approval-status-main">{{ $approvalStatusLabel }}</span>
                                    @if ($approvalTotalSteps > 0)
                                        <span class="admin-approval-status-meta">TTD {{ $approvedApprovalSteps }}/{{ $approvalTotalSteps }}</span>
                                    @endif
                                </span>
                            @else
                                <span class="admin-approval-status-trigger admin-approval-progress-none">
                                    <span class="admin-approval-status-main">Belum submit</span>
                                </span>
                            @endif
                        </td>
                        <td class="text-end">
                            @php
                                $remarks = collect($submission->remarks ?? []);
                                $remarksCount = (int) ($submission->remarks_count ?? $remarks->count());
                                $remarksModalId = $submission->model ? 'adminRemarksModal'.$submission->type.$submission->model->id : null;
                                $canResetInspectionStatus = $canUpdateInspectionStatus
                                    && $submission->type === 'commissioning'
                                    && ! $submission->model
                                    && ($submission->inspection_status_update_url ?? null)
                                    && in_array($submission->work_status ?? null, ['close', 'ongoing'], true);
                            @endphp
                            <div class="d-inline-flex align-items-center gap-2">
                                @if ($supportsRemarks && $remarksCount > 0 && $remarksModalId)
                                    <button type="button" class="admin-remarks-card" data-bs-toggle="modal" data-bs-target="#{{ $remarksModalId }}" title="Remarks" aria-label="Remarks">
                                        <i class="bi bi-exclamation-lg" aria-hidden="true"></i>
                                        <span class="admin-remarks-count">{{ $remarksCount }}</span>
                                    </button>
                                @endif

                                @if ($submission->pdf_route)
                                    <a href="{{ $submission->pdf_route }}" target="_blank" class="btn btn-sm btn-primary admin-inspection-icon-btn" title="PDF" aria-label="PDF">
                                        <i class="bi bi-filetype-pdf"></i>
                                    </a>
                                @elseif (! $canResetInspectionStatus)
                                    <span class="text-muted small">-</span>
                                @endif

                                @if ($canResetInspectionStatus)
                                    <button type="button"
                                            class="btn btn-sm btn-outline-warning admin-inspection-icon-btn"
                                            data-reset-inspection-status-button
                                            data-update-url="{{ $submission->inspection_status_update_url }}"
                                            data-equipment-label="{{ $submission->equipment ?: 'equipment ini' }}"
                                            title="Reset Status Equipment"
                                            aria-label="Reset Status Equipment">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                @endif

                                @if ($canDeleteInspectionSubmission && $submission->model)
                                    <form method="POST"
                                          action="{{ $submission->type === 'qc' ? route('admin.qc.submissions.destroy', $submission->model) : route('admin.commissioning.submissions.destroy', $submission->model) }}"
                                          class="d-inline"
                                          data-admin-delete-submission-form
                                          data-delete-label="{{ $submission->form_number ?: $submission->equipment }}"
                                          data-restore-url="{{ $submission->type === 'qc' ? route('admin.qc.submissions.restore-draft', $submission->model) : route('admin.commissioning.submissions.restore-draft', $submission->model) }}"
                                          data-can-restore="{{ $submission->status && $submission->status !== 'draft' ? 'true' : 'false' }}"
                                          data-submission-kind="{{ $submission->type_label }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger admin-inspection-icon-btn" title="Kelola Submission" aria-label="Kelola Submission">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            {{ $filters['type'] === 'commissioning' ? 'Belum ada master data equipment untuk filter ini.' : 'Belum ada submission untuk filter ini.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $submissions->links() }}
    </div>
</div>

@if ($supportsRemarks)
    @foreach ($submissions->getCollection()->filter(fn ($submission) => ($submission->remarks_count ?? 0) > 0 && $submission->model) as $submission)
        @php
            $remarksModalId = 'adminRemarksModal'.$submission->type.$submission->model->id;
            $remarks = collect($submission->remarks ?? []);
        @endphp
        <div class="modal fade admin-remarks-modal" id="{{ $remarksModalId }}" tabindex="-1" aria-labelledby="{{ $remarksModalId }}Label" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title" id="{{ $remarksModalId }}Label">Remarks {{ $submission->form_number }}</h5>
                            <div class="admin-remarks-modal-subtitle">{{ $submission->equipment ?: '-' }} &middot; {{ $submission->area ?: '-' }}</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="admin-remarks-list">
                            @foreach ($remarks as $remark)
                                @php
                                    $remarkResult = strtoupper(trim((string) ($remark['result'] ?? '')));
                                    $remarkResultClass = match ($remarkResult) {
                                        'YES' => 'is-yes',
                                        'NO' => 'is-no',
                                        default => '',
                                    };
                                @endphp
                                <article class="admin-remarks-item {{ $remarkResultClass }}">
                                    <div class="admin-remarks-item-meta">
                                        <span>{{ $remark['section'] ?? 'Remarks' }}</span>
                                    </div>
                                    @if (! empty($remark['item']))
                                        <div class="admin-remarks-item-title">{{ $remark['item'] }}</div>
                                    @endif
                                    <div class="admin-remarks-item-text">{!! nl2br(e($remark['text'] ?? '')) !!}</div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endif

@foreach ($submissions->getCollection()->filter(fn ($submission) => $submission->model?->approvalFlow) as $submission)
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
            document.querySelectorAll('[data-count-value]').forEach(function (element) {
                const target = Number.parseFloat(element.dataset.countValue || '0');
                const decimals = Number.parseInt(element.dataset.countDecimals || '0', 10);
                const suffix = element.dataset.countSuffix || '';
                const trim = element.dataset.countTrim === 'true';
                const duration = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 900;
                const formatter = new Intl.NumberFormat('id-ID', {
                    minimumFractionDigits: trim ? 0 : decimals,
                    maximumFractionDigits: decimals,
                });

                const render = function (value) {
                    element.textContent = formatter.format(value) + suffix;
                };

                if (!Number.isFinite(target) || duration === 0) {
                    render(Number.isFinite(target) ? target : 0);
                    return;
                }

                const start = performance.now();

                const tick = function (time) {
                    const progress = Math.min((time - start) / duration, 1);
                    const eased = 1 - Math.pow(1 - progress, 3);
                    render(target * eased);

                    if (progress < 1) {
                        requestAnimationFrame(tick);
                        return;
                    }

                    render(target);
                };

                render(0);
                requestAnimationFrame(tick);
            });

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
                            backgroundColor: '#2563eb',
                            borderColor: '#1d4ed8',
                            borderWidth: 1,
                            borderRadius: 7,
                            borderSkipped: false,
                            maxBarThickness: 46,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: {
                            duration: 900,
                            easing: 'easeOutCubic',
                        },
                        animations: {
                            y: {
                                from: 0,
                            },
                        },
                        plugins: {
                            legend: {
                                display: true,
                                align: 'start',
                                labels: {
                                    boxWidth: 10,
                                    boxHeight: 10,
                                    borderRadius: 3,
                                    color: '#475569',
                                    font: { size: 12, weight: 600 },
                                },
                            },
                            tooltip: {
                                backgroundColor: '#0f172a',
                                padding: 10,
                                titleFont: { size: 12, weight: 700 },
                                bodyFont: { size: 12 },
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
                                min: 0,
                                max: 100,
                                border: { display: false },
                                ticks: {
                                    stepSize: 20,
                                    precision: 0,
                                    color: '#64748b',
                                    padding: 8,
                                    callback: function (value) {
                                        return value + '%';
                                    },
                                },
                                grid: { display: false },
                            },
                            x: {
                                border: { display: false },
                                grid: { display: false },
                                ticks: {
                                    color: '#475569',
                                    font: { size: 12, weight: 600 },
                                    maxRotation: 0,
                                    minRotation: 0,
                                    padding: 10,
                                    callback: function (value) {
                                        const label = this.getLabelForValue(value);
                                        const words = String(label).split(' ');
                                        const lines = [];

                                        words.forEach(function (word) {
                                            const lastIndex = lines.length - 1;

                                            if (lastIndex >= 0 && `${lines[lastIndex]} ${word}`.length <= 12) {
                                                lines[lastIndex] = `${lines[lastIndex]} ${word}`;
                                                return;
                                            }

                                            lines.push(word);
                                        });

                                        return lines.slice(0, 2);
                                    },
                                },
                            },
                        },
                    },
                });
            });

            @if ($canUpdateInspectionStatus)
                document.querySelectorAll('[data-inspection-status-select]').forEach(function (select) {
                    select.addEventListener('change', async function () {
                        const previousValue = select.dataset.previousValue ?? '';

                        if (select.value === 'close' && select.dataset.hasDigitalForm !== 'true') {
                            const confirmed = confirm('Belum ada form commissioning digital untuk equipment ini. Jika dilanjutkan, status akan Close dan tersimpan ke riwayat. Lanjutkan?');

                            if (!confirmed) {
                                select.value = previousValue;
                                return;
                            }
                        }

                        select.disabled = true;

                        try {
                            const response = await fetch(select.dataset.updateUrl, {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                                },
                                body: JSON.stringify({ inspection_status: select.value }),
                            });

                            if (!response.ok) {
                                throw new Error('Gagal memperbarui status.');
                            }

                            window.location.reload();
                        } catch (error) {
                            select.value = previousValue;
                            select.disabled = false;
                            alert(error.message || 'Gagal memperbarui status.');
                        }
                    });

                    select.dataset.previousValue = select.value;
                });

                document.querySelectorAll('[data-reset-inspection-status-button]').forEach(function (button) {
                    button.addEventListener('click', async function () {
                        const label = button.dataset.equipmentLabel || 'equipment ini';
                        let confirmed = true;

                        if (window.Swal) {
                            const result = await window.Swal.fire({
                                title: 'Reset status equipment?',
                                text: `Equipment ${label} akan dikembalikan ke status belum dipakai dan bisa dipilih lagi di form Commissioning.`,
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Ya, reset',
                                cancelButtonText: 'Batal',
                                confirmButtonColor: '#f59e0b',
                            });

                            confirmed = result.isConfirmed;
                        } else {
                            confirmed = confirm(`Reset status equipment ${label} agar bisa dipakai lagi?`);
                        }

                        if (!confirmed) {
                            return;
                        }

                        button.disabled = true;

                        try {
                            const response = await fetch(button.dataset.updateUrl, {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                                },
                                body: JSON.stringify({ inspection_status: null }),
                            });

                            if (!response.ok) {
                                throw new Error('Gagal reset status equipment.');
                            }

                            window.location.reload();
                        } catch (error) {
                            button.disabled = false;

                            if (window.Swal) {
                                window.Swal.fire({
                                    icon: 'error',
                                    title: 'Reset gagal',
                                    text: error.message || 'Silakan coba lagi.',
                                });
                                return;
                            }

                            alert(error.message || 'Gagal reset status equipment.');
                        }
                    });
                });
            @endif
        </script>
    @endpush
@endif

@if ($canDeleteInspectionSubmission)
    @push('scripts')
        <script>
            function submitAdminSubmissionRestore(form) {
                const restoreUrl = form.dataset.restoreUrl;

                if (!restoreUrl) {
                    return;
                }

                const restoreForm = document.createElement('form');
                restoreForm.method = 'POST';
                restoreForm.action = restoreUrl;
                restoreForm.className = 'd-none';

                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = form.querySelector('input[name="_token"]')?.value || document.querySelector('meta[name="csrf-token"]')?.content || '';

                const method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                method.value = 'PATCH';

                restoreForm.appendChild(csrf);
                restoreForm.appendChild(method);
                document.body.appendChild(restoreForm);
                restoreForm.submit();
            }

            document.querySelectorAll('[data-admin-delete-submission-form]').forEach(function (form) {
                form.addEventListener('submit', async function (event) {
                    event.preventDefault();

                    const label = form.dataset.deleteLabel || 'submission ini';
                    const kind = form.dataset.submissionKind || 'submission';
                    const canRestore = form.dataset.canRestore === 'true';
                    let confirmed = false;

                    if (window.Swal) {
                        const result = await window.Swal.fire({
                            title: canRestore ? 'Kelola submission?' : 'Hapus submission?',
                            text: canRestore
                                ? `${kind} ${label} bisa dihapus permanen atau dikembalikan ke draft agar user bisa edit ulang.`
                                : `Submission ${label} akan dihapus permanen beserta attachment dan data approval terkait.`,
                            icon: 'warning',
                            showCancelButton: true,
                            showDenyButton: canRestore,
                            confirmButtonText: 'Ya, hapus permanen',
                            denyButtonText: 'Kembalikan ke draft',
                            cancelButtonText: 'Batal',
                            confirmButtonColor: '#dc3545',
                            denyButtonColor: '#2563eb',
                        });

                        confirmed = result.isConfirmed;

                        if (result.isDenied) {
                            submitAdminSubmissionRestore(form);
                            return;
                        }
                    } else {
                        if (canRestore && window.confirm(`Kembalikan ${kind} ${label} ke draft? Tekan Cancel untuk lanjut ke pilihan hapus.`)) {
                            submitAdminSubmissionRestore(form);
                            return;
                        }

                        confirmed = window.confirm(`Hapus permanen submission ${label}?`);
                    }

                    if (confirmed) {
                        form.submit();
                    }
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
            gap: .85rem;
            margin: 1rem 0 1rem;
        }

        .qc-progress-dashboard.has-remarks-metric {
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: .72rem;
        }

        .qc-progress-card {
            --card-accent: #0d6efd;
            min-height: 86px;
            display: flex;
            align-items: center;
            gap: .95rem;
            padding: .86rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: .75rem;
            background: #ffffff;
            box-shadow: 0 .5rem 1.25rem rgba(15, 23, 42, .055);
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }

        .qc-progress-card:hover {
            transform: translateY(-2px);
            border-color: #cbd5e1;
            box-shadow: 0 .75rem 1.55rem rgba(15, 23, 42, .08);
        }

        .qc-progress-card-icon {
            width: 46px;
            height: 46px;
            flex: 0 0 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: .65rem;
            color: #ffffff;
            background: var(--card-accent);
            font-size: 1.25rem;
            line-height: 1;
        }

        .qc-progress-card-title {
            max-width: 11rem;
            color: #64748b;
            font-size: .86rem;
            font-weight: 500;
            line-height: 1.2;
            text-transform: none;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .qc-progress-card-value {
            margin-top: .14rem;
            color: #0f172a;
            font-size: clamp(1.45rem, 2vw, 1.85rem);
            font-weight: 800;
            line-height: 1.12;
            font-variant-numeric: tabular-nums;
            letter-spacing: 0;
        }

        .qc-progress-dashboard.has-remarks-metric .qc-progress-card {
            min-height: 76px;
            gap: .7rem;
            padding: .66rem .78rem;
            border-radius: .65rem;
        }

        .qc-progress-dashboard.has-remarks-metric .qc-progress-card-icon {
            width: 40px;
            height: 40px;
            flex-basis: 40px;
            border-radius: .58rem;
            font-size: 1.08rem;
        }

        .qc-progress-dashboard.has-remarks-metric .qc-progress-card-title {
            max-width: 9.2rem;
            font-size: .78rem;
        }

        .qc-progress-dashboard.has-remarks-metric .qc-progress-card-value {
            font-size: clamp(1.28rem, 1.55vw, 1.58rem);
        }

        .qc-progress-card-total {
            --card-accent: #0d6efd;
        }

        .qc-progress-card-process {
            --card-accent: #198754;
        }

        .qc-progress-card-ongoing {
            --card-accent: #dc3545;
        }

        .qc-progress-card-percentage {
            --card-accent: #6f42c1;
        }

        .qc-progress-card-remarks {
            --card-accent: #0f766e;
        }

        .qc-area-detail-card,
        .qc-area-chart-card {
            height: 100%;
        }

        .qc-area-chart-card {
            border-color: #e2e8f0;
            background: #ffffff;
            box-shadow: 0 .45rem 1.1rem rgba(15, 23, 42, .055);
        }

        .qc-area-chart-wrap {
            height: 270px;
        }

        .qc-area-progress-table {
            width: 100%;
            min-width: 0;
            table-layout: fixed;
            margin-bottom: 0;
            color: #0f172a;
            font-size: .8rem;
            border-collapse: separate;
            border-spacing: 0;
        }

        .qc-area-progress-table th {
            background: #1e40af;
            color: #ffffff;
            font-size: .68rem;
            font-weight: 750;
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
            padding: .56rem .54rem;
            border-color: #e2e8f0;
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
            font-variant-numeric: tabular-nums;
        }

        .qc-area-progress-table tbody tr:hover td {
            background: #f8fafc;
        }

        .qc-area-progress-table tfoot th {
            background: #f1f5f9;
            color: #0f172a;
            font-weight: 800;
        }

        .admin-submission-table {
            min-width: 760px;
            margin-bottom: 0;
            font-size: .82rem;
        }

        .admin-table-filter-form {
            margin-bottom: 0;
        }

        .admin-table-filter-form .filter-card {
            margin-bottom: 0;
            border-bottom-right-radius: 0;
            border-bottom-left-radius: 0;
        }

        .admin-table-card {
            border-top: 0;
            border-top-right-radius: 0;
            border-top-left-radius: 0;
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
        .admin-submission-col-type { width: 156px; }
        .admin-submission-col-year { width: 62px; }
        .admin-submission-col-plant { width: 104px; }
        .admin-submission-col-area { width: 112px; }
        .admin-submission-col-location { width: 132px; }
        .admin-submission-col-status { width: 152px; }
        .admin-submission-col-action { width: 124px; }

        .admin-submission-table .badge {
            padding: .36rem .56rem;
            border-radius: .48rem;
            font-size: .68rem;
            font-weight: 750;
            letter-spacing: 0;
        }

        .admin-approval-status-trigger {
            max-width: 9.2rem;
            display: inline-grid;
            gap: .12rem;
            justify-items: start;
            padding: .32rem .58rem;
            border: 0;
            border-radius: .55rem;
            color: #075985;
            background: #cffafe;
            font-size: .68rem;
            font-weight: 800;
            line-height: 1.15;
            text-align: left;
            white-space: normal;
            cursor: pointer;
            transition: background-color .18s ease, color .18s ease;
        }

        .admin-approval-status-trigger:hover,
        .admin-approval-status-trigger:focus {
            color: #0c4a6e;
            background: #a5f3fc;
        }

        .admin-approval-status-trigger.is-active {
            color: #1d4ed8;
            background: #dbeafe;
        }

        .admin-approval-status-trigger.is-active:hover,
        .admin-approval-status-trigger.is-active:focus {
            background: #bfdbfe;
        }

        .admin-approval-status-trigger.is-approved {
            color: #166534;
            background: #dcfce7;
        }

        .admin-approval-status-trigger.is-revision,
        .admin-approval-status-trigger.is-revision_required {
            color: #92400e;
            background: #fef3c7;
        }

        .admin-approval-status-trigger.is-rejected,
        .admin-approval-status-trigger.is-cancelled {
            color: #991b1b;
            background: #fee2e2;
        }

        .admin-approval-status-trigger.admin-approval-progress-none {
            color: #475569;
            background: #f1f5f9;
        }

        .admin-approval-status-trigger.admin-approval-progress-0 {
            color: #1d4ed8;
            background: #dbeafe;
        }

        .admin-approval-status-trigger.admin-approval-progress-1 {
            color: #6d28d9;
            background: #ede9fe;
        }

        .admin-approval-status-trigger.admin-approval-progress-2 {
            color: #0f766e;
            background: #ccfbf1;
        }

        .admin-approval-status-trigger.admin-approval-progress-3 {
            color: #b45309;
            background: #fef3c7;
        }

        .admin-approval-status-trigger.admin-approval-progress-complete {
            color: #166534;
            background: #dcfce7;
        }

        .admin-approval-status-trigger:not(button) {
            cursor: default;
        }

        .admin-approval-status-main {
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .admin-approval-status-meta {
            color: currentColor;
            font-size: .62rem;
            font-weight: 700;
            opacity: .78;
        }

        .admin-submission-actor {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            min-width: 0;
        }

        .admin-submission-avatar {
            width: 2.1rem;
            height: 2.1rem;
            flex: 0 0 2.1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border-radius: .56rem;
            color: #ffffff;
            background: #1d4ed8;
            font-size: .72rem;
            font-weight: 850;
            line-height: 1;
        }

        .admin-submission-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .admin-submission-actor-text {
            min-width: 0;
            display: grid;
            gap: .18rem;
        }

        .admin-submission-actor-name {
            max-width: 6.9rem;
            color: #64748b;
            font-size: .68rem;
            font-weight: 650;
            line-height: 1.15;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .admin-submission-equipment {
            color: #172033;
            font-size: .89rem;
            font-weight: 750;
            line-height: 1.2;
        }

        .admin-submission-equipment-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .65rem;
        }

        .admin-submission-location {
            color: #172033;
            font-size: .8rem;
            font-weight: 750;
            line-height: 1.2;
        }

        .admin-submission-location-meta {
            margin-top: .18rem;
            color: #64748b;
            font-size: .72rem;
            line-height: 1.2;
        }

        .admin-work-status-badge {
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 4.7rem;
            padding: .22rem .48rem;
            border-radius: 999px;
            font-size: .66rem;
            font-weight: 800;
            line-height: 1.15;
        }

        .admin-work-status-close {
            color: #166534;
            background: #dcfce7;
        }

        .admin-work-status-ongoing {
            color: #92400e;
            background: #fef3c7;
        }

        .admin-work-status-select {
            flex: 0 0 auto;
            min-width: 6rem;
            padding: .22rem 1.65rem .22rem .58rem;
            border: 0;
            border-radius: 999px;
            font-size: .68rem;
            font-weight: 800;
            line-height: 1.15;
            cursor: pointer;
            background-position: right .55rem center;
            background-size: .65rem;
        }

        .admin-work-status-close {
            color: #166534;
            background-color: #dcfce7;
        }

        .admin-work-status-ongoing {
            color: #92400e;
            background-color: #fef3c7;
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

        .admin-submission-table:not(.has-location-column) td:not(:nth-child(6)),
        .admin-submission-table.has-location-column td:not(:nth-child(5)) {
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

        .admin-remarks-card {
            position: relative;
            width: 1.95rem;
            height: 1.95rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            border: 1px solid #f59e0b;
            border-radius: .36rem;
            color: #ffffff;
            background: #f59e0b;
            font-weight: 800;
            line-height: 1.1;
        }

        .admin-remarks-card:hover,
        .admin-remarks-card:focus {
            color: #ffffff;
            background: #d97706;
            border-color: #d97706;
        }

        .admin-remarks-card i {
            font-size: 1.05rem;
            line-height: 1;
        }

        .admin-remarks-count {
            position: absolute;
            top: -.46rem;
            right: -.46rem;
            min-width: 1.14rem;
            height: 1.14rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 .25rem;
            border: 2px solid #ffffff;
            border-radius: 999px;
            color: #ffffff;
            background: #dc2626;
            font-size: .62rem;
            font-weight: 900;
            line-height: 1;
            font-variant-numeric: tabular-nums;
        }

        .admin-remarks-modal-subtitle {
            margin-top: .18rem;
            color: #64748b;
            font-size: .82rem;
        }

        .admin-remarks-list {
            display: grid;
            gap: .75rem;
        }

        .admin-remarks-item {
            padding: .86rem .95rem;
            border: 1px solid #e2e8f0;
            border-radius: .7rem;
            background: #ffffff;
        }

        .admin-remarks-item.is-yes {
            border-color: #86efac;
            background: #dcfce7;
        }

        .admin-remarks-item.is-no {
            border-color: #fecaca;
            background: #fee2e2;
        }

        .admin-remarks-item-meta {
            display: flex;
            flex-wrap: wrap;
            gap: .35rem;
            margin-bottom: .5rem;
        }

        .admin-remarks-item-meta span {
            display: inline-flex;
            align-items: center;
            min-height: 1.45rem;
            padding: .18rem .48rem;
            border-radius: 999px;
            color: #475569;
            background: #f1f5f9;
            font-size: .68rem;
            font-weight: 800;
        }

        .admin-remarks-item.is-yes .admin-remarks-item-meta span {
            color: #166534;
            background: #bbf7d0;
        }

        .admin-remarks-item.is-no .admin-remarks-item-meta span {
            color: #991b1b;
            background: #fecaca;
        }

        .admin-remarks-item-title {
            margin-bottom: .35rem;
            color: #0f172a;
            font-size: .86rem;
            font-weight: 800;
        }

        .admin-remarks-item-text {
            color: #334155;
            font-size: .86rem;
            line-height: 1.45;
            overflow-wrap: anywhere;
        }

        @media (max-width: 1199.98px) {
            .qc-progress-dashboard {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .qc-progress-dashboard.has-remarks-metric {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .admin-submission-table {
                min-width: 720px;
            }

            .admin-submission-col-plant { width: 96px; }
            .admin-submission-col-area { width: 96px; }
            .admin-submission-col-location { width: 124px; }
            .admin-submission-col-type { width: 148px; }
            .admin-submission-col-status { width: 140px; }
            .admin-submission-col-action { width: 118px; }
        }

        @media (max-width: 575.98px) {
            .qc-progress-dashboard {
                grid-template-columns: 1fr;
            }

            .qc-progress-dashboard.has-remarks-metric {
                grid-template-columns: 1fr;
            }

            .qc-progress-card {
                min-height: 82px;
            }
        }
    </style>
@endpush

@include('approvals._copy-link-script')

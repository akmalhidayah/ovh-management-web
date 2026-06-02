@php
    $submissionRole = $submissionRole ?? 'qc';
    $submissionMode = $submissionMode ?? 'history';
    $isDraftList = $submissionMode === 'draft';
    $isQcList = $submissionRole === 'qc';
    $editRoute = $isQcList ? 'user.qc.submissions.edit' : 'user.commissioning.submissions.edit';
    $pdfRoute = $isQcList ? 'user.qc.submissions.pdf' : 'user.commissioning.submissions.pdf';
    $approvalLinkRoute = $isQcList ? 'user.qc.submissions.approval-link' : 'user.commissioning.submissions.approval-link';
    $deleteRoute = $isQcList ? 'user.qc.submissions.destroy' : 'user.commissioning.submissions.destroy';
    $emptyMessage = $emptyMessage ?? 'Belum ada data.';
@endphp

<div class="submission-mobile-list d-md-none">
    @forelse ($submissions as $submission)
        @php
            $approvalSteps = $submission->approvalFlow?->steps ?? collect();
            $approvedSteps = $approvalSteps->where('status', 'approved')->count();
            $activeStep = $approvalSteps->firstWhere('status', 'active');
            $statusLabel = $statusLabels[$submission->status] ?? \Illuminate\Support\Str::headline((string) $submission->status);
            $equipmentMeta = $isQcList
                ? ($submission->tag_num ?: 'Tanpa section')
                : ($submission->equipment_no ?: 'Tanpa ID equipment');
            $locationMeta = $isQcList
                ? ($submission->plant ?: 'Tanpa plant')
                : ($submission->functional_location ?: 'Tanpa functional location');
            $date = $isDraftList ? $submission->updated_at : $submission->submitted_at;
        @endphp

        <article class="submission-mobile-card">
            <div class="submission-mobile-head">
                <div class="min-w-0">
                    <div class="submission-mobile-form">{{ $submission->form_number }}</div>
                    <div class="submission-mobile-template">{{ $submission->template?->name ?: '-' }}</div>
                </div>
                <span class="commissioning-status-pill {{ $isDraftList ? 'is-draft' : 'is-submitted' }}">{{ $statusLabel }}</span>
            </div>

            <div class="submission-mobile-main">
                <div>
                    <span>Equipment</span>
                    <strong>{{ $submission->equipment ?: '-' }}</strong>
                    <small>{{ $equipmentMeta }}</small>
                </div>
                <div>
                    <span>Lokasi</span>
                    <strong>{{ $submission->area ?: '-' }}</strong>
                    <small>{{ $locationMeta }}</small>
                </div>
                <div>
                    <span>{{ $isDraftList ? 'Update' : 'Submit' }}</span>
                    <strong>{{ $date?->format('d M Y') ?: '-' }}</strong>
                    <small>{{ $date?->format('H:i') ?: '-' }}</small>
                </div>
            </div>

            @if (! $isDraftList && $approvalSteps->isNotEmpty())
                <div class="submission-mobile-approval">
                    <i class="bi bi-list-check"></i>
                    <span>
                        TTD {{ $approvedSteps }}/{{ $approvalSteps->count() }}
                        @if ($activeStep)
                            <span class="submission-mobile-active-step">Aktif: {{ $activeStep->label }}</span>
                        @endif
                    </span>
                </div>
            @endif

            <div class="submission-mobile-actions">
                @if ($isDraftList)
                    <a href="{{ route($editRoute, $submission) }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-pencil-square me-1"></i>Lanjutkan
                    </a>
                @else
                    @if ($submission->approvalFlow)
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#approvalProgressModal{{ $submission->id }}">
                            <i class="bi bi-list-check me-1"></i>Approval
                        </button>
                    @endif
                    <a href="{{ route($pdfRoute, $submission) }}" class="btn btn-sm btn-success" target="_blank">
                        <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                    </a>
                    @if ($submission->status === 'pending_approval' && $submission->approvalFlow?->steps->firstWhere('status', 'active'))
                        <button type="button" class="btn btn-sm btn-warning" data-copy-approval-link-url="{{ route($approvalLinkRoute, $submission) }}">
                            <i class="bi bi-link-45deg me-1"></i>Link
                        </button>
                    @endif
                    @if ($submission->status !== 'approved')
                        <form method="POST" action="{{ route($deleteRoute, $submission) }}" data-delete-submission-form>
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash me-1"></i>Hapus
                            </button>
                        </form>
                    @endif
                @endif
            </div>
        </article>
    @empty
        <div class="submission-mobile-empty">{{ $emptyMessage }}</div>
    @endforelse
</div>

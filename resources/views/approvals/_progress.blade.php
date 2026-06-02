@php($flow = $submission->approvalFlow ?? null)
@if ($flow)
    @php($modalId = $modalId ?? null)

    @if ($modalId)
        @php($copyApprovalLinkUrl = $copyApprovalLinkUrl ?? null)
        <div class="modal fade approval-progress-modal" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title">Detail Approval</h5>
                            <p>{{ $submission->form_number ?? '-' }}</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        @include('approvals._progress-list', [
                            'flow' => $flow,
                            'activeApprovalLinkUrl' => $copyApprovalLinkUrl,
                        ])
                    </div>
                    @if ($copyApprovalLinkUrl)
                        <div class="modal-footer">
                            <small>Gunakan link aktif untuk step approval yang sedang berjalan.</small>
                            <button type="button" class="btn approval-copy-link-btn" data-copy-approval-link-url="{{ $copyApprovalLinkUrl }}">
                                <i class="bi bi-link-45deg"></i>Salin Link TTD
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @else
        <section class="inspector-panel qc-form-card">
            <div class="qc-form-section-title"><h3>Progress Approval</h3></div>
            @include('approvals._progress-list', ['flow' => $flow])
        </section>
    @endif
@endif

@once
    @push('styles')
        <style>
            .approval-progress-modal .modal-content { border: 0; border-radius: .8rem; overflow: hidden; box-shadow: 0 24px 60px rgba(15, 23, 42, .22); }
            .approval-progress-modal .modal-header { align-items: flex-start; gap: 1rem; padding: 1.1rem 1.25rem; background: #f8fafc; border-bottom-color: #e2e8f0; }
            .approval-progress-modal .modal-title { margin: 0; color: #172033; font-weight: 800; }
            .approval-progress-modal .modal-header p { margin: .18rem 0 0; color: #64748b; font-size: .82rem; }
            .approval-progress-modal .modal-body { padding: 1.1rem 1.25rem; background: #fff; }
            .approval-progress-modal .modal-footer { justify-content: space-between; gap: .75rem; padding: .9rem 1.25rem; background: #f8fafc; border-top-color: #e2e8f0; }
            .approval-progress-modal .modal-footer small { color: #64748b; }
            .approval-copy-link-btn { display: inline-flex; align-items: center; gap: .45rem; border: 1px solid #f59e0b; background: #f59e0b; color: #fff; font-weight: 700; }
            .approval-copy-link-btn:hover { border-color: #d97706; background: #d97706; color: #fff; }
            .approval-copy-link-btn.is-copy-success { border-color: #15803d; background: #15803d; }
            .approval-copy-link-btn.is-copy-error { border-color: #b91c1c; background: #b91c1c; }
            .approval-open-link-btn { display: inline-flex; align-items: center; gap: .38rem; flex: 0 0 auto; padding: .32rem .62rem; border: 1px solid #2563eb; border-radius: .48rem; background: #2563eb; color: #fff; font-size: .74rem; font-weight: 800; line-height: 1.1; text-decoration: none; }
            .approval-open-link-btn:hover,
            .approval-open-link-btn:focus { border-color: #1d4ed8; background: #1d4ed8; color: #fff; }
            .approval-open-link-btn:disabled { opacity: .72; cursor: wait; }
            .approval-progress-list { display: grid; gap: .75rem; }
            .approval-progress-item { display: grid; grid-template-columns: 34px minmax(0, 1fr); gap: .8rem; align-items: start; border: 1px solid #e2e8f0; border-radius: .7rem; padding: .85rem; background: #fff; }
            .approval-progress-index { width: 34px; height: 34px; display: grid; place-items: center; border-radius: .55rem; background: #f1f5f9; color: #475569; font-size: .82rem; font-weight: 800; }
            .approval-progress-main { min-width: 0; display: grid; gap: .45rem; }
            .approval-progress-head { display: flex; align-items: center; justify-content: space-between; gap: .6rem; }
            .approval-progress-title { min-width: 0; display: flex; align-items: center; gap: .55rem; }
            .approval-progress-head strong { color: #172033; font-size: .95rem; }
            .approval-progress-head span { flex: 0 0 auto; border-radius: 999px; padding: .22rem .58rem; background: #f1f5f9; color: #475569; font-size: .72rem; font-weight: 800; }
            .approval-progress-meta { display: flex; flex-wrap: wrap; gap: .35rem .8rem; color: #64748b; }
            .approval-progress-meta small { display: inline-flex; align-items: center; gap: .35rem; color: #64748b; font-size: .8rem; }
            .approval-progress-reason { display: inline-flex; align-items: flex-start; gap: .4rem; margin: 0; color: #991b1b; font-size: .82rem; }
            .approval-progress-item.is-approved .approval-progress-index { background: #dcfce7; color: #166534; }
            .approval-progress-item.is-approved .approval-progress-head span { background: #dcfce7; color: #166534; }
            .approval-progress-item.is-active .approval-progress-index { background: #dbeafe; color: #1d4ed8; }
            .approval-progress-item.is-active .approval-progress-head span { background: #dbeafe; color: #1d4ed8; }
            .approval-progress-item.is-rejected .approval-progress-index,
            .approval-progress-item.is-cancelled .approval-progress-index,
            .approval-progress-item.is-rejected .approval-progress-head span,
            .approval-progress-item.is-cancelled .approval-progress-head span { background: #fee2e2; color: #991b1b; }
            @media (max-width: 640px) {
                .approval-progress-modal .modal-footer { align-items: stretch; flex-direction: column; }
                .approval-progress-head { align-items: flex-start; flex-direction: column; }
                .approval-progress-title { align-items: flex-start; flex-direction: column; gap: .45rem; }
            }
        </style>
    @endpush
@endonce

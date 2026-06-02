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
                            'submission' => $submission,
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
            @include('approvals._progress-list', ['flow' => $flow, 'submission' => $submission])
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
            .approval-open-link-btn { display: inline-flex; align-items: center; gap: .48rem; flex: 0 0 auto; min-height: 2.05rem; padding: .26rem .62rem .26rem .36rem; border: 1px solid #1d4ed8; border-radius: .62rem; background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; box-shadow: 0 8px 18px rgba(37, 99, 235, .22); font-size: .72rem; font-weight: 800; line-height: 1.1; text-align: left; text-decoration: none; transition: transform .16s ease, box-shadow .16s ease, background-color .16s ease; }
            .approval-open-link-btn:hover,
            .approval-open-link-btn:focus { border-color: #1e40af; background: linear-gradient(135deg, #1d4ed8, #1e40af); color: #fff; box-shadow: 0 10px 22px rgba(37, 99, 235, .28); transform: translateY(-1px); }
            .approval-open-link-btn:disabled { opacity: .78; cursor: wait; transform: none; }
            .approval-open-link-icon { width: 1.42rem; height: 1.42rem; display: inline-grid; place-items: center; flex: 0 0 1.42rem; border-radius: .45rem; background: rgba(255, 255, 255, .16); }
            .approval-open-link-text { display: grid; gap: .08rem; min-width: 0; }
            .approval-open-link-text span,
            .approval-open-link-text small { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .approval-open-link-text small { color: rgba(255, 255, 255, .78); font-size: .58rem; font-weight: 750; text-transform: uppercase; letter-spacing: 0; }
            .approval-redirect-overlay { position: fixed; inset: 0; z-index: 20000; display: grid; place-items: center; padding: 1.25rem; background: rgba(15, 23, 42, .72); backdrop-filter: blur(12px); opacity: 0; pointer-events: none; transition: opacity .18s ease; }
            .approval-redirect-overlay.is-visible { opacity: 1; pointer-events: auto; }
            .approval-redirect-panel { width: min(25rem, 100%); display: grid; justify-items: center; gap: .95rem; padding: 1.35rem; border: 1px solid rgba(255, 255, 255, .42); border-radius: .85rem; background: #ffffff; box-shadow: 0 24px 70px rgba(15, 23, 42, .28); text-align: center; }
            .approval-redirect-mark { width: 3.15rem; height: 3.15rem; display: grid; place-items: center; border-radius: .8rem; color: #fff; background: #2563eb; box-shadow: 0 12px 28px rgba(37, 99, 235, .32); font-size: 1.35rem; }
            .approval-redirect-copy { display: grid; gap: .32rem; }
            .approval-redirect-copy strong { color: #172033; font-size: 1rem; font-weight: 850; }
            .approval-redirect-copy span { color: #64748b; font-size: .86rem; line-height: 1.45; }
            .approval-redirect-bar { width: 100%; height: .42rem; overflow: hidden; border-radius: 999px; background: #e2e8f0; }
            .approval-redirect-bar span { width: 42%; height: 100%; display: block; border-radius: inherit; background: linear-gradient(90deg, #2563eb, #06b6d4); animation: approvalRedirectBar 1.05s ease-in-out infinite; }
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
            @keyframes approvalRedirectBar {
                0% { transform: translateX(-115%); }
                100% { transform: translateX(245%); }
            }
            @media (max-width: 640px) {
                .approval-progress-modal .modal-footer { align-items: stretch; flex-direction: column; }
                .approval-progress-head { align-items: flex-start; flex-direction: column; }
                .approval-progress-title { align-items: flex-start; flex-direction: column; gap: .45rem; }
            }
        </style>
    @endpush
@endonce

<div class="approval-progress-list">
    @foreach ($flow->steps as $step)
        @php
            $stepLabel = trim((string) $step->label);
            $unitKerjaLabel = trim((string) data_get($submission ?? null, 'approval_data.unit_kerja.label', ''));

            if (Str::upper($stepLabel) === 'UNIT KERJA' && $unitKerjaLabel !== '') {
                $stepLabel = $unitKerjaLabel;
            }
        @endphp
        <div class="approval-progress-item is-{{ $step->status }}">
            <div class="approval-progress-index">{{ $loop->iteration }}</div>
            <div class="approval-progress-main">
                <div class="approval-progress-head">
                    <div class="approval-progress-title">
                        <strong>{{ $stepLabel }}</strong>
                        @if (($activeApprovalLinkUrl ?? null) && $step->status === 'active')
                            <button type="button" class="approval-open-link-btn" data-open-approval-link-url="{{ $activeApprovalLinkUrl }}">
                                <span class="approval-open-link-icon"><i class="bi bi-box-arrow-up-right"></i></span>
                                <span class="approval-open-link-text">
                                    <span>Buka TTD</span>
                                    <small>langsung</small>
                                </span>
                            </button>
                        @endif
                    </div>
                    <span>{{ Str::headline(str_replace('_', ' ', $step->status)) }}</span>
                </div>

                <div class="approval-progress-meta">
                    @if ($step->approver_name)
                        <small><i class="bi bi-person"></i>{{ $step->approver_name }}{{ $step->approver_position ? ' - '.$step->approver_position : '' }}</small>
                    @endif
                    @if ($step->acted_at)
                        <small><i class="bi bi-clock"></i>{{ $step->acted_at->format('d M Y H:i') }}</small>
                    @endif
                    @if (! $step->approver_name && ! $step->acted_at)
                        <small><i class="bi bi-hourglass-split"></i>Belum diproses</small>
                    @endif
                </div>

                @if ($step->reject_reason)
                    <p class="approval-progress-reason"><i class="bi bi-chat-left-text"></i>{{ $step->reject_reason }}</p>
                @endif
            </div>
        </div>
    @endforeach
</div>

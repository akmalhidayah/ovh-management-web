<div class="approval-progress-list">
    @foreach ($flow->steps as $step)
        @php
            $stepLabel = trim((string) $step->label);
            $areaOwnerSourceLabel = trim((string) data_get($submission ?? null, 'approval_data.unit_kerja.label', ''));

            if ($areaOwnerSourceLabel === '') {
                $areaOwnerSourceLabel = trim((string) data_get($submission ?? null, 'approval_data.approved_by_unit_kerja.label', ''));
            }

            if ($areaOwnerSourceLabel === '') {
                $areaOwnerSourceLabel = trim((string) data_get($submission ?? null, 'header_data.unit_kerja', ''));
            }

            if ($areaOwnerSourceLabel === '') {
                $areaOwnerSourceLabel = trim((string) data_get($submission ?? null, 'general_info.unit_kerja', ''));
            }

            if (
                $areaOwnerSourceLabel !== ''
                && (
                    \App\Support\AreaOwnerLabel::isPlaceholder($stepLabel)
                    || Str::upper($stepLabel) === Str::upper($areaOwnerSourceLabel)
                )
            ) {
                $stepLabel = \App\Support\AreaOwnerLabel::approvalLabel($areaOwnerSourceLabel);
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
                                <i class="bi bi-box-arrow-up-right"></i>Buka TTD
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

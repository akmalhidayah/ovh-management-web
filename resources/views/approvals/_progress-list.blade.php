<div class="approval-progress-list">
    @foreach ($flow->steps as $step)
        <div class="approval-progress-item is-{{ $step->status }}">
            <div class="approval-progress-index">{{ $loop->iteration }}</div>
            <div class="approval-progress-main">
                <div class="approval-progress-head">
                    <strong>{{ $step->label }}</strong>
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

@php
    $capexCards = [
        ['label' => 'Total Paket', 'value' => '4', 'tone' => 'blue', 'icon' => 'bi-building-gear'],
        ['label' => 'Budget Plan', 'value' => '6,35 M', 'tone' => 'purple', 'icon' => 'bi-cash-stack'],
        ['label' => 'Review Teknis', 'value' => '2', 'tone' => 'amber', 'icon' => 'bi-clipboard-check'],
        ['label' => 'Approved', 'value' => '1', 'tone' => 'green-dark', 'icon' => 'bi-check2-circle'],
    ];
    $capexFlow = [
        ['label' => 'Scope & BOQ', 'meta' => 'Engineering', 'progress' => 72],
        ['label' => 'Budget Check', 'meta' => 'Finance', 'progress' => 58],
        ['label' => 'Approval Direksi', 'meta' => 'Management', 'progress' => 35],
    ];
@endphp

<section class="procurement-overview" aria-label="Ringkasan capex">
    <div class="procurement-kpi-grid procurement-kpi-grid-simple">
        @foreach ($capexCards as $item)
            <div class="procurement-kpi-card is-{{ $item['tone'] }}">
                <div class="procurement-kpi-icon"><i class="bi {{ $item['icon'] }}"></i></div>
                <div class="procurement-kpi-body">
                    <span>{{ $item['label'] }}</span>
                    <strong>{{ $item['value'] }}</strong>
                </div>
            </div>
        @endforeach
    </div>

    <div class="procurement-skeleton-layout">
        <div class="procurement-panel">
            <div class="procurement-panel-head">
                <h2>Approval Capex</h2>
                <span>Tahap review</span>
            </div>
            <div class="procurement-stage-list">
                @foreach ($capexFlow as $step)
                    <div class="procurement-stage">
                        <div>
                            <strong>{{ $step['label'] }}</strong>
                            <span>{{ $step['meta'] }}</span>
                        </div>
                        <div class="procurement-stage-bar"><span style="width: {{ $step['progress'] }}%"></span></div>
                        <em>{{ $step['progress'] }}%</em>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="procurement-panel procurement-skeleton-panel">
            <div class="procurement-panel-head">
                <h2>Budget Snapshot</h2>
                <span>2026</span>
            </div>
            <div class="procurement-skeleton-lines">
                <span class="w-75"></span>
                <span class="w-100"></span>
                <span class="w-50"></span>
                <span class="w-90"></span>
            </div>
        </div>
    </div>
</section>

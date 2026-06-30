@php
    $actionCards = [
        ['label' => 'Open Action', 'value' => '3', 'tone' => 'blue', 'icon' => 'bi-list-task'],
        ['label' => 'Due Week', 'value' => '2', 'tone' => 'amber', 'icon' => 'bi-calendar-week'],
        ['label' => 'Overdue', 'value' => '1', 'tone' => 'red', 'icon' => 'bi-exclamation-triangle'],
        ['label' => 'Closed', 'value' => '1', 'tone' => 'green-dark', 'icon' => 'bi-check2-square'],
    ];
    $actionLanes = [
        ['title' => 'Open', 'items' => ['Finalisasi spesifikasi brick', 'Validasi capex MCC panel']],
        ['title' => 'On Track', 'items' => ['Mobilisasi NDT shell kiln']],
        ['title' => 'Done', 'items' => ['Follow up delivery filter bag']],
    ];
@endphp

<section class="procurement-overview" aria-label="Ringkasan action log">
    <div class="procurement-kpi-grid procurement-kpi-grid-simple">
        @foreach ($actionCards as $item)
            <div class="procurement-kpi-card is-{{ $item['tone'] }}">
                <div class="procurement-kpi-icon"><i class="bi {{ $item['icon'] }}"></i></div>
                <div class="procurement-kpi-body">
                    <span>{{ $item['label'] }}</span>
                    <strong>{{ $item['value'] }}</strong>
                </div>
            </div>
        @endforeach
    </div>

    <div class="procurement-panel">
        <div class="procurement-panel-head">
            <h2>Follow Up Board</h2>
            <span>Minggu ini</span>
        </div>
        <div class="procurement-kanban-grid">
            @foreach ($actionLanes as $lane)
                <div class="procurement-lane">
                    <h3>{{ $lane['title'] }}</h3>
                    @foreach ($lane['items'] as $item)
                        <div class="procurement-lane-item">
                            <span></span>
                            <p>{{ $item }}</p>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</section>

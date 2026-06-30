@php
    $momCards = [
        ['label' => 'Meeting', 'value' => '4', 'tone' => 'blue', 'icon' => 'bi-calendar-event'],
        ['label' => 'Keputusan', 'value' => '9', 'tone' => 'green-dark', 'icon' => 'bi-check2-circle'],
        ['label' => 'Open Action', 'value' => '5', 'tone' => 'amber', 'icon' => 'bi-lightning-charge'],
        ['label' => 'Dokumen', 'value' => '4', 'tone' => 'purple', 'icon' => 'bi-file-earmark-text'],
    ];
    $momHighlights = [
        ['label' => 'Weekly Procurement Readiness', 'meta' => '30 Jun 2026 - Pengadaan'],
        ['label' => 'Capex Review Meeting', 'meta' => '26 Jun 2026 - Finance'],
        ['label' => 'Vendor Clarification', 'meta' => '24 Jun 2026 - Jasa'],
    ];
@endphp

<section class="procurement-overview" aria-label="Ringkasan minutes of meeting">
    <div class="procurement-kpi-grid procurement-kpi-grid-simple">
        @foreach ($momCards as $item)
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
                <h2>Meeting Notes</h2>
                <span>Agenda terbaru</span>
            </div>
            <div class="procurement-note-list">
                @foreach ($momHighlights as $item)
                    <div class="procurement-note-item">
                        <i class="bi bi-journal-text"></i>
                        <div>
                            <strong>{{ $item['label'] }}</strong>
                            <span>{{ $item['meta'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="procurement-panel procurement-skeleton-panel">
            <div class="procurement-panel-head">
                <h2>Decision Draft</h2>
                <span>Skeleton</span>
            </div>
            <div class="procurement-skeleton-lines">
                <span class="w-100"></span>
                <span class="w-75"></span>
                <span class="w-90"></span>
                <span class="w-50"></span>
            </div>
        </div>
    </div>
</section>

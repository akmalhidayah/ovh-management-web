@php
    $jasaCostCards = [
        ['label' => 'Plan', 'value' => '36,7 M', 'tone' => 'blue', 'icon' => 'bi-clipboard-data'],
        ['label' => 'PGO', 'value' => '3,1 M', 'tone' => 'purple', 'icon' => 'bi-diagram-3'],
        ['label' => 'Proses', 'value' => '87,6 M', 'tone' => 'orange', 'icon' => 'bi-arrow-repeat'],
        ['label' => 'Purchase Order', 'value' => '14,1 M', 'tone' => 'green-dark', 'icon' => 'bi-file-earmark-check'],
        ['label' => 'Invoice', 'value' => '10,1 M', 'tone' => 'green', 'icon' => 'bi-receipt'],
        ['label' => 'Purchase Order', 'value' => '4,0 M', 'tone' => 'red', 'icon' => 'bi-exclamation-circle'],
    ];
@endphp

<section class="procurement-overview procurement-service-overview" aria-label="Ringkasan pengadaan jasa">
    <div class="procurement-kpi-group">
        <h2>Cost Overhaul</h2>
        <div class="procurement-service-main">
            <div class="procurement-kpi-grid procurement-kpi-grid-service">
                @foreach ($jasaCostCards as $item)
                    <div class="procurement-kpi-card is-{{ $item['tone'] }}">
                        <div class="procurement-kpi-icon"><i class="bi {{ $item['icon'] }}"></i></div>
                        <div class="procurement-kpi-body">
                            <span>{{ $item['label'] }}</span>
                            <strong>{{ $item['value'] }}</strong>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="procurement-invoice-card">
                <div>
                    <span>Invoice</span>
                    <strong>80%</strong>
                </div>
                <p>Progress invoice jasa overhaul</p>
                <div class="procurement-invoice-bar">
                    <span style="width: 80%"></span>
                </div>
            </div>
        </div>
    </div>
</section>

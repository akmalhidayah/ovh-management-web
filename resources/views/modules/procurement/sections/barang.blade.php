@php
    $barangCostCards = [
        ['label' => 'Plan', 'value' => '87,6 M', 'tone' => 'blue', 'icon' => 'bi-clipboard-data'],
        ['label' => 'Proses', 'value' => '77,3 M', 'tone' => 'amber', 'icon' => 'bi-arrow-repeat'],
        ['label' => 'PO', 'value' => '75,0 M', 'tone' => 'olive', 'icon' => 'bi-file-earmark-check'],
        ['label' => 'Ready Gudang', 'value' => '0,94 M', 'tone' => 'green-dark', 'icon' => 'bi-box-seam'],
        ['label' => 'Goods Issue', 'value' => '71,7 M', 'tone' => 'green', 'icon' => 'bi-truck'],
    ];
    $barangProcessCards = [
        ['label' => 'Total Item', 'value' => '1250', 'tone' => 'blue', 'icon' => 'bi-collection'],
        ['label' => 'Cancel', 'value' => '563', 'tone' => 'red', 'icon' => 'bi-x-circle'],
        ['label' => 'Proses Pengadaan', 'value' => '11', 'tone' => 'blue', 'icon' => 'bi-cart-check'],
        ['label' => 'PO/Delivery', 'value' => '25', 'tone' => 'green-dark', 'icon' => 'bi-file-earmark-arrow-down'],
        ['label' => 'Ready Gudang', 'value' => '20', 'tone' => 'green-light', 'icon' => 'bi-box2-heart'],
        ['label' => 'Good Issue', 'value' => '631', 'tone' => 'green', 'icon' => 'bi-check2-square'],
    ];
@endphp

<section class="procurement-overview" aria-label="Ringkasan pengadaan barang">
    <div class="procurement-kpi-group">
        <h2>Cost Overhaul</h2>
        <div class="procurement-kpi-grid procurement-kpi-grid-cost">
            @foreach ($barangCostCards as $item)
                <div class="procurement-kpi-card is-{{ $item['tone'] }}">
                    <div class="procurement-kpi-icon"><i class="bi {{ $item['icon'] }}"></i></div>
                    <div class="procurement-kpi-body">
                        <span>{{ $item['label'] }}</span>
                        <strong>{{ $item['value'] }}</strong>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="procurement-overview-lower">
        <div class="procurement-kpi-group">
            <h2>Proses Pengadaan</h2>
            <div class="procurement-kpi-grid procurement-kpi-grid-process">
                @foreach ($barangProcessCards as $item)
                    <div class="procurement-kpi-card is-{{ $item['tone'] }}">
                        <div class="procurement-kpi-icon"><i class="bi {{ $item['icon'] }}"></i></div>
                        <div class="procurement-kpi-body">
                            <span>{{ $item['label'] }}</span>
                            <strong>{{ $item['value'] }}</strong>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="procurement-kpi-group procurement-budget-group">
            <h2>Pemanfaatan Budget</h2>
            <div class="procurement-budget-card">
                <span>Budget Utilization</span>
                <strong>96%</strong>
                <p>Rp 84,1 M dari plan sudah termanfaatkan</p>
                <div class="procurement-budget-bar">
                    <span style="width: 96%"></span>
                </div>
            </div>
        </div>
    </div>
</section>

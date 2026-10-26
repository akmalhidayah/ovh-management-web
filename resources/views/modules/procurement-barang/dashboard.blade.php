@php
    $budgetFlowCards = [
        ['label' => 'Procurement', 'value' => 'Rp 77,3 M', 'icon' => 'bi-cart-check', 'tone' => 'orange'],
        ['label' => 'Purchase Order', 'value' => 'Rp 77,3 M', 'icon' => 'bi-diagram-3', 'tone' => 'green'],
        ['label' => 'Good Issue', 'value' => 'Rp 77,3 M', 'icon' => 'bi-check-circle', 'tone' => 'purple'],
        ['label' => 'Outstanding', 'value' => 'Rp 77,3 M', 'icon' => 'bi-clock-fill', 'tone' => 'red'],
    ];
    $processCards = [
        ['label' => 'Total Item', 'value' => '42', 'icon' => 'bi-card-checklist', 'tone' => 'navy'],
        ['label' => 'Proses Pengadaan', 'value' => '11', 'icon' => 'bi-truck', 'tone' => 'navy'],
        ['label' => 'Ready/PO', 'value' => '25', 'icon' => 'bi-box2-heart', 'tone' => 'navy'],
        ['label' => 'Finish', 'value' => '23', 'icon' => 'bi-check-circle', 'tone' => 'navy'],
        ['label' => 'Cancel', 'value' => '631', 'icon' => 'bi-exclamation-triangle', 'tone' => 'red'],
        ['label' => 'Pending', 'value' => '10', 'icon' => 'bi-hourglass-split', 'tone' => 'amber'],
    ];
    $procurementPlantProgress = [['Tonasa 2', 18], ['Tonasa 3', 17], ['Tonasa 4', 21], ['Tonasa 5', 23]];
    $readinessPlantProgress = [['Tonasa 2', 0], ['Tonasa 3', 0], ['Tonasa 4', 4], ['Tonasa 5', 6]];
    $progressRequests = [['Tahap 2', 58], ['Tahap 1', 80], ['Firebricks', 46], ['Castable', 80], ['Anchor', 25]];
    $readinessRequests = [['Tahap 2', 20], ['Tahap 1', 15], ['Firebricks', 0], ['Castable', 0], ['Anchor', 10]];
@endphp

<section class="procurement-goods-filter" aria-label="Filter dashboard pengadaan barang">
    <label>
        <span>Tahun</span>
        <select aria-label="Tahun pengadaan"><option>2025</option><option selected>2026</option><option>2027</option></select>
    </label>
    <label>
        <span>Plant</span>
        <select aria-label="Plant pengadaan"><option>Tonasa 2</option><option>Tonasa 3</option><option>Tonasa 4</option><option selected>Tonasa 5</option></select>
    </label>
</section>

<section class="procurement-goods-budget" aria-label="Ringkasan budget pengadaan barang">
    <div class="procurement-goods-budget-main">
        <div class="procurement-goods-budget-headline">
            <article class="procurement-goods-metric is-wide">
                <span class="procurement-goods-metric-icon"><i class="bi bi-wallet2" aria-hidden="true"></i></span>
                <div><small>Request Budget</small><strong>Rp 77,3 M</strong></div>
            </article>
            <article class="procurement-goods-metric is-wide">
                <span class="procurement-goods-metric-icon"><i class="bi bi-patch-check-fill" aria-hidden="true"></i></span>
                <div><small>Plan/Approval Budget</small><strong>Rp 77,3 M</strong></div>
            </article>
        </div>

        <div class="procurement-goods-flow-grid">
            @foreach ($budgetFlowCards as $card)
                <article class="procurement-goods-mini-metric is-{{ $card['tone'] }}">
                    <span><i class="bi {{ $card['icon'] }}" aria-hidden="true"></i></span>
                    <div><small>{{ $card['label'] }}</small><strong>{{ $card['value'] }}</strong></div>
                </article>
            @endforeach
        </div>
    </div>

    <article class="procurement-goods-realization">
        <span class="procurement-goods-realization-icon"><i class="bi bi-graph-up-arrow" aria-hidden="true"></i></span>
        <small>Realisasi<br>Budget</small>
        <strong>96<span>%</span></strong>
        <p>Anggaran terealisasi</p>
    </article>
</section>

<section class="procurement-goods-owner-grid" aria-label="Budget per kelompok pengadaan">
    <article class="procurement-goods-owner">
        <span><i class="bi bi-gear-wide-connected" aria-hidden="true"></i></span>
        <div><small>BMS</small><strong>Rp 77,3 M</strong></div>
    </article>
    <article class="procurement-goods-owner">
        <span><i class="bi bi-tools" aria-hidden="true"></i></span>
        <div><small>PGO &amp; TS</small><strong>Rp 77,3 M</strong></div>
    </article>
    <article class="procurement-goods-owner-status">
        <div class="is-cancel"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i><span><small>Cancel</small><strong>Rp 77,3 M</strong></span></div>
        <div class="is-pending"><i class="bi bi-hourglass-split" aria-hidden="true"></i><span><small>Pending</small><strong>Rp 77,3 M</strong></span></div>
    </article>
</section>

<section class="procurement-goods-process" aria-labelledby="procurement-goods-process-title">
    <h2 id="procurement-goods-process-title">Proses Pengadaan <small>(Item Status)</small></h2>
    <div class="procurement-goods-process-grid">
        @foreach ($processCards as $card)
            <article class="procurement-goods-process-card is-{{ $card['tone'] }}">
                <span><i class="bi {{ $card['icon'] }}" aria-hidden="true"></i></span>
                <div><strong>{{ $card['value'] }}</strong><small>Item</small><p>{{ $card['label'] }}</p></div>
            </article>
        @endforeach
    </div>
</section>

<section class="procurement-goods-dual-grid">
    <article class="procurement-goods-panel is-blue">
        <h3>Procurement Progress</h3>
        <div class="procurement-goods-plant-progress">
            <div class="procurement-goods-ring is-21"><div><strong>21%</strong><span>All Progress</span></div></div>
            <div class="procurement-goods-plant-grid">
                @foreach ($procurementPlantProgress as [$plant, $progress])
                    <div><small>{{ $plant }}</small><strong>{{ $progress }}%</strong></div>
                @endforeach
            </div>
        </div>
    </article>

    <article class="procurement-goods-panel is-teal">
        <h3>Readiness</h3>
        <div class="procurement-goods-plant-progress">
            <div class="procurement-goods-ring is-5"><div><strong>5%</strong><span>All Readiness</span></div></div>
            <div class="procurement-goods-plant-grid">
                @foreach ($readinessPlantProgress as [$plant, $progress])
                    <div><small>{{ $plant }}</small><strong>{{ $progress }}%</strong></div>
                @endforeach
            </div>
        </div>
    </article>
</section>

<section class="procurement-goods-dual-grid">
    <article class="procurement-goods-panel is-blue">
        <h3>Procurement Status</h3>
        <div class="procurement-goods-status-chart">
            <div class="procurement-goods-status-ring is-procurement"><div><strong>369</strong><span>Total Items</span></div></div>
            <ul>
                <li><i class="is-process"></i><span>Proses Procurement</span><strong>78,9%</strong></li>
                <li><i class="is-cancel"></i><span>Cancel</span><strong>10,6%</strong></li>
                <li><i class="is-po"></i><span>Executed by PGO</span><strong>6,5%</strong></li>
                <li><i class="is-ready"></i><span>Ready</span><strong>4,1%</strong></li>
            </ul>
        </div>
    </article>

    <article class="procurement-goods-panel is-teal">
        <h3>Order Status</h3>
        <div class="procurement-goods-status-chart">
            <div class="procurement-goods-status-ring is-order"><div><strong>369</strong><span>Total Items</span></div></div>
            <ul>
                <li><i class="is-process"></i><span>Proses Procurement</span><strong>40,9%</strong></li>
                <li><i class="is-order-cancel"></i><span>Cancel</span><strong>21,4%</strong></li>
                <li><i class="is-purchase"></i><span>Purchase Order</span><strong>21,1%</strong></li>
                <li><i class="is-shipping"></i><span>Pengiriman</span><strong>8,9%</strong></li>
            </ul>
        </div>
    </article>
</section>

<section class="procurement-goods-dual-grid">
    <article class="procurement-goods-panel is-blue">
        <h3>Progress Request</h3>
        <div class="procurement-goods-request-list">
            @foreach ($progressRequests as [$label, $progress])
                <div><span>{{ $label }}</span><div class="procurement-goods-progress" data-progress="{{ $progress }}"><i></i></div><strong>{{ $progress }}%</strong></div>
            @endforeach
        </div>
        <a href="#procurement-goods-process-title">Lihat Detail</a>
    </article>

    <article class="procurement-goods-panel is-teal">
        <h3>Readiness Request</h3>
        <div class="procurement-goods-request-list">
            @foreach ($readinessRequests as [$label, $progress])
                <div><span>{{ $label }}</span><div class="procurement-goods-progress" data-progress="{{ $progress }}"><i></i></div><strong>{{ $progress }}%</strong></div>
            @endforeach
        </div>
        <a href="#procurement-goods-process-title">Lihat Detail</a>
    </article>
</section>

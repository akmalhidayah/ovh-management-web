<x-page-header title="Overview" subtitle="Ringkasan budget, progress, dan pengadaan overhaul."></x-page-header>

<div class="row g-3 mb-4">
    <div class="col-12 col-md-4"><x-stat-card title="Budget Terserap" value="Rp 7,4 M" icon="bi-wallet2" tone="primary" subtitle="58% dari total budget" /></div>
    <div class="col-12 col-md-4"><x-stat-card title="Progress Aktual" value="68%" icon="bi-graph-up-arrow" tone="success" subtitle="Target 72%" /></div>
    <div class="col-12 col-md-4"><x-stat-card title="Pengadaan Selesai" value="42 Item" icon="bi-box-seam" tone="info" subtitle="Dari 78 item" /></div>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-5">
        <div class="content-card h-100">
            <div class="card-heading"><h2>Grafik Pengadaan</h2><span class="text-muted">Kategori</span></div>
            <div class="chart-wrap chart-small"><canvas data-chart="doughnut"></canvas></div>
        </div>
    </div>
    <div class="col-12 col-lg-7">
        <div class="content-card h-100">
            <div class="card-heading"><h2>Trend Progress</h2><span class="text-muted">Bulanan</span></div>
            <div class="chart-wrap"><canvas data-chart="line"></canvas></div>
        </div>
    </div>
</div>

@php($roleLabel = auth()->user()->usertype === 'admin' ? 'Admin' : 'User')

<x-page-header title="Dashboard {{ $roleLabel }}" subtitle="Ringkasan dummy performa overhaul dan kesiapan pekerjaan."></x-page-header>

<div class="row g-3 mb-4">
    <div class="col-12 col-md-6 col-xl-4"><x-stat-card title="Total Budget" value="Rp 12,8 M" icon="bi-cash-stack" tone="primary" subtitle="Dummy tahun berjalan" /></div>
    <div class="col-12 col-md-6 col-xl-4"><x-stat-card title="Progress Overhaul" value="68%" icon="bi-activity" tone="success" subtitle="Naik 8% dari minggu lalu" /></div>
    <div class="col-12 col-md-6 col-xl-4"><x-stat-card title="Progress Procurement" value="54%" icon="bi-cart-check" tone="info" subtitle="16 item proses" /></div>
    <div class="col-12 col-md-6 col-xl-4"><x-stat-card title="Total Action Log" value="124" icon="bi-list-task" tone="warning" subtitle="21 open action" /></div>
    <div class="col-12 col-md-6 col-xl-4"><x-stat-card title="Total Equipment" value="286" icon="bi-cpu" tone="secondary" subtitle="Lintas plant" /></div>
    <div class="col-12 col-md-6 col-xl-4"><x-stat-card title="Dokumen Tersimpan" value="438" icon="bi-folder2-open" tone="danger" subtitle="Dokumen dummy" /></div>
</div>

<div class="row g-3">
    <div class="col-12 col-xl-8">
        <div class="content-card">
            <div class="card-heading">
                <h2>Grafik Progress Overhaul</h2>
                <span class="text-muted">Jan - Dec</span>
            </div>
            <div class="chart-wrap"><canvas data-chart="line"></canvas></div>
        </div>
    </div>
    <div class="col-12 col-xl-4">
        <div class="content-card h-100">
            <div class="card-heading">
                <h2>Status Action Log</h2>
                <span class="text-muted">Dummy</span>
            </div>
            <div class="chart-wrap chart-small"><canvas data-chart="doughnut"></canvas></div>
        </div>
    </div>
    <div class="col-12">
        <div class="content-card">
            <div class="card-heading">
                <h2>Grafik Procurement</h2>
                <span class="text-muted">Barang, jasa, capex</span>
            </div>
            <div class="chart-wrap"><canvas data-chart="bar"></canvas></div>
        </div>
    </div>
</div>

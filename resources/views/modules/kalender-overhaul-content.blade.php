<x-page-header title="Kalender Overhaul" subtitle="Kalender dummy untuk overhaul, barang, dan jasa."></x-page-header>

<x-filter-card>
    <div class="col-12 col-md-5">
        <label class="form-label">Tahun</label>
        <select class="form-select"><option>2026</option><option>2025</option></select>
    </div>
    <div class="col-12 col-md-5">
        <label class="form-label">Plant</label>
        <select class="form-select"><option>Semua Plant</option><option>Plant A</option><option>Plant B</option></select>
    </div>
    <div class="col-12 col-md-2">
        <button class="btn btn-outline-primary w-100"><i class="bi bi-funnel me-2"></i>Filter</button>
    </div>
</x-filter-card>

<div class="content-card">
    <ul class="nav nav-pills responsive-tabs mb-3">
        <li class="nav-item"><button class="nav-link active">Kalender Overhaul</button></li>
        <li class="nav-item"><button class="nav-link">Kalender Barang</button></li>
        <li class="nav-item"><button class="nav-link">Kalender Jasa</button></li>
    </ul>
    <div class="placeholder-panel calendar-placeholder">
        <img src="{{ asset('assets/images/placeholders/calendar-placeholder.svg') }}" alt="Calendar placeholder">
    </div>
</div>

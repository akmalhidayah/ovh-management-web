<x-page-header title="Master Data" subtitle="Menu referensi untuk admin. CRUD detail belum dibuat pada tahap UI awal."></x-page-header>

<div class="row g-3">
    @foreach ([
        ['Tahun', 'bi-calendar2-week', 'tahun'],
        ['Plant', 'bi-building', 'plant'],
        ['Area', 'bi-pin-map', 'area'],
        ['Equipment', 'bi-cpu', 'equipment'],
        ['Status', 'bi-tags', 'status'],
        ['Kategori Dokumen', 'bi-folder2', 'kategori-dokumen'],
        ['PIC/User', 'bi-people', 'pic-user'],
    ] as $item)
        <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <div class="master-card" id="{{ $item[2] }}">
                <div class="master-icon"><i class="bi {{ $item[1] }}"></i></div>
                <div>
                    <h2>{{ $item[0] }}</h2>
                    <p>Kelola data {{ strtolower($item[0]) }}</p>
                </div>
            </div>
        </div>
    @endforeach
</div>

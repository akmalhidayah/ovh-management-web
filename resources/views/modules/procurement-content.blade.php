<x-page-header title="Procurement" subtitle="Monitoring dummy pengadaan barang, jasa, jasa tambahan, dan capex.">
    <button class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Tambah Data</button>
</x-page-header>

<x-filter-card>
    <div class="col-12 col-md-4 col-xl-3">
        <label class="form-label">Tahun</label>
        <select class="form-select"><option>2026</option><option>2025</option></select>
    </div>
    <div class="col-12 col-md-4 col-xl-3">
        <label class="form-label">Plant</label>
        <select class="form-select"><option>Semua Plant</option><option>Plant A</option><option>Plant B</option></select>
    </div>
    <div class="col-12 col-md-4 col-xl-3">
        <label class="form-label">Jenis</label>
        <select class="form-select"><option>Semua Jenis</option><option>Barang</option><option>Jasa</option><option>Jasa Tambahan</option><option>Capex</option></select>
    </div>
    <div class="col-12 col-xl-3">
        <button class="btn btn-outline-primary w-100"><i class="bi bi-funnel me-2"></i>Filter</button>
    </div>
</x-filter-card>

<div class="content-card">
    <div class="table-responsive">
        <table class="table align-middle ovh-table">
            <thead><tr><th>No</th><th>Tahun</th><th>Plant</th><th>Jenis</th><th>Nama Item</th><th>Status</th><th>Progress</th><th>Action</th></tr></thead>
            <tbody>
                @foreach ([
                    ['2026', 'Plant A', 'Barang', 'Spare Part Turbine', 'Proses', 62],
                    ['2026', 'Plant B', 'Jasa', 'Alignment Rotor', 'Selesai', 100],
                    ['2026', 'Plant A', 'Capex', 'Control Panel', 'Draft', 20],
                    ['2026', 'Plant C', 'Jasa Tambahan', 'NDT Support', 'Terlambat', 44],
                ] as $i => $row)
                    <tr>
                        <td>{{ $i + 1 }}</td><td>{{ $row[0] }}</td><td>{{ $row[1] }}</td><td>{{ $row[2] }}</td><td>{{ $row[3] }}</td>
                        <td><x-status-badge :status="$row[4]" /></td>
                        <td><div class="progress ovh-progress"><div class="progress-bar" style="width: {{ $row[5] }}%">{{ $row[5] }}%</div></div></td>
                        <td><button class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

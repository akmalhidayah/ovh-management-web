<x-page-header title="Commissioning" subtitle="Data dummy commissioning equipment.">
    <button class="btn btn-outline-secondary"><i class="bi bi-download me-2"></i>Download</button>
    <button class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Tambah Data</button>
</x-page-header>

<div class="row g-3 mb-4">
    <div class="col-12 col-md-4"><x-stat-card title="Total Commissioning" value="38" icon="bi-tools" tone="primary" /></div>
    <div class="col-12 col-md-4"><x-stat-card title="Selesai" value="24" icon="bi-check2-circle" tone="success" /></div>
    <div class="col-12 col-md-4"><x-stat-card title="Proses" value="14" icon="bi-hourglass-split" tone="warning" /></div>
</div>

<x-filter-card>
    <div class="col-12 col-md-4"><label class="form-label">Tahun</label><select class="form-select"><option>2026</option></select></div>
    <div class="col-12 col-md-4"><label class="form-label">Plant</label><select class="form-select"><option>Semua Plant</option></select></div>
    <div class="col-12 col-md-4"><label class="form-label">Area</label><select class="form-select"><option>Semua Area</option></select></div>
</x-filter-card>

<div class="content-card">
    <div class="table-responsive">
        <table class="table align-middle ovh-table">
            <thead><tr><th>No</th><th>Tahun</th><th>Plant</th><th>Area</th><th>Equipment</th><th>Tanggal</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
                @foreach ([['2026','Plant A','Turbine','Turbine Generator','2026-05-12','Proses'], ['2026','Plant B','Boiler','Feed Pump','2026-05-18','Selesai'], ['2026','Plant C','Electrical','Switchgear','2026-05-22','Draft']] as $i => $row)
                    <tr><td>{{ $i + 1 }}</td><td>{{ $row[0] }}</td><td>{{ $row[1] }}</td><td>{{ $row[2] }}</td><td>{{ $row[3] }}</td><td>{{ $row[4] }}</td><td><x-status-badge :status="$row[5]" /></td><td><button class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></button></td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

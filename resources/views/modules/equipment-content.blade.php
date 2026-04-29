<x-page-header title="Equipment" subtitle="Daftar dummy equipment overhaul.">
    <button class="btn btn-outline-secondary"><i class="bi bi-download me-2"></i>Download</button>
    <button class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Tambah Equipment</button>
</x-page-header>

<div class="row g-3 mb-4">
    <div class="col-12 col-md-4"><x-stat-card title="Total Equipment" value="286" icon="bi-cpu" tone="primary" /></div>
    <div class="col-12 col-md-4"><x-stat-card title="Available" value="244" icon="bi-check2-circle" tone="success" /></div>
    <div class="col-12 col-md-4"><x-stat-card title="Maintenance" value="42" icon="bi-wrench-adjustable" tone="warning" /></div>
</div>

<x-filter-card>
    <div class="col-12 col-md-6"><label class="form-label">Plant</label><select class="form-select"><option>Semua Plant</option></select></div>
    <div class="col-12 col-md-6"><label class="form-label">Area</label><select class="form-select"><option>Semua Area</option></select></div>
</x-filter-card>

<div class="content-card">
    <div class="table-responsive">
        <table class="table align-middle ovh-table">
            <thead><tr><th>No</th><th>Kode Equipment</th><th>Nama Equipment</th><th>Plant</th><th>Area</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
                @foreach ([['EQ-TB-001','Turbine Generator','Plant A','Turbine','Selesai'], ['EQ-BL-014','Feed Water Pump','Plant B','Boiler','Proses'], ['EQ-EL-022','Switchgear 6kV','Plant C','Electrical','Draft']] as $i => $row)
                    <tr><td>{{ $i + 1 }}</td><td>{{ $row[0] }}</td><td>{{ $row[1] }}</td><td>{{ $row[2] }}</td><td>{{ $row[3] }}</td><td><x-status-badge :status="$row[4]" /></td><td><button class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></button></td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

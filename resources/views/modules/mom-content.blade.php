<x-page-header title="MoM" subtitle="Minute of meeting dan action log dummy.">
    <button class="btn btn-outline-secondary"><i class="bi bi-download me-2"></i>Download</button>
    <button class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Tambah MoM</button>
</x-page-header>

<x-filter-card>
    <div class="col-12 col-md-4"><label class="form-label">Tahun</label><select class="form-select"><option>2026</option></select></div>
    <div class="col-12 col-md-4"><label class="form-label">Plant</label><select class="form-select"><option>Semua Plant</option></select></div>
    <div class="col-12 col-md-4"><label class="form-label">Area</label><select class="form-select"><option>Semua Area</option></select></div>
</x-filter-card>

<div class="content-card">
    <div class="table-responsive">
        <table class="table align-middle ovh-table">
            <thead><tr><th>No</th><th>Tanggal Meeting</th><th>Judul Meeting</th><th>Plant</th><th>Area</th><th>Jumlah Action Log</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
                @foreach ([['2026-05-02','Weekly Overhaul Review','Plant A','Turbine',12,'Proses'], ['2026-05-09','Procurement Alignment','Plant B','Procurement',8,'Selesai'], ['2026-05-16','QC Finding Review','Plant C','Boiler',5,'Open']] as $i => $row)
                    <tr><td>{{ $i + 1 }}</td><td>{{ $row[0] }}</td><td>{{ $row[1] }}</td><td>{{ $row[2] }}</td><td>{{ $row[3] }}</td><td>{{ $row[4] }}</td><td><x-status-badge :status="$row[5]" /></td><td><button class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></button></td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

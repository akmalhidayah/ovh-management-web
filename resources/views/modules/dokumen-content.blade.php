<x-page-header title="Dokumen" subtitle="Repository dummy dokumen overhaul.">
    <button class="btn btn-outline-secondary"><i class="bi bi-download me-2"></i>Download</button>
    <button class="btn btn-primary"><i class="bi bi-cloud-upload me-2"></i>Upload Dokumen</button>
</x-page-header>

<x-filter-card>
    <div class="col-12 col-md-3"><label class="form-label">Tahun</label><select class="form-select"><option>2026</option></select></div>
    <div class="col-12 col-md-3"><label class="form-label">Plant</label><select class="form-select"><option>Semua Plant</option></select></div>
    <div class="col-12 col-md-3"><label class="form-label">Area</label><select class="form-select"><option>Semua Area</option></select></div>
    <div class="col-12 col-md-3"><label class="form-label">Kategori</label><select class="form-select"><option>Semua Kategori</option><option>Drawing</option><option>Report</option><option>Checklist</option></select></div>
</x-filter-card>

<div class="content-card">
    <div class="table-responsive">
        <table class="table align-middle ovh-table">
            <thead><tr><th>No</th><th>Nama Dokumen</th><th>Kategori</th><th>Tahun</th><th>Plant</th><th>Uploaded By</th><th>Tanggal Upload</th><th>Action</th></tr></thead>
            <tbody>
                @foreach ([['Checklist Commissioning TG','Checklist','2026','Plant A','Admin OVH','2026-05-03'], ['Report NDT Boiler','Report','2026','Plant B','User OVH','2026-05-06'], ['Drawing Control Panel','Drawing','2026','Plant C','Admin OVH','2026-05-10']] as $i => $row)
                    <tr><td>{{ $i + 1 }}</td><td>{{ $row[0] }}</td><td>{{ $row[1] }}</td><td>{{ $row[2] }}</td><td>{{ $row[3] }}</td><td>{{ $row[4] }}</td><td>{{ $row[5] }}</td><td><button class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></button></td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

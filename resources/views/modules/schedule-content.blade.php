<x-page-header title="Schedule" subtitle="Kurva S, gantt chart, dan action log dummy.">
    <button class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Tambah Action Log</button>
</x-page-header>

<div class="row g-3 mb-4">
    <div class="col-12 col-xl-5">
        <div class="content-card h-100">
            <div class="card-heading"><h2>Kurva S</h2><span class="text-muted">Plan vs Actual</span></div>
            <div class="chart-wrap"><canvas data-chart="line"></canvas></div>
        </div>
    </div>
    <div class="col-12 col-xl-7">
        <div class="content-card h-100">
            <div class="card-heading"><h2>Gantt Chart</h2><span class="text-muted">Placeholder</span></div>
            <div class="placeholder-panel gantt-placeholder">
                <img src="{{ asset('assets/images/placeholders/gantt-placeholder.svg') }}" alt="Gantt placeholder">
            </div>
        </div>
    </div>
</div>

<div class="content-card">
    <div class="table-responsive">
        <table class="table align-middle ovh-table">
            <thead><tr><th>No</th><th>Action Item</th><th>PIC</th><th>Due Date</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
                @foreach ([['Finalisasi scope pekerjaan', 'Andi', '2026-05-10', 'Proses'], ['Review spare critical', 'Sinta', '2026-05-14', 'Open'], ['Close punch list area boiler', 'Rudi', '2026-05-21', 'Terlambat']] as $i => $row)
                    <tr><td>{{ $i + 1 }}</td><td>{{ $row[0] }}</td><td>{{ $row[1] }}</td><td>{{ $row[2] }}</td><td><x-status-badge :status="$row[3]" /></td><td><button class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></button></td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

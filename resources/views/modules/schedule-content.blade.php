@php
    $isBarang = ($workType ?? 'barang') === 'barang';
@endphp

@if ($isBarang)
    <div class="work-plan-toolbar">
        <button class="btn btn-primary work-plan-update-btn" type="button">
            <i class="bi bi-plus-lg"></i>
            <span>Update Data</span>
        </button>
    </div>
@else
    <x-page-header :title="$workTypeLabel ?? 'Rencana Kerja'" subtitle="Kurva S, gantt chart, dan action log sementara untuk rancangan frontend.">
        <button class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Tambah Rencana Kerja</button>
    </x-page-header>
@endif

<div class="row g-3 mb-4">
    <div class="col-12 col-xl-5">
        <div class="content-card h-100 {{ $isBarang ? 'work-plan-chart-card' : '' }}">
            @if ($isBarang)
                <div class="work-plan-chart-heading">
                    <h2>Kurva S - Progress</h2>
                </div>
            @else
                <div class="card-heading"><h2>Kurva S</h2><span class="text-muted">Plan vs Actual</span></div>
            @endif
            <div class="chart-wrap {{ $isBarang ? 'work-plan-curve' : '' }}"><canvas data-chart="line"></canvas></div>
        </div>
    </div>
    <div class="col-12 col-xl-7">
        <div class="content-card h-100 {{ $isBarang ? 'work-plan-chart-card' : '' }}">
            @if ($isBarang)
                <div class="work-plan-chart-heading">
                    <h2>Gantt Chart - Progress</h2>
                    <span>Jadwal Overhaul - 2027</span>
                </div>
                <div class="work-plan-gantt" role="img" aria-label="Gantt chart jadwal overhaul Rencana Kerja Barang">
                    @foreach ([
                        ['Tonasa 5 (Kiln, RM, CM & Crusher)', '30 Hari', 0, 100, 'blue'],
                        ['Tonasa 4 (Finish Mill 420)', '25 Hari', 8, 86, 'green'],
                        ['Tonasa 4 (Kiln, RM, CM) & FM 419', '20 Hari', 28, 65, 'orange'],
                        ['Tonasa 2 & 3 (Finish Mill 2, 3 & Crusher)', '16 Hari', 46, 52, 'slate'],
                    ] as [$label, $duration, $start, $length, $color])
                        <div class="work-plan-gantt-row">
                            <span class="work-plan-gantt-label">{{ $label }}</span>
                            <span class="work-plan-gantt-track">
                                <span class="work-plan-gantt-bar is-{{ $color }}" style="--bar-start: {{ $start }}%; --bar-length: {{ $length }}%;"></span>
                            </span>
                            <strong class="work-plan-gantt-duration is-{{ $color }}">{{ $duration }}</strong>
                        </div>
                    @endforeach
                    <div class="work-plan-gantt-axis" aria-hidden="true">
                        <span>Hari 1</span><span>Hari 8</span><span>Hari 15</span><span>Hari 22</span><span>Hari 30</span>
                    </div>
                </div>
            @else
                <div class="card-heading"><h2>Gantt Chart</h2><span class="text-muted">Placeholder</span></div>
                <div class="placeholder-panel gantt-placeholder">
                    <img src="{{ asset('assets/images/placeholders/gantt-placeholder.svg') }}" alt="Gantt placeholder">
                </div>
            @endif
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

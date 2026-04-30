<x-user.page-header :title="$title" :subtitle="$subtitle" eyebrow="PGO Tasks" />

<x-user.filter-card title="Filter Tugas PGO" subtitle="Filter dummy untuk menyaring tugas berdasarkan tahun, plant, area, dan status.">
    <div class="row g-3">
        <div class="col-md-6 col-xl-3"><label class="form-label">Tahun</label><select class="form-select">@foreach ($filters['years'] as $option)<option>{{ $option }}</option>@endforeach</select></div>
        <div class="col-md-6 col-xl-3"><label class="form-label">Plant</label><select class="form-select">@foreach ($filters['plants'] as $option)<option>{{ $option }}</option>@endforeach</select></div>
        <div class="col-md-6 col-xl-3"><label class="form-label">Area</label><select class="form-select">@foreach ($filters['areas'] as $option)<option>{{ $option }}</option>@endforeach</select></div>
        <div class="col-md-6 col-xl-3"><label class="form-label">Status</label><select class="form-select">@foreach ($filters['statuses'] as $option)<option>{{ $option }}</option>@endforeach</select></div>
    </div>
</x-user.filter-card>

<section class="inspector-panel">
    <div class="table-responsive d-none d-lg-block">
        <table class="table align-middle inspector-table">
            <thead><tr><th>No</th><th>Tanggal</th><th>Pekerjaan</th><th>Plant</th><th>Area</th><th>PIC</th><th>Status</th><th class="text-end">Action</th></tr></thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr>
                        <td>{{ $row['no'] }}</td>
                        <td>{{ $row['date'] }}</td>
                        <td>{{ $row['job'] }}</td>
                        <td>{{ $row['plant'] }}</td>
                        <td>{{ $row['area'] }}</td>
                        <td>{{ $row['pic'] }}</td>
                        <td><x-user.status-badge :status="$row['status']" /></td>
                        <td class="text-end"><button type="button" class="btn btn-sm btn-primary">Detail</button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="d-grid gap-3 d-lg-none">
        @foreach ($rows as $row)
            <x-user.task-card :task="['job' => $row['job'], 'plant' => $row['plant'], 'area' => $row['area'], 'date' => $row['date'], 'pic' => $row['pic'], 'status' => $row['status']]">
                <button type="button" class="btn btn-primary w-100">Detail</button>
            </x-user.task-card>
        @endforeach
    </div>
</section>

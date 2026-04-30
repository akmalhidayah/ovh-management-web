<x-user.page-header :title="$title" :subtitle="$subtitle" eyebrow="Draft Workspace">
    <a href="{{ route($continueRoute) }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Buat Form Baru
    </a>
</x-user.page-header>

<x-user.filter-card title="Filter Draft" subtitle="Gunakan filter dummy untuk mensimulasikan pencarian draft berdasarkan kategori, form, plant, atau status.">
    <div class="row g-3">
        <div class="col-md-6 col-xl-2">
            <label class="form-label">Kategori</label>
            <select class="form-select">@foreach ($filters['categories'] as $option)<option>{{ $option }}</option>@endforeach</select>
        </div>
        <div class="col-md-6 col-xl-3">
            <label class="form-label">Jenis Form</label>
            <select class="form-select">@foreach ($filters['form_types'] as $option)<option>{{ $option }}</option>@endforeach</select>
        </div>
        <div class="col-md-6 col-xl-2">
            <label class="form-label">Plant</label>
            <select class="form-select">@foreach ($filters['plants'] as $option)<option>{{ $option }}</option>@endforeach</select>
        </div>
        <div class="col-md-6 col-xl-2">
            <label class="form-label">Status</label>
            <select class="form-select">@foreach ($filters['statuses'] as $option)<option>{{ $option }}</option>@endforeach</select>
        </div>
        <div class="col-md-6 col-xl-3">
            <label class="form-label">Tanggal</label>
            <input type="date" class="form-control" value="2025-05-21">
        </div>
    </div>
</x-user.filter-card>

<section class="inspector-panel">
    <div class="table-responsive d-none d-lg-block">
        <table class="table align-middle inspector-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kategori</th>
                    <th>Jenis Form</th>
                    <th>Equipment</th>
                    <th>Plant</th>
                    <th>Area</th>
                    <th>Terakhir Diubah</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr>
                        <td>{{ $row['no'] }}</td>
                        <td>{{ $row['category'] }}</td>
                        <td>{{ $row['form_type'] }}</td>
                        <td>{{ $row['equipment'] }}</td>
                        <td>{{ $row['plant'] }}</td>
                        <td>{{ $row['area'] }}</td>
                        <td>{{ $row['updated_at'] }}</td>
                        <td><x-user.status-badge :status="$row['status']" /></td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                <a href="{{ route($continueRoute) }}" class="btn btn-sm btn-primary">Lanjutkan</a>
                                <button type="button" class="btn btn-sm btn-outline-secondary">Preview</button>
                                <button type="button" class="btn btn-sm btn-outline-danger">Hapus</button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="d-grid gap-3 d-lg-none">
        @foreach ($rows as $row)
            <article class="inspector-mobile-card">
                <div class="d-flex justify-content-between gap-3 mb-3">
                    <div>
                        <span class="inspector-chip">{{ $row['category'] }}</span>
                        <h3 class="mt-2">{{ $row['form_type'] }}</h3>
                    </div>
                    <x-user.status-badge :status="$row['status']" />
                </div>
                <p class="mb-1"><strong>Equipment:</strong> {{ $row['equipment'] }}</p>
                <p class="mb-1"><strong>Plant:</strong> {{ $row['plant'] }}</p>
                <p class="mb-3"><strong>Area:</strong> {{ $row['area'] }}</p>
                <small class="text-muted d-block mb-3">Terakhir diubah {{ $row['updated_at'] }}</small>
                <div class="d-grid gap-2">
                    <a href="{{ route($continueRoute) }}" class="btn btn-primary">Lanjutkan</a>
                    <button type="button" class="btn btn-outline-secondary">Preview</button>
                    <button type="button" class="btn btn-outline-danger">Hapus</button>
                </div>
            </article>
        @endforeach
    </div>
</section>

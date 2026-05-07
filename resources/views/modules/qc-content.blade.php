@php
    $pageTitle = $pageTitle ?? 'QC';
    $statusLabels = $statusLabels ?? [];
    $summary = $summary ?? ['total' => 0, 'submitted' => 0, 'approved' => 0, 'revision' => 0];
    $filterOptions = $filterOptions ?? ['years' => collect(), 'plants' => collect(), 'areas' => collect(), 'templates' => collect()];
    $filters = $filters ?? ['status' => 'all', 'template_id' => 'all', 'year' => 'all', 'plant' => 'all', 'area' => 'all', 'search' => ''];
    $indexRouteName = 'admin.qc';
    $statusClasses = [
        'submitted' => 'text-bg-info',
        'approved' => 'text-bg-success',
        'revision' => 'text-bg-warning',
    ];
@endphp

<div class="row g-3 mb-4">
    <div class="col-12 col-md-6 col-xl-3"><x-stat-card title="Total Submission" :value="$summary['total']" icon="bi-shield-check" tone="primary" /></div>
    <div class="col-12 col-md-6 col-xl-3"><x-stat-card title="Menunggu Review" :value="$summary['submitted']" icon="bi-hourglass-split" tone="info" /></div>
    <div class="col-12 col-md-6 col-xl-3"><x-stat-card title="Disetujui" :value="$summary['approved']" icon="bi-check2-circle" tone="success" /></div>
    <div class="col-12 col-md-6 col-xl-3"><x-stat-card title="Perlu Revisi" :value="$summary['revision']" icon="bi-exclamation-triangle" tone="warning" /></div>
</div>

<form method="GET" action="{{ route($indexRouteName) }}">
    <x-filter-card>
        <div class="col-12 col-md-6 col-xl-2">
            <label class="form-label">Tahun</label>
            <select class="form-select" name="year">
                <option value="all">Semua Tahun</option>
                @foreach ($filterOptions['years'] as $year)
                    <option value="{{ $year }}" @selected($filters['year'] == $year)>{{ $year }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-6 col-xl-2">
            <label class="form-label">Plant</label>
            <select class="form-select" name="plant">
                <option value="all">Semua Plant</option>
                @foreach ($filterOptions['plants'] as $plant)
                    <option value="{{ $plant }}" @selected($filters['plant'] == $plant)>{{ $plant }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-6 col-xl-2">
            <label class="form-label">Area</label>
            <select class="form-select" name="area">
                <option value="all">Semua Area</option>
                @foreach ($filterOptions['areas'] as $area)
                    <option value="{{ $area }}" @selected($filters['area'] == $area)>{{ $area }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-6 col-xl-2">
            <label class="form-label">Status</label>
            <select class="form-select" name="status">
                <option value="all">Semua Status</option>
                @foreach ($statusLabels as $value => $label)
                    @continue($value === 'draft')
                    <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-6 col-xl-2">
            <label class="form-label">Template</label>
            <select class="form-select" name="template_id">
                <option value="all">Semua Template</option>
                @foreach ($filterOptions['templates'] as $template)
                    <option value="{{ $template->id }}" @selected((string) $filters['template_id'] === (string) $template->id)>{{ $template->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-6 col-xl-2">
            <label class="form-label">Cari</label>
            <div class="d-flex gap-2">
                <input type="search" class="form-control" name="search" value="{{ $filters['search'] }}" placeholder="No form / equipment">
                <button class="btn btn-primary" type="submit"><i class="bi bi-funnel"></i></button>
            </div>
        </div>
    </x-filter-card>
</form>

<div class="content-card">
    <div class="table-responsive">
        <table class="table align-middle ovh-table template-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tahun</th>
                    <th>Plant</th>
                    <th>Area</th>
                    <th>Equipment</th>
                    <th>Jenis Pemeriksaan</th>
                    <th>Template</th>
                    <th>Status QC</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($submissions as $submission)
                    <tr>
                        <td>{{ $submissions->firstItem() + $loop->index }}</td>
                        <td>{{ $submission->year ?: $submission->submitted_at?->format('Y') ?: '-' }}</td>
                        <td>{{ $submission->plant ?: '-' }}</td>
                        <td>{{ $submission->area ?: '-' }}</td>
                        <td>
                            <div class="fw-semibold">{{ $submission->equipment ?: '-' }}</div>
                            <div class="template-meta">
                                <span>{{ $submission->form_number ?: '-' }}</span>
                                <span>{{ $submission->submitted_at?->format('d M Y H:i') ?: '-' }}</span>
                            </div>
                        </td>
                        <td>{{ $submission->pekerjaan ?: '-' }}</td>
                        <td>{{ $submission->template?->name ?: '-' }}</td>
                        <td>
                            <span class="badge {{ $statusClasses[$submission->status] ?? 'text-bg-secondary' }}">
                                {{ $statusLabels[$submission->status] ?? $submission->status }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="template-actions">
                                <a href="{{ route('admin.qc.submissions.pdf', $submission) }}" target="_blank" class="btn btn-sm btn-primary">
                                    <i class="bi bi-filetype-pdf me-1"></i>PDF
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">Belum ada submission QC dari user QC.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $submissions->links() }}
    </div>
</div>

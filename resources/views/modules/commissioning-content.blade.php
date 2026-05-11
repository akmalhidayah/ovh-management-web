<div class="page-header">
    <div>
        <h1>Commissioning</h1>
        <p>Monitoring form commissioning yang dibuat user role Commissioning.</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-md-4"><x-stat-card title="Total Commissioning" :value="$summary['total']" icon="bi-tools" tone="primary" /></div>
    <div class="col-12 col-md-4"><x-stat-card title="Submitted" :value="$summary['submitted']" icon="bi-check2-circle" tone="success" /></div>
    <div class="col-12 col-md-4"><x-stat-card title="Draft" :value="$summary['draft']" icon="bi-pencil-square" tone="warning" /></div>
</div>

<div class="content-card">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-12 col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">Semua</option>
                <option value="submitted" @selected(request('status') === 'submitted')>Submitted</option>
                <option value="draft" @selected(request('status') === 'draft')>Draft</option>
            </select>
        </div>
        <div class="col-12 col-md-6">
            <label class="form-label">Cari</label>
            <input type="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Form no, equipment, area, functional location">
        </div>
        <div class="col-12 col-md-2 d-grid">
            <button class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Filter</button>
        </div>
    </form>
</div>

<div class="content-card">
    <div class="table-responsive">
        <table class="table align-middle ovh-table">
            <thead><tr><th>Form No</th><th>Template</th><th>User</th><th>Equipment</th><th>Area</th><th>Status</th><th>Submitted</th><th class="text-end">Action</th></tr></thead>
            <tbody>
                @forelse ($submissions as $submission)
                    <tr>
                        <td>{{ $submission->form_number }}</td>
                        <td>{{ $submission->template?->name }}</td>
                        <td>{{ $submission->user?->name ?: '-' }}</td>
                        <td>{{ $submission->equipment ?: '-' }}</td>
                        <td>{{ $submission->area ?: '-' }}</td>
                        <td><x-status-badge :status="$submission->status === 'submitted' ? 'Submitted' : 'Draft'" /></td>
                        <td>{{ $submission->submitted_at?->format('d M Y H:i') ?: '-' }}</td>
                        <td class="text-end">
                            @if ($submission->status === 'submitted')
                                <a href="{{ route('admin.commissioning.submissions.pdf', $submission) }}" class="btn btn-sm btn-success" target="_blank">PDF</a>
                            @else
                                <span class="text-muted small">Draft user</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">Belum ada submission commissioning.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $submissions->links() }}
</div>

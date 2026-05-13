@extends('layouts.user')

@section('title', 'Riwayat QC')

@section('content')
    <x-user.page-header title="Riwayat QC" subtitle="Daftar form QC yang sudah disubmit." eyebrow="History Workspace" />

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <section class="inspector-panel commissioning-list-panel">
        <div class="commissioning-list-toolbar">
            <div>
                <h2>Submission Terbaru</h2>
                <p>{{ $submissions->total() }} form QC tersimpan.</p>
            </div>
            <span class="commissioning-list-badge"><i class="bi bi-clock-history"></i>Submitted</span>
        </div>
        <form method="GET" action="{{ route('user.qc.history.index') }}" class="commissioning-filter-bar">
            <label for="qc-history-area">Area</label>
            <select id="qc-history-area" name="area" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="all" @selected(($selectedArea ?? 'all') === 'all')>Semua Area</option>
                @foreach ($areaOptions ?? [] as $area)
                    <option value="{{ $area }}" @selected(($selectedArea ?? 'all') === $area)>{{ $area }}</option>
                @endforeach
            </select>
            @if (($selectedArea ?? 'all') !== 'all')
                <a href="{{ route('user.qc.history.index') }}" class="btn btn-sm btn-light commissioning-icon-btn" title="Reset filter" aria-label="Reset filter">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </form>
        <div class="table-responsive">
            <table class="table align-middle commissioning-list-table">
                <thead>
                    <tr>
                        <th>Form</th>
                        <th>Template</th>
                        <th>Equipment</th>
                        <th>Lokasi</th>
                        <th>Submit</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($submissions as $submission)
                        <tr>
                            <td>
                                <div class="commissioning-form-no">{{ $submission->form_number }}</div>
                                <span class="commissioning-status-pill is-submitted">{{ $statusLabels[$submission->status] ?? $submission->status }}</span>
                            </td>
                            <td>
                                <div class="commissioning-main-text">{{ $submission->template?->name ?: '-' }}</div>
                                <small>{{ $submission->template?->category ?: 'QC' }}</small>
                            </td>
                            <td>
                                <div class="commissioning-main-text">{{ $submission->equipment ?: '-' }}</div>
                                <small>{{ $submission->tag_num ?: 'Tanpa section' }}</small>
                            </td>
                            <td>
                                <div class="commissioning-main-text">{{ $submission->area ?: '-' }}</div>
                                <small>{{ $submission->plant ?: 'Tanpa plant' }}</small>
                            </td>
                            <td>
                                <div class="commissioning-main-text">{{ $submission->submitted_at?->format('d M Y') ?: '-' }}</div>
                                <small>{{ $submission->submitted_at?->format('H:i') ?: '-' }}</small>
                            </td>
                            <td class="text-end">
                                <div class="commissioning-actions">
                                    <a href="{{ route('user.qc.submissions.show', $submission) }}" class="btn btn-sm btn-outline-primary commissioning-icon-btn" title="Detail" aria-label="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('user.qc.submissions.pdf', $submission) }}" class="btn btn-sm btn-success commissioning-icon-btn" target="_blank" title="PDF" aria-label="PDF">
                                        <i class="bi bi-file-earmark-pdf"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">Belum ada form QC yang disubmit.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $submissions->links() }}
        </div>
    </section>
@endsection

@push('styles')
<style>
    .commissioning-list-panel { padding: 1.25rem; }
    .commissioning-list-toolbar { display: flex; justify-content: space-between; gap: 1rem; align-items: center; margin-bottom: 1rem; }
    .commissioning-list-toolbar h2 { margin: 0; font-size: 1.1rem; font-weight: 800; color: #172033; }
    .commissioning-list-toolbar p { margin: .2rem 0 0; color: #64748b; }
    .commissioning-list-badge { display: inline-flex; align-items: center; gap: .45rem; padding: .45rem .7rem; border-radius: 999px; background: #f1f5f9; color: #475569; font-weight: 700; }
    .commissioning-filter-bar { display: flex; align-items: center; justify-content: flex-end; gap: .55rem; margin-bottom: 1rem; }
    .commissioning-filter-bar label { color: #475569; font-size: .82rem; font-weight: 800; }
    .commissioning-filter-bar .form-select { width: min(260px, 100%); }
    .commissioning-list-table { min-width: 1040px; margin-bottom: 0; }
    .commissioning-list-table thead th { padding: .85rem .75rem; background: #f8fafc; color: #475569; font-size: .78rem; letter-spacing: .04em; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; }
    .commissioning-list-table tbody td { padding: 1rem .75rem; border-bottom: 1px solid #e5e7eb; vertical-align: middle; }
    .commissioning-form-no, .commissioning-main-text { font-weight: 800; color: #172033; }
    .commissioning-list-table small { color: #64748b; }
    .commissioning-status-pill { display: inline-flex; margin-top: .4rem; padding: .22rem .55rem; border-radius: 999px; font-size: .75rem; font-weight: 800; }
    .commissioning-status-pill.is-submitted { background: #dcfce7; color: #166534; }
    .commissioning-actions { display: inline-flex; flex-wrap: nowrap; justify-content: flex-end; gap: .4rem; }
    .commissioning-icon-btn { width: 2.15rem; height: 2.15rem; display: inline-flex; align-items: center; justify-content: center; padding: 0; }
    @media (max-width: 767.98px) { .commissioning-list-toolbar { align-items: flex-start; flex-direction: column; } .commissioning-filter-bar { justify-content: flex-start; flex-wrap: wrap; } }
</style>
@endpush

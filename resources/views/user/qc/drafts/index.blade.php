@extends('layouts.user')

@section('title', 'Draft Form QC')

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <section class="inspector-panel commissioning-list-panel">
        <div class="commissioning-list-toolbar">
            <div>
                <h2>Draft Aktif</h2>
                <p>{{ $submissions->total() }} draft QC menunggu dilanjutkan.</p>
            </div>
            <div class="commissioning-toolbar-actions">
                <span class="commissioning-list-badge is-draft"><i class="bi bi-journal-richtext"></i>Draft</span>
                <a href="{{ route('user.qc.forms.create') }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Buat Form Baru
                </a>
            </div>
        </div>
        <form method="GET" action="{{ route('user.qc.drafts.index') }}" class="commissioning-filter-bar">
            <label for="qc-draft-area">Area</label>
            <select id="qc-draft-area" name="area" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="all" @selected(($selectedArea ?? 'all') === 'all')>Semua Area</option>
                @foreach ($areaOptions ?? [] as $area)
                    <option value="{{ $area }}" @selected(($selectedArea ?? 'all') === $area)>{{ $area }}</option>
                @endforeach
            </select>
            @if (($selectedArea ?? 'all') !== 'all')
                <a href="{{ route('user.qc.drafts.index') }}" class="btn btn-sm btn-light commissioning-icon-btn" title="Reset filter" aria-label="Reset filter">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </form>
        <div class="table-responsive d-none d-md-block">
            <table class="table align-middle commissioning-list-table">
                <thead>
                    <tr>
                        <th>Form</th>
                        <th>Template</th>
                        <th>Equipment</th>
                        <th>Lokasi</th>
                        <th>Update</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($submissions as $submission)
                        <tr>
                            <td>
                                <div class="commissioning-form-no">{{ $submission->form_number }}</div>
                                <span class="commissioning-status-pill is-draft">{{ $statusLabels[$submission->status] ?? $submission->status }}</span>
                            </td>
                            <td>
                                <div class="commissioning-main-text">{{ $submission->template?->name ?: '-' }}</div>
                                <small>{{ $submission->template?->category ?: 'QC' }}</small>
                            </td>
                            <td>
                                <div class="commissioning-main-text">{{ $submission->equipment ?: '-' }}</div>
                                <small>{{ $submission->tag_num ?: 'Belum ada section' }}</small>
                            </td>
                            <td>
                                <div class="commissioning-main-text">{{ $submission->area ?: '-' }}</div>
                                <small>{{ $submission->plant ?: 'Belum ada plant' }}</small>
                            </td>
                            <td>
                                <div class="commissioning-main-text">{{ $submission->updated_at?->format('d M Y') ?: '-' }}</div>
                                <small>{{ $submission->updated_at?->format('H:i') ?: '-' }}</small>
                            </td>
                            <td class="text-end">
                                <div class="commissioning-actions">
                                    <a href="{{ route('user.qc.submissions.edit', $submission) }}" class="btn btn-sm btn-primary commissioning-icon-btn" title="Lanjutkan" aria-label="Lanjutkan">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">Belum ada draft QC.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('user.shared.submission-mobile-list', [
            'submissionRole' => 'qc',
            'submissionMode' => 'draft',
            'emptyMessage' => 'Belum ada draft QC.',
        ])

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
    .commissioning-toolbar-actions { display: inline-flex; align-items: center; gap: .55rem; flex-wrap: wrap; }
    .commissioning-list-badge { display: inline-flex; align-items: center; gap: .45rem; padding: .45rem .7rem; border-radius: 999px; background: #f1f5f9; color: #475569; font-weight: 700; }
    .commissioning-list-badge.is-draft { background: #fff7ed; color: #9a3412; }
    .commissioning-filter-bar { display: flex; align-items: center; justify-content: flex-end; gap: .55rem; margin-bottom: 1rem; }
    .commissioning-filter-bar label { color: #475569; font-size: .82rem; font-weight: 800; }
    .commissioning-filter-bar .form-select { width: min(260px, 100%); }
    .commissioning-list-table { min-width: 1040px; margin-bottom: 0; }
    .commissioning-list-table thead th { padding: .85rem .75rem; background: #f8fafc; color: #475569; font-size: .78rem; letter-spacing: .04em; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; }
    .commissioning-list-table tbody td { padding: 1rem .75rem; border-bottom: 1px solid #e5e7eb; vertical-align: middle; }
    .commissioning-form-no, .commissioning-main-text { font-weight: 800; color: #172033; }
    .commissioning-list-table small { color: #64748b; }
    .commissioning-status-pill { display: inline-flex; margin-top: .4rem; padding: .22rem .55rem; border-radius: 999px; font-size: .75rem; font-weight: 800; }
    .commissioning-status-pill.is-draft { background: #ffedd5; color: #9a3412; }
    .commissioning-actions { display: inline-flex; flex-wrap: nowrap; justify-content: flex-end; gap: .4rem; }
    .commissioning-icon-btn { width: 2.15rem; height: 2.15rem; display: inline-flex; align-items: center; justify-content: center; padding: 0; }
    @media (max-width: 767.98px) { .commissioning-list-toolbar { align-items: flex-start; flex-direction: column; } .commissioning-filter-bar { justify-content: flex-start; flex-wrap: wrap; } }
</style>
@endpush

@include('user.shared.submission-mobile-list-styles')

@extends('layouts.user')

@section('title', 'Detail Form QC')

@section('content')
    <x-user.page-header title="Detail Form QC" subtitle="Preview data form QC yang tersimpan.">
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('user.qc.submissions.pdf', $submission) }}" target="_blank" class="btn btn-primary">
                <i class="bi bi-file-earmark-pdf me-2"></i>Download PDF
            </a>
            @if ($canCopyApprovalLink)
                <button type="button" class="btn btn-warning" data-copy-approval-link-url="{{ route('user.qc.submissions.approval-link', $submission) }}">
                    <i class="bi bi-link-45deg me-2"></i>Salin Link Approval
                </button>
            @endif
            <a href="{{ in_array($submission->status, ['draft', 'revision_required'], true) ? route('user.qc.drafts.index') : route('user.qc.history.index') }}" class="btn btn-outline-secondary">Kembali</a>
        </div>
    </x-user.page-header>

    @if (in_array($submission->status, ['draft', 'revision_required'], true))
        @include('qc-submissions._detail', ['submission' => $submission, 'statusLabels' => $statusLabels])
    @else
        <section class="inspector-panel qc-form-card">
            <div class="qc-form-card-head">
                <div>
                    <span>{{ $submission->form_number }}</span>
                    <h2>{{ $submission->template_name ?? $submission->template?->name ?? '-' }}</h2>
                    <p>{{ $statusLabels[$submission->status] ?? $submission->status }}</p>
                </div>
                <div class="qc-form-code">
                    <strong>{{ $submission->report_no ?: $submission->form_number }}</strong>
                    <span>{{ $submission->submitted_at?->format('d M Y H:i') ?: '-' }}</span>
                </div>
            </div>
        </section>
    @endif
@endsection

@include('approvals._copy-link-script')

@extends('layouts.user')

@section('title', 'Detail Commissioning')

@section('content')
    @php($header = $submission->header_data ?? [])
    @php($body = $submission->body_data ?? [])
    @php($schema = \App\Support\Commissioning\FixedCommissioningTemplate::normalizeSchema($submission->template?->body_schema))
    @php($labels = $schema['labels'])
    <div class="user-simple-form-header">
        <h1>{{ $submission->form_number }}</h1>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('user.commissioning.submissions.pdf', $submission) }}" class="btn btn-success" target="_blank">PDF</a>
            @if ($canCopyApprovalLink)
                <button type="button" class="btn btn-warning" data-copy-approval-link-url="{{ route('user.commissioning.submissions.approval-link', $submission) }}">Salin Link Approval</button>
            @endif
            <a href="{{ in_array($submission->status, ['draft', 'revision_required'], true) ? route('user.commissioning.drafts.index') : route('user.commissioning.history.index') }}" class="btn btn-outline-secondary">Kembali</a>
        </div>
    </div>
    @if (in_array($submission->status, ['draft', 'revision_required'], true))
        <section class="inspector-panel qc-form-card">
            <div class="qc-form-section-title"><h3>Informasi Umum</h3></div>
            <div class="qc-user-field-grid">
                @foreach (\App\Support\Commissioning\FixedCommissioningTemplate::headerFields() as $field)
                    <div class="qc-user-field"><span>{{ $field['label'] }}</span><div class="form-control bg-light">{{ $header[$field['key']] ?? '-' }}</div></div>
                @endforeach
            </div>
        </section>
    @else
        <section class="inspector-panel qc-form-card">
            <div class="qc-form-card-head">
                <div>
                    <span>{{ $submission->form_number }}</span>
                    <h2>{{ $submission->template_name ?? $submission->template?->name ?? '-' }}</h2>
                    <p>{{ Str::headline($submission->status) }}</p>
                </div>
                <div class="qc-form-code">
                    <strong>{{ $submission->form_number }}</strong>
                    <span>{{ $submission->submitted_at?->format('d M Y H:i') ?: '-' }}</span>
                </div>
            </div>
        </section>
    @endif
@endsection

@include('approvals._copy-link-script')

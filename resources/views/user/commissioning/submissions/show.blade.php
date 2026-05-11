@extends('layouts.user')

@section('title', 'Detail Commissioning')

@section('content')
    @php($header = $submission->header_data ?? [])
    @php($body = $submission->body_data ?? [])
    @php($schema = \App\Support\Commissioning\FixedCommissioningTemplate::normalizeSchema($submission->template?->body_schema))
    @php($labels = $schema['labels'])
    <div class="user-simple-form-header">
        <h1>{{ $submission->form_number }}</h1>
        <a href="{{ route('user.commissioning.submissions.pdf', $submission) }}" class="btn btn-success" target="_blank">PDF</a>
    </div>
    <section class="inspector-panel qc-form-card">
        <div class="qc-form-section-title"><h3>Informasi Umum</h3></div>
        <div class="qc-user-field-grid">
            @foreach (\App\Support\Commissioning\FixedCommissioningTemplate::headerFields() as $field)
                <div class="qc-user-field"><span>{{ $field['label'] }}</span><div class="form-control bg-light">{{ $header[$field['key']] ?? '-' }}</div></div>
            @endforeach
        </div>
    </section>
    <section class="inspector-panel qc-form-card">
        <div class="qc-form-section-title"><h3>{{ $labels['equipment_check_title'] }}</h3></div>
        <div class="table-responsive">
            <table class="commissioning-table">
                <thead><tr><th>No</th><th>Item</th><th>Check</th><th>Result</th><th>Remark</th></tr></thead>
                <tbody>
                    @foreach (($body['equipment_check_rows'] ?? []) as $row)
                        <tr><td>{{ $row['no'] ?? $loop->iteration }}</td><td>{{ $row['item'] ?? '-' }}</td><td>{{ ! empty($row['check']) ? 'Ya' : 'Tidak' }}</td><td>{{ $row['result'] ?? '-' }}</td><td>{{ $row['remark'] ?? '-' }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection

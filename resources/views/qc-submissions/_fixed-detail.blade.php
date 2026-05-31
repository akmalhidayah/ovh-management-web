@php
    use App\Support\QcTemplates\FixedQcTemplate;

    $templateSnapshot = $submission->template_snapshot ?? [];
    $templateType = $templateSnapshot['template_type'] ?? $submission->template?->template_type;
    $templateName = $submission->template_name ?? $templateSnapshot['name'] ?? $submission->template?->name;
    $templateBodySchema = $templateSnapshot['body_schema'] ?? $submission->template?->body_schema ?? [];
    $type = FixedQcTemplate::normalizeType($templateType);
    $generalInfo = $submission->general_info ?? [];
    $bodyData = $submission->body_data ?? [];
    $approvalData = $submission->approval_data ?? [];
    $schema = $templateType ? FixedQcTemplate::normalizeSchema($type, $templateBodySchema) : [];
    $approvalDefaults = $schema['approval_defaults'] ?? FixedQcTemplate::defaultApprovalDefaults($type);
    $rows = $submission->rows->groupBy('block_type');
    $headerRows = FixedQcTemplate::headerRows($type);
    $headerFieldMap = collect(FixedQcTemplate::headerFields($type))->keyBy('key');
    $headerValue = static function (string $key, mixed $fallback = '-') use ($generalInfo, $submission) {
        return match ($key) {
            'doc_number' => $generalInfo[$key] ?? $submission->report_no ?? $submission->form_number ?? $fallback,
            'plant' => $generalInfo[$key] ?? $submission->plant ?? $fallback,
            'date_time' => $generalInfo[$key] ?? optional($submission->submitted_at)->format('Y-m-d H:i') ?? $fallback,
            'inspector_qc' => $generalInfo[$key] ?? $submission->user?->name ?? $fallback,
            'unit_kerja' => $generalInfo[$key] ?? $submission->unit ?? $fallback,
            'durasi' => $generalInfo[$key] ?? $submission->durasi ?? $fallback,
            'pekerjaan' => $generalInfo[$key] ?? $submission->pekerjaan ?? $fallback,
            default => $generalInfo[$key] ?? $fallback,
        };
    };
@endphp

<div class="qc-submission-detail">
    <section class="inspector-panel qc-form-card">
        <div class="qc-form-card-head">
            <div>
                <span>{{ $submission->form_number }}</span>
                <h2>{{ $templateName }}</h2>
                <p>{{ FixedQcTemplate::templateTypeLabel($type) }} - {{ $statusLabels[$submission->status] ?? $submission->status }}</p>
            </div>
            <div class="qc-form-code">
                <strong>{{ $submission->report_no ?: $submission->form_number }}</strong>
                <span>{{ $submission->submitted_at?->format('d M Y H:i') ?: 'Draft' }}</span>
            </div>
        </div>
    </section>

    <section class="inspector-panel qc-form-card">
        <div class="qc-form-section-title"><h3>Header</h3></div>
        <div class="qc-user-field-grid">
            @foreach ($headerRows as $row)
                @foreach ($row as $fieldKey)
                    @php
                        $field = $headerFieldMap[$fieldKey];
                    @endphp
                    <div class="qc-user-field">
                        <span>{{ $field['label'] }}</span>
                        <div class="form-control bg-light">{{ $headerValue($fieldKey) }}</div>
                    </div>
                @endforeach
            @endforeach
        </div>
    </section>

    @if ($type === FixedQcTemplate::TYPE_WELDING)
        <section class="inspector-panel qc-form-card">
            <div class="qc-form-section-title"><h3>Metode QC dan Pengecekan ke</h3></div>
            <div><strong>Metode QC:</strong> {{ implode(', ', $bodyData['methods'] ?? []) ?: '-' }}</div>
            <div><strong>Pengecekan ke:</strong> {{ implode(', ', $bodyData['check_steps'] ?? []) ?: '-' }}</div>
            <div><strong>Final Check:</strong> {{ ! empty($bodyData['final_check']) ? 'Ya' : 'Tidak' }}</div>
        </section>

        <section class="inspector-panel qc-form-card">
            <div class="qc-form-section-title"><h3>Tabel Welder</h3></div>
            <div class="qc-user-table-wrap">
                <table class="qc-user-checklist-table">
                    <thead><tr><th>No</th><th>Nama Welder</th><th>Posisi Pengelasan</th><th>Diameter Electrode</th><th>Electrode/Filter</th><th>Amper</th><th>Keterangan</th></tr></thead>
                    <tbody>
                        @forelse (($rows['welding_welder'] ?? collect()) as $row)
                            @php $data = $row->row_data ?? []; @endphp
                            <tr>
                                <td>{{ $data['no'] ?? $loop->iteration }}</td>
                                <td>{{ $data['nama_welder'] ?? '-' }}</td>
                                <td>{{ $data['posisi_pengelasan'] ?? '-' }}</td>
                                <td>{{ $data['diameter_electrode'] ?? '-' }}</td>
                                <td>{{ $data['electrode_filter'] ?? '-' }}</td>
                                <td>{{ $data['amper'] ?? '-' }}</td>
                                <td>{{ $data['keterangan'] ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-3">Belum ada row.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="inspector-panel qc-form-card">
            <div class="qc-form-section-title"><h3>Tabel Hasil QC Welding</h3></div>
            <div class="qc-user-table-wrap">
                <table class="qc-user-checklist-table">
                    <thead><tr><th>No</th><th>Deskripsi</th><th>Status</th><th>Keterangan</th></tr></thead>
                    <tbody>
                        @forelse (($rows['welding_result'] ?? collect()) as $row)
                            @php $data = $row->row_data ?? []; @endphp
                            <tr>
                                <td>{{ $data['no'] ?? $loop->iteration }}</td>
                                <td>{{ $data['deskripsi'] ?? '-' }}</td>
                                <td>{{ $row->status_value ?: '-' }}</td>
                                <td>{{ $row->catatan ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">Belum ada row.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @elseif ($type === FixedQcTemplate::TYPE_CASTABLE)
        <section class="inspector-panel qc-form-card">
            <div class="qc-form-section-title"><h3>Customer Data</h3></div>
            <div class="qc-user-table-wrap">
                <table class="qc-user-checklist-table">
                    <tbody>
                        @foreach (FixedQcTemplate::castableCustomerRows() as $item)
                            <tr><td>{{ $item['no'] }}</td><td>{{ $item['label'] }}</td><td>{{ $bodyData['castable_customer'][$item['key']] ?? '-' }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
        <section class="inspector-panel qc-form-card">
            <div class="qc-form-section-title"><h3>Installation Record / Inspection Check List</h3></div>
            <div class="qc-user-table-wrap">
                <table class="qc-user-checklist-table">
                    <thead><tr><th>No</th><th>Item</th><th>Status / Value</th><th>Detail</th></tr></thead>
                    <tbody>
                        @foreach (FixedQcTemplate::castableInspectionRows() as $item)
                            @php
                                $saved = $bodyData['castable_checks'][$item['key']] ?? [];
                                $dimensions = $saved['dimensions'] ?? [];
                                $dimensionValue = collect([
                                    $dimensions['length'] ?? '',
                                    $dimensions['width'] ?? '',
                                    $dimensions['height'] ?? '',
                                ])->filter()->implode(' x ');
                                $displayValue = ($item['input'] ?? null) === 'dimension'
                                    ? ($dimensionValue ? "( {$dimensionValue} )" : ($saved['value'] ?? '-'))
                                    : ($saved['status'] ?? $saved['value'] ?? '-');
                            @endphp
                            <tr><td>{{ $item['no'] }}</td><td>{{ $item['label'] }}</td><td>{{ $displayValue }}</td><td>{{ $saved['detail'] ?? '-' }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @elseif ($type === FixedQcTemplate::TYPE_BRICS)
        <section class="inspector-panel qc-form-card">
            <div class="qc-form-section-title"><h3>Customer Data</h3></div>
            <div class="qc-user-table-wrap">
                <table class="qc-user-checklist-table">
                    <tbody>
                        @foreach (FixedQcTemplate::bricsCustomerRows() as $item)
                            <tr><td>{{ $item['no'] }}</td><td>{{ $item['label'] }}</td><td>{{ $bodyData['brics_customer'][$item['key']] ?? ($item['default'] ?? '-') }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
        <section class="inspector-panel qc-form-card">
            <div class="qc-form-section-title"><h3>Installation Record / Inspection Check List</h3></div>
            <div class="qc-user-table-wrap">
                <table class="qc-user-checklist-table">
                    <thead><tr><th>No</th><th>Item</th><th>Status</th><th>Remark</th></tr></thead>
                    <tbody>
                        @foreach (FixedQcTemplate::bricsInspectionSections() as $section)
                            <tr><th colspan="4">{{ trim(($section['number'] ?? '').' '.$section['title']) }}</th></tr>
                            @foreach ($section['items'] as $item)
                                @php
                                    $saved = $bodyData['brics_checks'][$item['key']] ?? [];
                                @endphp
                                <tr><td>{{ $item['no'] }}</td><td>{{ $item['label'] }}</td><td>{{ $saved['status'] ?? '-' }}</td><td>{{ $saved['remark'] ?? '-' }}</td></tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @else
        <section class="inspector-panel qc-form-card">
            <div><strong>Final Check:</strong> {{ ! empty($bodyData['final_check']) ? 'Ya' : 'Tidak' }}</div>
            <div class="qc-user-table-wrap mt-3">
                <table class="qc-user-checklist-table">
                    <thead><tr><th>Item Pengecekan</th><th>Standar</th><th>Status</th><th>Catatan</th></tr></thead>
                    <tbody>
                        @forelse (($rows['general'] ?? collect()) as $row)
                            @php $data = $row->row_data ?? []; @endphp
                            <tr>
                                <td>{{ $data['item_pengecekan'] ?? '-' }}</td>
                                <td>{{ $data['standar'] ?? '-' }}</td>
                                <td>{{ $row->status_value ?: '-' }}</td>
                                <td>{{ $row->catatan ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">Belum ada row.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    <section class="inspector-panel qc-form-card">
        <div class="qc-form-section-title"><h3>Catatan</h3></div>
        <div class="form-control bg-light" style="min-height: 92px;">{{ $submission->note ?: '-' }}</div>
    </section>

    <section class="inspector-panel qc-form-card">
        <div class="qc-form-section-title"><h3>Lampiran Foto/Gambar</h3></div>
        <div class="qc-attachment-grid">
            @forelse ($submission->attachments as $attachment)
                @php
                    $attachmentUrl = route('user.qc.attachments.show', $attachment);
                @endphp
                <a href="{{ $attachmentUrl }}" target="_blank" class="qc-upload-box text-decoration-none text-reset">
                    <div class="qc-upload-box-head">
                        <div>
                            <strong>{{ $attachment->label ?: $attachment->field_key }}</strong>
                            <span>{{ $attachment->original_name }}</span>
                        </div>
                        <i class="bi {{ $attachment->type === 'image' ? 'bi-image' : 'bi-file-earmark-text' }}"></i>
                    </div>
                    @if ($attachment->type === 'image')
                        <img src="{{ $attachmentUrl }}" alt="{{ $attachment->original_name }}" class="img-fluid rounded border">
                    @endif
                </a>
            @empty
                <div class="qc-empty-inline">Belum ada lampiran.</div>
            @endforelse
        </div>
    </section>

    <section class="inspector-panel qc-form-card">
        <div class="qc-form-section-title"><h3>Approval Footer</h3></div>
        <p class="text-muted">Baru bisa ter approve jika form sudah terisi semua & Final Check sudah tercentang:</p>
        @php
            $approvalColumns = FixedQcTemplate::approvalColumnsWithDefaults($type, $approvalDefaults, $approvalData, true);
        @endphp
        <div class="qc-user-approval-grid" style="--qc-approval-columns: {{ count($approvalColumns) }}">
            @foreach ($approvalColumns as $column)
                @php $approval = $approvalData[$column['key']] ?? []; @endphp
                <div class="qc-user-approval-box">
                    <strong>{{ $column['label'] }}</strong>
                    <small class="text-muted d-block">{{ $column['group'] }}</small>
                    <div>{{ $approval['name'] ?? ($approvalDefaults[$column['key']]['name'] ?? '-') ?: '-' }}</div>
                    <div>{{ $approval['date'] ?? '-' }}</div>
                    @if (! empty($approval['signature']))
                        <img src="{{ $approval['signature'] }}" alt="Tanda tangan" style="max-height: 54px;">
                    @endif
                </div>
            @endforeach
        </div>
    </section>
</div>

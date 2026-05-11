@php
    $generalInfo = $submission->general_info ?? [];
    $approvalData = $submission->approval_data ?? [];
    $rowsByBlock = $submission->rows->groupBy('qc_form_template_block_id');
    $signatureApproval = collect($approvalData)->first(fn ($approval) => is_array($approval) && ! empty($approval['signature']));
    $signedDateValue = $signatureApproval['signed_at'] ?? ($approvalData['tanggal'] ?? null);
    $signedDate = $signedDateValue ? \Illuminate\Support\Carbon::parse($signedDateValue)->format('d M Y H:i') : '-';
    $signerName = $signatureApproval['name'] ?? ($submission->user?->name ?: 'User QC');
    $signerRole = $signatureApproval['role'] ?? 'Quality Control Personil';
@endphp

@if ($submission->template?->template_type)
    @include('qc-submissions._fixed-detail', ['submission' => $submission, 'statusLabels' => $statusLabels])
@else
<div class="qc-submission-detail">
    <section class="inspector-panel qc-form-card">
        <div class="qc-form-card-head">
            <div>
                <span>{{ $submission->form_number }}</span>
                <h2>{{ $submission->template?->name }}</h2>
                <p>{{ $submission->template?->category ?: 'QC' }} - {{ $statusLabels[$submission->status] ?? $submission->status }}</p>
            </div>
            <div class="qc-form-code">
                <strong>{{ $submission->report_no ?: $submission->form_number }}</strong>
                <span>{{ $submission->submitted_at?->format('d M Y H:i') ?: 'Draft' }}</span>
            </div>
        </div>
    </section>

    <section class="inspector-panel qc-form-card">
        <div class="qc-form-section-title">
            <h3>Informasi Umum</h3>
        </div>
        <div class="qc-user-field-grid">
            @foreach ([
                'report_no' => 'Report No.',
                'ovh_plant' => 'OVH Plant',
                'tahun' => 'Tahun',
                'unit' => 'Unit',
                'alat' => 'Alat',
                'tag_num' => 'Tag Num.',
                'tgl_mulai' => 'Tgl. Mulai',
                'pekerjaan' => 'Pekerjaan',
                'durasi' => 'Durasi',
            ] as $key => $label)
                <div class="qc-user-field {{ $key === 'pekerjaan' ? 'wide' : '' }}">
                    <span>{{ $label }}</span>
                    <div class="form-control bg-light">{{ $generalInfo[$key] ?? $submission->{$key} ?? '-' }}</div>
                </div>
            @endforeach
        </div>
    </section>

    @foreach ($submission->template?->blocks ?? [] as $block)
        @continue(! in_array($block->type, ['checklist_table', 'measurement_table'], true))
        @php
            $columns = $block->config['columns'] ?? [];
            $blockRows = $rowsByBlock[$block->id] ?? collect();
        @endphp
        <section class="inspector-panel qc-form-card">
            <div class="qc-form-section-title">
                <h3>{{ $block->title }}</h3>
            </div>
            <div class="qc-user-table-wrap">
                <table class="qc-user-checklist-table">
                    <thead>
                        <tr>
                            @foreach ($columns as $column)
                                <th>{{ is_array($column) ? ($column['label'] ?? $column['key'] ?? '') : $column }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($blockRows as $row)
                            @php($data = $row->row_data ?? [])
                            <tr>
                                @foreach ($columns as $column)
                                    @php($key = is_array($column) ? ($column['key'] ?? Str::snake($column['label'] ?? 'kolom')) : Str::snake($column))
                                    <td>
                                        @if ($key === 'status')
                                            {{ $row->status_value ?: '-' }}
                                        @elseif ($key === 'catatan')
                                            {{ $row->catatan ?: '-' }}
                                        @elseif ($key === 'actual')
                                            {{ $row->aktual ?: '-' }}
                                        @else
                                            {{ $data[$key] ?? '-' }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr><td colspan="{{ max(count($columns), 1) }}" class="text-center text-muted py-3">Belum ada row.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endforeach

    <section class="inspector-panel qc-form-card">
        <div class="qc-form-section-title"><h3>Catatan</h3></div>
        <div class="form-control bg-light" style="min-height: 92px;">{{ $submission->note ?: '-' }}</div>
    </section>

    <section class="inspector-panel qc-form-card">
        <div class="qc-form-section-title"><h3>Lampiran</h3></div>
        <div class="qc-attachment-grid">
            @forelse ($submission->attachments as $attachment)
                @php($attachmentUrl = route('user.qc.attachments.show', $attachment))
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
        <div class="qc-form-section-title"><h3>Approval</h3></div>
        <div class="qc-user-approval-box position-relative text-center">
            <small class="text-muted position-absolute top-0 end-0 mt-3 me-3">{{ $signedDate }}</small>
            @if ($signatureApproval)
                <strong>{{ $signerName }}</strong>
                <div class="qc-signature-result d-block mt-3">
                    <img src="{{ $signatureApproval['signature'] }}" alt="Tanda tangan" class="mx-auto d-block">
                    <div class="mt-2">
                        <span>{{ $signerRole }}</span>
                    </div>
                </div>
            @else
                <div class="qc-readonly-box mt-3">Belum ada tanda tangan.</div>
            @endif
        </div>
    </section>
</div>
@endif

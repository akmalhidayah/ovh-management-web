@php
    $generalInfo = $submission->general_info ?? [];
    $approvalData = $submission->approval_data ?? [];
    $rowsByBlock = $submission->rows->groupBy('qc_form_template_block_id');
    $logoSig = public_path('assets/images/logo/logo-sig.png');
    $logoSt = public_path('assets/images/logo/logo-st2.png');
    $signatureApproval = collect($approvalData)->first(fn ($approval) => is_array($approval) && ! empty($approval['signature']));
    $signedDateValue = $signatureApproval['signed_at'] ?? ($approvalData['tanggal'] ?? null);
    $signedDate = $signedDateValue ? \Illuminate\Support\Carbon::parse($signedDateValue)->format('d M Y H:i') : '-';
    $signerName = $signatureApproval['name'] ?? ($submission->user?->name ?: 'User QC');
    $signerRole = $signatureApproval['role'] ?? 'Quality Control Personil';
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #111827; padding: 5px; vertical-align: top; }
        .no-border, .no-border td { border: 0; }
        .header-title { text-align: center; font-size: 18px; font-weight: 800; text-transform: uppercase; }
        .logo { height: 34px; object-fit: contain; }
        .meta td { height: 24px; }
        .section-title { margin: 12px 0 5px; font-weight: 800; font-size: 13px; }
        .table-head th { background: #eef2f7; text-align: center; font-weight: 800; }
        .signature-img { max-width: 160px; max-height: 54px; display: block; margin: 7px auto 7px; }
        .attachment-img { width: 118px; height: 86px; object-fit: cover; border: 1px solid #9ca3af; margin: 4px 6px 4px 0; }
        .muted { color: #6b7280; }
        .approval-box { position: relative; border: 1px solid #111827; min-height: 124px; padding: 22px 12px 12px; text-align: center; }
        .approval-date { position: absolute; top: 7px; right: 10px; font-size: 10px; color: #374151; }
        .approval-name { font-weight: 800; font-size: 12px; margin-bottom: 4px; }
        .approval-role { color: #6b7280; margin-top: 2px; }
    </style>
</head>
<body>
    <table>
        <tr>
            <td style="width: 22%;">
                @if (file_exists($logoSig))
                    <img src="{{ $logoSig }}" class="logo" alt="SIG">
                @endif
                @if (file_exists($logoSt))
                    <img src="{{ $logoSt }}" class="logo" alt="ST">
                @endif
            </td>
            <td class="header-title">Quality Control Record</td>
            <td style="width: 28%;">
                <table class="no-border">
                    <tr><td><strong>Report No</strong></td><td>: {{ $submission->report_no ?: $submission->form_number }}</td></tr>
                    <tr><td><strong>OVH Plant</strong></td><td>: {{ $submission->ovh_plant ?: '-' }}</td></tr>
                    <tr><td><strong>Tahun</strong></td><td>: {{ $submission->year ?: '-' }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="meta" style="margin-top: 8px;">
        <tr>
            <td><strong>Unit</strong></td><td>{{ $submission->unit ?: '-' }}</td>
            <td><strong>Alat</strong></td><td>{{ $submission->equipment ?: '-' }}</td>
        </tr>
        <tr>
            <td><strong>Tag Num.</strong></td><td>{{ $submission->tag_num ?: '-' }}</td>
            <td><strong>Tgl. Mulai</strong></td><td>{{ $submission->tgl_mulai?->format('d M Y') ?: '-' }}</td>
        </tr>
        <tr>
            <td><strong>Pekerjaan</strong></td><td>{{ $submission->pekerjaan ?: '-' }}</td>
            <td><strong>Durasi</strong></td><td>{{ $submission->durasi ?: '-' }}</td>
        </tr>
    </table>

    @foreach ($submission->template?->blocks ?? [] as $block)
        @continue(! in_array($block->type, ['checklist_table', 'measurement_table'], true))
        @php
            $columns = $block->config['columns'] ?? [];
            $blockRows = $rowsByBlock[$block->id] ?? collect();
        @endphp
        <div class="section-title">{{ $block->title }}</div>
        <table>
            <thead class="table-head">
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
                    <tr><td colspan="{{ max(count($columns), 1) }}" style="text-align:center;">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    @endforeach

    <div class="section-title">Catatan Umum</div>
    <table><tr><td style="height: 70px;">{{ $submission->note ?: '-' }}</td></tr></table>

    <div class="section-title">Lampiran</div>
    <table>
        <tr>
            <td>
                @forelse ($submission->attachments as $attachment)
                    @php($path = storage_path('app/public/'.$attachment->file_path))
                    @if ($attachment->type === 'image' && file_exists($path))
                        <div style="display:inline-block; margin-right: 8px;">
                            <img src="{{ $path }}" class="attachment-img" alt="{{ $attachment->original_name }}">
                            <div>{{ $attachment->label ?: $attachment->field_key }}</div>
                        </div>
                    @else
                        <div>{{ $attachment->label ?: $attachment->field_key }}: {{ $attachment->original_name }}</div>
                    @endif
                @empty
                    <span class="muted">Tidak ada lampiran.</span>
                @endforelse
            </td>
        </tr>
    </table>

    <div class="section-title">Approval</div>
    <div class="approval-box">
        <div class="approval-date">{{ $signedDate }}</div>
        @if ($signatureApproval)
            <div class="approval-name">{{ $signerName }}</div>
            <img src="{{ $signatureApproval['signature'] }}" class="signature-img" alt="Tanda tangan">
            <div class="approval-role">{{ $signerRole }}</div>
        @else
            <div style="padding-top: 30px;" class="muted">Belum ada tanda tangan.</div>
        @endif
    </div>
</body>
</html>

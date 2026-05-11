@php
    use App\Support\QcTemplates\FixedQcTemplate;
    use Illuminate\Support\Str;

    $isFixed = (bool) $submission->template?->template_type;
    $type = FixedQcTemplate::normalizeType($submission->template?->template_type);
    $generalInfo = $submission->general_info ?? [];
    $bodyData = $submission->body_data ?? [];
    $approvalData = $submission->approval_data ?? [];
    $rowsByBlock = $submission->rows->groupBy($isFixed ? 'block_type' : 'qc_form_template_block_id');
    $generalRows = $rowsByBlock->get('general', collect());
    $weldingWelderRows = $rowsByBlock->get('welding_welder', collect());
    $weldingResultRows = $rowsByBlock->get('welding_result', collect());
    $legacyBlocks = $submission->template?->blocks ?? collect();
    $logoSig = public_path('assets/images/logo/logo-sig.png');
    $logoSt = public_path('assets/images/logo/logo-st2.png');
    $value = fn (string $key, string $fallback = '') => $generalInfo[$key] ?? $fallback;
    $dateTime = $value('date_time') ?: $submission->submitted_at?->format('Y-m-d H:i');
    $attachments = $submission->attachments->groupBy('field_key');
    $approvalByRole = collect(FixedQcTemplate::approvalColumns())->mapWithKeys(fn ($column) => [
        $column['role'] => $approvalData[$column['key']] ?? [],
    ]);
    $approval = fn (string $role) => $approvalByRole[$role] ?? [];
    $check = fn (bool $checked) => $checked ? '&#10003;' : '';
    $pdfTitlePekerjaan = $submission->pekerjaan ?: ($generalInfo['pekerjaan'] ?? 'Form QC');
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Quality Control - {{ $pdfTitlePekerjaan }}</title>
    <style>
        @page { margin: 8mm; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            color: #000;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 9px;
            line-height: 1.25;
        }
        table { width: 100%; border-collapse: collapse; }
        td, th { vertical-align: middle; }
        .sheet {
            width: 100%;
            background-image:
                linear-gradient(#e2e2e2 1px, transparent 1px),
                linear-gradient(90deg, #e2e2e2 1px, transparent 1px);
            background-size: 18mm 7mm;
        }
        .top-table td {
            height: 26mm;
            border: 1px solid #000;
            background: #fff;
            text-align: center;
        }
        .logo-cell { width: 17%; }
        .title-cell {
            width: 66%;
            font-family: DejaVu Serif, serif;
            font-size: 19px;
            font-weight: 700;
            letter-spacing: .4px;
        }
        .title-work {
            display: block;
            margin-top: 2mm;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0;
        }
        .sig-logo { width: 31mm; }
        .st-logo { width: 21mm; height: 21mm; object-fit: contain; }
        .info-table td {
            width: 25%;
            height: 8mm;
            padding: 1.2mm 1.5mm;
            border: 1px solid #000;
            background: #fff;
        }
        .info-label { font-weight: 700; }
        .info-value { font-style: italic; }
        .section-gap { height: 4mm; }
        .welding-section-gap { height: 6.5mm; }
        .page-break { page-break-before: always; }
        .data-table th,
        .data-table td {
            border: 1px solid #000;
            background: #fff;
        }
        .data-table th {
            height: 7mm;
            padding: 1mm;
            text-align: center;
            font-size: 9px;
            font-weight: 700;
        }
        .data-table td {
            min-height: 6mm;
            padding: 1mm 1.2mm;
            font-size: 8px;
        }
        .welding-data-table th {
            height: 8mm;
            padding: 1.4mm 1.2mm;
        }
        .welding-data-table td {
            height: 7.5mm;
            padding: 1.3mm 1.5mm;
            line-height: 1.35;
        }
        .center { text-align: center; }
        .note-box {
            height: 22mm;
            padding: 1.5mm;
            border: 1px solid #000;
            background: #fff;
            font-size: 8.5px;
            margin-bottom: 7mm;
        }
        .approval-block {
            clear: both;
            page-break-inside: avoid;
        }
        .note-label,
        .attachment-label {
            margin-bottom: 1.5mm;
            font-size: 8.5px;
            font-style: italic;
        }
        .attachment-page {
            min-height: 270mm;
            padding-top: 0;
        }
        .attachment-grid th,
        .attachment-grid td {
            width: 50%;
            border: 1px solid #000;
            background: #fff;
            text-align: center;
        }
        .attachment-grid th {
            height: 8mm;
            padding-top: 2mm;
            font-size: 10px;
            font-style: italic;
            font-weight: 400;
        }
        .attachment-grid td {
            height: 155mm;
            padding: 4mm;
            vertical-align: top;
        }
        .attachment-img {
            display: block;
            max-width: 82mm;
            max-height: 140mm;
            margin: 3mm auto;
            border: 1px solid #999;
        }
        .approval-table td,
        .approval-table th {
            border: 1px solid #000;
            background: #fff;
            text-align: center;
        }
        .approval-table th {
            height: 13mm;
            padding: 1mm;
            font-size: 7.3px;
            font-weight: 700;
        }
        .approval-sign-row td {
            height: 22mm;
            padding: 1mm;
            font-size: 8px;
        }
        .approval-date-row td {
            height: 5mm;
            padding-left: 1mm;
            text-align: left;
            font-size: 7.5px;
        }
        .inspector-title {
            font-size: 10px;
            line-height: 1.45;
        }
        .sig-img {
            display: block;
            max-width: 28mm;
            max-height: 12mm;
            margin: 0 auto 1mm;
        }
        .legacy-table th,
        .legacy-table td {
            border: 1px solid #000;
            padding: 4px;
        }
    </style>
</head>
<body>
@if ($isFixed)
    <div class="sheet">
        <table class="top-table">
            <tr>
                <td class="logo-cell">
                    @if (file_exists($logoSig))
                        <img src="{{ $logoSig }}" class="sig-logo" alt="SIG">
                    @endif
                </td>
                <td class="title-cell">
                    FORM QUALITY CONTROL
                    <span class="title-work">{{ $pdfTitlePekerjaan }}</span>
                </td>
                <td class="logo-cell">
                    @if (file_exists($logoSt))
                        <img src="{{ $logoSt }}" class="st-logo" alt="ST">
                    @endif
                </td>
            </tr>
        </table>

        <table class="info-table">
            <tr>
                <td><span class="info-label">Doc.Number</span> : <span class="info-value">{{ $value('doc_number', $submission->report_no ?: $submission->form_number) }}</span></td>
                <td><span class="info-label">Functional Location</span> : <span class="info-value">{{ $value('functional_location') }}</span></td>
                <td><span class="info-label">Tahun</span> : <span class="info-value">{{ $value('tahun') }}</span></td>
                <td><span class="info-label">Date &amp; Time</span> : <span class="info-value">{{ $dateTime }}</span></td>
            </tr>
            <tr>
                <td><span class="info-label">Tag.Num</span> : <span class="info-value">{{ $value('tag_num') }}</span></td>
                <td><span class="info-label">Area</span> : <span class="info-value">{{ $value('area') }}</span></td>
                <td><span class="info-label">Name Equipment</span> : <span class="info-value">{{ $value('name_equipment') }}</span></td>
                <td><span class="info-label">ID Equipment</span> : <span class="info-value">{{ $value('id_equipment') }}</span></td>
            </tr>
            <tr>
                <td><span class="info-label">Alat</span> : <span class="info-value">{{ $value('alat') }}</span></td>
                <td><span class="info-label">Pekerjaan</span> : <span class="info-value">{{ $value('pekerjaan') }}</span></td>
                <td><span class="info-label">Unit Kerja</span> : <span class="info-value">{{ $value('unit_kerja') }}</span></td>
                <td><span class="info-label">Durasi</span> : <span class="info-value">{{ $value('durasi') }}</span></td>
            </tr>
        </table>

        <div class="section-gap"></div>

        @if ($type === FixedQcTemplate::TYPE_WELDING)
            <table class="data-table welding-data-table">
                <tr>
                    <th colspan="4">Metode QC</th>
                    <th style="border: 0; background: transparent;"></th>
                    <th colspan="3">Pengecekan ke</th>
                </tr>
                <tr>
                    @foreach (FixedQcTemplate::defaultMethods() as $method)
                        <th>{{ $method }}</th>
                    @endforeach
                    <th style="border: 0; background: transparent;"></th>
                    @foreach (FixedQcTemplate::defaultCheckSteps() as $step)
                        <th>{{ $step }}</th>
                    @endforeach
                </tr>
                <tr>
                    @foreach (FixedQcTemplate::defaultMethods() as $method)
                        <td class="center">{!! $check(in_array($method, $bodyData['methods'] ?? [], true)) !!}</td>
                    @endforeach
                    <td style="border: 0; background: transparent;"></td>
                    @foreach (FixedQcTemplate::defaultCheckSteps() as $step)
                        <td class="center">{!! $check(in_array($step, $bodyData['check_steps'] ?? [], true)) !!}</td>
                    @endforeach
                </tr>
            </table>

            <div class="welding-section-gap"></div>

            <table class="data-table welding-data-table">
                <tr>
                    <th style="width: 7%;">No</th>
                    <th>Nama Welder</th>
                    <th>Posisi Pengelasan</th>
                    <th>Diameter Electrode</th>
                    <th>Electrode/Filter</th>
                    <th>Amper</th>
                    <th>Keterangan</th>
                </tr>
                @forelse ($weldingWelderRows as $row)
                    @php $data = $row->row_data ?? []; @endphp
                    <tr>
                        <td class="center">{{ $data['no'] ?? $loop->iteration }}</td>
                        <td>{{ $data['nama_welder'] ?? '' }}</td>
                        <td>{{ $data['posisi_pengelasan'] ?? '' }}</td>
                        <td>{{ $data['diameter_electrode'] ?? '' }}</td>
                        <td>{{ $data['electrode_filter'] ?? '' }}</td>
                        <td>{{ $data['amper'] ?? '' }}</td>
                        <td>{{ $data['keterangan'] ?? '' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7">&nbsp;</td></tr>
                @endforelse
            </table>

            <div class="welding-section-gap"></div>

            <table class="data-table welding-data-table">
                <tr>
                    <th style="width: 7%;">No</th>
                    <th>Deskripsi</th>
                    <th style="width: 12%;">Baik</th>
                    <th style="width: 16%;">Perlu Perbaikan</th>
                    <th style="width: 14%;">Tidak Layak</th>
                    <th>Keterangan</th>
                </tr>
                @forelse ($weldingResultRows as $row)
                    @php $data = $row->row_data ?? []; @endphp
                    <tr>
                        <td class="center">{{ $data['no'] ?? $loop->iteration }}</td>
                        <td>{{ $data['deskripsi'] ?? '' }}</td>
                        <td class="center">{!! $check($row->status_value === 'Baik') !!}</td>
                        <td class="center">{!! $check($row->status_value === 'Perlu Perbaikan') !!}</td>
                        <td class="center">{!! $check($row->status_value === 'Tidak Layak') !!}</td>
                        <td>{{ $row->catatan ?: '' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6">&nbsp;</td></tr>
                @endforelse
            </table>
        @else
            <table class="data-table">
                <tr>
                    <th rowspan="2" style="width: 22%;">Item Pengecekan</th>
                    <th rowspan="2" style="width: 25%;">Standar</th>
                    <th rowspan="2" style="width: 12%;">Actual</th>
                    <th colspan="2" style="width: 18%;">Status</th>
                    <th rowspan="2">Catatan</th>
                </tr>
                <tr>
                    <th>Ok</th>
                    <th>Not Ok</th>
                </tr>
                @forelse ($generalRows as $row)
                    @php $data = $row->row_data ?? []; @endphp
                    <tr>
                        <td>{{ $data['item_pengecekan'] ?? '' }}</td>
                        <td>{{ $data['standar'] ?? '' }}</td>
                        <td class="center">{{ $row->aktual ?: '' }}</td>
                        <td class="center">{!! $check($row->status_value === 'Ok') !!}</td>
                        <td class="center">{!! $check($row->status_value === 'Not Ok') !!}</td>
                        <td>{{ $row->catatan ?: '' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="center"><em>Mengikuti Jenis Alat</em></td>
                        <td class="center"><em>Mengikuti Jenis Alat</em></td>
                        <td class="center"><em>Manual</em></td>
                        <td class="center">&#9744;</td>
                        <td class="center">&#9744;</td>
                        <td></td>
                    </tr>
                @endforelse
            </table>
        @endif

        <div class="section-gap"></div>

        <div class="note-label">Catatan:</div>
        <div class="note-box">{{ $submission->note ?: '' }}</div>

        <div class="approval-block">
        <table class="approval-table">
            <tr>
                <th rowspan="2" style="width: 15%;" class="inspector-title">TTD<br>QC Inspektor</th>
                <th colspan="2">Checked by / Diperiksa Oleh :</th>
                <th rowspan="2" style="width: 18%;">Approved by / Disetujui oleh :<br>UNIT KERJA</th>
                <th rowspan="2" style="width: 21%;">Known by / Diketahui Oleh :<br>OVERHAUL MANAGEMENT</th>
            </tr>
            <tr>
                <th style="width: 18%;">QC LEADER</th>
                <th style="width: 28%;">COORDINATOR QC &amp;<br>COMMISSIONING</th>
            </tr>
            <tr class="approval-sign-row">
                @foreach (['QC Inspektor', 'QC Leader', 'Coordinator QC & Commissioning', 'Unit Kerja', 'Overhaul Management'] as $role)
                    @php $ap = $approval($role); @endphp
                    <td>
                        @if (! empty($ap['signature']))
                            <img src="{{ $ap['signature'] }}" class="sig-img" alt="Tanda tangan">
                        @endif
                        {{ $ap['name'] ?? '' }}
                    </td>
                @endforeach
            </tr>
            <tr class="approval-date-row">
                @foreach (['QC Inspektor', 'QC Leader', 'Coordinator QC & Commissioning', 'Unit Kerja', 'Overhaul Management'] as $role)
                    @php $ap = $approval($role); @endphp
                    <td>Date : {{ $ap['date'] ?? '' }}</td>
                @endforeach
            </tr>
        </table>
        </div>

        <div class="page-break"></div>

        <div class="attachment-page">
            <table class="top-table">
                <tr>
                    <td class="logo-cell">
                        @if (file_exists($logoSig))
                            <img src="{{ $logoSig }}" class="sig-logo" alt="SIG">
                        @endif
                    </td>
                    <td class="title-cell">
                        FORM QUALITY CONTROL
                        <span class="title-work">{{ $pdfTitlePekerjaan }}</span>
                    </td>
                    <td class="logo-cell">
                        @if (file_exists($logoSt))
                            <img src="{{ $logoSt }}" class="st-logo" alt="ST">
                        @endif
                    </td>
                </tr>
            </table>

            <table class="info-table">
                <tr>
                    <td><span class="info-label">Doc.Number</span> : <span class="info-value">{{ $value('doc_number', $submission->report_no ?: $submission->form_number) }}</span></td>
                    <td><span class="info-label">Functional Location</span> : <span class="info-value">{{ $value('functional_location') }}</span></td>
                    <td><span class="info-label">Tahun</span> : <span class="info-value">{{ $value('tahun') }}</span></td>
                    <td><span class="info-label">Date &amp; Time</span> : <span class="info-value">{{ $dateTime }}</span></td>
                </tr>
                <tr>
                    <td><span class="info-label">Tag.Num</span> : <span class="info-value">{{ $value('tag_num') }}</span></td>
                    <td><span class="info-label">Area</span> : <span class="info-value">{{ $value('area') }}</span></td>
                    <td><span class="info-label">Name Equipment</span> : <span class="info-value">{{ $value('name_equipment') }}</span></td>
                    <td><span class="info-label">ID Equipment</span> : <span class="info-value">{{ $value('id_equipment') }}</span></td>
                </tr>
                <tr>
                    <td><span class="info-label">Alat</span> : <span class="info-value">{{ $value('alat') }}</span></td>
                    <td><span class="info-label">Pekerjaan</span> : <span class="info-value">{{ $value('pekerjaan') }}</span></td>
                    <td><span class="info-label">Unit Kerja</span> : <span class="info-value">{{ $value('unit_kerja') }}</span></td>
                    <td><span class="info-label">Durasi</span> : <span class="info-value">{{ $value('durasi') }}</span></td>
                </tr>
            </table>

            <div class="section-gap"></div>
            <div class="attachment-label">Lampiran</div>

            <table class="attachment-grid">
                <tr>
                    <th>Foto Before</th>
                    <th>Foto After</th>
                </tr>
                <tr>
                    @foreach (['foto_before', 'foto_after'] as $key)
                        <td>
                        @foreach (($attachments[$key] ?? collect())->take(1) as $attachment)
                            @php
                                $path = \Illuminate\Support\Facades\Storage::disk('local')->exists($attachment->file_path)
                                    ? \Illuminate\Support\Facades\Storage::disk('local')->path($attachment->file_path)
                                    : storage_path('app/public/'.$attachment->file_path);
                            @endphp
                            @if ($attachment->type === 'image' && file_exists($path))
                                <img src="{{ $path }}" class="attachment-img" alt="{{ $attachment->original_name }}">
                            @endif
                        @endforeach
                        </td>
                    @endforeach
                </tr>
            </table>
        </div>
    </div>
@else
    <table>
        <tr>
            <td style="width: 22%;">
                @if (file_exists($logoSig))
                    <img src="{{ $logoSig }}" style="height:32px;" alt="SIG">
                @endif
                @if (file_exists($logoSt))
                    <img src="{{ $logoSt }}" style="height:32px;" alt="ST">
                @endif
            </td>
            <td style="text-align:center; font-size:17px; font-weight:800;">Quality Control Record</td>
            <td style="width: 28%;">{{ $submission->template?->name ?: '-' }}<br>{{ $submission->report_no ?: $submission->form_number }}</td>
        </tr>
    </table>

    @foreach ($legacyBlocks as $block)
        @continue(! in_array($block->type, ['checklist_table', 'measurement_table'], true))
        @php
            $columns = $block->config['columns'] ?? [];
            $blockRows = $rowsByBlock->get($block->id, collect());
        @endphp
        <h3>{{ $block->title }}</h3>
        <table class="legacy-table">
            <tr>
                @foreach ($columns as $column)
                    <th>{{ is_array($column) ? ($column['label'] ?? $column['key'] ?? '') : $column }}</th>
                @endforeach
            </tr>
            @foreach ($blockRows as $row)
                @php $data = $row->row_data ?? []; @endphp
                <tr>
                    @foreach ($columns as $column)
                        @php $key = is_array($column) ? ($column['key'] ?? Str::snake($column['label'] ?? 'kolom')) : Str::snake($column); @endphp
                        <td>{{ $key === 'status' ? ($row->status_value ?: '-') : ($key === 'catatan' ? ($row->catatan ?: '-') : ($data[$key] ?? '-')) }}</td>
                    @endforeach
                </tr>
            @endforeach
        </table>
    @endforeach
@endif
</body>
</html>

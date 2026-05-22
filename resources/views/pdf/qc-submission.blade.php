@php
    use App\Support\QcTemplates\FixedQcTemplate;
    use App\Support\Pdf\SignatureImage;
    use App\Models\ApprovalStep;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $templateSnapshot = $submission->template_snapshot ?? [];
    $templateType = $templateSnapshot['template_type'] ?? $submission->template?->template_type;
    $templateName = $submission->template_name ?? $templateSnapshot['name'] ?? $submission->template?->name;
    $templateBodySchema = $templateSnapshot['body_schema'] ?? $submission->template?->body_schema ?? [];
    $isFixed = (bool) $templateType;
    $type = FixedQcTemplate::normalizeType($templateType);
    $generalInfo = $submission->general_info ?? [];
    $bodyData = $submission->body_data ?? [];
    $legacyApprovalData = $submission->approval_data ?? [];
    $templateSchema = $isFixed ? FixedQcTemplate::normalizeSchema($type, $templateBodySchema) : [];
    $approvalDefaults = $templateSchema['approval_defaults'] ?? FixedQcTemplate::defaultApprovalDefaults($type);
    $approvalColumns = FixedQcTemplate::approvalColumns($type);
    $rowsByBlock = $submission->rows->groupBy($isFixed ? 'block_type' : 'qc_form_template_block_id');
    $generalRows = $rowsByBlock->get('general', collect());
    $weldingWelderRows = $rowsByBlock->get('welding_welder', collect());
    $weldingResultRows = $rowsByBlock->get('welding_result', collect());
    $legacyBlocks = collect($templateSnapshot['blocks'] ?? [])
        ->map(fn ($block) => (object) $block);
    if ($legacyBlocks->isEmpty()) {
        $legacyBlocks = $submission->template?->blocks ?? collect();
    }
    $logoSig = public_path('assets/images/logo/logo-sig.png');
    $logoSt = public_path('assets/images/logo/logo-st2.png');
    $value = fn (string $key, string $fallback = '') => $generalInfo[$key] ?? $fallback;
    $dateTime = $value('date_time') ?: $submission->submitted_at?->format('Y-m-d H:i');
    $attachments = $submission->attachments->groupBy('field_key');
    $approvalSignatureSource = static function ($step) {
        if (! empty($step?->signature_path) && Storage::disk('public')->exists($step->signature_path)) {
            return Storage::disk('public')->path($step->signature_path);
        }

        return $step?->signature_data;
    };
    $flowSteps = $submission->approvalFlow?->steps ?? collect();
    $approvalData = $legacyApprovalData;
    if ($flowSteps->isNotEmpty()) {
        foreach (array_values($approvalColumns) as $index => $column) {
            $step = $flowSteps->firstWhere('step_order', $index + 1);
            if (! $step || $step->status !== ApprovalStep::STATUS_APPROVED) {
                continue;
            }

            $legacyApproval = $legacyApprovalData[$column['key']] ?? [];
            $stepSignature = $approvalSignatureSource($step);
            $approvalData[$column['key']] = [
                'name' => $step->approver_name ?: ($legacyApproval['name'] ?? ''),
                'role' => $step->approver_position ?: ($legacyApproval['role'] ?? $column['label']),
                'signature' => $stepSignature ?: ($legacyApproval['signature'] ?? null),
                'date' => $step->acted_at?->format('Y-m-d') ?: ($legacyApproval['date'] ?? ''),
                'signed_at' => $step->acted_at?->toISOString() ?: ($legacyApproval['signed_at'] ?? null),
            ];
        }
    }
    $approvalByRole = collect($approvalColumns)->mapWithKeys(function ($column) use ($approvalData, $approvalDefaults) {
        $approval = $approvalData[$column['key']] ?? [];

        if (blank($approval['name'] ?? null)) {
            $approval['name'] = $approvalDefaults[$column['key']]['name'] ?? '';
        }

        return [$column['role'] => $approval];
    });
    $approval = fn (string $role) => $approvalByRole[$role] ?? [];
    $approvalByKey = collect($approvalColumns)->mapWithKeys(function ($column) use ($approvalData, $approvalDefaults) {
        $approval = $approvalData[$column['key']] ?? [];

        if (blank($approval['name'] ?? null)) {
            $approval['name'] = $approvalDefaults[$column['key']]['name'] ?? '';
        }

        return [$column['key'] => $approval];
    });
    $check = fn (bool $checked) => $checked ? '<span class="pdf-check-mark">&#10003;</span>' : '';
    $signature = fn (?string $source) => SignatureImage::forPdf($source);
    $pdfTitlePekerjaan = $type === FixedQcTemplate::TYPE_BRICS
        ? ($bodyData['brics_customer']['subject'] ?? 'BRICK INSTALLATIONS')
        : ($submission->pekerjaan ?: ($generalInfo['pekerjaan'] ?? 'Form QC'));
    $headerRows = collect(FixedQcTemplate::headerRows($type))
        ->map(fn ($row) => collect($row)->map(fn ($key) => [
            'label' => collect(FixedQcTemplate::headerFields($type))->firstWhere('key', $key)['label'] ?? $key,
            'value' => match ($key) {
                'doc_number' => $value('doc_number', $submission->report_no ?: $submission->form_number),
                'date_time' => $dateTime,
                'inspector_qc' => $value('inspector_qc', $submission->user?->name ?: '-'),
                'plant' => $value('plant', $submission->plant ?: '-'),
                default => $value($key),
            },
        ])->all())
        ->all();
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Quality Control - {{ $pdfTitlePekerjaan }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm;
        }
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
        .top-table {
            margin-bottom: 4mm;
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
        .info-table {
            table-layout: fixed;
        }
        .info-table td {
            height: 6.8mm;
            padding: 1mm 1.2mm;
            border: 1px solid #000;
            background: #fff;
            font-size: 7.2px;
        }
        .info-label-cell {
            width: 12%;
            font-family: DejaVu Serif, serif;
            font-weight: 700;
            white-space: nowrap;
            word-break: keep-all;
        }
        .info-value-cell {
            width: 21.33%;
        }
        .info-label {
            display: inline-block;
            font-weight: 700;
            white-space: nowrap;
        }
        .info-value { font-style: normal; }
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
            background: #e5e2d8;
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
        .pdf-check-mark {
            display: inline-block;
            font-size: 13px;
            font-weight: 800;
            line-height: 1;
            margin: 0 auto;
        }
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
            min-height: auto;
            padding-top: 0;
        }
        .attachment-grid {
            table-layout: fixed;
            page-break-inside: auto;
        }
        .attachment-grid td {
            width: 50%;
            border: 1px solid #000;
            background: #fff;
            text-align: center;
        }
        .attachment-grid th {
            height: 8mm;
            padding: 1.5mm;
            border: 1px solid #000;
            background: #e5e2d8;
            font-size: 9px;
            font-style: italic;
            font-weight: 700;
            text-align: center;
        }
        .attachment-grid td {
            height: {{ $type === FixedQcTemplate::TYPE_CASTABLE ? '78mm' : '88mm' }};
            padding: 2.4mm;
            vertical-align: top;
            page-break-inside: avoid;
        }
        .attachment-support-grid {
            margin-top: 4mm;
        }
        .attachment-empty {
            padding: 10mm 2mm;
            border: 1px solid #000;
            background: #fff;
            text-align: center;
            font-style: italic;
        }
        .attachment-img {
            display: block;
            max-width: 76mm;
            max-height: {{ $type === FixedQcTemplate::TYPE_CASTABLE ? '62mm' : '72mm' }};
            margin: 0 auto;
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
            background: #e5e2d8;
            font-size: 7.3px;
            font-weight: 700;
        }
        .approval-sign-row td {
            height: 24mm;
            padding: 1mm;
            font-size: 9.5px;
            font-weight: 700;
            overflow: hidden;
            vertical-align: middle;
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
            width: auto;
            height: 9mm;
            max-width: 28mm;
            margin: 0 auto 0.7mm;
        }
        .sig-name {
            display: block;
            text-align: center;
        }
        .legacy-table th,
        .legacy-table td {
            border: 1px solid #000;
            padding: 4px;
        }
        .fixed-section-title {
            border: 1px solid #000;
            background: #e5e2d8;
            padding: 1.2mm;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .brics-section-gap {
            margin-top: 3mm;
        }
        .fixed-form-table th,
        .fixed-form-table td {
            border: 1px solid #000;
            background: #fff;
            padding: 1mm;
            font-size: 7.2px;
        }
        .fixed-form-table th {
            background: #e5e2d8;
            font-weight: 800;
            text-align: center;
        }
        .pdf-input-line {
            min-height: 4mm;
            border-bottom: 1px dotted #555;
        }
        .pdf-small-box {
            display: inline-block;
            width: 8mm;
            height: 4mm;
            border: 1px solid #000;
            vertical-align: middle;
        }
        .castable-monitor-page {
            page-break-inside: auto;
            background: #fff;
        }
        .castable-monitor-head {
            margin-bottom: 3mm;
        }
        .castable-monitor-head td {
            height: 18mm;
            border: 1px solid #000;
            background: #fff;
        }
        .castable-monitor-logo {
            width: 18mm;
            display: block;
            margin: 0 auto;
        }
        .castable-monitor-meta {
            width: 32%;
            padding: 1mm;
            font-size: 7px;
            font-weight: 700;
        }
        .castable-monitor-meta table td {
            height: auto;
            border: 0;
            padding: 0.3mm 0;
            text-align: left;
        }
        .castable-monitor-title {
            text-align: center;
            font-size: 14px;
            font-weight: 800;
            letter-spacing: 0;
        }
        .castable-monitor-table {
            table-layout: fixed;
            border-collapse: collapse;
            margin-top: 3mm;
        }
        .castable-monitor-table th,
        .castable-monitor-table td {
            border: 1px solid #000;
            background: #fff;
            padding: 0.35mm;
            font-size: 5px;
            line-height: 1.05;
            vertical-align: middle;
            word-break: break-word;
        }
        .castable-monitor-table th {
            text-align: center;
            font-weight: 700;
        }
        .castable-monitor-table td {
            height: 4.8mm;
        }
        .castable-monitor-note-sign {
            margin-top: 2mm;
            table-layout: fixed;
        }
        .castable-monitor-note-sign td {
            border: 0;
            vertical-align: top;
            font-size: 7px;
        }
        .castable-note-lines div {
            margin-top: 1.2mm;
            border-bottom: 1px dotted #000;
            height: 2.7mm;
        }
        .castable-monitor-sign-cell {
            text-align: center;
        }
        .castable-monitor-sign-space {
            height: 9mm;
            display: block;
        }
        .castable-monitor-sign-img {
            display: block;
            max-width: 24mm;
            max-height: 10mm;
            margin: 1mm auto;
            object-fit: contain;
        }
        .castable-one-sheet {
            background: #fff;
            font-size: 7px;
            line-height: 1.18;
        }
        .castable-sheet {
            background: #fff;
            background-image: none;
        }
        .castable-one-sheet table {
            table-layout: fixed;
            border-collapse: collapse;
        }
        .castable-one-sheet th,
        .castable-one-sheet td {
            border: 0.45px solid #000;
            background: #fff;
            padding: 0.65mm 0.8mm;
            vertical-align: middle;
            word-break: break-word;
        }
        .castable-one-sheet th {
            font-weight: 800;
            text-align: center;
        }
        .castable-title-table td {
            height: 22mm;
            padding: 1.5mm;
        }
        .castable-title-table .title-cell {
            font-size: 16px;
        }
        .castable-rule {
            height: 6mm;
            border-bottom: 0.45px solid #000;
        }
        .castable-installation-section {
            page-break-inside: avoid;
        }
        .castable-section-title {
            background: #e5e2d8 !important;
            font-size: 8px;
            font-weight: 900;
        }
        .castable-customer,
        .castable-inspection,
        .castable-detail,
        .castable-sample {
            table-layout: auto !important;
        }
        .castable-customer th,
        .castable-monitor-compact th,
        .castable-inspection th,
        .castable-detail th,
        .castable-sample th,
        .castable-approval th {
            background: #e5e2d8 !important;
        }
        .castable-customer td {
            height: 4.3mm;
            font-weight: 700;
        }
        .castable-no-cell {
            width: 8mm !important;
            min-width: 8mm !important;
            max-width: 8mm !important;
            text-align: center;
        }
        .castable-customer .castable-label-cell {
            width: 55mm !important;
        }
        .castable-inspection .castable-label-cell {
            width: 52mm !important;
        }
        .castable-detail .castable-label-cell,
        .castable-sample .castable-label-cell {
            width: 42% !important;
        }
        .castable-value-cell {
            width: auto !important;
        }
        .castable-customer td:last-child {
            font-weight: 400;
        }
        .castable-title-row {
            height: 5mm;
            font-size: 8px;
        }
        .castable-monitor-compact th {
            height: 5mm;
            font-size: 6.2px;
            line-height: 1.12;
        }
        .castable-monitor-compact td {
            height: 5.2mm;
            font-size: 6.2px;
        }
        .castable-monitor-location,
        .castable-monitor-compact th:nth-child(10),
        .castable-monitor-compact td:nth-child(10) {
            border-left: 0.45px solid #000 !important;
        }
        .text-left {
            text-align: left !important;
        }
        .castable-section-heading {
            margin-top: 4mm;
            border-top: 0.45px solid #000;
            border-bottom: 0.45px solid #000;
            border-left: 0.45px solid #000;
            border-right: 0.45px solid #000;
            background: #e5e2d8;
            padding: 1.2mm 0.6mm;
            font-size: 9.5px;
            font-weight: 900;
        }
        .castable-body-grid > tbody > tr > td {
            border: 0;
            padding: 0;
            vertical-align: top;
        }
        .castable-body-left {
            width: 70%;
            padding-right: 3mm !important;
        }
        .castable-body-right {
            width: 30%;
        }
        .castable-inspection td,
        .castable-detail td,
        .castable-sample td,
        .castable-sample th,
        .castable-inspection th,
        .castable-detail th {
            height: 4.3mm;
            font-size: 6.8px;
        }
        .castable-inspection td:nth-child(2),
        .castable-detail td:first-child,
        .castable-sample td:first-child {
            font-weight: 800;
        }
        .castable-customer td:first-child,
        .castable-inspection td:first-child,
        .castable-inspection th:first-child {
            width: 8mm;
            min-width: 8mm;
            max-width: 8mm;
        }
        .option-gap {
            display: inline-block;
            width: 5mm;
        }
        .castable-small-note {
            margin-top: 1.5mm;
            font-size: 6.8px;
            font-style: italic;
        }
        .castable-sample {
            margin-top: 2.2mm;
        }
        .castable-sample th {
            font-size: 8.5px;
        }
        .castable-sample-sign {
            display: block;
            width: 14mm;
            max-width: 14mm;
            max-height: 6mm;
            margin-bottom: 0.7mm;
            object-fit: contain;
        }
        .castable-footer-grid {
            margin-top: 6mm;
        }
        .castable-footer-grid > tbody > tr > td {
            border: 0;
            padding: 0;
            vertical-align: top;
        }
        .castable-note-lines {
            min-height: 12mm;
            padding-top: 1mm;
            white-space: pre-line;
        }
        .castable-approval-caption {
            font-size: 6.4px;
            font-style: italic;
            margin-bottom: 0.8mm;
        }
        .castable-approval th,
        .castable-approval td {
            height: 5.2mm;
            text-align: center;
            font-size: 5.8px;
            line-height: 1.05;
        }
        .castable-approval-sign td {
            height: 13mm;
            font-weight: 700;
            vertical-align: middle;
        }
        .castable-approval-mark {
            display: block;
            height: 8mm;
            text-align: center;
        }
        .castable-approval-img {
            display: block;
            width: auto;
            height: 6mm;
            max-width: 15mm;
            margin: 0 auto;
        }
        .castable-approval-name {
            display: block;
            clear: both;
            text-align: center;
        }
    </style>
</head>
<body>
@if ($isFixed && $type === FixedQcTemplate::TYPE_CASTABLE)
    <div class="sheet castable-sheet">
        @include('pdf.qc-castable-sheet', ['bodyData' => $bodyData])

        <div class="page-break"></div>

        @include('pdf.qc-attachments')
    </div>
@elseif ($isFixed)
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

        @if ($type !== FixedQcTemplate::TYPE_BRICS)
            @include('pdf.partials.qc-info-table', ['rows' => $headerRows])

            <div class="section-gap"></div>
        @endif

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
        @elseif ($type === FixedQcTemplate::TYPE_BRICS)
            @include('pdf.qc-brics-body', ['bodyData' => $bodyData])
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
            @if ($type === FixedQcTemplate::TYPE_CASTABLE)
                <table class="approval-table" style="width: 56%; margin-left: 44%;">
                    <tr>
                        <th style="width: 25%;">Tanggal</th>
                        @foreach ($approvalColumns as $column)
                            <th>{{ $column['label'] }}</th>
                        @endforeach
                    </tr>
                    <tr class="approval-sign-row">
                        <td>{{ collect($approvalByKey)->pluck('date')->filter()->first() ?: '' }}</td>
                        @foreach ($approvalColumns as $column)
                            @php $ap = $approvalByKey[$column['key']] ?? []; @endphp
                            <td>
                                @if (! empty($ap['signature']))
                                    <img src="{{ $signature($ap['signature']) }}" class="sig-img" alt="Tanda tangan">
                                @endif
                                <span class="sig-name">{{ $ap['name'] ?? '' }}</span>
                            </td>
                        @endforeach
                    </tr>
                </table>
                <div style="margin-top: 2mm; font-size: 8.5px; line-height: 1.55;">
                    <div><strong>*1</strong>&nbsp;&nbsp;&nbsp;&nbsp; Supervisor/Inspector pekerjaan</div>
                    <div><strong>*2</strong>&nbsp;&nbsp;&nbsp;&nbsp; Manager/atasan supervisor/inspector</div>
                    <div><strong>*3</strong>&nbsp;&nbsp;&nbsp;&nbsp; Manager bidang terkait ( maint mekanikal/electrical atau production support dll)</div>
                </div>
            @elseif ($type === FixedQcTemplate::TYPE_BRICS)
                <table class="approval-table">
                    <tr>
                        @foreach ($approvalColumns as $column)
                            <th>{{ strtoupper($column['group']) }}</th>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach ($approvalColumns as $column)
                            <td>{{ strtoupper($column['label']) }}</td>
                        @endforeach
                    </tr>
                    <tr class="approval-sign-row">
                        @foreach ($approvalColumns as $column)
                            @php $ap = $approvalByKey[$column['key']] ?? []; @endphp
                            <td>
                                @if (! empty($ap['signature']))
                                    <img src="{{ $signature($ap['signature']) }}" class="sig-img" alt="Tanda tangan">
                                @endif
                                <span class="sig-name">{{ $ap['name'] ?? '' }}</span>
                            </td>
                        @endforeach
                    </tr>
                    <tr class="approval-date-row">
                        @foreach ($approvalColumns as $column)
                            @php $ap = $approvalByKey[$column['key']] ?? []; @endphp
                            <td>Date : {{ $ap['date'] ?? '' }}</td>
                        @endforeach
                    </tr>
                </table>
            @else
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
                                    <img src="{{ $signature($ap['signature']) }}" class="sig-img" alt="Tanda tangan">
                                @endif
                                <span class="sig-name">{{ $ap['name'] ?? '' }}</span>
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
            @endif
        </div>

        <div class="page-break"></div>

        @include('pdf.qc-attachments')

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
            <td style="width: 28%;">{{ $templateName ?: '-' }}<br>{{ $submission->report_no ?: $submission->form_number }}</td>
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

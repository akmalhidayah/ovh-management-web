@php
    use App\Support\Commissioning\FixedCommissioningTemplate;
    use App\Support\Pdf\SignatureImage;
    use App\Models\ApprovalStep;
    use Illuminate\Support\Facades\Storage;
    $header = $submission->header_data ?? [];
    $body = $submission->body_data ?? [];
    $templateSnapshot = $submission->template_snapshot ?? [];
    $templateName = $submission->template_name ?? $templateSnapshot['name'] ?? $submission->template?->name;
    $templateBodySchema = $templateSnapshot['body_schema'] ?? $submission->template?->body_schema ?? [];
    $value = fn ($key, $default = '-') => $header[$key] ?? $default;
    $logoSig = public_path('assets/images/logo/logo-sig.png');
    $logoSt = public_path('assets/images/logo/logo-st2.png');
    $titleDetail = $templateName ?: 'Form Commissioning';
    $schema = FixedCommissioningTemplate::normalizeSchema($templateBodySchema);
    $labels = $schema['labels'];
    $motorRatingFields = $schema['motor_rating_fields'];
    $motorTestFields = $schema['motor_test_fields'];
    $gearboxRatingFields = $schema['gearbox_rating_fields'];
    $gearboxTestFields = $schema['gearbox_test_fields'];
    $approvalDefaults = $schema['approval_defaults'] ?? FixedCommissioningTemplate::defaultApprovalDefaults();
    $legacyApprovalData = $submission->approval_data ?? [];
    $approvalSignatureSource = static function ($step) {
        if (! empty($step?->signature_path) && Storage::disk('public')->exists($step->signature_path)) {
            return Storage::disk('public')->path($step->signature_path);
        }

        return $step?->signature_data;
    };
    $approvalColumns = array_values(FixedCommissioningTemplate::approvalColumns());
    $flowSteps = $submission->approvalFlow?->steps ?? collect();
    $approvalData = $legacyApprovalData;
    if ($flowSteps->isNotEmpty()) {
        $approvalData = collect($approvalColumns)
            ->mapWithKeys(fn (array $column) => [
                $column['key'] => [
                    'label' => $legacyApprovalData[$column['key']]['label'] ?? $column['label'],
                ],
            ])
            ->all();

        foreach ($approvalColumns as $index => $column) {
            $step = $flowSteps->firstWhere('step_order', $index + 1);
            if (! $step || $step->status !== ApprovalStep::STATUS_APPROVED) {
                continue;
            }

            $approvalData[$column['key']] = array_merge($approvalData[$column['key']] ?? [], [
                'name' => $step->approver_name ?? '',
                'role' => $step->approver_position ?? $column['label'],
                'label' => $legacyApprovalData[$column['key']]['label'] ?? $column['label'],
                'signature' => $approvalSignatureSource($step),
                'date' => $step->acted_at?->format('Y-m-d') ?? '',
            ]);
        }
    }
    $imageAttachments = $submission->attachments
        ->where('type', 'image')
        ->values();
    $checkMarkSvg = 'data:image/svg+xml;base64,'.base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 28 22"><path d="M3 11.5 10.4 18.5 25 3.5" fill="none" stroke="#000" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"/></svg>');
    $check = fn (bool $checked) => $checked ? '<img src="'.$checkMarkSvg.'" class="pdf-check-mark" alt="check">' : '';
    $signature = fn (?string $source) => SignatureImage::forPdf($source);
    $headerRows = [
        [
            ['label' => 'Doc.Number', 'value' => $value('doc_number', $submission->form_number)],
            ['label' => 'Plant', 'value' => $value('plant')],
            ['label' => 'Section No.', 'value' => $value('tag_num')],
        ],
        [
            ['label' => 'Functional Location', 'value' => $value('functional_location')],
            ['label' => 'ID Equipment', 'value' => $value('id_equipment')],
            ['label' => 'Name Equipment', 'value' => $value('name_equipment')],
        ],
        [
            ['label' => 'Area', 'value' => $value('area')],
            ['label' => 'Date & Time', 'value' => $value('date_time')],
            ['label' => 'User Commissioning', 'value' => $value('inspector_commissioning', $submission->user?->name ?: '-')],
        ],
    ];
@endphp
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Commissioning - {{ $titleDetail }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm;
        }
        body { margin: 0; font-family: DejaVu Sans, sans-serif; font-size: 8.5px; color: #111; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #111; padding: 4px; text-align: center; vertical-align: middle; line-height: 1.35; }
        th {
            background: #e5e2d8;
            font-weight: bold;
            text-align: center;
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
        .section { font-size: 10px; font-weight: bold; margin-top: 16px; margin-bottom: 5px; text-align: left; }
        .no-border td { border: 0; }
        .black { background: #000 !important; color: #fff; }
        .approval td { text-align: center; vertical-align: top; }
        .approval .approval-group td { height: 18px; padding: 2px 3px; background: #e5e2d8; }
        .approval .approval-role th { height: 18px; padding: 3px; }
        .approval .approval-sign td { height: 28mm; padding: 1mm; font-size: 9px; font-weight: 700; overflow: hidden; vertical-align: middle; }
        .approval .approval-date td { height: 16px; text-align: left; }
        .approval-block {
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .approval-block table {
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .approval tr {
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .top-table { margin-bottom: 12px; }
        .section-table { margin-bottom: 16px; }
        .header-table td { text-align: left; padding: 1.2mm 1.5mm; }
        .equipment-check-table td.item-cell,
        .equipment-check-table td.remark-cell { text-align: left; }
        .info-label { font-weight: 700; }
        .info-value { font-style: italic; }
        .notes-table td { text-align: left; vertical-align: top; }
        .notes-table td { height: 108px; }
        .doc-image-grid {
            width: 100%;
            margin-top: 4mm;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 2mm;
        }
        .doc-image-grid td {
            height: 28mm;
            padding: 1mm;
            border: 1px solid #bbb;
            text-align: center;
            vertical-align: middle;
        }
        .doc-image-grid.images-1 td { height: 42mm; }
        .doc-image-grid.images-2 td { height: 34mm; }
        .doc-image {
            display: block;
            max-width: 100%;
            max-height: 26mm;
            margin: 0 auto;
            object-fit: contain;
        }
        .doc-image-grid.images-1 .doc-image { max-height: 40mm; }
        .doc-image-grid.images-2 .doc-image { max-height: 32mm; }
        .motor-table { table-layout: fixed; }
        .pdf-table-gap { height: 4mm; }
        .gearbox-rating-table { width: 100%; margin-bottom: 5mm; table-layout: fixed; }
        .gearbox-test-table { width: 100%; margin-top: 0; table-layout: fixed; }
        .field-unit { display: block; font-size: 7px; font-weight: normal; }
        .pdf-check-mark {
            display: block;
            width: 4.6mm;
            height: 3.6mm;
            margin: 0 auto;
        }
        .sig-img {
            display: block;
            width: auto;
            height: 16mm;
            max-width: 95%;
            margin: 0 auto .7mm;
        }
        .sig-name {
            display: block;
            text-align: center;
        }
    </style>
</head>
<body>
    <table class="top-table">
        <tr>
            <td class="logo-cell">
                @if (file_exists($logoSig))
                    <img src="{{ $logoSig }}" class="sig-logo" alt="SIG">
                @endif
            </td>
            <td class="title-cell">
                FORM COMMISSIONING
                <span class="title-work">{{ $titleDetail }}</span>
            </td>
            <td class="logo-cell">
                @if (file_exists($logoSt))
                    <img src="{{ $logoSt }}" class="st-logo" alt="ST">
                @endif
            </td>
        </tr>
    </table>

    <table class="header-table section-table">
        @foreach ($headerRows as $row)
            <tr>
                @foreach ($row as $cell)
                    <td><span class="info-label">{{ $cell['label'] }}</span> : <span class="info-value">{{ $cell['value'] }}</span></td>
                @endforeach
            </tr>
        @endforeach
    </table>

    <div class="section">{{ $labels['motor_title'] }}</div>
    <table class="motor-table section-table">
        <tr>
            @foreach ($motorRatingFields as $field)
                <th>
                    {{ $field['label'] }}
                    @if ($unitLabel = FixedCommissioningTemplate::fieldUnitLabel($field))
                        <span class="field-unit">{{ $unitLabel }}</span>
                    @endif
                </th>
            @endforeach
            <th class="black" colspan="4">RMS Vibration velocity - ISO 10816-1</th>
        </tr>
        <tr>
            @foreach ($motorRatingFields as $field)
                <td>{{ FixedCommissioningTemplate::valueWithUnit($body['motor_rating'][$field['key']] ?? '', $field) }}</td>
            @endforeach
            <td colspan="4">Power &lt;= 15 kW: &lt; 4.5 mm/s<br>15 kW &lt; Power &lt;= 300 kW: &lt; 7.1 mm/s<br>300 kW &lt; Power &lt;= 10 MW: &lt; 11.2 mm/s</td>
        </tr>
    </table>
    <div class="pdf-table-gap"></div>
    <table class="motor-table section-table">
        <tr>
            @foreach ($motorTestFields as $field)
                @if ($field['key'] === 'r')
                    <th colspan="3">P H A S E</th>
                @elseif ($field['key'] === 'horizontal')
                    <th colspan="3">Vibration test</th>
                @elseif (! in_array($field['key'], ['s', 't', 'vertical', 'axial'], true))
                    <th rowspan="2">
                        {{ $field['label'] }}
                        @if ($unitLabel = FixedCommissioningTemplate::fieldUnitLabel($field))
                            <span class="field-unit">{{ $unitLabel }}</span>
                        @endif
                    </th>
                @endif
            @endforeach
        </tr>
        <tr>
            @foreach ($motorTestFields as $field)
                @if (in_array($field['key'], ['r', 's', 't', 'horizontal', 'vertical', 'axial'], true))
                    <th>
                        {{ $field['label'] }}
                        @if ($unitLabel = FixedCommissioningTemplate::fieldUnitLabel($field))
                            <span class="field-unit">{{ $unitLabel }}</span>
                        @endif
                    </th>
                @endif
            @endforeach
        </tr>
        @foreach (($body['motor_test_rows'] ?? []) as $row)
            <tr>
                @foreach ($motorTestFields as $field)
                    <td>{{ FixedCommissioningTemplate::valueWithUnit($row[$field['key']] ?? '', $field) }}</td>
                @endforeach
            </tr>
        @endforeach
    </table>

    <div class="section">{{ $labels['gearbox_title'] }}</div>
    <table class="gearbox-rating-table">
        <tr>
            @foreach ($gearboxRatingFields as $field)
                <th>
                    {{ $field['label'] }}
                    @if ($unitLabel = FixedCommissioningTemplate::fieldUnitLabel($field))
                        <span class="field-unit">{{ $unitLabel }}</span>
                    @endif
                </th>
            @endforeach
        </tr>
        <tr>
            @foreach ($gearboxRatingFields as $field)
                <td>{{ FixedCommissioningTemplate::valueWithUnit($body['gearbox_rating'][$field['key']] ?? '', $field) }}</td>
            @endforeach
        </tr>
    </table>
    <table class="gearbox-test-table section-table">
        <tr>
            @foreach ($gearboxTestFields as $field)
                @if ($field['key'] === 'horizontal')
                    <th colspan="3">Vibration test</th>
                @elseif (! in_array($field['key'], ['vertical', 'axial'], true))
                    <th rowspan="2">
                        {{ $field['label'] }}
                        @if ($unitLabel = FixedCommissioningTemplate::fieldUnitLabel($field))
                            <span class="field-unit">{{ $unitLabel }}</span>
                        @endif
                    </th>
                @endif
            @endforeach
        </tr>
        <tr>
            @foreach ($gearboxTestFields as $field)
                @if (in_array($field['key'], ['horizontal', 'vertical', 'axial'], true))
                    <th>
                        {{ $field['label'] }}
                        @if ($unitLabel = FixedCommissioningTemplate::fieldUnitLabel($field))
                            <span class="field-unit">{{ $unitLabel }}</span>
                        @endif
                    </th>
                @endif
            @endforeach
        </tr>
        @foreach (($body['gearbox_test_rows'] ?? []) as $row)
            <tr>
                @foreach ($gearboxTestFields as $field)
                    <td>{{ FixedCommissioningTemplate::valueWithUnit($row[$field['key']] ?? '', $field) }}</td>
                @endforeach
            </tr>
        @endforeach
    </table>

    <div class="section">{{ $labels['equipment_check_title'] }}</div>
    <table class="section-table equipment-check-table">
        <tr><th>No.</th><th>Item</th><th>Check</th><th>YES</th><th>NO</th><th>NA</th><th>Remark</th></tr>
        @foreach (($body['equipment_check_rows'] ?? []) as $row)
            <tr><td>{{ $row['no'] ?? $loop->iteration }}</td><td class="item-cell">{{ $row['item'] ?? '' }}</td><td>{!! $check(! empty($row['check'])) !!}</td><td>{!! $check(($row['result'] ?? '') === 'YES') !!}</td><td>{!! $check(($row['result'] ?? '') === 'NO') !!}</td><td>{!! $check(($row['result'] ?? '') === 'NA') !!}</td><td class="remark-cell">{{ $row['remark'] ?? '' }}</td></tr>
        @endforeach
    </table>

    <table class="notes-table section-table">
        <tr>
            <td style="width: 50%;"><strong>{{ $labels['note_label'] }}:</strong><br>{{ $submission->note ?: '-' }}</td>
            <td style="width: 50%;">
                <strong>{{ $labels['documentation_label'] }}</strong><br>
                @php
                    $documentationImages = $imageAttachments->take(6)
                        ->map(function ($attachment) {
                            $path = \Illuminate\Support\Facades\Storage::disk('local')->exists($attachment->file_path)
                                ? \Illuminate\Support\Facades\Storage::disk('local')->path($attachment->file_path)
                                : storage_path('app/public/'.$attachment->file_path);

                            return file_exists($path) ? ['path' => $path, 'name' => $attachment->original_name] : null;
                        })
                        ->filter()
                        ->values();
                @endphp
                @if ($documentationImages->isNotEmpty())
                    @php
                        $imageCount = $documentationImages->count();
                        $chunkSize = $imageCount <= 3 ? $imageCount : 3;
                    @endphp
                    <table class="doc-image-grid images-{{ min($imageCount, 3) }}">
                        @foreach ($documentationImages->chunk($chunkSize) as $row)
                            <tr>
                                @foreach ($row as $image)
                                    <td><img src="{{ $image['path'] }}" class="doc-image" alt="{{ $image['name'] }}"></td>
                                @endforeach
                            </tr>
                        @endforeach
                    </table>
                @else
                    -
                @endif
            </td>
        </tr>
    </table>

    <div class="approval-block">
        <table class="approval">
            <tr class="approval-group">
                <td colspan="2">Checked by / Diperiksa Oleh :</td>
                <td>Approved by / Disetujui oleh :</td>
                <td>Known by / Diketahui Oleh :</td>
            </tr>
            <tr class="approval-role">
                @foreach ($approvalColumns as $column)
                    @php($data = $approvalData[$column['key']] ?? [])
                    <th>{{ $data['label'] ?? $column['label'] }}</th>
                @endforeach
            </tr>
            <tr class="approval-sign">
                @foreach ($approvalColumns as $column)
                    @php($data = $approvalData[$column['key']] ?? [])
                    <td>
                        @if (! empty($data['signature']))
                            <img src="{{ $signature($data['signature']) }}" class="sig-img" alt="Tanda tangan">
                        @endif
                        <span class="sig-name">{{ $data['name'] ?? ($approvalDefaults[$column['key']]['name'] ?? '') }}</span>
                    </td>
                @endforeach
            </tr>
            <tr class="approval-date">
                @foreach ($approvalColumns as $column)
                    @php($data = $approvalData[$column['key']] ?? [])
                    <td>Date : {{ $data['date'] ?? '' }}</td>
                @endforeach
            </tr>
        </table>
    </div>
</body>
</html>

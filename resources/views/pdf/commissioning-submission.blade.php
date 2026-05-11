@php
    use App\Support\Commissioning\FixedCommissioningTemplate;
    $header = $submission->header_data ?? [];
    $body = $submission->body_data ?? [];
    $value = fn ($key, $default = '-') => $header[$key] ?? $default;
    $logoSig = public_path('assets/images/logo/logo-sig.png');
    $logoSt = public_path('assets/images/logo/logo-st2.png');
    $titleDetail = $submission->template?->name ?: 'Form Commissioning';
    $schema = FixedCommissioningTemplate::normalizeSchema($submission->template?->body_schema);
    $labels = $schema['labels'];
    $motorRatingFields = $schema['motor_rating_fields'];
    $motorTestFields = $schema['motor_test_fields'];
    $gearboxRatingFields = $schema['gearbox_rating_fields'];
    $gearboxTestFields = $schema['gearbox_test_fields'];
    $imageAttachments = $submission->attachments
        ->where('type', 'image')
        ->values();
@endphp
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Commissioning - {{ $titleDetail }}</title>
    <style>
        @page { margin: 8mm; }
        body { margin: 0; font-family: DejaVu Sans, sans-serif; font-size: 8.5px; color: #111; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #111; padding: 4px; text-align: center; vertical-align: middle; line-height: 1.35; }
        th { font-weight: bold; text-align: center; }
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
        .section { font-size: 10px; font-weight: bold; margin-top: 12px; margin-bottom: 3px; text-align: left; }
        .no-border td { border: 0; }
        .black { background: #000; color: #fff; }
        .approval td { text-align: center; vertical-align: top; }
        .approval .approval-group td { height: 18px; padding: 2px 3px; }
        .approval .approval-role th { height: 18px; padding: 3px; }
        .approval .approval-sign td { height: 58px; }
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
        .section-table { margin-bottom: 12px; }
        .header-table td { text-align: center; }
        .notes-table td { text-align: left; vertical-align: top; }
        .notes-table td { height: 108px; }
        .doc-image { max-width: 46%; max-height: 100px; margin: 3px; object-fit: contain; border: 1px solid #bbb; }
        .motor-table { table-layout: fixed; }
        .gearbox-rating-table { width: 55%; }
        .gearbox-test-table { width: 70%; margin-top: 4px; }
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
        <tr>
            <td><strong>Doc.Number</strong><br>{{ $value('doc_number', $submission->form_number) }}</td>
            <td><strong>Tahun</strong><br>{{ $value('tahun') }}</td>
            <td><strong>Area</strong><br>{{ $value('area') }}</td>
            <td><strong>Date & Time</strong><br>{{ $value('date_time') }}</td>
        </tr>
        <tr>
            <td><strong>Tag.Num</strong><br>{{ $value('tag_num') }}</td>
            <td><strong>Functional Location</strong><br>{{ $value('functional_location') }}</td>
            <td><strong>Name Equipment</strong><br>{{ $value('name_equipment') }}</td>
            <td><strong>ID Equipment</strong><br>{{ $value('id_equipment') }}</td>
        </tr>
    </table>

    <div class="section">{{ $labels['motor_title'] }}</div>
    <table class="motor-table section-table">
        <tr>
            @foreach ($motorRatingFields as $field)
                <th>{{ $field['label'] }}</th>
            @endforeach
            <th class="black" colspan="4">RMS Vibration velocity - ISO 10816-1</th>
        </tr>
        <tr>
            @foreach ($motorRatingFields as $field)
                <td>{{ $body['motor_rating'][$field['key']] ?? '' }}</td>
            @endforeach
            <td colspan="4">Power &lt;= 15 kW: &lt; 4.5 mm/s<br>15 kW &lt; Power &lt;= 300 kW: &lt; 7.1 mm/s<br>300 kW &lt; Power &lt;= 10 MW: &lt; 11.2 mm/s</td>
        </tr>
        <tr>
            @foreach ($motorTestFields as $field)
                @if ($field['key'] === 'r')
                    <th colspan="3">P H A S E</th>
                @elseif ($field['key'] === 'horizontal')
                    <th colspan="3">Vibration test</th>
                @elseif (! in_array($field['key'], ['s', 't', 'vertical', 'axial'], true))
                    <th rowspan="2">
                        {{ $field['label'] }}
                        @if ($field['key'] === 'starting_current')
                            <br><small>(Ampere)</small>
                        @elseif ($field['key'] === 'time')
                            <br><small>(Interval 10 minutes)</small>
                        @endif
                    </th>
                @endif
            @endforeach
        </tr>
        <tr>
            @foreach ($motorTestFields as $field)
                @if (in_array($field['key'], ['r', 's', 't', 'horizontal', 'vertical', 'axial'], true))
                    <th>{{ $field['label'] }}</th>
                @endif
            @endforeach
        </tr>
        @foreach (($body['motor_test_rows'] ?? []) as $row)
            <tr>
                @foreach ($motorTestFields as $field)
                    <td>{{ $row[$field['key']] ?? '' }}</td>
                @endforeach
            </tr>
        @endforeach
    </table>

    <div class="section">{{ $labels['gearbox_title'] }}</div>
    <table class="gearbox-rating-table">
        <tr>
            @foreach ($gearboxRatingFields as $field)
                <th>{{ $field['label'] }}</th>
            @endforeach
        </tr>
        <tr>
            @foreach ($gearboxRatingFields as $field)
                <td>{{ $body['gearbox_rating'][$field['key']] ?? '' }}</td>
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
                        @if ($field['key'] === 'time')
                            <br><small>(Interval 10 minutes)</small>
                        @endif
                    </th>
                @endif
            @endforeach
        </tr>
        <tr>
            @foreach ($gearboxTestFields as $field)
                @if (in_array($field['key'], ['horizontal', 'vertical', 'axial'], true))
                    <th>{{ $field['label'] }}</th>
                @endif
            @endforeach
        </tr>
        @foreach (($body['gearbox_test_rows'] ?? []) as $row)
            <tr>
                @foreach ($gearboxTestFields as $field)
                    <td>{{ $row[$field['key']] ?? '' }}</td>
                @endforeach
            </tr>
        @endforeach
    </table>

    <div class="section">{{ $labels['equipment_check_title'] }}</div>
    <table class="section-table">
        <tr><th>No.</th><th>Item</th><th>Check</th><th>YES</th><th>NO</th><th>NA</th><th>Remark</th></tr>
        @foreach (($body['equipment_check_rows'] ?? []) as $row)
            <tr><td>{{ $row['no'] ?? $loop->iteration }}</td><td>{{ $row['item'] ?? '' }}</td><td>{{ ! empty($row['check']) ? '✓' : '' }}</td><td>{{ ($row['result'] ?? '') === 'YES' ? '✓' : '' }}</td><td>{{ ($row['result'] ?? '') === 'NO' ? '✓' : '' }}</td><td>{{ ($row['result'] ?? '') === 'NA' ? '✓' : '' }}</td><td>{{ $row['remark'] ?? '' }}</td></tr>
        @endforeach
    </table>

    <table class="notes-table section-table">
        <tr>
            <td style="width: 50%;"><strong>{{ $labels['note_label'] }}:</strong><br>{{ $submission->note ?: '-' }}</td>
            <td style="width: 50%;">
                <strong>{{ $labels['documentation_label'] }}</strong><br>
                @forelse ($imageAttachments->take(6) as $attachment)
                    @php($path = \Illuminate\Support\Facades\Storage::disk('local')->exists($attachment->file_path) ? \Illuminate\Support\Facades\Storage::disk('local')->path($attachment->file_path) : storage_path('app/public/'.$attachment->file_path))
                    @if (file_exists($path))
                        <img src="{{ $path }}" class="doc-image" alt="{{ $attachment->original_name }}">
                    @endif
                @empty
                    -
                @endforelse
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
                @foreach (FixedCommissioningTemplate::approvalColumns() as $column)
                    <th>{{ $column['label'] }}</th>
                @endforeach
            </tr>
            <tr class="approval-sign">
                @foreach (FixedCommissioningTemplate::approvalColumns() as $column)
                    @php($data = $submission->approval_data[$column['key']] ?? [])
                    <td>{{ $data['name'] ?? '' }}</td>
                @endforeach
            </tr>
            <tr class="approval-date">
                @foreach (FixedCommissioningTemplate::approvalColumns() as $column)
                    @php($data = $submission->approval_data[$column['key']] ?? [])
                    <td>Date : {{ $data['date'] ?? '' }}</td>
                @endforeach
            </tr>
        </table>
    </div>
</body>
</html>

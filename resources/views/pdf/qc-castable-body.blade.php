@php
    use App\Support\QcTemplates\FixedQcTemplate;

    $customer = $bodyData['castable_customer'] ?? [];
    $checks = $bodyData['castable_checks'] ?? [];
    $sample = $bodyData['castable_sample'] ?? [];
    $sampleSignature = is_array($sample['qc_sign_date'] ?? null) ? $sample['qc_sign_date'] : [];
    $dimensionValue = static function (array $saved): string {
        $dimensions = $saved['dimensions'] ?? [];
        $values = [
            trim((string) ($dimensions['length'] ?? '')),
            trim((string) ($dimensions['width'] ?? '')),
            trim((string) ($dimensions['height'] ?? '')),
        ];

        if (collect($values)->filter()->isNotEmpty()) {
            return '( '.implode(' x ', $values).' )';
        }

        return trim((string) ($saved['value'] ?? ''));
    };
@endphp

<div class="fixed-section-title">Customer Data</div>
<table class="fixed-form-table">
    <tbody>
        @foreach (FixedQcTemplate::castableCustomerRows() as $row)
            <tr>
                <td style="width: 4%;" class="center">{{ $row['no'] }}</td>
                <td style="width: 33%;">{{ $row['label'] }}</td>
                <td>{{ $customer[$row['key']] ?? ($row['hint'] ?? '') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="section-gap"></div>
<div class="fixed-section-title">Installation Record / Inspection Check List</div>
<table class="fixed-form-table">
    <tbody>
        @foreach (FixedQcTemplate::castableInspectionRows() as $row)
            @php
                $saved = $checks[$row['key']] ?? [];
            @endphp
            <tr>
                <td style="width: 4%;" class="center">{{ $row['no'] }}</td>
                <td style="width: 29%;">{{ $row['label'] }}</td>
                <td style="width: 28%;">
                    @if (! empty($row['options']))
                        @foreach ($row['options'] as $option)
                            <span class="pdf-small-box">{!! $check(($saved['status'] ?? '') === $option) !!}</span> {{ $option }}
                            @if (! $loop->last)&nbsp;&nbsp;@endif
                        @endforeach
                    @elseif (($row['input'] ?? null) === 'dimension')
                        : {{ $dimensionValue($saved) }}
                    @else
                        : {{ $saved['value'] ?? '' }}
                    @endif
                </td>
                <td style="width: 13%;" class="center">{{ $row['unit'] ?? '' }}</td>
                <td style="width: 26%;">
                    @if (! empty($row['detail_label']))
                        {{ $row['detail_label'] }} : {{ $saved['detail'] ?? '' }}
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="small">*Sample For Laboratory test by QC</div>

<table class="fixed-form-table" style="width: 36%; margin-left: 64%; margin-top: 2mm;">
    <tr><th colspan="2">SAMPLE DATA</th></tr>
    @foreach (FixedQcTemplate::castableSampleRows() as $row)
        <tr>
            <td style="width: 50%;">{{ $row['label'] }}</td>
            <td>
                @if ($row['key'] === 'qc_sign_date')
                    :
                    @if (! empty($sampleSignature['signature'] ?? null))
                        <img src="{{ $signature($sampleSignature['signature']) }}" class="sig-img" alt="Tanda tangan QC">
                    @endif
                    {{ $sampleSignature['date'] ?? '' }}
                @else
                    : {{ $sample[$row['key']] ?? '' }}
                @endif
            </td>
        </tr>
    @endforeach
</table>

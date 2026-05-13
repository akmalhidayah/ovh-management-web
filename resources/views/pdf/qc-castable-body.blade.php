@php
    use App\Support\QcTemplates\FixedQcTemplate;

    $customer = $bodyData['castable_customer'] ?? [];
    $checks = $bodyData['castable_checks'] ?? [];
    $sample = $bodyData['castable_sample'] ?? [];
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
                    @else
                        {{ $saved['value'] ?? '' }}
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
            <td>: {{ $sample[$row['key']] ?? '' }}</td>
        </tr>
    @endforeach
</table>

@php
    use App\Support\QcTemplates\FixedQcTemplate;
    use Illuminate\Support\Str;

    $customer = $bodyData['brics_customer'] ?? [];
    $meta = $bodyData['brics_meta'] ?? [];
    $technical = $bodyData['brics_technical'] ?? [];
    $manpower = $bodyData['brics_manpower'] ?? [];
    $manpowerRows = collect($bodyData['brics_manpower_rows'] ?? [])
        ->map(fn ($row) => [
            'left_label' => $row['left_label'] ?? '',
            'left_value' => $row['left_value'] ?? '',
            'right_label' => $row['right_label'] ?? '',
            'right_value' => $row['right_value'] ?? '',
        ])
        ->filter(fn ($row) => collect($row)->filter()->isNotEmpty())
        ->values();

    if ($manpowerRows->isEmpty()) {
        $manpowerRows = collect(FixedQcTemplate::bricsManpowerRows())
            ->map(fn ($row) => [
                'left_label' => $row['left'],
                'left_value' => $manpower[Str::slug($row['left'], '_')] ?? $manpower[str($row['left'])->snake()->toString()] ?? '',
                'right_label' => $row['right'],
                'right_value' => $manpower[Str::slug($row['right'], '_')] ?? $manpower[str($row['right'])->snake()->toString()] ?? '',
            ]);
    }

    $weather = $bodyData['brics_weather'] ?? [];
    $checks = $bodyData['brics_checks'] ?? [];
@endphp

<div class="fixed-section-title">Customer Data</div>
<table class="fixed-form-table">
    <tbody>
        @foreach (FixedQcTemplate::bricsCustomerRows() as $row)
            <tr>
                <td style="width: 4%;" class="center">{{ $row['no'] }}</td>
                <td style="width: 21%;">{{ $row['label'] }}</td>
                <td>{{ $customer[$row['key']] ?? ($row['default'] ?? '') }}</td>
                @if ($loop->first)
                    <td rowspan="2" style="width: 9%;" class="center">OWNER</td>
                    <td rowspan="2" style="width: 11%;">{{ $meta['owner'] ?? '' }}</td>
                @elseif ($loop->iteration === 3)
                    <td rowspan="2" class="center">TYPE<br>INSPECT</td>
                    <td rowspan="2">{{ $meta['type_inspect'] ?? '' }}</td>
                @elseif ($loop->iteration === 5)
                    <td rowspan="2" class="center">NO.<br>REPORT</td>
                    <td rowspan="2">{{ $meta['no_report'] ?? '' }}</td>
                @endif
            </tr>
        @endforeach
    </tbody>
</table>

<div class="fixed-section-title brics-section-gap">Kiln Technical Information</div>
<table class="fixed-form-table">
    <tr>
        @foreach (FixedQcTemplate::bricsTechnicalRows() as $row)
            <td style="width: 16.66%;"><strong>{{ $row['label'] }}</strong> : {{ $technical[$row['key']] ?? '' }}</td>
            @if ($loop->iteration % 2 === 0 && ! $loop->last)</tr><tr>@endif
        @endforeach
    </tr>
</table>

<table class="fixed-form-table brics-section-gap">
    <tr>
        <th colspan="4" style="width: 66%;">MANPOWER</th>
        <th colspan="3">WEATHER</th>
    </tr>
    @foreach ($manpowerRows as $index => $row)
        <tr>
            <td style="width: 16%;">{{ $row['left_label'] }}</td>
            <td style="width: 17%;">{{ $row['left_value'] }}</td>
            <td style="width: 16%;">{{ $row['right_label'] }}</td>
            <td style="width: 17%;">{{ $row['right_value'] }}</td>
            @if ($index < 2)
                @php
                    $time = $index === 0 ? 'day' : 'night';
                @endphp
                <td class="center">{{ strtoupper($time) }}</td>
                <td class="center"><span class="pdf-small-box">{!! $check(($weather[$time] ?? '') === 'Rainy') !!}</span> RAINY</td>
                <td class="center"><span class="pdf-small-box">{!! $check(($weather[$time] ?? '') === 'Clear') !!}</span> CLEAR</td>
            @else
                <td colspan="3"></td>
            @endif
        </tr>
    @endforeach
</table>

<div class="fixed-section-title brics-section-gap">Installation Record / Inspection Check List</div>
<table class="fixed-form-table">
    <tbody>
        @foreach (FixedQcTemplate::bricsInspectionSections() as $section)
            <tr>
                <th colspan="2" style="text-align: left;">{{ trim(($section['number'] ?? '').' '.$section['title']) }}</th>
                <th style="width: 11%;">OK</th>
                <th style="width: 11%;">NO</th>
                <th style="width: 39%;">REMARK</th>
            </tr>
            @foreach ($section['items'] as $row)
                @php
                    $saved = $checks[$row['key']] ?? [];
                @endphp
                <tr>
                    <td style="width: 5%;" class="center">{{ $row['no'] }}</td>
                    <td style="width: 34%;">{{ $row['label'] }}</td>
                    <td class="center"><span class="pdf-small-box">{!! $check(($saved['status'] ?? '') === 'OK') !!}</span></td>
                    <td class="center"><span class="pdf-small-box">{!! $check(($saved['status'] ?? '') === 'NO') !!}</span></td>
                    <td>{{ $saved['remark'] ?? '' }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>

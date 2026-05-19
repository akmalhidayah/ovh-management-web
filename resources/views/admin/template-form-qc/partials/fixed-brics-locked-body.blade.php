@php
    use App\Support\QcTemplates\FixedQcTemplate;
@endphp

<div class="table-responsive mb-3">
    <table class="table table-bordered align-middle mb-0">
        <tbody>
            <tr class="table-light"><th colspan="5">CUSTOMER DATA</th></tr>
            @foreach (FixedQcTemplate::bricsCustomerRows() as $row)
                <tr>
                    <td class="text-center" style="width: 64px;">{{ $row['no'] }}</td>
                    <td class="fw-semibold" style="width: 26%;">{{ $row['label'] }}</td>
                    <td><input type="text" class="form-control form-control-sm" value="{{ $row['default'] ?? '' }}" disabled></td>
                    @if ($loop->first)
                        <td rowspan="2" class="text-center fw-semibold" style="width: 110px;">OWNER</td>
                        <td rowspan="2"><input type="text" class="form-control form-control-sm" disabled></td>
                    @elseif ($loop->iteration === 3)
                        <td rowspan="2" class="text-center fw-semibold">TYPE<br>INSPECT</td>
                        <td rowspan="2"><input type="text" class="form-control form-control-sm" disabled></td>
                    @elseif ($loop->iteration === 5)
                        <td rowspan="2" class="text-center fw-semibold">NO.<br>REPORT</td>
                        <td rowspan="2"><input type="text" class="form-control form-control-sm" disabled></td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="table-responsive mb-3">
    <table class="table table-bordered align-middle mb-0">
        <tbody>
            <tr class="table-light"><th colspan="4">KILN TECHNICAL INFORMATION</th></tr>
            @foreach (array_chunk(FixedQcTemplate::bricsTechnicalRows(), 2) as $rowPair)
                <tr>
                    @foreach ($rowPair as $row)
                        <td class="fw-semibold" style="width: 20%;">{{ $row['label'] }}</td>
                        <td><input type="{{ $row['type'] ?? 'text' }}" class="form-control form-control-sm" disabled></td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="table-responsive mb-3">
    <table class="table table-bordered align-middle mb-0">
        <tbody>
            <tr class="table-light">
                <th colspan="4" class="text-center">MANPOWER</th>
                <th colspan="3" class="text-center">WEATHER</th>
            </tr>
            @foreach (FixedQcTemplate::bricsManpowerRows() as $index => $row)
                <tr>
                    @foreach (['left', 'right'] as $side)
                        <td class="fw-semibold">{{ $row[$side] }}</td>
                        <td><input type="text" class="form-control form-control-sm" disabled></td>
                    @endforeach
                    @if ($index < 2)
                        <td class="text-center fw-semibold">{{ $index === 0 ? 'DAY' : 'NIGHT' }}</td>
                        <td class="text-center"><label><input type="checkbox" disabled> RAINY</label></td>
                        <td class="text-center"><label><input type="checkbox" disabled> CLEAR</label></td>
                    @else
                        <td colspan="3"></td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="table-responsive">
    <table class="table table-bordered align-middle mb-0">
        <tbody>
            @foreach (FixedQcTemplate::bricsInspectionSections() as $section)
                <tr class="table-light">
                    <th colspan="2">{{ trim(($section['number'] ?? '').' '.$section['title']) }}</th>
                    <th class="text-center" style="width: 110px;">OK</th>
                    <th class="text-center" style="width: 110px;">NO</th>
                    <th class="text-center" style="width: 34%;">REMARK</th>
                </tr>
                @foreach ($section['items'] as $row)
                    <tr>
                        <td class="text-center" style="width: 64px;">{{ $row['no'] }}</td>
                        <td class="fw-semibold">{{ $row['label'] }}</td>
                        <td class="text-center"><input type="checkbox" disabled></td>
                        <td class="text-center"><input type="checkbox" disabled></td>
                        <td><input type="text" class="form-control form-control-sm" disabled></td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</div>

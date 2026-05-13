@php
    use App\Support\QcTemplates\FixedQcTemplate;

    $bricsCustomer = $oldBody['brics_customer'] ?? [];
    $bricsMeta = $oldBody['brics_meta'] ?? [];
    $bricsTechnical = $oldBody['brics_technical'] ?? [];
    $bricsManpower = $oldBody['brics_manpower'] ?? [];
    $bricsWeather = $oldBody['brics_weather'] ?? [];
    $bricsChecks = $oldBody['brics_checks'] ?? [];
@endphp

<section class="inspector-panel qc-form-card">
    <div class="qc-form-section-title"><h3>Customer Data</h3></div>
    <div class="qc-user-table-wrap">
        <table class="qc-user-checklist-table qc-user-fixed-table">
            <colgroup>
                <col style="width: 7%">
                <col style="width: 25%">
                <col style="width: 48%">
                <col style="width: 10%">
                <col style="width: 10%">
            </colgroup>
            <tbody>
                @foreach (FixedQcTemplate::bricsCustomerRows() as $row)
                    <tr>
                        <td class="text-center">{{ $row['no'] }}</td>
                        <td>{{ $row['label'] }}</td>
                        <td>
                            <input type="text"
                                   name="body[brics_customer][{{ $row['key'] }}]"
                                   value="{{ $bricsCustomer[$row['key']] ?? ($row['default'] ?? '') }}"
                                   class="form-control form-control-sm">
                        </td>
                        @if ($loop->first)
                            <td rowspan="2" class="text-center fw-semibold">OWNER</td>
                            <td rowspan="2">
                                <input type="text"
                                       name="body[brics_meta][owner]"
                                       value="{{ $bricsMeta['owner'] ?? '' }}"
                                       class="form-control form-control-sm">
                            </td>
                        @elseif ($loop->iteration === 3)
                            <td rowspan="2" class="text-center fw-semibold">TYPE<br>INSPECT</td>
                            <td rowspan="2">
                                <input type="text"
                                       name="body[brics_meta][type_inspect]"
                                       value="{{ $bricsMeta['type_inspect'] ?? '' }}"
                                       class="form-control form-control-sm">
                            </td>
                        @elseif ($loop->iteration === 5)
                            <td rowspan="2" class="text-center fw-semibold">NO.<br>REPORT</td>
                            <td rowspan="2">
                                <input type="text"
                                       name="body[brics_meta][no_report]"
                                       value="{{ $bricsMeta['no_report'] ?? '' }}"
                                       class="form-control form-control-sm">
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>

<section class="inspector-panel qc-form-card">
    <div class="qc-form-section-title"><h3>Kiln Technical Information</h3></div>
    <div class="qc-user-field-grid">
        @foreach (FixedQcTemplate::bricsTechnicalRows() as $row)
            <label class="qc-user-field">
                <span>{{ $row['label'] }}</span>
                <input type="text"
                       name="body[brics_technical][{{ $row['key'] }}]"
                       value="{{ $bricsTechnical[$row['key']] ?? '' }}"
                       class="form-control">
            </label>
        @endforeach
    </div>
</section>

<section class="inspector-panel qc-form-card">
    <div class="qc-form-section-title"><h3>Manpower & Weather</h3></div>
    <div class="qc-user-table-wrap mb-3">
        <table class="qc-user-checklist-table qc-user-fixed-table">
            <tbody>
                @foreach (FixedQcTemplate::bricsManpowerRows() as $row)
                    <tr>
                        @foreach (['left', 'right'] as $side)
                            @php
                                $label = $row[$side];
                            @endphp
                            <td>{{ $label }}</td>
                            <td>
                                <input type="text"
                                       name="body[brics_manpower][{{ str($label)->snake() }}]"
                                       value="{{ $bricsManpower[str($label)->snake()->toString()] ?? '' }}"
                                       class="form-control form-control-sm">
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="qc-user-table-wrap">
        <table class="qc-user-checklist-table qc-user-fixed-table">
            <thead><tr><th>Weather</th><th>Rainy</th><th>Clear</th></tr></thead>
            <tbody>
                @foreach (['day' => 'DAY', 'night' => 'NIGHT'] as $key => $label)
                    <tr>
                        <td>{{ $label }}</td>
                        @foreach (['Rainy', 'Clear'] as $weather)
                            <td class="text-center">
                                <input type="radio"
                                       name="body[brics_weather][{{ $key }}]"
                                       value="{{ $weather }}"
                                       data-final-check-ok="1"
                                       @checked(($bricsWeather[$key] ?? null) === $weather)>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>

<section class="inspector-panel qc-form-card">
    <div class="qc-form-section-title"><h3>Installation Record / Inspection Check List</h3></div>
    <div class="qc-user-table-wrap">
        <table class="qc-user-checklist-table qc-user-fixed-table">
            <colgroup>
                <col style="width: 7%">
                <col style="width: 29%">
                <col style="width: 12%">
                <col style="width: 12%">
                <col style="width: 40%">
            </colgroup>
            <tbody>
                @foreach (FixedQcTemplate::bricsInspectionSections() as $section)
                    <tr>
                        <th colspan="5">{{ trim(($section['number'] ?? '').' '.$section['title']) }}</th>
                    </tr>
                    @foreach ($section['items'] as $row)
                        @php
                            $saved = $bricsChecks[$row['key']] ?? [];
                        @endphp
                        <tr>
                            <td class="text-center">{{ $row['no'] }}</td>
                            <td>{{ $row['label'] }}</td>
                            @foreach (['OK', 'NO'] as $status)
                                <td class="text-center">
                                    <input type="radio"
                                           name="body[brics_checks][{{ $row['key'] }}][status]"
                                           value="{{ $status }}"
                                           @if ($status === 'OK') data-final-check-ok="1" @endif
                                           @checked(($saved['status'] ?? null) === $status)>
                                </td>
                            @endforeach
                            <td>
                                <input type="text"
                                       name="body[brics_checks][{{ $row['key'] }}][remark]"
                                       value="{{ $saved['remark'] ?? '' }}"
                                       class="form-control form-control-sm">
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</section>

@php
    use App\Support\QcTemplates\FixedQcTemplate;

    $customer = $bodyData['castable_customer'] ?? [];
    $checks = $bodyData['castable_checks'] ?? [];
    $sample = $bodyData['castable_sample'] ?? [];
    $monitoringRows = collect($bodyData['castable_monitoring_rows'] ?? [])
        ->filter(fn ($row) => collect($row)->except(['no'])->filter(fn ($value) => filled($value))->isNotEmpty())
        ->values();

    if ($monitoringRows->isEmpty()) {
        $monitoringRows = collect([collect(FixedQcTemplate::castableMonitoringColumns())
            ->mapWithKeys(fn ($column) => [$column['key'] => ''])
            ->merge(['no' => '1'])
            ->all()]);
    }

    $formatDimension = static function (array $saved): string {
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

    $detailRows = collect(FixedQcTemplate::castableInspectionRows())
        ->filter(fn ($row) => ! empty($row['detail_label']))
        ->values();
@endphp

<div class="castable-one-sheet">
    @include('pdf.partials.qc-castable-header')

    <div class="castable-rule"></div>

    <table class="castable-customer">
        <colgroup>
            <col style="width: 8mm;">
            <col style="width: 55mm;">
            <col>
        </colgroup>
        <tr>
            <th colspan="3" class="text-left castable-section-title">CUSTOMER DATA</th>
        </tr>
        @foreach (FixedQcTemplate::castableCustomerRows() as $row)
            <tr>
                <td class="center castable-no-cell">{{ $row['no'] }}</td>
                <td class="castable-label-cell">{{ $row['label'] }}</td>
                <td class="castable-value-cell">{{ $customer[$row['key']] ?? ($row['hint'] ?? '') }}</td>
            </tr>
        @endforeach
    </table>

    <div class="castable-rule"></div>

    <table class="castable-monitor-compact">
        <colgroup>
            <col style="width: 7mm;">
            <col style="width: 17mm;">
            <col style="width: 17mm;">
            <col style="width: 18mm;">
            <col style="width: 16mm;">
            <col style="width: 19mm;">
            <col style="width: 18mm;">
            <col style="width: 17mm;">
            <col style="width: 18mm;">
            <col style="width: 22mm;">
            <col style="width: 22mm;">
        </colgroup>
        <thead>
            <tr>
                <th colspan="11" class="castable-title-row">Monitoring Installation Castable</th>
            </tr>
            <tr>
                <th rowspan="3">No.</th>
                <th colspan="2">Quantity Material/Mixing</th>
                <th rowspan="3">Temperatur Material<br>(kering)</th>
                <th rowspan="3">Temperatur Ruangan<br>&deg;C</th>
                <th rowspan="3">Waktu Aduk<br>(... Standard...) Menit</th>
                <th colspan="3">Air</th>
                <th rowspan="3" class="castable-monitor-location">Lokasi Pemasangan</th>
                <th rowspan="3">Keterangan</th>
            </tr>
            <tr>
                <th colspan="2" class="text-left">Type : {{ $bodyData['castable_monitoring_type'] ?? '....................' }}</th>
                <th rowspan="2">Persentase<br>(... Standard...)<br>(%)</th>
                <th rowspan="2">(... Standard...)<br>PH</th>
                <th rowspan="2">Temperatur<br>(... Standard...)<br>(&deg;C)</th>
            </tr>
            <tr>
                <th>Quantity<br>(kg)</th>
                <th>Batch number</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($monitoringRows as $row)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    @foreach (FixedQcTemplate::castableMonitoringColumns() as $column)
                        <td>{{ $row[$column['key']] ?? '' }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="castable-installation-section">
        <div class="castable-section-heading">INSTALLATION RECORD / INSPECTION CHECK LIST</div>

        <table class="castable-body-grid">
            <tr>
                <td class="castable-body-left">
                    <table class="castable-inspection">
                        <colgroup>
                            <col style="width: 8mm;">
                            <col style="width: 52mm;">
                            <col>
                        </colgroup>
                        <tr>
                            <th>No</th>
                            <th>Item</th>
                            <th>Status / Data</th>
                        </tr>
                        @foreach (FixedQcTemplate::castableInspectionRows() as $row)
                            @php $saved = $checks[$row['key']] ?? []; @endphp
                            <tr>
                                <td class="center castable-no-cell">{{ $row['no'] }}</td>
                                <td class="castable-label-cell">{{ $row['label'] }}</td>
                                <td>
                                    @if (! empty($row['options']))
                                        @foreach ($row['options'] as $option)
                                            <span class="pdf-small-box">{!! $check(($saved['status'] ?? '') === $option) !!}</span>
                                            <span>{{ $option }}</span>
                                            @if (! $loop->last)<span class="option-gap"></span>@endif
                                        @endforeach
                                    @elseif (($row['input'] ?? null) === 'dimension')
                                        : {{ $formatDimension($saved) }} <strong>mm</strong>
                                    @else
                                        : {{ $saved['value'] ?? '' }} <strong>{{ $row['unit'] ?? '' }}</strong>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </table>
                    <div class="castable-small-note">*Sample For Laboratory test by QC</div>
                </td>
                <td class="castable-body-right">
                    <table class="castable-detail">
                        <colgroup>
                            <col style="width: 42%;">
                            <col>
                        </colgroup>
                        <tr><th colspan="2">DETAIL CHECK</th></tr>
                        @foreach ($detailRows as $row)
                            @php $saved = $checks[$row['key']] ?? []; @endphp
                            <tr>
                                <td class="castable-label-cell">{{ $row['detail_label'] }} :</td>
                                <td>{{ $saved['detail'] ?? '' }}</td>
                            </tr>
                        @endforeach
                    </table>

                    <table class="castable-sample">
                        <colgroup>
                            <col style="width: 42%;">
                            <col>
                        </colgroup>
                        <tr><th colspan="2">SAMPLE DATA</th></tr>
                        @foreach (FixedQcTemplate::castableSampleRows() as $row)
                            <tr>
                                <td class="castable-label-cell">{{ $row['label'] }} :</td>
                                <td>{{ $sample[$row['key']] ?? '' }}</td>
                            </tr>
                        @endforeach
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <table class="castable-footer-grid">
        <tr>
            <td style="width: 42%;">
                <strong>Catatan :</strong>
                <div class="castable-note-lines">{{ $submission->note ?: '' }}</div>
            </td>
            <td>
                <div class="castable-approval-caption">Baru bisa ter approve jika form sudah terisi semua:</div>
                <table class="castable-approval">
                    <tr>
                        @foreach ($approvalColumns as $column)
                            <th>
                                {{ $column['label'] }}<br>
                                <span style="font-size: 7.5px; font-weight: 400;">{{ $column['group'] }}</span>
                            </th>
                        @endforeach
                    </tr>
                    <tr class="castable-approval-sign">
                        @foreach ($approvalColumns as $column)
                            @php $ap = $approvalByKey[$column['key']] ?? []; @endphp
                            <td>
                                <span class="castable-approval-mark">
                                    @if (! empty($ap['signature']))
                                        <img src="{{ $signature($ap['signature']) }}" class="castable-approval-img" alt="Tanda tangan">
                                    @endif
                                </span>
                                <span class="castable-approval-name">{{ $ap['name'] ?? '' }}</span>
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach ($approvalColumns as $column)
                            @php $ap = $approvalByKey[$column['key']] ?? []; @endphp
                            <td>Date : {{ $ap['date'] ?? '' }}</td>
                        @endforeach
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>

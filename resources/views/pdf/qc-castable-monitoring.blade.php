@php
    use App\Support\QcTemplates\FixedQcTemplate;

    $monitorRows = collect($bodyData['castable_monitoring_rows'] ?? [])->values();
    $monitorSignatures = $bodyData['castable_monitoring_signatures'] ?? [];
    $monitoringType = trim((string) ($bodyData['castable_monitoring_type'] ?? ''));
    try {
        $monitorDate = $dateTime ? \Illuminate\Support\Carbon::parse($dateTime)->format('Y-m-d') : '';
    } catch (\Throwable) {
        $monitorDate = (string) $dateTime;
    }
    $monitorJob = $generalInfo['pekerjaan'] ?? $submission->pekerjaan ?? 'Overhaul';
@endphp

<div class="castable-monitor-page">
    <table class="castable-monitor-head">
        <tr>
            <td style="width: 10%;">
                @if (file_exists($logoSig))
                    <img src="{{ $logoSig }}" class="castable-monitor-logo" alt="SIG">
                @endif
            </td>
            <td class="castable-monitor-meta">
                <table>
                    <tr>
                        <td style="width: 24mm;">Tanggal</td>
                        <td style="width: 4mm;">:</td>
                        <td>{{ $monitorDate }}</td>
                    </tr>
                    <tr>
                        <td>Pekerjaan</td>
                        <td>:</td>
                        <td>{{ $monitorJob }}</td>
                    </tr>
                </table>
            </td>
            <td class="castable-monitor-title">Monitoring Installation Castable</td>
        </tr>
    </table>

    <table class="castable-monitor-table">
        <colgroup>
            <col style="width: 3%">
            <col style="width: 6.5%">
            <col style="width: 8.3%">
            <col style="width: 6.7%">
            <col style="width: 6.5%">
            <col style="width: 8.7%">
            <col style="width: 8.7%">
            <col style="width: 8.7%">
            <col style="width: 9.1%">
            <col style="width: 14.3%">
            <col style="width: 19.5%">
        </colgroup>
        <thead>
            <tr>
                <th rowspan="3">No.</th>
                <th colspan="2">Quantity Material/Mixing</th>
                <th rowspan="3">Temperatur Material<br>(kering)</th>
                <th rowspan="3">Temperatur Ruangan<br>C</th>
                <th rowspan="3">Waktu Aduk<br>(... Standard...)<br>Menit</th>
                <th colspan="3">Air</th>
                <th rowspan="3">Lokasi Pemasangan</th>
                <th rowspan="3">Keterangan</th>
            </tr>
            <tr>
                <th colspan="2" style="text-align: left;">Type : {{ $monitoringType ?: '....................' }}</th>
                <th rowspan="2">Persentase<br>(... Standard...)<br>(%)</th>
                <th rowspan="2">(... Standard...)<br>PH</th>
                <th rowspan="2">Temperatur<br>(... Standard...)<br>(C)</th>
            </tr>
            <tr>
                <th>Quantity<br>(kg)</th>
                <th>Batch number</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($monitorRows as $index => $row)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $row['quantity'] ?? '' }}</td>
                    <td>{{ $row['batch_number'] ?? '' }}</td>
                    <td>{{ $row['material_temperature'] ?? '' }}</td>
                    <td>{{ $row['room_temperature'] ?? '' }}</td>
                    <td>{{ $row['mixing_time'] ?? '' }}</td>
                    <td>{{ $row['water_percentage'] ?? '' }}</td>
                    <td>{{ $row['water_ph'] ?? '' }}</td>
                    <td>{{ $row['water_temperature'] ?? '' }}</td>
                    <td>{{ $row['installation_location'] ?? '' }}</td>
                    <td>{{ $row['remark'] ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="castable-monitor-note-sign">
        <tr>
            <td style="width: 58%;">
                <strong>Catatan :</strong>
                <div class="castable-note-lines">
                    <div>{{ $bodyData['castable_monitoring_note'] ?? '' }}</div>
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
            </td>
            @foreach (FixedQcTemplate::castableMonitoringSignatures() as $definition)
                @php
                    $monitorSignature = $monitorSignatures[$definition['key']] ?? [];
                    $monitorSignatureImage = ! empty($monitorSignature['signature'] ?? null) ? $signature($monitorSignature['signature']) : null;
                @endphp
                <td class="castable-monitor-sign-cell" style="width: 21%;">
                    <div>{{ $definition['heading'] }}</div>
                    @if ($monitorSignatureImage)
                        <img src="{{ $monitorSignatureImage }}" class="castable-monitor-sign-img" alt="Tanda tangan">
                    @else
                        <span class="castable-monitor-sign-space"></span>
                    @endif
                    <div>{{ $monitorSignature['name'] ?? '' }}</div>
                    <div>{{ $definition['role'] }}</div>
                    <div>{{ $monitorSignature['date'] ?? '' }}</div>
                </td>
            @endforeach
        </tr>
    </table>
</div>

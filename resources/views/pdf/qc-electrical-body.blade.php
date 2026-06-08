@foreach ([
    'electrical_stator_rows' => ['title' => 'PENGUKURAN INSULATION RESISTANCE & POLARIZATION INDEX (STATOR)', 'measurement' => true, 'fields' => ['item' => 'Parameter', 'second_30' => '30 detik', 'minute_1' => '1 Menit', 'minute_10' => '10 Menit', 'pi' => 'PI']],
    'electrical_rotor_rows' => ['title' => 'PENGUKURAN INSULATION RESISTANCE & POLARIZATION INDEX (ROTOR)', 'measurement' => true, 'fields' => ['item' => 'Parameter', 'second_30' => '30 detik', 'minute_1' => '1 Menit', 'minute_10' => '10 Menit', 'pi' => 'PI']],
    'electrical_ovality_rows' => ['title' => 'PENGUKURAN OVALITY', 'fields' => ['ring' => 'Ring', 'tir' => 'TIR', 'standard' => 'Standar']],
    'electrical_installation_rows' => ['title' => 'CHECKLIST INSTALASI', 'fields' => ['activity' => 'Aktivitas', 'standard' => 'Standar', 'status' => 'OK/TIDAK', 'remark' => 'Keterangan / Remarks']],
    'electrical_uncouple_rows' => ['title' => 'UNCOUPLE TESTING', 'uncouple' => true, 'fields' => ['item' => 'Item', 'value_1' => 'Hasil 1', 'value_2' => 'Hasil 2', 'value_3' => 'Hasil 3']],
] as $bodyKey => $section)
    <div class="fixed-section-title {{ $loop->first ? '' : 'brics-section-gap' }}">{{ $section['title'] }}</div>
    <table class="data-table">
        @if (! empty($section['measurement']))
            <tr><th style="width: 6%;">No</th><th>Parameter</th><th colspan="4">Nilai</th></tr>
        @else
            <tr><th style="width: 6%;">No</th>@foreach ($section['fields'] as $label)<th>{{ $label }}</th>@endforeach</tr>
        @endif
        @foreach ($bodyData[$bodyKey] ?? [] as $index => $row)
            @php
                $item = strtoupper(trim((string) ($row['item'] ?? '')));
                $isSingleMeasurement = ! empty($section['measurement']) && in_array($item, ['TEST VOLTAGE', 'WINDING TEMP'], true);
                $isSpeed = ! empty($section['uncouple']) && $item === 'SPEED';
                $isBearingTemperature = ! empty($section['uncouple']) && $item === 'TEMP BEARING DE';
            @endphp
            @if (! empty($section['measurement']) && $index === 2)
                <tr>
                    <td class="center">3</td>
                    <td><strong>WAKTU</strong></td>
                    <td class="center">30 detik</td>
                    <td class="center">1 Menit</td>
                    <td class="center">10 Menit</td>
                    <td class="center"><strong>PI</strong></td>
                </tr>
            @endif
            <tr>
                <td class="center">{{ ! empty($section['measurement']) && $index >= 2 ? $index + 2 : $index + 1 }}</td>
                @if ($isSingleMeasurement)
                    <td>{{ $row['item'] ?? '' }}</td>
                    <td colspan="4">{{ $row['value'] ?? '' }}</td>
                @elseif ($isSpeed)
                    <td>{{ $row['item'] ?? '' }}</td>
                    <td colspan="3">{{ $row['value_1'] ?? '' }}</td>
                @elseif ($isBearingTemperature)
                    <td>{{ $row['item'] ?? '' }}</td>
                    <td>DE: {{ $row['value_1'] ?? '' }}</td>
                    <td colspan="2">NDE: {{ $row['value_2'] ?? '' }}</td>
                @else
                    @foreach ($section['fields'] as $field => $label)
                        <td>{{ $row[$field] ?? '' }}</td>
                    @endforeach
                @endif
            </tr>
        @endforeach
    </table>
    @if (in_array($bodyKey, ['electrical_stator_rows', 'electrical_rotor_rows'], true))
        <table style="margin-top: 1mm; font-weight: 700;"><tr><td>*Standar IR 1+MΩ/kV</td><td style="text-align: right;">*Standar PI &gt; 2</td></tr></table>
    @endif
@endforeach

@foreach ([
    'electrical_stator_rows' => ['title' => 'PENGUKURAN INSULATION RESISTANCE & POLARIZATION INDEX (STATOR)', 'fields' => ['item' => 'Parameter', 'value' => 'Nilai', 'second_30' => '30 detik', 'minute_1' => '1 Menit', 'minute_10' => '10 Menit', 'pi' => 'PI']],
    'electrical_rotor_rows' => ['title' => 'PENGUKURAN INSULATION RESISTANCE & POLARIZATION INDEX (ROTOR)', 'fields' => ['item' => 'Parameter', 'value' => 'Nilai', 'second_30' => '30 detik', 'minute_1' => '1 Menit', 'minute_10' => '10 Menit', 'pi' => 'PI']],
    'electrical_ovality_rows' => ['title' => 'PENGUKURAN OVALITY', 'fields' => ['ring' => 'Ring', 'tir' => 'TIR', 'standard' => 'Standar']],
    'electrical_installation_rows' => ['title' => 'CHECKLIST INSTALASI', 'fields' => ['activity' => 'Aktivitas', 'standard' => 'Standar', 'status' => 'OK/TIDAK', 'remark' => 'Keterangan / Remarks']],
    'electrical_uncouple_rows' => ['title' => 'UNCOUPLE TESTING', 'fields' => ['item' => 'Item', 'value_1' => 'Hasil 1', 'value_2' => 'Hasil 2', 'value_3' => 'Hasil 3']],
] as $bodyKey => $section)
    <div class="fixed-section-title {{ $loop->first ? '' : 'brics-section-gap' }}">{{ $section['title'] }}</div>
    <table class="data-table">
        <tr><th style="width: 6%;">No</th>@foreach ($section['fields'] as $label)<th>{{ $label }}</th>@endforeach</tr>
        @foreach ($bodyData[$bodyKey] ?? [] as $row)
            <tr>
                <td class="center">{{ $loop->iteration }}</td>
                @foreach ($section['fields'] as $field => $label)
                    <td>{{ $row[$field] ?? '' }}</td>
                @endforeach
            </tr>
        @endforeach
    </table>
    @if (in_array($bodyKey, ['electrical_stator_rows', 'electrical_rotor_rows'], true))
        <table style="margin-top: 1mm; font-weight: 700;"><tr><td>*Standar IR 1+MΩ/kV</td><td style="text-align: right;">*Standar PI &gt; 2</td></tr></table>
    @endif
@endforeach

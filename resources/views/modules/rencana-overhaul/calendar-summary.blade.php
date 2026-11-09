@php
    // Data dummy tampilan (frontend saja). Ganti dengan data dari controller saat backend siap.
    $firstYear = 2026;
    $years = range($firstYear, $firstYear + 9);
    // plan: hari rencana per tahun (10 tahun). actual: hari aktual per tahun (null = belum ada).
    $plants = [
        ['no' => 1, 'name' => 'TONASA 5', 'areas' => [
            ['KILN, COAL MILL, RAW MILL & CRUSHER', [18, 28, 16, 16, 22.5, 16, 16, 22.5, 16, 16], [21.95]],
            ['FINISH MILL 552', [15, 16, 16, 16, 22.5, 16, 16, 22.5, 16, 16], [22.34]],
            ['FINISH MILL 553', [15, 20, 16, 16, 22.5, 16, 16, 22.5, 16, 16], [16]],
        ]],
        ['no' => 2, 'name' => 'TONASA 4', 'areas' => [
            ['KILN, COAL MILL, RAW MILL & CRUSHER', [19, 28, 16, 16, 22.5, 16, 16, 22.5, 16, 16], [16]],
            ['FINISH MILL 419', [24, 19, 16, 16, 22.5, 16, 16, 22.5, 16, 16], [24.5]],
            ['FINISH MILL 420', [15, 25, 16, 16, 22.5, 16, 16, 22.5, 16, 16], [20]],
        ]],
        ['no' => 3, 'name' => 'TONASA 2', 'areas' => [
            ['FINISH MILL 2', [24, 16, 16, 16, 22.5, 16, 16, 22.5, 16, 16], [15]],
        ]],
        ['no' => 4, 'name' => 'TONASA 3', 'areas' => [
            ['FINISH MILL 3', [24, 16, 16, 16, 22.5, 16, 16, 22.5, 16, 16], [15]],
        ]],
        ['no' => 5, 'name' => 'POWER PLANT', 'areas' => [
            ['BTG C', [18, null, 16, null, 22.5, null, 16, null, 16, null], [21.95]],
            ['BTG D', [null, 28, null, 16, null, 16, null, 22.5, null, 16], []],
        ]],
    ];

    $fmt = static fn (float|int $value): string => rtrim(rtrim(number_format($value, 2, ',', ''), '0'), ',');

    // Ringkasan keseluruhan per tahun.
    $planTotals = array_fill(0, count($years), 0.0);
    $actualTotals = array_fill(0, count($years), 0.0);
    $areaCount = 0;
    foreach ($plants as $plant) {
        foreach ($plant['areas'] as [, $plan, $actual]) {
            $areaCount++;
            foreach ($years as $i => $y) {
                $planTotals[$i] += (float) ($plan[$i] ?? 0);
                $actualTotals[$i] += (float) ($actual[$i] ?? 0);
            }
        }
    }
@endphp

<div class="rop-heading">
    <h2>KALENDER OVERHAUL</h2>
    <div class="rop-export">
        <button type="button" class="rop-btn rop-btn--green">Export Excel</button>
        <button type="button" class="rop-btn rop-btn--pink">Download PDF</button>
    </div>
</div>

<div class="rop-summary" aria-label="Ringkasan keseluruhan">
    <div><span>Total Pabrik</span><strong>{{ count($plants) }}</strong></div>
    <div><span>Total Area</span><strong>{{ $areaCount }}</strong></div>
    <div><span>Plan {{ $years[0] }}</span><strong>{{ $fmt($planTotals[0]) }} <small>hari</small></strong></div>
    <div><span>Aktual {{ $years[0] }}</span><strong>{{ $fmt($actualTotals[0]) }} <small>hari</small></strong></div>
    <div><span>Selisih {{ $years[0] }}</span><strong>{{ $fmt($actualTotals[0] - $planTotals[0]) }} <small>hari</small></strong></div>
</div>

<div class="rop-gantt-card">
    <div class="rop-toolbar">
        <button type="button" class="rop-chip rop-chip--soft">Edit</button>
        <button type="button" class="rop-chip rop-chip--navy">Tambahkan</button>
        <span class="rop-range"><i class="bi bi-chevron-left"></i> {{ $years[0] }} - {{ end($years) }} <i class="bi bi-chevron-right"></i></span>
    </div>

    <div class="rop-gantt-scroll">
        <table class="rop-gantt rop-cal">
            <thead>
                <tr>
                    <th rowspan="2" class="c-no">No.</th>
                    <th rowspan="2" class="c-plant">PABRIK</th>
                    <th rowspan="2" class="c-area">AREA</th>
                    <th rowspan="2" class="c-pa">P/A</th>
                    <th colspan="{{ count($years) }}" class="rop-year-head">TAHUN</th>
                </tr>
                <tr>
                    @foreach ($years as $y)
                        <th class="rop-month">{{ $y }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($plants as $plant)
                    @foreach ($plant['areas'] as $areaIndex => [$areaName, $plan, $actual])
                        @foreach (['Plan', 'Actual'] as $type)
                            <tr>
                                @if ($areaIndex === 0 && $type === 'Plan')
                                    <td rowspan="{{ count($plant['areas']) * 2 }}" class="c-no">{{ $plant['no'] }}</td>
                                    <td rowspan="{{ count($plant['areas']) * 2 }}" class="c-plant">{{ $plant['name'] }}</td>
                                @endif
                                @if ($type === 'Plan')
                                    <td rowspan="2" class="c-area">{{ $areaName }}</td>
                                @endif
                                <td class="c-pa {{ $type === 'Plan' ? 'is-plan' : 'is-actual' }}">{{ $type }}</td>
                                @foreach ($years as $i => $y)
                                    @php($value = $type === 'Plan' ? ($plan[$i] ?? null) : ($actual[$i] ?? null))
                                    <td class="rop-val {{ $type === 'Plan' ? 'is-plan' : 'is-actual' }} {{ $value === null ? 'is-empty' : '' }}">
                                        @if ($value !== null)
                                            <b>{{ $fmt($value) }}</b> <small>HARI</small>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                @endforeach
            </tbody>
            <tfoot>
                <tr class="rop-total-row">
                    <th colspan="4" scope="row">TOTAL PLAN</th>
                    @foreach ($planTotals as $total)
                        <td><b>{{ $fmt($total) }}</b> <small>HARI</small></td>
                    @endforeach
                </tr>
                <tr class="rop-total-row">
                    <th colspan="4" scope="row">TOTAL ACTUAL</th>
                    @foreach ($actualTotals as $total)
                        <td>@if ($total > 0)<b>{{ $fmt($total) }}</b> <small>HARI</small>@endif</td>
                    @endforeach
                </tr>
            </tfoot>
        </table>
    </div>
</div>

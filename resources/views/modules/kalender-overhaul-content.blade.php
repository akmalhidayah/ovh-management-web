@php
    // Data dummy tampilan (frontend saja). Ganti dengan data dari controller saat backend siap.
    $year = 2026;
    $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $weeksPerMonth = 4;
    // start = indeks minggu (0-47), length = jumlah minggu, days = total hari.
    $plants = [
        ['no' => 1, 'name' => 'TONASA 5', 'areas' => [
            ['name' => 'KILN, COAL MILL, RAW MILL & CRUSHER', 'start' => 10, 'length' => 7, 'days' => 28],
            ['name' => 'FINISH MILL 552', 'start' => 10, 'length' => 4, 'days' => 16],
            ['name' => 'FINISH MILL 553', 'start' => 10, 'length' => 5, 'days' => 20],
        ]],
        ['no' => 2, 'name' => 'TONASA 4', 'areas' => [
            ['name' => 'KILN, COAL MILL, RAW MILL & CRUSHER', 'start' => 20, 'length' => 7, 'days' => 28],
            ['name' => 'FINISH MILL 419', 'start' => 20, 'length' => 4, 'days' => 19],
            ['name' => 'FINISH MILL 420', 'start' => 22, 'length' => 6, 'days' => 25],
        ]],
        ['no' => 3, 'name' => 'TONASA 2', 'areas' => [
            ['name' => 'FINISH MILL 2', 'start' => 26, 'length' => 4, 'days' => 16],
        ]],
        ['no' => 4, 'name' => 'TONASA 3', 'areas' => [
            ['name' => 'FINISH MILL 3', 'start' => 5, 'length' => 3, 'days' => 16],
        ]],
        ['no' => 5, 'name' => 'POWER PLANT', 'areas' => [
            ['name' => 'BTG C', 'start' => 10, 'length' => 7, 'days' => 28],
            ['name' => 'BTG D', 'start' => 20, 'length' => 7, 'days' => 28],
        ]],
    ];
    $programs = [
        'Penggantian BTA 86 Meter',
        'Penggantian lifering/tyre pendasi 2',
        'Penggantian ducting TAD',
        'Penggantian bag filter coal mill',
        'Pengelasan tabal',
        'Penggantian grinding table RM 5',
        'Penggantian Grinding Roller RM 5',
        'Penggantian Hammer Chrusher',
    ];
    $notes = [
        'Jadwal overhaul Finish Mill 2 dimajukan menyesuaikan RKAP Produksi',
        'Overhaul Tonasa 4 (pemotongan shell kiln) tetap direncanakan tahun 2027, agar dilakukan revisi hari overhaul.',
        'Perbaikan pondasi maindrive utara dan selatan dibatalkan, jika dilakukan perbaikan hari overhaul Tonasa 4 akan melebihi 28 hari.',
    ];
    $tabs = ['overhaul' => 'Overhaul', 'barang' => 'Barang', 'jasa' => 'Jasa', 'kalender' => 'Kalender'];
@endphp

@push('styles')
    <link href="{{ asset('assets/css/rencana-overhaul.css') }}?v={{ file_exists(public_path('assets/css/rencana-overhaul.css')) ? filemtime(public_path('assets/css/rencana-overhaul.css')) : time() }}" rel="stylesheet">
@endpush

<div class="rop" data-rop>
    <div class="rop-tabs" role="tablist" aria-label="Rencana overhaul dan pengadaan">
        @foreach ($tabs as $key => $label)
            <button type="button" role="tab" id="rop-tab-{{ $key }}" class="rop-tab {{ $loop->first ? 'is-active' : '' }}"
                    aria-selected="{{ $loop->first ? 'true' : 'false' }}" aria-controls="rop-panel-{{ $key }}" data-rop-tab="{{ $key }}">{{ $label }}</button>
        @endforeach
    </div>

    <div class="rop-yearbar">
        <label class="rop-year">
            <span>Tahun</span>
            <select aria-label="Tahun">
                @foreach ([2026, 2025, 2024] as $y)
                    <option @selected($y === $year)>{{ $y }}</option>
                @endforeach
            </select>
        </label>
        <button type="button" class="rop-btn rop-btn--navy">Buat Baru</button>
    </div>

    {{-- Tab Overhaul --}}
    <div role="tabpanel" id="rop-panel-overhaul" aria-labelledby="rop-tab-overhaul" data-rop-panel="overhaul" class="rop-panel">
        <div class="rop-heading">
            <h2>RENCANA DAN TIMELINE OVERHAUL<br>PT SEMEN TONASA</h2>
            <div class="rop-export">
                <button type="button" class="rop-btn rop-btn--green">Export Excel</button>
                <button type="button" class="rop-btn rop-btn--pink">Download PDF</button>
            </div>
        </div>

        <div class="rop-gantt-card">
            <div class="rop-toolbar">
                <span class="rop-chip">Perubahan <b>0</b></span>
                <button type="button" class="rop-chip rop-chip--teal">Edit Data Gantt</button>
                <button type="button" class="rop-chip rop-chip--navy">+ Tambahkan</button>
                <span class="rop-range"><i class="bi bi-chevron-left"></i> Januari - Desember {{ $year }} <i class="bi bi-chevron-right"></i></span>
                <span class="rop-view"><button type="button" class="is-on">Minggu</button><button type="button">Bulan</button></span>
            </div>

            <div class="rop-gantt-scroll">
                <table class="rop-gantt">
                    <thead>
                        <tr>
                            <th rowspan="3" class="c-no">No.</th>
                            <th rowspan="3" class="c-plant">Pabrik</th>
                            <th rowspan="3" class="c-area">Area</th>
                            <th rowspan="3" class="c-pa">P/A</th>
                            <th colspan="{{ count($months) * $weeksPerMonth }}" class="rop-year-head">{{ $year }}</th>
                            <th rowspan="3" class="c-total">Total Hari</th>
                            <th rowspan="3" class="c-note">Note</th>
                        </tr>
                        <tr>
                            @foreach ($months as $month)
                                <th colspan="{{ $weeksPerMonth }}" class="rop-month">{{ strtoupper($month) }}</th>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach ($months as $month)
                                @for ($w = 1; $w <= $weeksPerMonth; $w++)
                                    <th class="rop-week">M{{ $w }}</th>
                                @endfor
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($plants as $plant)
                            @foreach ($plant['areas'] as $areaIndex => $area)
                                @foreach (['P', 'A'] as $type)
                                    <tr>
                                        @if ($areaIndex === 0 && $type === 'P')
                                            <td rowspan="{{ count($plant['areas']) * 2 }}" class="c-no">{{ $plant['no'] }}</td>
                                            <td rowspan="{{ count($plant['areas']) * 2 }}" class="c-plant">{{ $plant['name'] }}</td>
                                        @endif
                                        @if ($type === 'P')
                                            <td rowspan="2" class="c-area">{{ $area['name'] }}</td>
                                        @endif
                                        <td class="c-pa {{ $type === 'P' ? 'is-plan' : 'is-actual' }}">{{ $type }}</td>
                                        @for ($i = 0; $i < count($months) * $weeksPerMonth; $i++)
                                            @if ($type === 'P' && $i === $area['start'])
                                                <td colspan="{{ $area['length'] }}" class="rop-bar-cell"><span class="rop-bar"></span></td>
                                                @php($i += $area['length'] - 1)
                                            @else
                                                <td class="rop-cell {{ $i % $weeksPerMonth === 0 ? 'is-month-start' : '' }}"></td>
                                            @endif
                                        @endfor
                                        @if ($type === 'P')
                                            <td rowspan="2" class="c-total">{{ $area['days'] }}</td>
                                            <td rowspan="2" class="c-note"></td>
                                        @endif
                                    </tr>
                                @endforeach
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rop-bottom">
            <section class="rop-program">
                <h3>PROGRAM KERJA</h3>
                <div class="rop-program-head"><span>Pabrik</span><span>Area</span><span>Program Kerja</span></div>
                <div class="rop-program-body">
                    <span class="rop-program-plant">Tonasa 5</span>
                    <ul class="rop-program-area"><li>Kiln</li><li>Coal Mill</li><li>Raw Mill</li><li>Crusher</li></ul>
                    <ol class="rop-program-list">
                        @foreach ($programs as $program)
                            <li>{{ $program }}</li>
                        @endforeach
                    </ol>
                </div>
                <a href="#" class="rop-detail">Detail</a>
            </section>

            <section class="rop-notes">
                <h3>CATATAN PENTING</h3>
                <ul>
                    @foreach ($notes as $note)
                        <li>{{ $note }}</li>
                    @endforeach
                </ul>
                <a href="#" class="rop-detail">Detail</a>
            </section>
        </div>
    </div>

    {{-- Tab Barang & Jasa: timeline pengadaan --}}
    @foreach (['barang', 'jasa'] as $kind)
        <div role="tabpanel" id="rop-panel-{{ $kind }}" aria-labelledby="rop-tab-{{ $kind }}" data-rop-panel="{{ $kind }}" class="rop-panel" hidden>
            @include('modules.rencana-overhaul.procurement-timeline', ['kind' => $kind])
        </div>
    @endforeach

    {{-- Tab Kalender: ringkasan multi-tahun --}}
    <div role="tabpanel" id="rop-panel-kalender" aria-labelledby="rop-tab-kalender" data-rop-panel="kalender" class="rop-panel" hidden>
        @include('modules.rencana-overhaul.calendar-summary')
    </div>
</div>

@push('scripts')
    <script>
        (function () {
            document.querySelectorAll('[data-rop]').forEach(function (root) {
                var tabs = root.querySelectorAll('[data-rop-tab]');
                var panels = root.querySelectorAll('[data-rop-panel]');
                tabs.forEach(function (tab) {
                    tab.addEventListener('click', function () {
                        tabs.forEach(function (t) {
                            var on = t === tab;
                            t.classList.toggle('is-active', on);
                            t.setAttribute('aria-selected', on ? 'true' : 'false');
                        });
                        panels.forEach(function (p) {
                            p.hidden = p.getAttribute('data-rop-panel') !== tab.getAttribute('data-rop-tab');
                        });
                    });
                });
            });
        })();
    </script>
@endpush

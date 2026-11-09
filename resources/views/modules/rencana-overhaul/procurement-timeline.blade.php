@php
    // Data dummy tampilan (frontend saja). Ganti dengan data dari controller saat backend siap.
    // Format baris: [deskripsi, PIC, mulai rencana, panjang rencana, mulai aktual, panjang aktual, total hari]
    // Mulai = indeks bulan sejak Jan 2026 (0-18: Jan 2026 - Jul 2027), panjang = jumlah bulan.
    $startYear = 2026;
    $monthCount = 19;
    $shortMonths = ['JAN', 'FEB', 'MAR', 'APR', 'MEI', 'JUN', 'JUL', 'AGU', 'SEP', 'OKT', 'NOV', 'DES'];
    $labelMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    $datasets = [
        'jasa' => [
            'title' => 'RENCANA DAN TIMELINE PENGADAAN JASA',
            'rows' => [
                ['RAPAT KOORDINASI RENCANA PENGADAAN BARANG OVERHAUL & KIRIM FORM AJUAN', 'OHH', 5, 1, 5, 1, 1],
                ['BUAT LIST AJUAN, NOTIFIKASI, ORDER & KELENGKAPAN DOKUMEN (DKO, RFI & DLL)', 'VISER & PNS', 5, 2, 5, 2, 7],
                ['REKAP, EVALUASI AJUAN DAN BA', 'OHH', 6, 2, 7, 2, 29],
                ['BUAT DOKUMEN, REVIEW, DAN PERSETUJUAN DOKUMEN TENDER', 'PNS & USD1', 7, 3, 8, 3, 22],
                ['KIRIM DOKUMEN TENDER KE PENGADAAN', 'SPO', 8, 2, 8, 2, 16],
                ['PROSES PENGADAAN (TENDER - PENGUMUMAN PEMENANG)', 'PENGADAAN', 9, 4, 11, 4, 90],
                ['KONTRAK & APPROVAL', 'SPO', 11, 3, 12, 3, 15],
                ['BAP / PURCHASE ORDER', 'VISER & PNS', 12, 1, 12, 4, 5],
                ['BUAT HPP & PERSETUJUAN (KONTRAK)', 'VISER & PNS', 13, 2, 13, 2, 39],
                ['BUAT PO & KONFIRMASI VENDOR', 'SPO', 15, 2, 15, 2, 62],
                ['KICK OFF MEETING VENDOR', 'OHH', 16, 1, 16, 2, 122],
                ['CHECK UP', 'VENDOR', 17, 1, 17, 1, 180],
                ['SAFETY INDUCTION', 'VENDOR & K3', 18, 1, 16, 2, 240],
                ['SAFETY INDUCTION', 'VENDOR & K3', 18, 1, 17, 1, 150],
            ],
        ],
        'barang' => [
            'title' => 'RENCANA DAN TIMELINE PENGADAAN BARANG',
            'rows' => [
                ['RAPAT KOORDINASI RENCANA PENGADAAN BARANG OVERHAUL & KIRIM FORM AJUAN', 'OHH', 1, 1, 1, 1, 1],
                ['BUAT LIST AJUAN, NOTIFIKASI, ORDER & KELENGKAPAN DOKUMEN (DKO, RFI & DLL)', 'VISER & PNS', 1, 2, 1, 2, 12],
                ['REKAP, EVALUASI AJUAN DAN BA', 'OHH', 3, 2, 3, 2, 25],
                ['BUAT PR & PERSETUJUAN PR', 'PNS & USD1', 4, 2, 5, 2, 20],
                ['PROSES PENGADAAN (TENDER - PENGUMUMAN PEMENANG)', 'PENGADAAN', 6, 4, 7, 4, 85],
                ['KONTRAK & APPROVAL', 'SPO', 9, 2, 10, 2, 14],
                ['BUAT PO & KONFIRMASI VENDOR', 'SPO', 10, 2, 11, 2, 30],
                ['PRODUKSI / PENGIRIMAN BARANG', 'VENDOR', 11, 5, 12, 5, 150],
                ['PENERIMAAN & INSPEKSI BARANG (GR)', 'GUDANG & QC', 15, 2, 16, 2, 35],
            ],
        ],
    ];

    $dataset = $datasets[$kind];
    $range = static function (int $start, int $length) use ($labelMonths, $startYear): string {
        $end = $start + $length - 1;
        $endDay = \Carbon\Carbon::create($startYear + intdiv($end, 12), ($end % 12) + 1, 1)->endOfMonth()->day;

        return sprintf('01 %s %d - %d %s %d', $labelMonths[$start % 12], $startYear + intdiv($start, 12), $endDay, $labelMonths[$end % 12], $startYear + intdiv($end, 12));
    };
@endphp

<div class="rop-heading">
    <h2>{{ $dataset['title'] }}</h2>
    <div class="rop-export">
        <button type="button" class="rop-btn rop-btn--green">Export Excel</button>
        <button type="button" class="rop-btn rop-btn--pink">Download PDF</button>
    </div>
</div>

<div class="rop-gantt-card">
    <div class="rop-toolbar">
        <button type="button" class="rop-chip rop-chip--teal">Edit Data Gantt</button>
        <button type="button" class="rop-chip rop-chip--navy">+ Tambahkan</button>
        <span class="rop-range"><i class="bi bi-chevron-left"></i> Januari {{ $startYear }} - Juli {{ $startYear + 1 }} <i class="bi bi-chevron-right"></i></span>
        <span class="rop-view"><button type="button" class="is-on">Minggu</button><button type="button">Bulan</button></span>
    </div>

    <div class="rop-gantt-scroll">
        <table class="rop-gantt rop-proc">
            <thead>
                <tr>
                    <th rowspan="2" class="c-no">No.</th>
                    <th rowspan="2" class="c-desc">DESKRIPSI</th>
                    <th rowspan="2" class="c-pic">PIC</th>
                    <th rowspan="2" class="c-pa">P/A</th>
                    <th colspan="12" class="rop-year-head">{{ $startYear }}</th>
                    <th colspan="{{ $monthCount - 12 }}" class="rop-year-head">{{ $startYear + 1 }}</th>
                    <th rowspan="2" class="c-total">TOTAL HARI</th>
                    <th rowspan="2" class="c-note">NOTE</th>
                </tr>
                <tr>
                    @for ($m = 0; $m < $monthCount; $m++)
                        <th class="rop-month rop-month--sm">{{ $shortMonths[$m % 12] }}</th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @foreach ($dataset['rows'] as $index => [$desc, $pic, $planStart, $planLen, $actStart, $actLen, $days])
                    @foreach (['P', 'A'] as $type)
                        <tr>
                            @if ($type === 'P')
                                <td rowspan="2" class="c-no">{{ $index + 1 }}</td>
                                <td rowspan="2" class="c-desc">{{ $desc }}</td>
                                <td rowspan="2" class="c-pic">{{ $pic }}</td>
                            @endif
                            <td class="c-pa {{ $type === 'P' ? 'is-plan' : 'is-actual' }}">{{ $type }}</td>
                            @php($barStart = $type === 'P' ? $planStart : $actStart)
                            @php($barLen = $type === 'P' ? $planLen : $actLen)
                            @for ($m = 0; $m < $monthCount; $m++)
                                @if ($m === $barStart)
                                    <td colspan="{{ $barLen }}" class="rop-bar-cell">
                                        @if ($type === 'P')
                                            <span class="rop-bar"></span>
                                        @else
                                            <span class="rop-bar rop-bar--light"><em>{{ $range($actStart, $actLen) }}</em></span>
                                        @endif
                                    </td>
                                    @php($m += $barLen - 1)
                                @else
                                    <td class="rop-cell {{ $m % 12 === 0 ? 'is-month-start' : '' }}"></td>
                                @endif
                            @endfor
                            @if ($type === 'P')
                                <td rowspan="2" class="c-total is-days">{{ $days }}</td>
                                <td rowspan="2" class="c-note"></td>
                            @endif
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>

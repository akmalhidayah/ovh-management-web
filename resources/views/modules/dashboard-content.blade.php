@php
    // Data dummy tampilan (frontend saja). Ganti dengan data dari controller saat backend siap.
    $plants = ['Tonasa 2', 'Tonasa 3', 'Tonasa 4', 'Tonasa 5', 'Power Plant'];
    $years = [2026, 2025, 2024];
    $budgets = [
        ['title' => 'Barang', 'percent' => '2%', 'rows' => [['Anggaran Disetujui', '27 M'], ['Realisasi PO', '10 M'], ['Realisasi GI', '2 M']]],
        ['title' => 'Jasa', 'percent' => '90%', 'rows' => [['Anggaran Disetujui', '25 M'], ['Realisasi PO', '24 M'], ['Selesai', '23 M']]],
        ['title' => 'Capex', 'percent' => '45%', 'rows' => [['Anggaran Disetujui', '106 M'], ['Realisasi PO', '104 M'], ['Realisasi GI', '43 M']]],
    ];
    $procurement = [
        ['Tonasa 2', '30%', '30%', '30%'], ['Tonasa 3', '30%', '30%', '30%'], ['Tonasa 4', '30%', '30%', '30%'],
        ['Tonasa 5', '30%', '30%', '30%'], ['Power Plant', '30%', '30%', '30%'],
    ];
    $readiness = [
        ['Tonasa 2', '30%', '30%', '30%'], ['Tonasa 3', '30%', '30%', '30%'], ['Tonasa 4', '30%', '30%', '30%'],
        ['Tonasa 5', '30%', '30%', '30%'], ['Power Plant', '30%', '30%', '30%'],
    ];
    $upcoming = [
        ['Tonasa 5', '03-04/2026', '-280'], ['Tonasa 4', '05-06/2026', '-280'],
        ['Tonasa 3', '07/2026', '-280'], ['Power Plant', '05-06/2026', '-280'],
    ];
    $news = [
        ['29/10/2026', 'Jadwal overhaul diubah sesuai dengan hasil rapat direktorat'],
        ['20/10/2026', 'Jadwal dan hari overhaul telah disetujui oleh management'],
        ['20/10/2026', 'Rencana Anggaran Overhaul 2027 perlu dilakukan revisi dan persetujuan kembali sesuai dengan rencana biaya pemeliharaan yang terlah disetujui'],
    ];
@endphp

@push('styles')
    <link href="{{ asset('assets/css/admin-dashboard.css') }}?v={{ file_exists(public_path('assets/css/admin-dashboard.css')) ? filemtime(public_path('assets/css/admin-dashboard.css')) : time() }}" rel="stylesheet">
@endpush

<div class="adash">
    <section class="adash-hero" style="--adash-hero-image: url('{{ asset('assets/images/illustrations/bg-login.jpg') }}')">
        <h1>Selamat Datang</h1>
    </section>

    <form class="adash-filter" onsubmit="return false">
        <div class="adash-filter-fields">
            <label class="adash-field">
                <span>Plant</span>
                <select aria-label="Plant">
                    @foreach ($plants as $plant)
                        <option @selected($plant === 'Tonasa 5')>{{ $plant }}</option>
                    @endforeach
                </select>
            </label>
            <label class="adash-field">
                <span>Tahun</span>
                <select aria-label="Tahun">
                    @foreach ($years as $year)
                        <option>{{ $year }}</option>
                    @endforeach
                </select>
            </label>
        </div>
        <div class="adash-filter-actions">
            <button type="button" class="adash-refresh" aria-label="Muat ulang"><i class="bi bi-arrow-repeat"></i></button>
            <button type="button" class="adash-btn">Perbarui</button>
        </div>
    </form>

    <h2 class="adash-section-title">ANGGARAN</h2>
    <div class="adash-budgets">
        @foreach ($budgets as $budget)
            <article class="adash-budget">
                <h3>{{ strtoupper($budget['title']) }}</h3>
                <div class="adash-budget-body">
                    <dl class="adash-budget-rows">
                        @foreach ($budget['rows'] as [$label, $value])
                            <div class="adash-budget-row">
                                <dt>{{ $label }}</dt>
                                <dd>{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                    <div class="adash-budget-percent">{{ $budget['percent'] }}</div>
                </div>
            </article>
        @endforeach
    </div>

    <div class="adash-grid">
        <section class="adash-card adash-nearest">
            <h3 class="adash-card-title">STATUS OVERHAUL TERDEKAT</h3>
            <p class="adash-nearest-name">OVERHAUL TONASA 2</p>
            <div class="adash-countdown">
                <span class="adash-countdown-label">Hitungan Mundur</span>
                <span class="adash-countdown-value"><strong>180</strong> Hari</span>
            </div>
        </section>

        <section class="adash-card">
            <h3 class="adash-card-title adash-card-title--sm">PROGRES PENGADAAN</h3>
            <div class="adash-table-wrap">
                <table class="adash-table">
                    <thead><tr><th scope="col">Pabrik</th><th scope="col">Barang</th><th scope="col">Jasa</th><th scope="col">Capex</th></tr></thead>
                    <tbody>
                        @foreach ($procurement as [$plant, $barang, $jasa, $capex])
                            <tr><th scope="row">{{ $plant }}</th><td>{{ $barang }}</td><td>{{ $jasa }}</td><td class="is-warn">{{ $capex }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="adash-card">
            <h3 class="adash-card-title adash-card-title--sm">KESIAPAN</h3>
            <div class="adash-table-wrap">
                <table class="adash-table">
                    <thead><tr><th scope="col">Pabrik</th><th scope="col">Barang</th><th scope="col">Jasa</th><th scope="col">Capex</th></tr></thead>
                    <tbody>
                        @foreach ($readiness as [$plant, $barang, $jasa, $capex])
                            <tr><th scope="row">{{ $plant }}</th><td>{{ $barang }}</td><td>{{ $jasa }}</td><td class="is-warn">{{ $capex }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="adash-card adash-next">
            <h3 class="adash-card-title adash-card-title--sm">OVERHAUL BERIKUTNYA</h3>
            <div class="adash-next-head"><span>Pabrik</span><span>Jadwal</span><span>Hari</span><span>Status</span></div>
            <ul class="adash-next-list">
                @foreach ($upcoming as [$plant, $schedule, $days])
                    <li>
                        <strong>{{ $plant }}</strong>
                        <span>{{ $schedule }}</span>
                        <span class="is-warn">{{ $days }}</span>
                        <span class="adash-pill">Menunggu</span>
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="adash-info">
            <h3>INFORMASI</h3>
            <ul>
                @foreach ($news as [$date, $text])
                    <li><time>{{ $date }}</time><p>{{ $text }}</p></li>
                @endforeach
            </ul>
        </section>
    </div>
</div>

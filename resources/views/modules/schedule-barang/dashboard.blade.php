<div class="sb-dash">
    <div class="sb-dash-top">
        <h2 class="sb-heading">ANGGARAN</h2>
        <div class="sb-kpis sb-kpis--navy">
            @foreach ($sb['budget'] as $card)
                <article class="sb-kpi">
                    <p class="sb-kpi-label">{{ $card['label'] }}</p>
                    <span class="sb-kpi-check" aria-hidden="true"><i class="bi bi-check-lg"></i></span>
                    <p class="sb-kpi-value"><small>Rp</small><strong>{{ $card['value'] }}</strong></p>
                    <span class="sb-kpi-unit">{{ $card['unit'] }}</span>
                </article>
            @endforeach
        </div>

        <h2 class="sb-heading">ITEM AJUAN</h2>
        <div class="sb-kpis sb-kpis--teal">
            @foreach ($sb['items'] as $card)
                <article class="sb-kpi">
                    <p class="sb-kpi-label">{{ $card['label'] }}</p>
                    <span class="sb-kpi-check" aria-hidden="true"><i class="bi {{ $card['icon'] === 'x' ? 'bi-x-lg' : 'bi-check-lg' }}"></i></span>
                    <p class="sb-kpi-value"><strong>{{ $card['value'] }}</strong></p>
                    <span class="sb-kpi-unit">Item</span>
                </article>
            @endforeach
        </div>

        <aside class="sb-donut-card" aria-label="Persentase anggaran disetujui">
            <header><h3>PERSENTASE ANGGARAN DISETUJUI</h3><i class="bi bi-graph-up-arrow" aria-hidden="true"></i></header>
            <div class="sb-donut" style="--p: {{ $sb['approvedPercent'] }}" role="img" aria-label="{{ $sb['approvedPercent'] }} persen">
                <span>{{ $sb['approvedPercent'] }}<small>%</small></span>
            </div>
        </aside>
    </div>

    <div class="sb-dash-bottom">
        <section class="sb-card sb-plants" aria-labelledby="sb-plants-title">
            <h3 id="sb-plants-title" class="sb-card-title">AJUAN TIAP PABRIK</h3>
            <ul>
                @foreach ($sb['plantTotals'] as $item)
                    <li data-sb-plant-row="{{ $item['plant'] }}">
                        <strong>{{ strtoupper($item['plant']) }}</strong>
                        <span class="sb-plants-value">{{ $item['value'] }} <small>M</small></span>
                        <span class="sb-chip"><i class="bi bi-caret-down-fill" aria-hidden="true"></i> {{ $item['change'] }}</span>
                    </li>
                @endforeach
            </ul>
            <p class="sb-plants-total"><span>TOTAL AJUAN</span><strong>{{ $sb['plantTotalAll'] }} M</strong></p>
        </section>

        <section class="sb-card sb-recap" aria-labelledby="sb-recap-title" data-sb-recap>
            <header class="sb-card-head">
                <h3 id="sb-recap-title" class="sb-card-title">REKAP AJUAN UNIT KERJA</h3>
                <button type="button" class="sb-link" data-sb-recap-all>Lihat Semua <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
            </header>
            <div class="sb-scroll">
                <table class="sb-table sb-table--recap">
                    <thead><tr><th>NO</th><th>UNIT KERJA</th><th>PLANT</th><th>PAGU</th><th>AJUAN</th><th>SERAPAN</th></tr></thead>
                    <tbody>
                        @foreach ($sb['unitRecap'] as $row)
                            <tr data-sb-recap-row data-plant="{{ $row['plant'] }}" data-text="{{ strtolower($row['unit'].' '.$row['plant']) }}">
                                <td>{{ $row['no'] }}</td>
                                <td class="sb-unit">{{ $row['unit'] }}</td>
                                <td><span class="sb-plant-tag">{{ $row['plant'] }}</span></td>
                                <td>{{ $row['pagu'] }}</td>
                                <td>{{ $row['ajuan'] }}</td>
                                <td><span class="sb-pill {{ $row['over'] ? 'is-danger' : 'is-ok' }}">{{ $row['percent'] }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="sb-empty" data-sb-recap-empty hidden>Tidak ada data yang cocok dengan filter.</p>
            <footer class="sb-foot">
                <span data-sb-recap-summary aria-live="polite"></span>
                <nav class="sb-pager" data-sb-recap-pager aria-label="Pagination rekap ajuan unit kerja"></nav>
            </footer>
        </section>

        <section class="sb-card sb-notes" aria-labelledby="sb-notes-title" data-sb-notes>
            <h3 id="sb-notes-title" class="sb-card-title">CATATAN</h3>
            <ul>
                @foreach ($sb['remarks'] as $i => $remark)
                    <li data-sb-note-item @if ($i >= 5) hidden data-extra @endif>
                        <div>
                            <p>{{ $remark['text'] }}</p>
                            <p class="sb-note-detail" hidden>{{ $remark['detail'] }}</p>
                            <button type="button" class="sb-readmore" aria-expanded="false" data-sb-readmore>Read More <i class="bi bi-caret-right-fill" aria-hidden="true"></i></button>
                        </div>
                        <span class="sb-badge {{ $remark['status'] === 'OK' ? 'is-ok' : 'is-cancel' }}">{{ $remark['status'] }}</span>
                    </li>
                @endforeach
            </ul>
            <footer class="sb-foot">
                <button type="button" class="sb-link" data-sb-notes-toggle data-total="{{ count($sb['remarks']) }}">Menampilkan 5 dari {{ count($sb['remarks']) }} catatan evaluasi</button>
            </footer>
        </section>
    </div>
</div>

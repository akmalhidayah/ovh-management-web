<div class="sj-dash">
    <div class="sj-dash-top">
        <div class="sj-dash-left">
            <h2 class="sj-heading">ANGGARAN</h2>
            <div class="sj-kpis sj-kpis--budget">
                @foreach ($sj['budget'] as $card)
                    <article class="sj-kpi">
                        <p class="sj-kpi-label">{{ $card['label'] }}<span class="sj-kpi-rp">Rp</span></p>
                        <span class="sj-kpi-icon {{ ($card['round'] ?? false) ? 'is-round' : '' }}" aria-hidden="true"><i class="bi {{ $card['icon'] }}"></i></span>
                        <p class="sj-kpi-value"><strong>{{ $card['value'] }}</strong></p>
                        <span class="sj-kpi-unit">{{ $card['unit'] }}</span>
                    </article>
                @endforeach
            </div>

            <h2 class="sj-heading">ITEM AJUAN</h2>
            <div class="sj-kpis sj-kpis--items">
                @foreach ($sj['items'] as $card)
                    <article class="sj-kpi">
                        <p class="sj-kpi-label">{{ $card['label'] }}</p>
                        <span class="sj-kpi-icon {{ ($card['round'] ?? false) ? 'is-round' : '' }}" aria-hidden="true"><i class="bi {{ $card['icon'] }}"></i></span>
                        <p class="sj-kpi-value"><strong>{{ $card['value'] }}</strong></p>
                        <span class="sj-kpi-unit">Item</span>
                    </article>
                @endforeach
            </div>
        </div>

        <aside class="sj-donut-card" aria-label="Realisasi biaya">
            <header><h3>REALISASI BIAYA</h3><i class="bi bi-graph-up-arrow" aria-hidden="true"></i></header>
            <div class="sj-donut" style="--p: {{ $sj['costPercent'] }}" role="img" aria-label="{{ $sj['costPercent'] }} persen">
                <span>{{ $sj['costPercent'] }}<small>%</small></span>
            </div>
        </aside>
    </div>

    <div class="sj-dash-bottom">
        <section class="sj-card sj-plants" aria-labelledby="sj-plants-title">
            <h3 id="sj-plants-title" class="sj-card-title">AJUAN PLANT</h3>
            <ul>
                @foreach ($sj['plantTotals'] as $item)
                    <li data-sj-plant-row="{{ $item['plant'] }}">
                        <strong>{{ strtoupper($item['plant']) }}</strong>
                        <span class="sj-plants-value">{{ $item['value'] }} <small>M</small></span>
                        <span class="sj-chip"><i class="bi bi-caret-down-fill" aria-hidden="true"></i> {{ $item['change'] }}</span>
                    </li>
                @endforeach
            </ul>
            <p class="sj-plants-total"><span>TOTAL AJUAN</span><strong>{{ $sj['plantTotalAll'] }} M</strong></p>
        </section>

        <section class="sj-card sj-recap" aria-labelledby="sj-recap-title" data-sj-recap>
            <header class="sj-card-head">
                <h3 id="sj-recap-title" class="sj-card-title">RENCANA KERJA JASA</h3>
                <button type="button" class="sj-link" data-sj-recap-all>Lihat Semua <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
            </header>
            <div class="sj-scroll">
                <table class="sj-table sj-table--recap">
                    <thead><tr><th>NO</th><th>UNIT KERJA</th><th>PLANT</th><th>PAGU</th><th>AJUAN</th><th>SERAPAN</th></tr></thead>
                    <tbody>
                        @foreach ($sj['unitRecap'] as $row)
                            <tr data-sj-recap-row data-plant="{{ $row['plant'] }}" data-text="{{ strtolower($row['unit'].' '.$row['plant']) }}">
                                <td>{{ $row['no'] }}</td>
                                <td class="sj-unit">{{ $row['unit'] }}</td>
                                <td><span class="sj-plant-tag">{{ $row['plant'] }}</span></td>
                                <td>{{ $row['pagu'] }}</td>
                                <td>{{ $row['ajuan'] }}</td>
                                <td><span class="sj-pill {{ $row['over'] ? 'is-danger' : 'is-ok' }}">{{ $row['percent'] }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="sj-empty" data-sj-recap-empty hidden>Tidak ada data yang cocok dengan filter.</p>
            <footer class="sj-foot">
                <span data-sj-recap-summary aria-live="polite"></span>
                <nav class="sj-pager" data-sj-recap-pager aria-label="Pagination rencana kerja jasa"></nav>
            </footer>
        </section>

        <section class="sj-card sj-notes" aria-labelledby="sj-notes-title" data-sj-notes>
            <h3 id="sj-notes-title" class="sj-card-title">REMARKS</h3>
            <ul>
                @foreach ($sj['remarks'] as $i => $remark)
                    <li data-sj-note-item @if ($i >= 5) hidden data-extra @endif>
                        <div>
                            <p>{{ $remark['text'] }}</p>
                            <p class="sj-note-detail" hidden>{{ $remark['detail'] }}</p>
                            <button type="button" class="sj-readmore" aria-expanded="false" data-sj-readmore>Read More <i class="bi bi-caret-right-fill" aria-hidden="true"></i></button>
                        </div>
                        <span class="sj-badge {{ $remark['status'] === 'OK' ? 'is-ok' : 'is-cancel' }}">{{ $remark['status'] }}</span>
                    </li>
                @endforeach
            </ul>
            <footer class="sj-foot">
                <button type="button" class="sj-link" data-sj-notes-toggle data-total="{{ count($sj['remarks']) }}">Menampilkan 5 dari {{ count($sj['remarks']) }} catatan evaluasi</button>
            </footer>
        </section>
    </div>
</div>

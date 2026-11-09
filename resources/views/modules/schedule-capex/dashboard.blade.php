<div class="sc-dash">
    <div class="sc-dash-top">
        <h2 class="sc-heading">ANGGARAN</h2>
        <div class="sc-kpis sc-kpis--navy">
            @foreach ($sc['budget'] as $card)
                <article class="sc-kpi">
                    <p class="sc-kpi-label">{{ $card['label'] }}<span class="sc-kpi-rp">Rp</span></p>
                    @if (!empty($card['icon']))
                        <span class="sc-kpi-check" aria-hidden="true"><i class="bi {{ $card['icon'] }}"></i></span>
                    @endif
                    <p class="sc-kpi-value"><strong>{{ $card['value'] }}</strong></p>
                    <span class="sc-kpi-unit">{{ $card['unit'] }}</span>
                </article>
            @endforeach
        </div>

        <h2 class="sc-heading">ITEM AJUAN</h2>
        <div class="sc-kpis sc-kpis--teal">
            @foreach ($sc['items'] as $card)
                <article class="sc-kpi">
                    <p class="sc-kpi-label">{{ $card['label'] }}</p>
                    <span class="sc-kpi-check" aria-hidden="true"><i class="bi {{ $card['icon'] }}"></i></span>
                    <p class="sc-kpi-value"><strong>{{ $card['value'] }}</strong></p>
                    <span class="sc-kpi-unit">Item</span>
                </article>
            @endforeach
        </div>

        <aside class="sc-donut-card" aria-label="Realisasi biaya">
            <header><h3>REALISASI BIAYA</h3><i class="bi bi-graph-up-arrow" aria-hidden="true"></i></header>
            <div class="sc-donut" style="--p: {{ $sc['costPercent'] }}" role="img" aria-label="{{ $sc['costPercent'] }} persen">
                <span>{{ $sc['costPercent'] }}<small>%</small></span>
            </div>
        </aside>
    </div>

    <div class="sc-dash-bottom">
        <section class="sc-card sc-plants" aria-labelledby="sc-plants-title">
            <h3 id="sc-plants-title" class="sc-card-title">AJUAN PLANT</h3>
            <ul>
                @foreach ($sc['plantTotals'] as $item)
                    <li data-sc-plant-row="{{ $item['plant'] }}">
                        <strong>{{ strtoupper($item['plant']) }}</strong>
                        <span class="sc-plants-value">{{ $item['value'] }} <small>M</small></span>
                        <span class="sc-chip"><i class="bi bi-caret-down-fill" aria-hidden="true"></i> {{ $item['change'] }}</span>
                    </li>
                @endforeach
            </ul>
            <p class="sc-plants-total"><span>TOTAL AJUAN</span><strong>{{ $sc['plantTotalAll'] }} M</strong></p>
        </section>

        <section class="sc-card sc-recap" aria-labelledby="sc-recap-title" data-sc-recap>
            <header class="sc-card-head">
                <h3 id="sc-recap-title" class="sc-card-title">RENCANA KERJA CAPEX</h3>
                <button type="button" class="sc-link" data-sc-recap-all>Lihat Semua <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
            </header>
            <div class="sc-scroll">
                <table class="sc-table sc-table--recap">
                    <thead><tr><th>NO</th><th>UNIT KERJA</th><th>PLANT</th><th>PAGU</th><th>AJUAN</th><th>SERAPAN</th></tr></thead>
                    <tbody>
                        @foreach ($sc['unitRecap'] as $row)
                            <tr data-sc-recap-row data-plant="{{ $row['plant'] }}" data-text="{{ strtolower($row['unit'].' '.$row['plant']) }}">
                                <td>{{ $row['no'] }}</td>
                                <td class="sc-unit">{{ $row['unit'] }}</td>
                                <td><span class="sc-plant-tag">{{ $row['plant'] }}</span></td>
                                <td>{{ $row['pagu'] }}</td>
                                <td>{{ $row['ajuan'] }}</td>
                                <td><span class="sc-pill {{ $row['over'] ? 'is-danger' : 'is-ok' }}">{{ $row['percent'] }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="sc-empty" data-sc-recap-empty hidden>Tidak ada data yang cocok dengan filter.</p>
            <footer class="sc-foot">
                <span data-sc-recap-summary aria-live="polite"></span>
                <nav class="sc-pager" data-sc-recap-pager aria-label="Pagination rencana kerja capex"></nav>
            </footer>
        </section>

        <section class="sc-card sc-notes" aria-labelledby="sc-notes-title" data-sc-notes>
            <h3 id="sc-notes-title" class="sc-card-title">REMARKS</h3>
            <ul>
                @foreach ($sc['remarks'] as $i => $remark)
                    <li data-sc-note-item @if ($i >= 5) hidden data-extra @endif>
                        <div>
                            <p>{{ $remark['text'] }}</p>
                            <p class="sc-note-detail" hidden>{{ $remark['detail'] }}</p>
                            <button type="button" class="sc-readmore" aria-expanded="false" data-sc-readmore>Read More <i class="bi bi-caret-right-fill" aria-hidden="true"></i></button>
                        </div>
                        <span class="sc-badge {{ $remark['status'] === 'OK' ? 'is-ok' : 'is-cancel' }}">{{ $remark['status'] }}</span>
                    </li>
                @endforeach
            </ul>
            <footer class="sc-foot">
                <button type="button" class="sc-link" data-sc-notes-toggle data-total="{{ count($sc['remarks']) }}">Menampilkan 5 dari {{ count($sc['remarks']) }} catatan evaluasi</button>
            </footer>
        </section>
    </div>
</div>

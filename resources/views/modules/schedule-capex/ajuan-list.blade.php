@php
    $listTitles = ['disetujui' => 'Ajuan Disetujui', 'dibatalkan' => 'Ajuan Dibatalkan', 'rekap' => 'Rekap Ajuan'];
@endphp
<section class="sc-card sc-list" data-sc-list="{{ $listKey }}" aria-labelledby="sc-list-title-{{ $listKey }}">
    <header class="sc-list-head">
        <h2 id="sc-list-title-{{ $listKey }}">DAFTAR AJUAN CAPEX</h2>
        <p>Rincian ajuan per unit kerja, spesifikasi dan harga</p>
    </header>
    <div class="sc-scroll">
        <table class="sc-table sc-table--list">
            <thead>
                <tr>
                    <th>NO.</th><th>PLANT</th><th>UNIT KERJA</th><th>MRP</th><th>NO. MATERIAL</th><th>SHORT TEXT MATERIAL</th>
                    <th class="is-num">QUANTITY</th><th>OUM</th><th class="is-num">HARGA SATUAN</th><th class="is-num">HARGA TOTAL</th>
                    <th>STATUS BOM</th><th>DRAWING</th><th>RIF</th><th>EDIT</th>
                    <th class="is-cancel">CANCEL</th><th>NOTE</th>
                </tr>
            </thead>
            <tbody data-sc-body></tbody>
        </table>
    </div>
    <p class="sc-empty" data-sc-empty hidden>Tidak ada ajuan yang cocok dengan filter.</p>
    <footer class="sc-foot">
        <span data-sc-summary aria-live="polite"></span>
        <nav class="sc-pager" data-sc-pager aria-label="Pagination {{ $listTitles[$listKey] }}"></nav>
    </footer>
</section>

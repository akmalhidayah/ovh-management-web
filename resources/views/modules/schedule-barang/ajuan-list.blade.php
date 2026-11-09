@php
    $listTitles = ['disetujui' => 'Ajuan Disetujui', 'dibatalkan' => 'Ajuan Dibatalkan', 'rekap' => 'Rekap Ajuan'];
@endphp
<section class="sb-card sb-list" data-sb-list="{{ $listKey }}" aria-labelledby="sb-list-title-{{ $listKey }}">
    <header class="sb-list-head">
        <h2 id="sb-list-title-{{ $listKey }}">DAFTAR AJUAN BARANG</h2>
        <p>Rincian ajuan per unit kerja, spesifikasi dan harga</p>
    </header>
    <div class="sb-scroll">
        <table class="sb-table sb-table--list">
            <thead>
                <tr>
                    <th>NO.</th><th>PLANT</th><th>UNIT KERJA</th><th>DESKRIPSI PROYEK</th><th>NO. AKUN</th><th>SPESIFIKASI</th>
                    <th class="is-num">QTY</th><th>UOM</th><th class="is-num">HARGA UNIT</th><th class="is-num">TOTAL HARGA</th>
                    <th>STATUS AJUAN</th><th>NOTE</th><th>EDIT</th>
                </tr>
            </thead>
            <tbody data-sb-body></tbody>
        </table>
    </div>
    <p class="sb-empty" data-sb-empty hidden>Tidak ada ajuan yang cocok dengan filter.</p>
    <footer class="sb-foot">
        <span data-sb-summary aria-live="polite"></span>
        <nav class="sb-pager" data-sb-pager aria-label="Pagination {{ $listTitles[$listKey] }}"></nav>
    </footer>
</section>

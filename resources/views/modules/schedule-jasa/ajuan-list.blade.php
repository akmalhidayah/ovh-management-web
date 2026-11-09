@php
    $listTitles = ['disetujui' => 'Ajuan Disetujui', 'intern' => 'Ajuan Dikerjakan Intern', 'dibatalkan' => 'Ajuan Dibatalkan', 'rekap' => 'Rekap Ajuan'];
    $withProcess = $listKey === 'rekap';
@endphp
<section class="sj-card sj-list" data-sj-list="{{ $listKey }}" aria-labelledby="sj-list-title-{{ $listKey }}">
    <header class="sj-list-head">
        <h2 id="sj-list-title-{{ $listKey }}">DAFTAR AJUAN JASA</h2>
        <p>Rincian ajuan per unit kerja, spesifikasi dan harga</p>
    </header>
    <div class="sj-scroll">
        <table class="sj-table sj-table--list">
            <thead>
                <tr>
                    <th>NO.</th><th>UNIT KERJA</th><th>PLANT</th><th>NOTIFIKASI</th><th>DESCRIPTION PEKERJAAN</th>
                    <th class="is-num">DURASI</th><th>OUM</th><th class="is-num">QTY</th><th>OUM</th>
                    <th class="is-num">HARGA SATUAN</th><th class="is-num">TOTAL HARGA</th><th>STATUS AJUAN</th>
                    @if ($withProcess)<th>PROSES PENGADAAN</th>@endif
                    <th>NOTE</th><th>EDIT</th>
                </tr>
            </thead>
            <tbody data-sj-body></tbody>
        </table>
    </div>
    <p class="sj-empty" data-sj-empty hidden>Tidak ada ajuan yang cocok dengan filter.</p>
    <footer class="sj-foot">
        <span data-sj-summary aria-live="polite"></span>
        <nav class="sj-pager" data-sj-pager aria-label="Pagination {{ $listTitles[$listKey] }}"></nav>
    </footer>
</section>

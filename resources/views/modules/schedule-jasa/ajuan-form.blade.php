<section class="sj-card sj-list sj-form" data-sj-list="form" aria-labelledby="sj-form-title">
    <header class="sj-list-head">
        <h2 id="sj-form-title">DAFTAR AJUAN JASA</h2>
        <p>Rincian ajuan per unit kerja, spesifikasi dan harga</p>
    </header>
    <div class="sj-scroll">
        <table class="sj-table sj-table--wide">
            <thead>
                <tr class="sj-groups">
                    <th colspan="9" class="g-bom">BOM</th>
                    <th colspan="5" class="g-tech">SPESIFIKASI TEKNIS (BAGIAN AWAL)</th>
                    <th colspan="5" class="g-price">SPESIFIKASI LAMPIRAN &amp; HARGA / PO</th>
                    <th colspan="8" class="g-eval">EVALUASI TIM OVH (FOLD STAMA)</th>
                </tr>
                <tr>
                    <th>NO.</th><th>PLANT</th><th>UNIT KERJA</th><th>PLANT SECTION</th><th>PROGRAM KERJA</th><th>ID EQUIP BOM</th><th class="is-num">QTY EQ</th><th>UOM</th><th>MRP TYPE</th>
                    <th>LIFETIME</th><th>NOTIFIKASI</th><th>ORDER</th><th>RESERVASI</th><th>NO. MASTER</th>
                    <th>PO NUMBER</th><th>TGL PO</th><th class="is-num">HARGA SAT</th><th class="is-num">QTY</th><th class="is-num">HARGA TOTAL</th>
                    <th>PIC EVALUATOR</th><th class="is-num">QTY STD</th><th>STATUS BOM</th><th>KET. EVALUASI</th>
                    <th>STATUS AJUAN</th><th>PROSES PENGADAAN</th><th>NOTE</th><th>EDIT</th>
                </tr>
            </thead>
            <tbody data-sj-body></tbody>
        </table>
    </div>
    <p class="sj-empty" data-sj-empty hidden>Tidak ada ajuan yang cocok dengan filter.</p>
    <footer class="sj-foot">
        <span data-sj-summary aria-live="polite"></span>
        <nav class="sj-pager" data-sj-pager aria-label="Pagination form ajuan"></nav>
    </footer>
</section>

<div class="sj-formbar">
    <button type="button" class="sj-btn sj-btn--navy" data-sj-back><i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali</button>
    <div>
        <button type="button" class="sj-btn sj-btn--outline" data-sj-draft><i class="bi bi-floppy" aria-hidden="true"></i> Simpan Draft</button>
        <button type="button" class="sj-btn sj-btn--teal" data-sj-send><i class="bi bi-send" aria-hidden="true"></i> Kirim Ajuan</button>
    </div>
</div>

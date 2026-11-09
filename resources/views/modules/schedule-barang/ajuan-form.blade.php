<section class="sb-card sb-list sb-form" data-sb-list="form" aria-labelledby="sb-form-title">
    <header class="sb-list-head">
        <h2 id="sb-form-title">DAFTAR AJUAN BARANG</h2>
        <p>Rincian ajuan per unit kerja, spesifikasi dan harga</p>
    </header>
    <div class="sb-scroll">
        <table class="sb-table sb-table--wide">
            <thead>
                <tr class="sb-groups">
                    <th colspan="9" class="g-bom">BOM</th>
                    <th colspan="5" class="g-tech">SPESIFIKASI TEKNIS (BAGIAN AWAL)</th>
                    <th colspan="5" class="g-price">SPESIFIKASI LAMPIRAN &amp; HARGA / PO</th>
                    <th colspan="4" class="g-eval">EVALUASI TIM OVH (FOLD STAMA)</th>
                    <th colspan="3" class="g-act"></th>
                </tr>
                <tr>
                    <th>NO.</th><th>PLANT</th><th>UNIT KERJA</th><th>PLANT SECTION</th><th>PROGRAM KERJA</th><th>ID EQUIP BOM</th><th class="is-num">QTY EQ</th><th>UOM</th><th>MRP TYPE</th>
                    <th>LIFETIME</th><th>NOTIFIKASI</th><th>ORDER</th><th>RESERVASI</th><th>NO. MASTER</th>
                    <th>PO NUMBER</th><th>TGL PO</th><th class="is-num">HARGA SAT</th><th class="is-num">QTY</th><th class="is-num">HARGA TOTAL</th>
                    <th>PIC EVALUATOR</th><th class="is-num">QTY STD</th><th>STATUS BOM</th><th>KET. EVALUASI</th>
                    <th>STATUS AJUAN</th><th>NOTE</th><th>EDIT</th>
                </tr>
            </thead>
            <tbody data-sb-body></tbody>
        </table>
    </div>
    <p class="sb-empty" data-sb-empty hidden>Tidak ada ajuan yang cocok dengan filter.</p>
    <footer class="sb-foot">
        <span data-sb-summary aria-live="polite"></span>
        <nav class="sb-pager" data-sb-pager aria-label="Pagination form ajuan"></nav>
    </footer>
</section>

<div class="sb-formbar">
    <button type="button" class="sb-btn sb-btn--navy" data-sb-back><i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali</button>
    <div>
        <button type="button" class="sb-btn sb-btn--outline" data-sb-draft><i class="bi bi-floppy" aria-hidden="true"></i> Simpan Draft</button>
        <button type="button" class="sb-btn sb-btn--teal" data-sb-send><i class="bi bi-send" aria-hidden="true"></i> Kirim Ajuan</button>
    </div>
</div>

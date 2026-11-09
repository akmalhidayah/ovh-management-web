<section class="sc-card sc-list sc-form" data-sc-list="form" aria-labelledby="sc-form-title">
    <header class="sc-list-head">
        <h2 id="sc-form-title">DAFTAR AJUAN CAPEX</h2>
        <p>Rincian ajuan per unit kerja, spesifikasi dan harga</p>
    </header>
    <div class="sc-scroll">
        <table class="sc-table sc-table--wide">
            <thead>
                <tr class="sc-groups">
                    <th colspan="9" class="g-bom">BOM</th>
                    <th colspan="5" class="g-tech">SPESIFIKASI TEKNIS (BAGIAN AWAL)</th>
                    <th colspan="5" class="g-price">SPESIFIKASI LAMPIRAN &amp; HARGA / PO</th>
                    <th colspan="10" class="g-eval">EVALUASI TIM OVH (FOLD STAMA)</th>
                </tr>
                <tr>
                    <th>NO.</th><th>PLANT</th><th>UNIT KERJA</th><th>PLANT SECTION</th><th>PROGRAM KERJA</th><th>ID EQUIP BOM</th><th class="is-num">QTY EQ</th><th>UOM</th><th>MRP TYPE</th>
                    <th>LIFETIME</th><th>NOTIFIKASI</th><th>ORDER</th><th>RESERVASI</th><th>NO. MASTER</th>
                    <th>PO NUMBER</th><th>TGL PO</th><th class="is-num">HARGA SAT</th><th class="is-num">QTY</th><th class="is-num">HARGA TOTAL</th>
                    <th>PIC EVALUATOR</th><th class="is-num">QTY STD</th><th>SHORT TEXT MATERIAL</th><th>STATUS BOM</th><th>DRAWING</th><th>RIF</th><th>STATUS AJUAN</th><th>EDIT</th>
                    <th class="is-cancel">CANCEL</th><th>NOTE</th>
                </tr>
            </thead>
            <tbody data-sc-body></tbody>
        </table>
    </div>
    <p class="sc-empty" data-sc-empty hidden>Tidak ada ajuan yang cocok dengan filter.</p>
    <footer class="sc-foot">
        <span data-sc-summary aria-live="polite"></span>
        <nav class="sc-pager" data-sc-pager aria-label="Pagination form ajuan"></nav>
    </footer>
</section>

<div class="sc-formbar">
    <button type="button" class="sc-btn sc-btn--navy" data-sc-back><i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali</button>
    <div>
        <button type="button" class="sc-btn sc-btn--outline" data-sc-draft><i class="bi bi-floppy" aria-hidden="true"></i> Simpan Draft</button>
        <button type="button" class="sc-btn sc-btn--teal" data-sc-send><i class="bi bi-send" aria-hidden="true"></i> Kirim Ajuan</button>
    </div>
</div>

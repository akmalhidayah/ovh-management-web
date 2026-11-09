<div class="sb-modal" data-sb-modal="edit" role="dialog" aria-modal="true" aria-labelledby="sb-edit-title" hidden>
    <div class="sb-modal-backdrop" data-sb-close></div>
    <form class="sb-modal-panel sb-edit" data-sb-edit-form novalidate>
        <header class="sb-edit-head">
            <h2 id="sb-edit-title"><span class="sb-bar" aria-hidden="true"></span> RINCIAN INFORMASI &amp; STATUS <span data-sb-edit-mode>(EDIT)</span></h2>
            <span class="sb-edit-badge"><i class="bi bi-lock-fill" aria-hidden="true"></i> Mode Edit Material</span>
        </header>

        <div class="sb-edit-body">
            <section class="sb-edit-section">
                <h3><i class="bi bi-geo-alt-fill" aria-hidden="true"></i> 1. LOKASI, UNIT KERJA &amp; IDENTIFIKASI PERALATAN</h3>
                <div class="sb-grid sb-grid--s1">
                    <label>Tahun<input name="year" type="text" inputmode="numeric" readonly></label>
                    <label>Unit Kerja<input name="unit" type="text" required></label>
                    <label>Plant Section<input name="plant_section" type="text"></label>
                    <label>Program Kerja<input name="program" type="text"></label>
                    <label>ID Equipment (BOM)<input name="equipment_id" type="text"></label>
                    <label>Qty BOM<input name="qty_bom" type="number" min="0"></label>
                    <label>UoM<input name="uom_bom" type="text"></label>
                    <label>MRP Type<input name="mrp_type" type="text"></label>
                    <label class="is-highlight is-warn">Lifetime (Tanggal)<input name="lifetime" type="text" placeholder="dd/mm/yyyy - dd/mm/yyyy"></label>
                    <label>Rencana Pemakaian<input name="usage_plan" type="text"></label>
                </div>
            </section>

            <section class="sb-edit-section">
                <h3><i class="bi bi-list-task" aria-hidden="true"></i> 2. WORK ORDER, SPESIFIKASI &amp; RENCANA KEBUTUHAN</h3>
                <div class="sb-grid sb-grid--s2">
                    <label>Notifikasi<input name="notification" type="text"></label>
                    <label>Order<input name="order" type="text"></label>
                    <label>Reservasi<input name="reservation" type="text"></label>
                    <label>Mat. Group<input name="mat_group" type="text"></label>
                    <label>No. Material<input name="material_no" type="text" inputmode="numeric"></label>
                    <label>Mat. Padanan<input name="mat_padanan" type="text"></label>
                    <div class="sb-longtext">
                        <span class="sb-label" id="sb-longtext-label">Long Text Material (Deskripsi Lengkap)</span>
                        <div class="sb-rte">
                            <div class="sb-rte-bar" role="toolbar" aria-label="Format teks">
                                <button type="button" data-cmd="bold" aria-label="Tebal"><b>B</b></button>
                                <button type="button" data-cmd="italic" aria-label="Miring"><i>I</i></button>
                                <button type="button" data-cmd="underline" aria-label="Garis bawah"><u>U</u></button>
                                <button type="button" data-cmd="insertUnorderedList" aria-label="Daftar"><i class="bi bi-list-ul"></i></button>
                                <button type="button" data-cmd="createLink" aria-label="Tautan"><i class="bi bi-link-45deg"></i></button>
                            </div>
                            <div class="sb-rte-area" contenteditable="true" role="textbox" aria-multiline="true" aria-labelledby="sb-longtext-label" data-sb-longtext></div>
                        </div>
                    </div>
                    <label class="sb-span-short">Short Text Material<input name="short_text" type="text" class="is-strong"></label>
                    <label>Quantity<input name="qty" type="number" min="0" step="1" required></label>
                    <label>UoM<input name="uom" type="text"></label>
                    <label class="is-highlight is-ok">Waktu Pengiriman (Bulan)<input name="delivery" type="text"></label>
                    <label>Garansi (Bulan)<input name="warranty" type="number" min="0"></label>
                    <label>Jenis Garansi<input name="warranty_type" type="text"></label>
                    <label>COO / COM / COA / Sertifikat<input name="coo" type="text"></label>
                    <label>Drawing / Foto / RFI<input name="drawing" type="text"></label>
                    <label>Link E-Commerce / Vendor<input name="link" type="url" placeholder="https://"></label>
                </div>
            </section>

            <section class="sb-edit-section">
                <h3><i class="bi bi-tag-fill" aria-hidden="true"></i> 3. SUMBER REFERENSI HARGA</h3>
                <div class="sb-grid sb-grid--s3">
                    <label>Ref. Purchase Order (PO)<input name="po_ref" type="text"></label>
                    <label>Tanggal PO<input name="po_date" type="date"></label>
                    <label>Harga Satuan (Rp)<input name="price" type="number" min="0" step="1" class="is-right" required></label>
                    <label>Quantity<input name="qty_price" type="number" readonly class="is-right" tabindex="-1"></label>
                    <label class="is-highlight is-ok">Total Harga (Rp)<input name="total" type="text" readonly class="is-right is-strong" tabindex="-1"></label>
                </div>
            </section>

            <section class="sb-edit-section">
                <h3><i class="bi bi-check2-square" aria-hidden="true"></i> 4. EVALUASI &amp; STATUS BOM</h3>
                <div class="sb-grid sb-grid--s4">
                    <label>Kategori
                        <select name="category">
                            <option>Critical Sparepart</option><option>Consumable</option><option>Capital Spare</option><option>Insurance Spare</option>
                        </select>
                    </label>
                    <label>Kategori Material<input name="material_category" type="text"></label>
                    <label class="sb-span-note">Keterangan / Catatan Evaluasi Tim Pengadaan<textarea name="evaluation_note" rows="3"></textarea></label>
                    <label>Qty Disetujui<input name="qty_approved" type="number" min="0"></label>
                    <label>Status Ajuan
                        <select name="status"><option value="Lanjut">Lanjut</option><option value="Pending">Pending</option><option value="Batal">Batal</option></select>
                    </label>
                    <div class="sb-bom">
                        <span class="sb-label">Status BOM</span>
                        <div class="sb-toggle" role="group" aria-label="Status BOM">
                            <button type="button" data-bom="OK" aria-pressed="true"><i class="bi bi-check-lg" aria-hidden="true"></i> OK</button>
                            <button type="button" data-bom="NO" aria-pressed="false">NO</button>
                        </div>
                        <input type="hidden" name="bom_status" value="OK">
                    </div>
                </div>
            </section>
            <p class="sb-form-error" data-sb-edit-error role="alert" hidden></p>
        </div>

        <footer class="sb-edit-foot">
            <span>PT Semen Tonasa Overhaul Executive Management System</span>
            <div>
                <button type="button" class="sb-btn sb-btn--soft" data-sb-close>Batal</button>
                <button type="submit" class="sb-btn sb-btn--navy"><i class="bi bi-floppy" aria-hidden="true"></i> Simpan</button>
            </div>
        </footer>
    </form>
</div>

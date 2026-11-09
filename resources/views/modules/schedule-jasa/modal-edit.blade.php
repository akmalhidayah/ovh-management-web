<div class="sj-modal" data-sj-modal="edit" role="dialog" aria-modal="true" aria-labelledby="sj-edit-title" hidden>
    <div class="sj-modal-backdrop" data-sj-close></div>
    <form class="sj-modal-panel sj-edit" data-sj-edit-form novalidate>
        <header class="sj-edit-head">
            <div class="sj-edit-title">
                <span class="sj-edit-icon" aria-hidden="true"><i class="bi bi-pencil-square"></i></span>
                <div>
                    <h2 id="sj-edit-title">RINCIAN INFORMASI &amp; STATUS <span data-sj-edit-mode>(EDIT)</span></h2>
                    <p>Formulir Pembaruan Data Pekerjaan Overhaul PT Semen Tonasa</p>
                </div>
            </div>
            <button type="button" class="sj-btn sj-btn--soft" data-sj-reset><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i> Reset Form</button>
        </header>

        <div class="sj-edit-body">
            <section class="sj-edit-section">
                <h3>1. UNIT KERJA &amp; LOKASI</h3>
                <div class="sj-grid sj-grid--loc">
                    <label>Tahun<input name="year" type="text" readonly></label>
                    <label>Unit Kerja<input name="unit" type="text" required></label>
                    <label>Plant<input name="plant" type="text" list="sj-plant-options"></label>
                    <label>Area<input name="area" type="text"></label>
                    <label>Functional Location<input name="functional_location" type="text"></label>
                    <label>ID Equipment<input name="equipment_id" type="text"></label>
                </div>
            </section>

            <section class="sj-edit-section">
                <h3>2. WORK ORDER, SCOPE PEKERJAAN &amp; HARGA</h3>
                <div class="sj-grid sj-grid--work">
                    <div class="sj-work-left">
                        <label class="is-wide">Notifikasi<input name="notification" type="text" inputmode="numeric"></label>
                        <label class="is-wide">Deskripsi Pekerjaan<input name="description" type="text" required></label>
                        <label>Durasi<input name="duration" type="number" min="0" step="1" class="is-right"></label>
                        <label>OUM<input name="uom_duration" type="text"></label>
                        <label>Quantity<input name="qty" type="number" min="0" step="1" class="is-right" required></label>
                        <label>OUM<input name="uom_qty" type="text"></label>
                        <label class="is-half">Harga Satuan (Rp)<input name="price" type="number" min="0" step="1" class="is-right" required></label>
                        <label class="is-half">Total Harga (Rp)<input name="total" type="text" class="is-right is-total" readonly tabindex="-1"></label>
                    </div>
                    <div class="sj-work-right">
                        <label>Detail Pekerjaan<textarea name="detail" placeholder="Tuliskan rincian lengkap scope pekerjaan, metode pelaksanaan, serta persyaratan teknis di sini..."></textarea></label>
                    </div>
                </div>
            </section>

            <section class="sj-edit-section">
                <h3>3. EVALUASI</h3>
                <div class="sj-grid sj-grid--eval">
                    <label>Status Ajuan
                        <select name="status" class="is-status">
                            <option value="Lanjut">Lanjut (Verifikasi Sesuai)</option>
                            <option value="PGO">PGO (Dikerjakan Intern)</option>
                            <option value="TS">TS (Dikerjakan Intern)</option>
                            <option value="BMS">BMS (Dikerjakan Intern)</option>
                            <option value="Batal">Batal (Dibatalkan)</option>
                        </select>
                    </label>
                    <label class="sj-span-rows">Keterangan<textarea name="evaluation_note" placeholder="Catatan evaluasi, alasan perubahan status, atau instruksi tindak lanjut..."></textarea></label>
                    <label>Proses Pengadaan<input name="process" type="text" list="sj-process-options" placeholder="Kontrak / Fabrikasi / Tender / ..."></label>
                </div>
                <datalist id="sj-process-options"><option value="Kontrak"><option value="Fabrikasi"><option value="Tender"><option value="Cancel"><option value="BMS Review"></datalist>
                <datalist id="sj-plant-options">@foreach ($sj['plants'] as $plant)<option value="{{ $plant }}">@endforeach</datalist>
            </section>
            <p class="sj-form-error" data-sj-edit-error role="alert" hidden></p>
        </div>

        <footer class="sj-edit-foot">
            <span><i class="bi bi-shield-check" aria-hidden="true"></i> Sistem Informasi Overhaul PT Semen Tonasa</span>
            <div>
                <button type="button" class="sj-btn sj-btn--soft" data-sj-close>Batal</button>
                <button type="submit" class="sj-btn sj-btn--navy"><i class="bi bi-floppy" aria-hidden="true"></i> Simpan Perubahan</button>
            </div>
        </footer>
    </form>
</div>

<div class="sc-modal" data-sc-modal="note" role="dialog" aria-modal="true" aria-labelledby="sc-note-title" hidden>
    <div class="sc-modal-backdrop" data-sc-close></div>
    <div class="sc-modal-panel sc-note">
        <header class="sc-note-head">
            <span class="sc-note-icon" aria-hidden="true"><i class="bi bi-clipboard-check"></i></span>
            <div>
                <h2 id="sc-note-title">Overhaul Remark &amp; Note App</h2>
                <p>Kelola catatan evaluasi material, status BOM, dan log aktivitas harian <span data-sc-note-context></span></p>
            </div>
            <div class="sc-note-counters">
                <span class="sc-counter">Total Remark: <b data-sc-note-total>0</b></span>
                <span class="sc-counter is-ok">Selesai: <b data-sc-note-donecount>0</b></span>
                <button type="button" class="sc-modal-x" data-sc-close aria-label="Tutup"><i class="bi bi-x-lg"></i></button>
            </div>
        </header>

        <div class="sc-note-body">
            <form class="sc-note-form" data-sc-note-form novalidate>
                <h3><i class="bi bi-plus-square-fill" aria-hidden="true"></i> <span data-sc-note-form-title>TAMBAH REMARK BARU</span></h3>
                <label>Tanggal *<input type="date" name="date" required></label>
                <label>Status *
                    <select name="status">
                        <option value="Pending">Pending (Menunggu)</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Selesai">Selesai</option>
                        <option value="Penting">Penting</option>
                    </select>
                </label>
                <label>Informasi / Catatan Material *<textarea name="text" rows="6" placeholder="Tuliskan catatan atau rincian remark material di sini..." required></textarea></label>
                <p class="sc-form-error" data-sc-note-error role="alert" hidden></p>
                <button type="submit" class="sc-btn sc-btn--navy sc-btn--block"><i class="bi bi-floppy" aria-hidden="true"></i> <span data-sc-note-submit>Simpan Remark</span></button>
                <button type="button" class="sc-btn sc-btn--soft sc-btn--block" data-sc-note-cancel hidden>Batal Ubah</button>
            </form>

            <div class="sc-note-list">
                <div class="sc-note-tools">
                    <label class="sc-note-search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" data-sc-note-search placeholder="Cari informasi catatan material..."></label>
                    <select data-sc-note-filter aria-label="Filter status">
                        <option value="">Semua Status</option><option>Pending</option><option>In Progress</option><option>Selesai</option><option>Penting</option>
                    </select>
                    <button type="button" class="sc-icon-btn is-green" data-sc-note-export aria-label="Export catatan"><i class="bi bi-file-earmark-spreadsheet"></i></button>
                    <button type="button" class="sc-icon-btn" data-sc-note-print aria-label="Cetak catatan"><i class="bi bi-printer"></i></button>
                </div>
                <div class="sc-scroll">
                    <table class="sc-table sc-table--note">
                        <thead><tr><th>NO</th><th>TANGGAL</th><th>INFORMASI / CATATAN</th><th>STATUS</th><th>AKSI</th></tr></thead>
                        <tbody data-sc-note-body></tbody>
                    </table>
                </div>
                <p class="sc-empty" data-sc-note-empty hidden>Belum ada catatan.</p>
                <footer class="sc-foot">
                    <span data-sc-note-summary aria-live="polite"></span>
                    <span class="sc-storage"><i class="bi bi-cloud-check-fill" aria-hidden="true"></i> LocalStorage Aktif</span>
                </footer>
            </div>
        </div>
    </div>
</div>

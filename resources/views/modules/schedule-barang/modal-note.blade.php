<div class="sb-modal" data-sb-modal="note" role="dialog" aria-modal="true" aria-labelledby="sb-note-title" hidden>
    <div class="sb-modal-backdrop" data-sb-close></div>
    <div class="sb-modal-panel sb-note">
        <header class="sb-note-head">
            <span class="sb-note-icon" aria-hidden="true"><i class="bi bi-clipboard-check"></i></span>
            <div>
                <h2 id="sb-note-title">Overhaul Remark &amp; Note App</h2>
                <p>Kelola catatan evaluasi material, status BOM, dan log aktivitas harian <span data-sb-note-context></span></p>
            </div>
            <div class="sb-note-counters">
                <span class="sb-counter">Total Remark: <b data-sb-note-total>0</b></span>
                <span class="sb-counter is-ok">Selesai: <b data-sb-note-donecount>0</b></span>
                <button type="button" class="sb-modal-x" data-sb-close aria-label="Tutup"><i class="bi bi-x-lg"></i></button>
            </div>
        </header>

        <div class="sb-note-body">
            <form class="sb-note-form" data-sb-note-form novalidate>
                <h3><i class="bi bi-plus-square-fill" aria-hidden="true"></i> <span data-sb-note-form-title>TAMBAH REMARK BARU</span></h3>
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
                <p class="sb-form-error" data-sb-note-error role="alert" hidden></p>
                <button type="submit" class="sb-btn sb-btn--navy sb-btn--block"><i class="bi bi-floppy" aria-hidden="true"></i> <span data-sb-note-submit>Simpan Remark</span></button>
                <button type="button" class="sb-btn sb-btn--soft sb-btn--block" data-sb-note-cancel hidden>Batal Ubah</button>
            </form>

            <div class="sb-note-list">
                <div class="sb-note-tools">
                    <label class="sb-note-search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" data-sb-note-search placeholder="Cari informasi catatan material..."></label>
                    <select data-sb-note-filter aria-label="Filter status">
                        <option value="">Semua Status</option><option>Pending</option><option>In Progress</option><option>Selesai</option><option>Penting</option>
                    </select>
                    <button type="button" class="sb-icon-btn is-green" data-sb-note-export aria-label="Export catatan"><i class="bi bi-file-earmark-spreadsheet"></i></button>
                    <button type="button" class="sb-icon-btn" data-sb-note-print aria-label="Cetak catatan"><i class="bi bi-printer"></i></button>
                </div>
                <div class="sb-scroll">
                    <table class="sb-table sb-table--note">
                        <thead><tr><th>NO</th><th>TANGGAL</th><th>INFORMASI / CATATAN</th><th>STATUS</th><th>AKSI</th></tr></thead>
                        <tbody data-sb-note-body></tbody>
                    </table>
                </div>
                <p class="sb-empty" data-sb-note-empty hidden>Belum ada catatan.</p>
                <footer class="sb-foot">
                    <span data-sb-note-summary aria-live="polite"></span>
                    <span class="sb-storage"><i class="bi bi-cloud-check-fill" aria-hidden="true"></i> LocalStorage Aktif</span>
                </footer>
            </div>
        </div>
    </div>
</div>

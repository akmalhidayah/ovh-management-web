<div class="sj-modal" data-sj-modal="note" role="dialog" aria-modal="true" aria-labelledby="sj-note-title" hidden>
    <div class="sj-modal-backdrop" data-sj-close></div>
    <div class="sj-modal-panel sj-note">
        <header class="sj-note-head">
            <span class="sj-note-icon" aria-hidden="true"><i class="bi bi-clipboard-check"></i></span>
            <div>
                <h2 id="sj-note-title">Overhaul Remark &amp; Note App</h2>
                <p>Kelola catatan evaluasi material, status BOM, dan log aktivitas harian <span data-sj-note-context></span></p>
            </div>
            <div class="sj-note-counters">
                <span class="sj-counter">Total Remark: <b data-sj-note-total>0</b></span>
                <span class="sj-counter is-ok">Selesai: <b data-sj-note-donecount>0</b></span>
                <button type="button" class="sj-modal-x" data-sj-close aria-label="Tutup"><i class="bi bi-x-lg"></i></button>
            </div>
        </header>

        <div class="sj-note-body">
            <form class="sj-note-form" data-sj-note-form novalidate>
                <h3><i class="bi bi-plus-square-fill" aria-hidden="true"></i> <span data-sj-note-form-title>TAMBAH REMARK BARU</span></h3>
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
                <p class="sj-form-error" data-sj-note-error role="alert" hidden></p>
                <button type="submit" class="sj-btn sj-btn--navy sj-btn--block"><i class="bi bi-floppy" aria-hidden="true"></i> <span data-sj-note-submit>Simpan Remark</span></button>
                <button type="button" class="sj-btn sj-btn--soft sj-btn--block" data-sj-note-cancel hidden>Batal Ubah</button>
            </form>

            <div class="sj-note-list">
                <div class="sj-note-tools">
                    <label class="sj-note-search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" data-sj-note-search placeholder="Cari informasi catatan material..."></label>
                    <select data-sj-note-filter aria-label="Filter status">
                        <option value="">Semua Status</option><option>Pending</option><option>In Progress</option><option>Selesai</option><option>Penting</option>
                    </select>
                    <button type="button" class="sj-icon-btn is-green" data-sj-note-export aria-label="Export catatan"><i class="bi bi-file-earmark-spreadsheet"></i></button>
                    <button type="button" class="sj-icon-btn" data-sj-note-print aria-label="Cetak catatan"><i class="bi bi-printer"></i></button>
                </div>
                <div class="sj-scroll">
                    <table class="sj-table sj-table--note">
                        <thead><tr><th>NO</th><th>TANGGAL</th><th>INFORMASI / CATATAN</th><th>STATUS</th><th>AKSI</th></tr></thead>
                        <tbody data-sj-note-body></tbody>
                    </table>
                </div>
                <p class="sj-empty" data-sj-note-empty hidden>Belum ada catatan.</p>
                <footer class="sj-foot">
                    <span data-sj-note-summary aria-live="polite"></span>
                    <span class="sj-storage"><i class="bi bi-cloud-check-fill" aria-hidden="true"></i> LocalStorage Aktif</span>
                </footer>
            </div>
        </div>
    </div>
</div>

@include('modules.schedule-barang.partials.work-list-data')

<section class="barang-work-list" data-barang-work-list data-mode="{{ $listMode }}" aria-label="Daftar rencana kerja barang">
    <div class="bwl-toolbar">
        <label>Tahun<select class="form-select" data-year><option value="">Semua tahun</option><option>2026</option><option selected>2027</option><option>2028</option></select></label>
        <label>Plant<select class="form-select" data-plant><option value="">Semua plant</option><option>Tonasa 2</option><option>Tonasa 3</option><option>Tonasa 4</option><option selected>Tonasa 5</option><option>Power Plant</option></select></label>
        <label class="bwl-search">Search<span><i class="bi bi-search" aria-hidden="true"></i><input type="search" class="form-control" data-search placeholder="Cari unit kerja, notifikasi, pekerjaan, status..."></span></label>
        <div class="bwl-toolbar-actions">
            @if ($listMode === 'semua')
                <button type="button" class="bwl-button bwl-button-light" data-toggle-detail aria-pressed="false"><i class="bi bi-layout-three-columns" aria-hidden="true"></i><span>Detail Kolom</span></button>
            @endif
            <button type="button" class="bwl-button bwl-button-export" data-export title="Unduh data hasil filter dalam format CSV untuk Excel"><i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i>Export CSV</button>
            <button type="button" class="bwl-button bwl-button-primary" data-add><i class="bi bi-plus-lg" aria-hidden="true"></i>Tambah Ajuan</button>
        </div>
    </div>
    <div class="bwl-preview-note"><i class="bi bi-info-circle" aria-hidden="true"></i>Pratinjau frontend. Perubahan sementara akan kembali ke data awal saat halaman dimuat ulang.</div>
    <div class="bwl-feedback" data-feedback role="status" hidden></div>
    <div class="bwl-card">
        <div class="bwl-table-scroll" tabindex="0" role="region" aria-label="Tabel rencana kerja barang, geser untuk melihat seluruh kolom">
            <table class="bwl-table">
                <thead><tr>
                    <th scope="col">No.</th><th scope="col" data-detail-col hidden>Tahun</th><th scope="col">Unit Kerja</th><th scope="col">Plant</th>
                    <th scope="col" data-detail-col hidden>Area</th><th scope="col" data-detail-col hidden>Functional Location</th><th scope="col" data-detail-col hidden>ID Equipment</th>
                    <th scope="col">Notifikasi</th><th scope="col">Description Pekerjaan</th><th scope="col">Durasi</th><th scope="col">UOM</th><th scope="col">Qty</th><th scope="col">UOM</th>
                    <th scope="col" class="bwl-number">Harga Satuan</th><th scope="col" class="bwl-number">Total Harga</th><th scope="col">Status Ajuan</th>
                    @if ($listMode === 'semua')<th scope="col">Proses Pengadaan</th>@endif
                    <th scope="col">Note</th><th scope="col">Edit</th>
                </tr></thead>
                <tbody data-rows></tbody>
            </table>
        </div>
        <div class="bwl-empty" data-empty hidden><i class="bi bi-search" aria-hidden="true"></i><strong>Tidak ada ajuan yang cocok</strong><span>Ubah kata pencarian atau pilihan tahun dan plant.</span><button type="button" class="bwl-button bwl-button-light" data-reset>Reset Filter</button></div>
        <div class="bwl-footer">
            <span data-summary aria-live="polite"></span>
            <div class="bwl-pagination-controls"><label>Per halaman <select data-page-size><option>10</option><option selected>20</option><option>50</option></select></label><nav data-pagination aria-label="Halaman daftar ajuan"></nav></div>
        </div>
    </div>
    <button type="button" class="bwl-button bwl-button-light" data-close-detail hidden><i class="bi bi-arrow-left" aria-hidden="true"></i>Kembali ke tampilan ringkas</button>

    <div class="modal fade bwl-modal" id="barang-work-edit" tabindex="-1" aria-labelledby="barang-work-edit-title" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable"><div class="modal-content">
            <div class="modal-header"><div><h2 class="modal-title" id="barang-work-edit-title">Tambah Ajuan</h2><p>Isi rencana pekerjaan dan estimasi kebutuhan.</p></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
            <div class="modal-body">
                <form id="barang-work-form" class="bwl-form">
                    <label>Tahun<input class="form-control" name="year" type="number" min="2000" max="2100" required></label>
                    <label>Plant<select class="form-select" name="plant"><option>Tonasa 2</option><option>Tonasa 3</option><option>Tonasa 4</option><option>Tonasa 5</option><option>Power Plant</option></select></label>
                    <label class="bwl-span-2">Unit Kerja<input class="form-control" name="unit" maxlength="120" required></label>
                    <label>Notifikasi<input class="form-control" name="notification" maxlength="30" inputmode="numeric"></label>
                    <label>Area<input class="form-control" name="area" maxlength="120"></label>
                    <label>Functional Location<input class="form-control" name="location" maxlength="80"></label>
                    <label>ID Equipment<input class="form-control" name="equipment" maxlength="40"></label>
                    <label class="bwl-span-2">Deskripsi Pekerjaan<textarea class="form-control" name="description" rows="3" maxlength="600" required></textarea></label>
                    <label>Durasi (hari)<input class="form-control" name="duration" type="number" min="1" max="3650" step="1" required></label>
                    <label>Quantity<input class="form-control" name="quantity" type="number" min="0.01" max="1000000" step="0.01" required></label>
                    <label>UOM<select class="form-select" name="uom"><option>LOT</option><option>M2</option><option>PCS</option><option>SET</option><option>UNIT</option></select></label>
                    <label>Harga Satuan (Rp)<input class="form-control" name="price" type="number" min="0" max="1000000000000" step="0.01" required></label>
                    <label>Status Ajuan<select class="form-select" name="status"><option>Lanjut</option><option>PGO</option><option>TS</option><option>BMS</option><option>Cancel</option></select></label>
                    <label>Proses Pengadaan<select class="form-select" name="process"><option>Kontrak</option><option>Fabrikasi</option><option>Tender</option><option>Cancel</option><option>PO/Done</option><option>BMS Review</option></select></label>
                    <div class="bwl-form-total bwl-span-2"><span>Total Harga</span><output data-total>Rp 0,00</output></div>
                </form>
            </div>
            <div class="modal-footer"><button type="button" class="bwl-button bwl-button-light" data-bs-dismiss="modal">Batal</button><button type="submit" form="barang-work-form" class="bwl-button bwl-button-primary"><i class="bi bi-check2" aria-hidden="true"></i>Simpan</button></div>
        </div></div>
    </div>

    <div class="modal fade bwl-modal" id="barang-work-notes" tabindex="-1" aria-labelledby="barang-work-notes-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable"><div class="modal-content">
            <div class="modal-header"><div><h2 class="modal-title" id="barang-work-notes-title">Catatan Ajuan</h2><p data-notes-description></p></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
            <div class="modal-body"><div class="bwl-notes-list" data-notes-list></div><form id="barang-note-form"><label for="barang-note-input" class="form-label">Tambah catatan</label><textarea id="barang-note-input" class="form-control" rows="3" maxlength="1000" placeholder="Tulis catatan pekerjaan..." required></textarea></form></div>
            <div class="modal-footer"><button type="button" class="bwl-button bwl-button-light" data-bs-dismiss="modal">Tutup</button><button type="submit" form="barang-note-form" class="bwl-button bwl-button-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i>Tambah Catatan</button></div>
        </div></div>
    </div>
</section>

@include('modules.schedule-barang.partials.work-list-styles')
@include('modules.schedule-barang.partials.work-list-script')

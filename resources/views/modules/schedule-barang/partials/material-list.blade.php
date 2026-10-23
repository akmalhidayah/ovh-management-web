@php
    $materialReference = [
        ['Tonasa 4', 'Seksi Pemeliharaan EP', 'M01', '1000293841', 'BEARING BALL 6205 C3 SKF', 24, 'PCE', 125000, 'OK', true, 'R01'],
        ['Tonasa 5', 'Seksi Operasi FM 2/3', 'M02', '1000293845', 'OIL SEAL 45x62x8 NBR', 10, 'PCE', 85000, 'NO', false, 'R02'],
        ['Tonasa 2', 'Seksi Operasi FM 4', 'M01', '1000301120', 'FIREBRICKS TYPE AL-60 HIGH ALUMINA', 500, 'PCS', 45000, 'OK', true, 'R01'],
        ['Tonasa 3', 'Seksi Operasi RKC 4', 'M03', '1000305582', 'CASTABLE DENSE C-16 SUPER', 120, 'BAG', 320000, 'NO', false, 'R03'],
        ['Power Plant', 'Seksi Operasi FM 5', 'M01', '1000312009', 'ANCHOR V-TYPE STAINLESS STEEL 310', 1500, 'PCS', 18500, 'OK', true, 'R01'],
        ['Tonasa 5', 'Seksi Pemeliharaan Raw Mill', 'M02', '1000318742', 'V-BELT B-85 GATES SUPER HC', 16, 'PCE', 140000, 'OK', true, 'R02'],
        ['Tonasa 4', 'Seksi Pemeliharaan Listrik', 'M01', '1000320011', 'CABLE NYY 4x16 MM2 0,6/1KV', 250, 'MTR', 110000, 'NO', false, 'R01'],
        ['Tonasa 3', 'Seksi Operasi Crusher', 'M02', '1000324490', 'HAMMER CRUSHER HIGH MANGANESE', 12, 'PCE', 4500000, 'OK', true, 'R02'],
    ];
    $materialRows = [];
    for ($index = 0; $index < 30; $index++) {
        [$plant, $unit, $mrp, $materialNo, $shortText, $quantity, $uom, $price, $bomStatus, $hasDrawing, $rif] = $materialReference[$index % count($materialReference)];
        $isCancelled = $listMode === 'cancel' || ($listMode === 'semua' && in_array($index % 8, [1, 3, 6], true));
        $materialRows[] = [
            'id' => 'material-reference-'.($index + 1),
            'year' => 2026,
            'plant' => $plant,
            'unit' => $unit,
            'mrp' => $mrp,
            'material_no' => $materialNo,
            'short_text' => $shortText,
            'quantity' => $quantity,
            'uom' => $uom,
            'price' => $price,
            'bom_status' => $listMode === 'cancel' ? 'NO' : $bomStatus,
            'has_drawing' => $hasDrawing,
            'rif' => $rif,
            'cancelled' => $isCancelled,
            'plant_section' => '—',
            'program' => '—',
            'equipment' => '—',
            'functional_location' => '—',
            'lifetime' => '—',
            'estimation' => '—',
            'order' => '—',
            'reservation' => '—',
            'pic_evaluator' => '—',
            'evaluation_note' => '—',
            'notes' => $index < 3 ? [['id' => 'initial-'.($index + 1), 'text' => 'Catatan awal material untuk ditindaklanjuti.', 'time' => 'Data referensi']] : [],
        ];
    }
@endphp

<section class="schedule-work-list material-work-list" data-material-work-list data-mode="{{ $listMode }}" aria-label="Daftar rencana kerja barang">
    <div class="bwl-toolbar">
        <label>Tahun<select class="form-select" data-year><option value="">Semua tahun</option><option selected>2026</option><option>2027</option></select></label>
        <label>Plant<select class="form-select" data-plant><option value="" selected>Semua plant</option><option>Tonasa 2</option><option>Tonasa 3</option><option>Tonasa 4</option><option>Tonasa 5</option><option>Power Plant</option></select></label>
        <label class="bwl-search">Search<span><i class="bi bi-search" aria-hidden="true"></i><input type="search" class="form-control" data-search placeholder="Cari unit kerja, material, MRP, atau status..."></span></label>
        <div class="bwl-toolbar-actions">
            @if ($listMode === 'semua')
                <button type="button" class="bwl-button bwl-button-light" data-toggle-detail aria-pressed="false"><i class="bi bi-layout-three-columns" aria-hidden="true"></i><span>Detail BOM</span></button>
            @endif
            <button type="button" class="bwl-button bwl-button-export" data-export title="Unduh data hasil filter dalam format CSV"><i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i>Export CSV</button>
            <button type="button" class="bwl-button bwl-button-primary" data-add><i class="bi bi-plus-lg" aria-hidden="true"></i>Tambah Ajuan</button>
        </div>
    </div>

    <div class="bwl-preview-note"><i class="bi bi-info-circle" aria-hidden="true"></i>Pratinjau frontend. Perubahan sementara akan kembali ke data awal saat halaman dimuat ulang.</div>
    <div class="bwl-feedback" data-feedback role="status" hidden></div>

    <div class="bwl-card">
        <div class="bwl-table-scroll" tabindex="0" role="region" aria-label="Tabel material, geser untuk melihat seluruh kolom">
            <table class="bwl-table material-table">
                <thead>
                    <tr>
                        <th scope="col">No.</th><th scope="col" data-material-detail hidden>Tahun</th><th scope="col">Plant</th><th scope="col">Unit Kerja</th><th scope="col">MRP</th>
                        <th scope="col">No. Material</th><th scope="col">Short Text Material</th><th scope="col">Quantity</th><th scope="col">UOM</th>
                        <th scope="col" class="bwl-number">Harga Satuan</th><th scope="col" class="bwl-number">Harga Total</th><th scope="col">Status BOM</th>
                        <th scope="col" data-material-detail hidden>Plant Section</th><th scope="col" data-material-detail hidden>Program Kerja</th><th scope="col" data-material-detail hidden>ID Equipment/BOM</th>
                        <th scope="col" data-material-detail hidden>Functional Location</th><th scope="col" data-material-detail hidden>Lifetime</th><th scope="col" data-material-detail hidden>Estimasi Waktu</th>
                        <th scope="col" data-material-detail hidden>Order</th><th scope="col" data-material-detail hidden>Reservasi</th><th scope="col" data-material-detail hidden>PIC Evaluator</th><th scope="col" data-material-detail hidden>Ket. Evaluator</th>
                        <th scope="col">Drawing</th><th scope="col">RIF</th><th scope="col">Edit</th><th scope="col">Cancel</th><th scope="col">Note</th>
                    </tr>
                </thead>
                <tbody data-rows></tbody>
            </table>
        </div>
        <div class="bwl-empty" data-empty hidden><i class="bi bi-search" aria-hidden="true"></i><strong>Tidak ada material yang cocok</strong><span>Ubah kata pencarian atau pilihan tahun dan plant.</span><button type="button" class="bwl-button bwl-button-light" data-reset>Reset Filter</button></div>
        <div class="bwl-footer">
            <span data-summary aria-live="polite"></span>
            <div class="bwl-pagination-controls"><label>Per halaman <select data-page-size><option selected>8</option><option>15</option><option>30</option></select></label><nav data-pagination aria-label="Halaman daftar material"></nav></div>
        </div>
    </div>
    <button type="button" class="bwl-button bwl-button-light" data-close-detail hidden><i class="bi bi-arrow-left" aria-hidden="true"></i>Kembali ke tampilan ringkas</button>

    <div class="modal fade bwl-modal" id="material-edit-modal" tabindex="-1" aria-labelledby="material-edit-title" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable"><div class="modal-content">
            <div class="modal-header"><div><h2 class="modal-title" id="material-edit-title">Tambah Ajuan Barang</h2><p>Isi identitas material dan estimasi kebutuhan.</p></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
            <div class="modal-body"><form id="material-edit-form" class="bwl-form">
                <label>Tahun<input class="form-control" name="year" type="number" min="2000" max="2100" required></label>
                <label>Plant<select class="form-select" name="plant"><option>Tonasa 2</option><option>Tonasa 3</option><option>Tonasa 4</option><option>Tonasa 5</option><option>Power Plant</option></select></label>
                <label class="bwl-span-2">Unit Kerja<input class="form-control" name="unit" maxlength="120" required></label>
                <label>MRP<input class="form-control" name="mrp" maxlength="20" required></label>
                <label>No. Material<input class="form-control" name="material_no" maxlength="40" required></label>
                <label class="bwl-span-2">Short Text Material<input class="form-control" name="short_text" maxlength="180" required></label>
                <label>Quantity<input class="form-control" name="quantity" type="number" min="0.01" max="1000000" step="0.01" required></label>
                <label>UOM<select class="form-select" name="uom"><option>PCE</option><option>PCS</option><option>BAG</option><option>MTR</option><option>LOT</option><option>SET</option><option>UNIT</option></select></label>
                <label>Harga Satuan (Rp)<input class="form-control" name="price" type="number" min="0" max="1000000000000" step="0.01" required></label>
                <label>Status BOM<select class="form-select" name="bom_status"><option>OK</option><option>NO</option></select></label>
                <label>RIF<input class="form-control" name="rif" maxlength="20"></label>
                <label class="bwl-check"><input type="checkbox" name="has_drawing" value="1">Drawing tersedia</label>
                <div class="bwl-form-total bwl-span-2"><span>Total Harga</span><output data-total>Rp 0,00</output></div>
            </form></div>
            <div class="modal-footer"><button type="button" class="bwl-button bwl-button-light" data-bs-dismiss="modal">Batal</button><button type="submit" form="material-edit-form" class="bwl-button bwl-button-primary"><i class="bi bi-check2" aria-hidden="true"></i>Simpan</button></div>
        </div></div>
    </div>

    <div class="modal fade bwl-modal" id="material-notes-modal" tabindex="-1" aria-labelledby="material-notes-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable"><div class="modal-content">
            <div class="modal-header"><div><h2 class="modal-title" id="material-notes-title">Catatan Material</h2><p data-notes-description></p></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
            <div class="modal-body"><div class="bwl-notes-list" data-notes-list></div><form id="material-note-form"><label for="material-note-input" class="form-label">Tambah catatan</label><textarea id="material-note-input" class="form-control" rows="3" maxlength="1000" placeholder="Tulis catatan material..." required></textarea></form></div>
            <div class="modal-footer"><button type="button" class="bwl-button bwl-button-light" data-bs-dismiss="modal">Tutup</button><button type="submit" form="material-note-form" class="bwl-button bwl-button-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i>Tambah Catatan</button></div>
        </div></div>
    </div>
</section>

@include('modules.schedule-shared.work-list-styles')
@include('modules.schedule-barang.partials.material-list-assets', ['materialRows' => $materialRows])

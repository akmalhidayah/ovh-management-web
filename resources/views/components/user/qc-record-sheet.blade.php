@props(['record'])

@php
    $signatureModalId = 'qcSignatureModal-' . uniqid();

    $fields = [
        ['label' => 'Report No.', 'value' => $record['report_no'] ?? ''],
        ['label' => 'OVH Plant', 'value' => $record['plant'] ?? ''],
        ['label' => 'Tahun', 'value' => $record['year'] ?? ''],
        ['label' => 'Unit', 'value' => $record['unit'] ?? ''],
        ['label' => 'Alat', 'value' => $record['equipment'] ?? ''],
        ['label' => 'Section', 'value' => $record['tag_num'] ?? ''],
        ['label' => 'Tgl. Mulai', 'value' => $record['start_date'] ?? ''],
        ['label' => 'Pekerjaan', 'value' => $record['job'] ?? ''],
        ['label' => 'Durasi (menit)', 'value' => $record['duration'] ?? ''],
    ];
@endphp

<section {{ $attributes->class(['qc-record-wrap qc-dynamic-record']) }} data-qc-record-form>
    <div class="qc-record-field-grid">
        @foreach ($fields as $field)
            <label class="qc-record-field {{ $field['label'] === 'Pekerjaan' ? 'wide' : '' }}">
                <span>{{ $field['label'] }}</span>
                <input type="text" class="form-control" value="{{ $field['value'] }}">
            </label>
        @endforeach
    </div>

    <div class="qc-checklist-table-wrap d-none d-lg-block">
        <table class="qc-checklist-table">
            <thead>
                <tr>
                    <th>Kategori</th>
                    <th>Item Pengecekan</th>
                    <th>Standar</th>
                    <th>Status</th>
                    <th>Catatan</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody data-qc-table-body>
                @foreach ($record['rows'] as $row)
                    <tr data-qc-item-row data-row-id="qc-row-{{ $loop->iteration }}">
                        <td><input type="text" class="form-control" value="{{ $row['category'] }}"></td>
                        <td><input type="text" class="form-control" value="{{ $row['item'] }}"></td>
                        <td><input type="text" class="form-control" value="{{ $row['standard'] }}"></td>
                        <td>
                            <div class="qc-status-toggle">
                                <label><input type="checkbox" name="qc_status_{{ $loop->iteration }}_ok" value="OK"><span>OK</span></label>
                                <label><input type="checkbox" name="qc_status_{{ $loop->iteration }}_not_ok" value="Not OK"><span>Not OK</span></label>
                            </div>
                        </td>
                        <td><textarea class="form-control" rows="1" placeholder="Catatan...">{{ $row['notes'] }}</textarea></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-light inspector-icon-action" title="Hapus item" data-qc-remove-row>
                                <i class="bi bi-trash3"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="qc-checklist-mobile d-lg-none" data-qc-mobile-list>
        @foreach ($record['rows'] as $row)
            <article class="qc-mobile-item" data-qc-mobile-row data-row-id="qc-row-{{ $loop->iteration }}">
                <div class="d-flex justify-content-between gap-3">
                    <label class="flex-grow-1">
                        <span>Kategori</span>
                        <input type="text" class="form-control" value="{{ $row['category'] }}">
                    </label>
                    <button type="button" class="btn btn-light inspector-icon-action mt-4" title="Hapus item" data-qc-remove-row>
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>
                <label>
                    <span>Item Pengecekan</span>
                    <input type="text" class="form-control" value="{{ $row['item'] }}">
                </label>
                <label>
                    <span>Standar</span>
                    <input type="text" class="form-control" value="{{ $row['standard'] }}">
                </label>
                <div>
                    <span class="qc-mobile-label">Status</span>
                    <div class="qc-status-toggle">
                        <label><input type="checkbox" name="qc_mobile_status_{{ $loop->iteration }}_ok" value="OK"><span>OK</span></label>
                        <label><input type="checkbox" name="qc_mobile_status_{{ $loop->iteration }}_not_ok" value="Not OK"><span>Not OK</span></label>
                    </div>
                </div>
                <label>
                    <span>Catatan</span>
                    <textarea class="form-control" rows="2" placeholder="Catatan...">{{ $row['notes'] }}</textarea>
                </label>
            </article>
        @endforeach
    </div>

    <div class="qc-add-row-area">
        <button type="button" class="btn btn-primary" data-qc-add-row>
            <i class="bi bi-plus-lg me-2"></i>Tambah Item
        </button>
    </div>

    <div class="qc-record-footer-grid">
        <label class="qc-record-note-box">
            <span>Catatan</span>
            <textarea class="form-control" rows="4" placeholder="Tuliskan catatan umum pemeriksaan..."></textarea>
        </label>
        <div class="qc-approval-box">
            <div class="qc-approval-title">Approval</div>
            <div class="qc-approval-grid">
                <label><span>Tanggal</span><input type="date" class="form-control"></label>
                <div class="qc-signature-trigger">
                    <span>*1 Diisi</span>
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#{{ $signatureModalId }}">
                        <i class="bi bi-vector-pen me-2"></i>Tanda Tangan
                    </button>
                </div>
                <label class="qc-approval-locked"><span>*2 Disetujui</span><input type="text" class="form-control" value="Terkunci" readonly></label>
                <label class="qc-approval-locked"><span>*3 Disetujui</span><input type="text" class="form-control" value="Terkunci" readonly></label>
            </div>
            <small>*1 Quality Control Personil, *2 atasan Quality Control, *3 manager bidang terkait.</small>
        </div>
    </div>

    <div class="modal fade" id="{{ $signatureModalId }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content qc-signature-modal" data-signature-pad>
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">Tanda Tangan QC</h5>
                        <small class="text-muted">Canvas dummy untuk simulasi tanda tangan.</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <canvas class="qc-signature-canvas" width="760" height="260" data-signature-canvas></canvas>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-signature-clear>Hapus</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Simpan Dummy</button>
                </div>
            </div>
        </div>
    </div>
</section>

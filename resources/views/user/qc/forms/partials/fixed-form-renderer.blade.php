@php
    use App\Support\QcTemplates\FixedQcTemplate;

    $type = FixedQcTemplate::normalizeType($selectedTemplate->template_type);
    $schema = FixedQcTemplate::schemaForTemplate($selectedTemplate);
    $approvalDefaults = $schema['approval_defaults'] ?? FixedQcTemplate::defaultApprovalDefaults($type);
    $signerName = auth()->user()?->name ?: 'User QC';
    $today = now()->toDateString();
    $draftHeader = $draftSubmission?->general_info ?? [];
    $draftBody = $draftSubmission?->body_data ?? [];
    $draftApproval = $draftSubmission?->approval_data ?? [];
    $oldBody = old('body', $draftBody);
    $oldHeader = old('header', $draftHeader);
    $oldApproval = old('approval', $draftApproval);
    $methodValues = old('body.methods', $draftBody['methods'] ?? []);
    $checkStepValues = old('body.check_steps', $draftBody['check_steps'] ?? []);
    $submittedWelderRows = collect(old('body.welder_rows', $draftBody['welder_rows'] ?? []))->values();
    $submittedResultRows = collect(old('body.result_rows', $draftBody['result_rows'] ?? []))->values();
    $welderRows = collect($schema['welder_rows'] ?? [])
        ->values()
        ->map(function ($templateRow, $index) use ($submittedWelderRows) {
            $submittedRow = $submittedWelderRows->get($index, []);

            return array_merge($templateRow, $submittedRow, [
                'no' => $templateRow['no'] ?? $submittedRow['no'] ?? $index + 1,
            ]);
        })
        ->all();
    $resultRows = collect($schema['result_rows'] ?? [])
        ->values()
        ->map(function ($templateRow, $index) use ($submittedResultRows) {
            $submittedRow = $submittedResultRows->get($index, []);

            return array_merge($templateRow, [
                'no' => $templateRow['no'] ?? $submittedRow['no'] ?? $index + 1,
                'deskripsi' => $templateRow['deskripsi'] ?? $submittedRow['deskripsi'] ?? '',
                'status' => $submittedRow['status'] ?? null,
                'keterangan' => $submittedRow['keterangan'] ?? $templateRow['keterangan'] ?? '',
            ]);
        })
        ->all();
    $generalRows = old('body.general_rows', ($draftBody['general_rows'] ?? []) ?: ($schema['rows'] ?? []));
    $noteValue = old('note', $draftSubmission?->note ?? '');
    $masterDataRecords = collect($activeQcMasterDataRecords ?? []);
    $selectedMasterDataId = old('header.master_data_record_id', $oldHeader['master_data_record_id'] ?? null);
    if (! $selectedMasterDataId && (! empty($oldHeader['name_equipment']) || ! empty($oldHeader['functional_location']))) {
        $selectedMasterDataId = optional($masterDataRecords->first(function ($record) use ($oldHeader) {
            return (
                $record->description === ($oldHeader['name_equipment'] ?? null)
                || $record->func_location === ($oldHeader['functional_location'] ?? null)
            ) && (! isset($oldHeader['id_equipment']) || $record->equipment_no === $oldHeader['id_equipment']);
        }))->id;
    }
    $selectedMasterDataRecord = $selectedMasterDataId ? $masterDataRecords->firstWhere('id', (int) $selectedMasterDataId) : null;
    $areaOptions = $masterDataRecords->pluck('area')->filter()->unique()->sort()->values();
    $selectedArea = old('header.area', $oldHeader['area'] ?? ($selectedMasterDataRecord?->area ?? ''));
    if ($selectedArea && ! $areaOptions->contains($selectedArea)) {
        $areaOptions->push($selectedArea);
    }
    $areaOptions = $areaOptions->sort()->values();
    $masterDataOptions = $masterDataRecords->map(fn ($record) => [
        'id' => (string) $record->id,
        'plant' => (string) ($record->plant ?? ''),
        'functionalLocation' => (string) ($record->func_location ?? ''),
        'tahun' => (string) ($record->year ?? ''),
        'tagNum' => (string) ($record->section_no ?? ''),
        'area' => (string) ($record->area ?? ''),
        'idEquipment' => (string) ($record->equipment_no ?? ''),
        'nameEquipment' => (string) ($record->description ?? ''),
        'label' => trim(($record->description ?: '-') . ' - ' . ($record->equipment_no ?: '-') . ' (' . ($record->area ?: '-') . ')'),
    ])->values();
    $headerRows = FixedQcTemplate::headerRows($type);
    $headerRows = collect($headerRows)
        ->map(fn ($row) => array_values(array_filter($row, fn ($fieldKey) => $fieldKey !== 'area')))
        ->filter(fn ($row) => $row !== [])
        ->values()
        ->all();
    foreach ($headerRows as $rowIndex => $row) {
        $nameEquipmentIndex = array_search('name_equipment', $row, true);
        if ($nameEquipmentIndex !== false) {
            array_splice($headerRows[$rowIndex], $nameEquipmentIndex, 0, 'area');
            break;
        }
    }
    $headerFieldMap = collect(FixedQcTemplate::headerFields($type))->keyBy('key');
    $durationMinuteOptions = range(5, 60, 5);
@endphp

<div class="qc-user-form" data-fixed-qc-form>
    <section class="inspector-panel qc-form-card">
        <div class="qc-form-card-head">
            <div>
                <span>{{ FixedQcTemplate::templateTypeLabel($type) }}</span>
                <h2>{{ $selectedTemplate->name }}</h2>
                @if ($selectedTemplate->description)
                    <p>{{ $selectedTemplate->description }}</p>
                @endif
            </div>
            <div class="qc-form-code">
                <strong>{{ $selectedTemplate->code ?: 'Tanpa kode' }}</strong>
                <span>Versi {{ $selectedTemplate->version }}</span>
            </div>
        </div>
    </section>

    <section class="inspector-panel qc-form-card">
        <div class="qc-form-section-title">
            <h3>Header</h3>
        </div>
        <div class="qc-user-field-grid">
            @foreach ($headerRows as $row)
                @foreach ($row as $fieldKey)
                    @php
                        $field = $headerFieldMap[$fieldKey];
                        $fieldValue = $oldHeader[$fieldKey] ?? ($fieldKey === 'doc_number' ? ($autoDocNumber ?? '') : ($fieldKey === 'inspector_qc' ? $signerName : ''));
                        $isAutoFilledByMasterData = in_array($fieldKey, ['plant', 'tahun', 'tag_num', 'functional_location', 'id_equipment'], true);
                    @endphp
                    <label class="qc-user-field">
                        <span>{{ $field['label'] }}</span>
                        @if ($fieldKey === 'name_equipment')
                            <input type="hidden" name="header[name_equipment]" value="{{ $fieldValue }}" data-header-input="name_equipment">
                            <select name="header[master_data_record_id]" class="form-select" data-master-data-select>
                                <option value="">Pilih Area terlebih dahulu</option>
                            </select>
                        @elseif ($fieldKey === 'area')
                            <input type="{{ $field['type'] }}"
                                   name="header[{{ $fieldKey }}]"
                                   value="{{ $selectedArea }}"
                                   class="form-control"
                                   data-header-input="{{ $fieldKey }}"
                                   placeholder="Pilih area di bagian atas"
                                   readonly>
                        @elseif ($fieldKey === 'durasi')
                            <input type="text"
                                   name="header[durasi]"
                                   value="{{ $fieldValue }}"
                                   class="form-control"
                                   data-header-input="durasi"
                                   list="qc-duration-minute-options"
                                   inputmode="numeric"
                                   placeholder="Contoh: 30">
                        @else
                            <input type="{{ $field['type'] }}"
                                   name="header[{{ $fieldKey }}]"
                                   value="{{ $fieldValue }}"
                                   class="form-control"
                                   data-header-input="{{ $fieldKey }}"
                                   @if (in_array($fieldKey, ['doc_number', 'inspector_qc'], true) || $isAutoFilledByMasterData) readonly @endif>
                        @endif
                    </label>
                @endforeach
            @endforeach
        </div>
        <datalist id="qc-duration-minute-options">
            @foreach ($durationMinuteOptions as $minutes)
                <option value="{{ $minutes }}">{{ $minutes }} menit</option>
            @endforeach
        </datalist>
    </section>

    @if ($type === FixedQcTemplate::TYPE_WELDING)
        <section class="inspector-panel qc-form-card">
            <div class="qc-form-section-title">
                <h3>Metode QC dan Pengecekan ke</h3>
            </div>
            <div class="qc-method-check-grid">
                <div class="qc-user-table-wrap">
                    <table class="qc-user-checklist-table qc-user-fixed-table qc-method-table">
                        <thead>
                            <tr><th colspan="{{ count(FixedQcTemplate::defaultMethods()) }}">Metode QC</th></tr>
                            <tr>
                                @foreach (FixedQcTemplate::defaultMethods() as $method)
                                    <th>{{ $method }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                @foreach (FixedQcTemplate::defaultMethods() as $method)
                                    <td class="text-center">
                                        <input type="checkbox" name="body[methods][]" value="{{ $method }}" @checked(in_array($method, $methodValues, true))>
                                    </td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="qc-user-table-wrap">
                    <table class="qc-user-checklist-table qc-user-fixed-table qc-check-step-table">
                        <thead>
                            <tr><th colspan="{{ count(FixedQcTemplate::defaultCheckSteps()) }}">Pengecekan ke</th></tr>
                            <tr>
                                @foreach (FixedQcTemplate::defaultCheckSteps() as $step)
                                    <th>{{ $step }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                @foreach (FixedQcTemplate::defaultCheckSteps() as $step)
                                    <td class="text-center">
                                        <input type="checkbox" name="body[check_steps][]" value="{{ $step }}" @checked(in_array($step, $checkStepValues, true))>
                                    </td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="inspector-panel qc-form-card">
            <div class="qc-form-section-title">
                <h3>Tabel Welder</h3>
            </div>
            <div class="qc-user-table-wrap">
                <table class="qc-user-checklist-table qc-user-fixed-table qc-welding-welder-table">
                    <colgroup>
                        <col style="width: 6%">
                        <col style="width: 15%">
                        <col style="width: 16%">
                        <col style="width: 15%">
                        <col style="width: 15%">
                        <col style="width: 13%">
                        <col style="width: 20%">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Welder</th>
                            <th>Posisi Pengelasan</th>
                            <th>Diameter Electrode</th>
                            <th>Electrode/Filter</th>
                            <th>Amper</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody data-user-welder-list>
                        @forelse ($welderRows as $index => $row)
                            <tr data-user-welder-row>
                                <td><input type="text" name="body[welder_rows][{{ $index }}][no]" value="{{ $row['no'] ?? $loop->iteration }}" class="form-control form-control-sm text-center" readonly></td>
                                <td><input type="text" name="body[welder_rows][{{ $index }}][nama_welder]" value="{{ $row['nama_welder'] ?? '' }}" class="form-control form-control-sm"></td>
                                <td><input type="text" name="body[welder_rows][{{ $index }}][posisi_pengelasan]" value="{{ $row['posisi_pengelasan'] ?? '' }}" class="form-control form-control-sm"></td>
                                <td><input type="text" name="body[welder_rows][{{ $index }}][diameter_electrode]" value="{{ $row['diameter_electrode'] ?? '' }}" class="form-control form-control-sm"></td>
                                <td><input type="text" name="body[welder_rows][{{ $index }}][electrode_filter]" value="{{ $row['electrode_filter'] ?? '' }}" class="form-control form-control-sm"></td>
                                <td><input type="text" name="body[welder_rows][{{ $index }}][amper]" value="{{ $row['amper'] ?? '' }}" class="form-control form-control-sm"></td>
                                <td><input type="text" name="body[welder_rows][{{ $index }}][keterangan]" value="{{ $row['keterangan'] ?? '' }}" class="form-control form-control-sm"></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-3">Belum ada row welder dari template admin.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="inspector-panel qc-form-card">
            <div class="qc-form-section-title">
                <h3>Tabel Hasil QC Welding</h3>
            </div>
            <div class="qc-user-table-wrap">
                <table class="qc-user-checklist-table qc-user-fixed-table qc-welding-result-table">
                    <colgroup>
                        <col style="width: 6%">
                        <col style="width: 28%">
                        <col style="width: 12%">
                        <col style="width: 15%">
                        <col style="width: 14%">
                        <col style="width: 25%">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Deskripsi</th>
                            <th>Baik</th>
                            <th>Perlu Perbaikan</th>
                            <th>Tidak Layak</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody data-user-result-list>
                        @forelse ($resultRows as $index => $row)
                            <tr data-user-result-row>
                                <td><input type="text" name="body[result_rows][{{ $index }}][no]" value="{{ $row['no'] ?? $loop->iteration }}" class="form-control form-control-sm text-center" readonly></td>
                                <td><input type="text" name="body[result_rows][{{ $index }}][deskripsi]" value="{{ $row['deskripsi'] ?? '' }}" class="form-control form-control-sm" readonly></td>
                                @foreach (['Baik', 'Perlu Perbaikan', 'Tidak Layak'] as $status)
                                    <td class="text-center"><input type="radio" name="body[result_rows][{{ $index }}][status]" value="{{ $status }}" @checked(($row['status'] ?? null) === $status) @if ($status === 'Baik') data-qc-ok-status @else data-qc-not-ok-status @endif></td>
                                @endforeach
                                <td><input type="text" name="body[result_rows][{{ $index }}][keterangan]" value="{{ $row['keterangan'] ?? '' }}" class="form-control form-control-sm"></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">Belum ada row hasil QC dari template admin.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @elseif ($type === FixedQcTemplate::TYPE_CASTABLE)
        @include('user.qc.forms.partials.fixed-castable-body', ['oldBody' => $oldBody])
    @elseif ($type === FixedQcTemplate::TYPE_BRICS)
        @include('user.qc.forms.partials.fixed-brics-body', ['oldBody' => $oldBody])
    @else
        <section class="inspector-panel qc-form-card">
            <div class="qc-user-table-wrap">
                <table class="qc-user-checklist-table">
                    <thead>
                        <tr>
                            <th>Item Pengecekan</th>
                            <th>Standar</th>
                            <th>Actual</th>
                            <th>Status</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($generalRows as $index => $row)
                            <tr>
                                <td><input type="text" name="body[general_rows][{{ $index }}][item_pengecekan]" value="{{ $row['item_pengecekan'] ?? '' }}" class="form-control form-control-sm"></td>
                                <td><input type="text" name="body[general_rows][{{ $index }}][standar]" value="{{ $row['standar'] ?? '' }}" class="form-control form-control-sm"></td>
                                <td><input type="text" name="body[general_rows][{{ $index }}][actual]" value="{{ $row['actual'] ?? $row['actual_default'] ?? '' }}" class="form-control form-control-sm"></td>
                                <td>
                                    <div class="qc-user-status-inline">
                                        <label><input type="radio" name="body[general_rows][{{ $index }}][status]" value="Ok" @checked(($row['status'] ?? null) === 'Ok') data-qc-ok-status> <span>Ok</span></label>
                                        <label><input type="radio" name="body[general_rows][{{ $index }}][status]" value="Not Ok" @checked(($row['status'] ?? null) === 'Not Ok') data-qc-not-ok-status> <span>Not Ok</span></label>
                                    </div>
                                </td>
                                <td><textarea name="body[general_rows][{{ $index }}][catatan]" class="form-control qc-user-table-note">{{ $row['catatan'] ?? '' }}</textarea></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">Belum ada row default.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    @if ($type === FixedQcTemplate::TYPE_CASTABLE)
        <section class="inspector-panel qc-form-card">
            <div class="qc-form-section-title"><h3>Catatan Form QC</h3></div>
            <label class="qc-user-note-box">
                <span>Catatan Form QC Castable</span>
                <textarea class="form-control" name="note" rows="4" placeholder="Tulis catatan umum form QC Castable">{{ $noteValue }}</textarea>
            </label>
        </section>
    @else
        <section class="inspector-panel qc-form-card">
            <div class="qc-form-section-title"><h3>Catatan</h3></div>
            <label class="qc-user-note-box">
                <span>Catatan</span>
                <textarea class="form-control" name="note" rows="4" placeholder="Tulis catatan umum pemeriksaan">{{ $noteValue }}</textarea>
            </label>
        </section>
    @endif

    <section class="inspector-panel qc-form-card">
        <div class="qc-form-section-title"><h3>Lampiran Foto/Gambar</h3></div>
        <div class="qc-attachment-grid">
            @foreach ([
                'foto_before' => 'Foto Before',
                'foto_after' => 'Foto After',
                'dokumen_pendukung' => 'Dokumen Pendukung',
            ] as $attachmentKey => $attachmentLabel)
                <div class="qc-upload-box" data-upload-box data-upload-type="image">
                    <div class="qc-upload-box-head">
                        <div>
                            <strong>{{ $attachmentLabel }}</strong>
                            <span>Hanya JPG atau PNG. Bisa memilih beberapa file gambar.</span>
                        </div>
                        <i class="bi bi-images"></i>
                    </div>
                    <input type="file" class="form-control" name="attachments[{{ $attachmentKey }}][]" data-upload-input accept=".jpg,.jpeg,.png,image/jpeg,image/png" multiple>
                    <div class="qc-upload-message" data-upload-message></div>
                    <div class="qc-upload-preview" data-upload-preview></div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="inspector-panel qc-form-card">
        <div class="qc-form-section-title"><h3>Approval Footer</h3></div>
        <p class="text-muted">Final Check dapat dicentang setelah pemeriksaan selesai.</p>
        <label class="d-inline-flex align-items-center gap-2 mb-2">
            <input type="checkbox" name="body[final_check]" value="1" @checked((bool) ($oldBody['final_check'] ?? false)) data-final-check>
            <strong>Final Check</strong>
        </label>
        @php
            $approvalColumns = FixedQcTemplate::approvalColumns($type);
        @endphp
        <div class="qc-user-approval-grid" style="--qc-approval-columns: {{ count($approvalColumns) }}">
            @foreach ($approvalColumns as $column)
                @php
                    $isInspector = ($column['role'] ?? null) === 'QC Inspektor';
                    $defaultApprovalName = $approvalDefaults[$column['key']]['name'] ?? '';
                    $approvalName = $oldApproval[$column['key']]['name'] ?? ($defaultApprovalName ?: ($isInspector ? $signerName : ''));
                @endphp
                <div class="qc-user-approval-box {{ $isInspector ? '' : 'is-locked' }}" data-signature-card="{{ $column['key'] }}" @if ($isInspector) data-qc-inspector-approval @endif>
                    <div class="qc-approval-label-row">
                        <strong>{{ $column['label'] }}</strong>
                    </div>
                    <small class="text-muted d-block mb-2">{{ $column['group'] }}</small>
                    <input type="text" class="form-control mb-2" name="approval[{{ $column['key'] }}][name]" value="{{ $approvalName }}" placeholder="Nama">
                    @if ($isInspector)
                        <input type="date" class="form-control mb-2" name="approval[{{ $column['key'] }}][date]" value="{{ $oldApproval[$column['key']]['date'] ?? $today }}" data-inspector-approval-control>
                    @else
                        <input type="date" class="form-control mb-2" name="approval[{{ $column['key'] }}][date]" value="{{ $oldApproval[$column['key']]['date'] ?? '' }}" disabled>
                    @endif
                    <input type="hidden" name="approval[{{ $column['key'] }}][signature]" value="{{ $oldApproval[$column['key']]['signature'] ?? '' }}" data-signature-input>
                    <input type="file" name="approval_signature_files[{{ $column['key'] }}]" accept="image/png,image/jpeg" class="d-none" data-signature-file-input>
                    <input type="hidden" name="approval[{{ $column['key'] }}][role]" value="{{ $column['label'] }}">
                    <input type="hidden" name="approval[{{ $column['key'] }}][signed_at]" value="{{ $oldApproval[$column['key']]['signed_at'] ?? '' }}" data-signature-time-input>
                    @if ($isInspector)
                        <div class="qc-signature-empty" data-signature-empty>
                            <i class="bi bi-pen"></i>
                            <span>Belum ditandatangani</span>
                        </div>
                        <div class="qc-signature-result d-none" data-signature-result>
                            <img alt="Preview tanda tangan" data-signature-preview>
                            <div>
                                <strong data-signature-signer>{{ $column['label'] }}</strong>
                                <span>{{ $column['group'] }}</span>
                                <small data-signature-time></small>
                            </div>
                        </div>
                        <div class="qc-signature-actions mt-2">
                            <button type="button" class="btn btn-outline-primary" data-signature-open data-signature-label="{{ $column['label'] }}" data-inspector-approval-control>
                                <i class="bi bi-pen me-1"></i><span data-signature-button-label>Tanda Tangan</span>
                            </button>
                            <button type="button" class="btn btn-outline-danger d-none" data-signature-remove>Hapus</button>
                        </div>
                    @else
                        <div class="qc-signature-locked">
                            <i class="bi bi-lock"></i>
                            <span>Tanda tangan terkunci.</span>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    <div class="modal fade" id="qcSignatureModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content qc-signature-modal">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">Tanda Tangan</h5>
                        <small class="text-muted" data-signature-modal-label></small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <canvas class="qc-signature-canvas" width="900" height="280" data-signature-canvas></canvas>
                    <div class="qc-signature-help">Gunakan mouse atau sentuhan layar untuk membuat tanda tangan.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-signature-clear>Clear</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" data-signature-save>Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <section class="inspector-panel qc-form-actions-card">
        <div>
            <h3>Action Form</h3>
            <p>Simpan draft bisa belum lengkap. Submit wajib header, body, status checklist, dan Final Check lengkap.</p>
        </div>
        <div class="qc-form-actions">
            <button type="submit" name="action" value="draft" class="btn btn-primary" formnovalidate>Simpan Draft</button>
            <button type="submit" name="action" value="submit" class="btn btn-success">Submit QC</button>
        </div>
    </section>
</div>

@push('scripts')
    <script>
        (() => {
            const masterDataSelect = document.querySelector('[data-master-data-select]');
            const areaSelect = document.querySelector('[data-master-area-select]');
            const masterDataOptions = @json($masterDataOptions);
            const selectedMasterDataId = @json((string) ($selectedMasterDataId ?? ''));

            const setHeaderValue = (key, value) => {
                const input = document.querySelector(`[data-header-input="${key}"]`);
                if (input) input.value = value || '';
            };

            const clearMasterDataHeader = () => {
                ['plant', 'tahun', 'functional_location', 'tag_num', 'id_equipment', 'name_equipment'].forEach((key) => {
                    setHeaderValue(key, '');
                });
            };

            const filterMasterDataOptions = () => {
                const area = areaSelect?.value || '';
                if (!masterDataSelect) return;

                setHeaderValue('area', area);

                const currentValue = masterDataSelect.value || selectedMasterDataId;
                masterDataSelect.innerHTML = '';

                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = area ? 'Pilih Name Equipment' : 'Pilih Area terlebih dahulu';
                masterDataSelect.appendChild(placeholder);

                masterDataOptions
                    .filter((record) => area && record.area === area)
                    .forEach((record) => {
                        const option = document.createElement('option');
                        option.value = record.id;
                        option.textContent = record.label;
                        option.dataset.plant = record.plant;
                        option.dataset.functionalLocation = record.functionalLocation;
                        option.dataset.tahun = record.tahun;
                        option.dataset.tagNum = record.tagNum;
                        option.dataset.area = record.area;
                        option.dataset.idEquipment = record.idEquipment;
                        option.dataset.nameEquipment = record.nameEquipment;
                        masterDataSelect.appendChild(option);
                    });

                masterDataSelect.disabled = !area;

                if (currentValue && masterDataSelect.querySelector(`option[value="${CSS.escape(currentValue)}"]`)) {
                    masterDataSelect.value = currentValue;
                } else {
                    clearMasterDataHeader();
                }
            };

            const syncMasterDataHeader = () => {
                const option = masterDataSelect?.selectedOptions?.[0];
                if (!option || !option.value) {
                    clearMasterDataHeader();
                    return;
                }

                setHeaderValue('plant', option.dataset.plant);
                setHeaderValue('tahun', option.dataset.tahun);
                setHeaderValue('functional_location', option.dataset.functionalLocation);
                setHeaderValue('tag_num', option.dataset.tagNum);
                setHeaderValue('area', option.dataset.area);
                setHeaderValue('id_equipment', option.dataset.idEquipment);
                setHeaderValue('name_equipment', option.dataset.nameEquipment);
            };

            areaSelect?.addEventListener('change', () => {
                filterMasterDataOptions();
                syncMasterDataHeader();
            });
            masterDataSelect?.addEventListener('change', syncMasterDataHeader);
            filterMasterDataOptions();
            syncMasterDataHeader();

        })();
    </script>
@endpush

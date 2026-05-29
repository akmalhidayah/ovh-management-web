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
    $selectedMasterDataId = old('header.master_data_record_id', request('master_data_record_id', $oldHeader['master_data_record_id'] ?? null));
    if (! $selectedMasterDataId && (! empty($oldHeader['tag_num']) || ! empty($oldHeader['name_equipment']) || ! empty($oldHeader['functional_location']))) {
        $selectedMasterDataId = optional($masterDataRecords->first(function ($record) use ($oldHeader) {
            return (
                $record->section_no === ($oldHeader['tag_num'] ?? null)
                || $record->description === ($oldHeader['name_equipment'] ?? null)
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
    if (! $selectedArea && $areaOptions->isNotEmpty()) {
        $selectedArea = $areaOptions->first();
    }
    $masterDataOptions = $masterDataRecords->map(fn ($record) => [
        'id' => (string) $record->id,
        'plant' => (string) ($record->plant ?? ''),
        'functionalLocation' => (string) ($record->func_location ?? ''),
        'tahun' => (string) ($record->year ?? ''),
        'tagNum' => (string) ($record->section_no ?? ''),
        'area' => (string) ($record->area ?? ''),
        'idEquipment' => (string) ($record->equipment_no ?? ''),
        'nameEquipment' => (string) ($record->description ?? ''),
        'label' => trim(($record->section_no ?: '-') . ' - ' . ($record->description ?: '-') . ' (' . ($record->equipment_no ?: '-') . ')'),
    ])->values();
    $organizationSections = collect($activeOrganizationSections ?? []);
    $selectedOrganizationSection = old('header.unit_kerja', $oldHeader['unit_kerja'] ?? '');
    $organizationSectionOptions = $organizationSections
        ->map(fn ($section, $index) => [
            'id' => (string) ($section->id ?? $index),
            'department' => (string) ($section->department ?? ''),
            'unitKerja' => (string) ($section->unit_kerja ?? ''),
            'section' => (string) ($section->section ?? ''),
        ])
        ->filter(fn ($section) => $section['section'] !== '')
        ->sortBy(fn ($section) => implode('|', [$section['department'], $section['unitKerja'], $section['section']]))
        ->values();
    if ($selectedOrganizationSection !== '' && ! $organizationSectionOptions->contains(fn ($section) => $section['section'] === $selectedOrganizationSection)) {
        $organizationSectionOptions->push([
            'id' => (string) old('header.organization_section_id', $oldHeader['organization_section_id'] ?? ''),
            'department' => (string) old('header.department', $oldHeader['department'] ?? ''),
            'unitKerja' => (string) old('header.work_unit', $oldHeader['work_unit'] ?? ''),
            'section' => (string) $selectedOrganizationSection,
        ]);
    }
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
                        $isAutoFilledByMasterData = in_array($fieldKey, ['plant', 'tahun', 'functional_location', 'id_equipment', 'name_equipment'], true);
                    @endphp
                    <label class="qc-user-field">
                        <span>{{ $field['label'] }}</span>
                        @if ($fieldKey === 'tag_num')
                            <input type="hidden" name="header[tag_num]" value="{{ $fieldValue }}" data-header-input="tag_num">
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
                        @elseif ($fieldKey === 'unit_kerja')
                            <input type="hidden" name="header[department]" value="{{ old('header.department', $oldHeader['department'] ?? '') }}" data-header-input="department">
                            <input type="hidden" name="header[work_unit]" value="{{ old('header.work_unit', $oldHeader['work_unit'] ?? '') }}" data-header-input="work_unit">
                            <input type="hidden" name="header[organization_section_id]" value="{{ old('header.organization_section_id', $oldHeader['organization_section_id'] ?? '') }}" data-header-input="organization_section_id">
                            <select name="header[unit_kerja]" class="form-select" data-organization-section-select>
                                <option value="">Pilih Unit Kerja</option>
                                @foreach ($organizationSectionOptions as $sectionOption)
                                    <option value="{{ $sectionOption['section'] }}"
                                            data-id="{{ $sectionOption['id'] }}"
                                            data-department="{{ $sectionOption['department'] }}"
                                            data-work-unit="{{ $sectionOption['unitKerja'] }}"
                                            @selected((string) $selectedOrganizationSection === (string) $sectionOption['section'])>
                                        {{ $sectionOption['section'] }}
                                    </option>
                                @endforeach
                            </select>
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
        @include('user.qc.forms.partials.fixed-welding-body')
    @elseif ($type === FixedQcTemplate::TYPE_CASTABLE)
        @include('user.qc.forms.partials.fixed-castable-body', ['oldBody' => $oldBody])
    @elseif ($type === FixedQcTemplate::TYPE_BRICS)
        @include('user.qc.forms.partials.fixed-brics-body', ['oldBody' => $oldBody])
    @else
        @include('user.qc.forms.partials.fixed-general-body')
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
                @php
                    $temporaryAttachmentTokens = old("temporary_attachments.{$attachmentKey}", []);
                    $temporaryAttachmentMetas = collect($temporaryAttachmentTokens)
                        ->map(fn ($token) => ['token' => $token] + (session("qc_temporary_attachments.{$token}") ?? []))
                        ->filter(fn ($attachment) => isset($attachment['original_name']));
                    $isRequiredAttachment = in_array($attachmentKey, ['foto_before', 'foto_after'], true);
                    $hasExistingAttachment = ($draftSubmission ?? null)
                        ? $draftSubmission->attachments->where('field_key', $attachmentKey)->isNotEmpty()
                        : false;
                    $uploadInputId = "qc-upload-{$attachmentKey}";
                    $cameraInputId = "qc-camera-{$attachmentKey}";
                @endphp
                <div class="qc-upload-box" data-upload-box data-upload-type="image">
                    <div class="qc-upload-box-head">
                        <div>
                            <strong>{{ $attachmentLabel }} @if ($isRequiredAttachment)<span class="text-danger">*</span>@endif</strong>
                            <span>Hanya JPG atau PNG. Bisa memilih beberapa file gambar. {{ $isRequiredAttachment ? 'Wajib diisi saat submit.' : 'Opsional.' }}</span>
                        </div>
                        <i class="bi bi-images"></i>
                    </div>
                    <input
                        id="{{ $uploadInputId }}"
                        type="file"
                        class="visually-hidden"
                        name="attachments[{{ $attachmentKey }}][]"
                        data-upload-input
                        accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                        multiple
                    >
                    @if ($isRequiredAttachment)
                        <input
                            id="{{ $cameraInputId }}"
                            type="file"
                            class="visually-hidden"
                            data-camera-input
                            accept="image/*"
                            capture="environment"
                        >
                    @endif
                    <div class="qc-upload-actions">
                        @if ($isRequiredAttachment)
                            <div class="dropdown">
                                <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-camera me-1"></i>Upload Foto
                                </button>
                                <div class="dropdown-menu">
                                    <label class="dropdown-item" for="{{ $cameraInputId }}">
                                        <i class="bi bi-camera me-2"></i>Ambil Foto
                                    </label>
                                    <label class="dropdown-item" for="{{ $uploadInputId }}">
                                        <i class="bi bi-images me-2"></i>Pilih dari Galeri/File
                                    </label>
                                </div>
                            </div>
                        @else
                            <label class="btn btn-sm btn-outline-primary" for="{{ $uploadInputId }}">
                                <i class="bi bi-upload me-1"></i>Pilih File
                            </label>
                        @endif
                    </div>
                    <div class="qc-upload-message" data-upload-message></div>
                    <div class="qc-upload-preview" data-upload-preview></div>
                    @if ($temporaryAttachmentMetas->isNotEmpty())
                        <div class="mt-2 small text-muted">
                            <strong class="d-block text-body">Lampiran tersimpan sementara:</strong>
                            @foreach ($temporaryAttachmentMetas as $attachment)
                                <input type="hidden" name="temporary_attachments[{{ $attachmentKey }}][]" value="{{ $attachment['token'] }}">
                                <div><i class="bi bi-check2-circle me-1 text-success"></i>{{ $attachment['original_name'] }}</div>
                            @endforeach
                        </div>
                    @endif
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
            $approvalColumns = FixedQcTemplate::approvalColumnsWithDefaults($type, $approvalDefaults, $oldApproval);
        @endphp
        <div class="qc-user-approval-grid" style="--qc-approval-columns: {{ count($approvalColumns) }}">
            @foreach ($approvalColumns as $column)
                @php
                    $isInspector = ($column['role'] ?? null) === 'QC Inspektor';
                    $defaultApprovalName = $approvalDefaults[$column['key']]['name'] ?? '';
                    $approvalName = $oldApproval[$column['key']]['name'] ?? ($defaultApprovalName ?: ($isInspector ? $signerName : ''));
                    $isUnitKerjaApprover = in_array($type, [FixedQcTemplate::TYPE_GENERAL, FixedQcTemplate::TYPE_WELDING], true)
                        && ($column['role'] ?? null) === 'Unit Kerja';
                    $approvalGroup = $column['group'];
                    $approvalLabel = $isUnitKerjaApprover
                        ? ($oldHeader['unit_kerja'] ?? ($selectedOrganizationSection ?: $column['label']))
                        : $column['label'];
                    $groupEditable = FixedQcTemplate::approvalGroupIsEditable($type, $column['key']);
                    $labelEditable = FixedQcTemplate::approvalLabelIsEditable($type, $column['key']);
                    $editablePlaceholder = FixedQcTemplate::approvalEditablePlaceholder($type, $column['key']);
                    $approvalGroupInput = FixedQcTemplate::approvalEditableValue($type, $column['key'], $approvalGroup);
                    $approvalLabelInput = FixedQcTemplate::approvalEditableValue($type, $column['key'], $approvalLabel);
                @endphp
                <div class="qc-user-approval-box {{ $isInspector ? '' : 'is-locked' }}" data-signature-card="{{ $column['key'] }}" @if ($isInspector) data-qc-inspector-approval @endif>
                    <div class="qc-approval-label-row">
                        @if ($groupEditable)
                            <strong>{{ $approvalLabel }}</strong>
                            <input type="text"
                                   class="form-control form-control-sm mt-2"
                                   name="approval[{{ $column['key'] }}][group]"
                                   value="{{ $approvalGroupInput }}"
                                   placeholder="{{ $editablePlaceholder }}">
                        @elseif ($labelEditable)
                            <small class="text-muted d-block">{{ $approvalGroup }}</small>
                            <input type="text"
                                   class="form-control form-control-sm mt-2 fw-semibold"
                                   name="approval[{{ $column['key'] }}][label]"
                                   value="{{ $approvalLabelInput }}"
                                   placeholder="{{ $editablePlaceholder }}">
                        @else
                            <small class="text-muted d-block">{{ $approvalGroup }}</small>
                            <strong @if ($isUnitKerjaApprover) data-unit-kerja-approval-label @endif>{{ $approvalLabel }}</strong>
                            @if ($isUnitKerjaApprover)
                                <input type="hidden" name="approval[{{ $column['key'] }}][label]" value="{{ $approvalLabel }}" data-unit-kerja-approval-label-input>
                            @endif
                        @endif
                    </div>
                    <input
                        type="text"
                        class="form-control mb-2"
                        name="approval[{{ $column['key'] }}][name]"
                        value="{{ $approvalName }}"
                        placeholder="Nama penanda tangan"
                    >
                    @if ($isInspector)
                        <input type="date" class="form-control mb-2" name="approval[{{ $column['key'] }}][date]" value="{{ $oldApproval[$column['key']]['date'] ?? $today }}" data-inspector-approval-control>
                    @else
                        <input type="date" class="form-control mb-2" name="approval[{{ $column['key'] }}][date]" value="{{ $oldApproval[$column['key']]['date'] ?? '' }}" disabled>
                    @endif
                    <input type="hidden" name="approval[{{ $column['key'] }}][signature]" value="{{ $oldApproval[$column['key']]['signature'] ?? '' }}" data-signature-input>
                    <input type="file" name="approval_signature_files[{{ $column['key'] }}]" accept="image/png,image/jpeg" class="d-none" data-signature-file-input>
                    <input type="hidden" name="approval[{{ $column['key'] }}][role]" value="{{ $column['role'] ?? $column['label'] }}">
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

@once
    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
        <style>
            .qc-user-field .ts-wrapper.single .ts-control {
                min-height: 3.5rem;
                border-color: #d7dfeb;
                border-radius: .75rem;
                padding: .9rem 1rem;
                font-size: 1rem;
            }

            .qc-user-field .ts-wrapper.focus .ts-control {
                border-color: #86b7fe;
                box-shadow: 0 0 0 .25rem rgba(13, 110, 253, .16);
            }

            .qc-user-field .ts-dropdown {
                border-color: #d7dfeb;
                border-radius: .75rem;
                overflow: hidden;
                box-shadow: 0 1rem 2rem rgba(15, 23, 42, .12);
            }

            .qc-user-field .ts-dropdown .option {
                padding: .65rem .85rem;
                font-size: .94rem;
            }

            .qc-user-field .ts-dropdown .active {
                background: #eef4ff;
                color: #172033;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    @endpush
@endonce

@push('scripts')
    <script>
        (() => {
            const masterDataSelect = document.querySelector('[data-master-data-select]');
            const areaSelect = document.querySelector('[data-master-area-select]');
            const organizationSectionSelect = document.querySelector('[data-organization-section-select]');
            const unitKerjaApprovalLabel = document.querySelector('[data-unit-kerja-approval-label]');
            const unitKerjaApprovalLabelInput = document.querySelector('[data-unit-kerja-approval-label-input]');
            const masterDataOptions = @json($masterDataOptions);
            const selectedMasterDataId = @json((string) ($selectedMasterDataId ?? ''));
            let masterDataTomSelect = null;
            let organizationSectionTomSelect = null;

            const destroyMasterDataSearch = () => {
                if (masterDataTomSelect) {
                    masterDataTomSelect.destroy();
                    masterDataTomSelect = null;
                }
            };

            const initMasterDataSearch = () => {
                if (!masterDataSelect || !window.TomSelect) return;

                masterDataTomSelect = new TomSelect(masterDataSelect, {
                    create: false,
                    allowEmptyOption: true,
                    maxOptions: 1000,
                    searchField: ['text'],
                    placeholder: areaSelect?.value ? 'Cari section...' : 'Pilih area terlebih dahulu',
                    render: {
                        no_results: () => '<div class="no-results">Section tidak ditemukan</div>',
                    },
                });
            };

            const setHeaderValue = (key, value) => {
                const input = document.querySelector(`[data-header-input="${key}"]`);
                if (input) input.value = value || '';
            };

            const initOrganizationSectionSearch = () => {
                if (!organizationSectionSelect || !window.TomSelect) return;

                organizationSectionTomSelect = new TomSelect(organizationSectionSelect, {
                    create: false,
                    allowEmptyOption: true,
                    maxOptions: 1000,
                    searchField: ['text'],
                    placeholder: 'Cari Unit Kerja...',
                    render: {
                        option: function (data, escape) {
                            const option = data.$option;
                            const department = option?.dataset.department || '';
                            const workUnit = option?.dataset.workUnit || '';
                            const meta = [workUnit, department].filter(Boolean).join(' - ');

                            return `<div>
                                <div>${escape(data.text)}</div>
                                ${meta ? `<small class="text-muted">${escape(meta)}</small>` : ''}
                            </div>`;
                        },
                        no_results: () => '<div class="no-results">Unit Kerja tidak ditemukan</div>',
                    },
                });
            };

            const syncOrganizationSection = () => {
                const option = organizationSectionSelect?.selectedOptions?.[0];
                const section = option?.value || '';
                setHeaderValue('organization_section_id', option?.dataset.id || '');
                setHeaderValue('department', option?.dataset.department || '');
                setHeaderValue('work_unit', option?.dataset.workUnit || '');
                if (unitKerjaApprovalLabel) {
                    unitKerjaApprovalLabel.textContent = section || 'Unit Kerja';
                }
                if (unitKerjaApprovalLabelInput) {
                    unitKerjaApprovalLabelInput.value = section;
                }
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
                destroyMasterDataSearch();
                masterDataSelect.innerHTML = '';

                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = area ? 'Pilih Section' : 'Pilih Area terlebih dahulu';
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

                initMasterDataSearch();
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
            organizationSectionSelect?.addEventListener('change', syncOrganizationSection);
            filterMasterDataOptions();
            syncMasterDataHeader();
            initOrganizationSectionSearch();
            syncOrganizationSection();

        })();

        (() => {
            document.querySelectorAll('[data-qc-inspector-approval]').forEach((card) => {
                const input = card.querySelector('[data-signature-input]');
                const signature = input?.value || '';

                if (!signature) {
                    return;
                }

                const preview = card.querySelector('[data-signature-preview]');
                const signedAtInput = card.querySelector('[data-signature-time-input]');
                const signedAt = signedAtInput?.value ? new Date(signedAtInput.value) : null;

                if (preview) {
                    preview.src = signature;
                }

                card.querySelector('[data-signature-empty]')?.classList.add('d-none');
                card.querySelector('[data-signature-result]')?.classList.remove('d-none');
                card.querySelector('[data-signature-remove]')?.classList.remove('d-none');

                const buttonLabel = card.querySelector('[data-signature-button-label]');
                if (buttonLabel) {
                    buttonLabel.textContent = 'Ubah';
                }

                if (signedAt && !Number.isNaN(signedAt.getTime())) {
                    const time = card.querySelector('[data-signature-time]');
                    if (time) {
                        time.textContent = signedAt.toLocaleString('id-ID', {
                            dateStyle: 'medium',
                            timeStyle: 'short',
                        });
                    }
                }
            });
        })();
    </script>
@endpush

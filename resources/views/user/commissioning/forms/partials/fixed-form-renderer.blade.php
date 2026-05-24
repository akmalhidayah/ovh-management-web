@php
    use App\Support\Commissioning\FixedCommissioningTemplate;

    $draftSubmission = $draftSubmission ?? null;
    $schema = \App\Support\Commissioning\FixedCommissioningTemplate::normalizeSchema($selectedTemplate->body_schema);
    $draftHeader = $draftSubmission?->header_data ?? [];
    $draftBody = $draftSubmission?->body_data ?? [];
    $oldHeader = old('header', $draftHeader);
    $oldBody = old('body', $draftBody);
    $masterDataRecords = collect($activeMasterDataRecords ?? []);
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
        'tahun' => (string) ($record->year ?? ''),
        'plant' => (string) ($record->plant ?? ''),
        'area' => (string) ($record->area ?? ''),
        'tagNum' => (string) ($record->section_no ?? ''),
        'functionalLocation' => (string) ($record->func_location ?? ''),
        'nameEquipment' => (string) ($record->description ?? ''),
        'idEquipment' => (string) ($record->equipment_no ?? ''),
        'label' => trim(($record->description ?: '-') . ' - ' . ($record->equipment_no ?: '-') . ' (' . ($record->area ?: '-') . ')'),
    ])->values();
    $checkRows = old('body.equipment_check_rows', ($draftBody['equipment_check_rows'] ?? []) ?: ($schema['equipment_check_rows'] ?? []));
    $motorRows = old('body.motor_test_rows', ($draftBody['motor_test_rows'] ?? []) ?: ($schema['motor_test_rows'] ?? []));
    $gearboxRows = old('body.gearbox_test_rows', ($draftBody['gearbox_test_rows'] ?? []) ?: ($schema['gearbox_test_rows'] ?? []));
    $motorRating = old('body.motor_rating', $draftBody['motor_rating'] ?? []);
    $gearboxRating = old('body.gearbox_rating', $draftBody['gearbox_rating'] ?? []);
    $approval = old('approval', $draftSubmission?->approval_data ?? []);
    $signerName = auth()->user()?->name ?: 'User Commissioning';
    $labels = $schema['labels'];
    $motorRatingFields = $schema['motor_rating_fields'];
    $motorTestFields = $schema['motor_test_fields'];
    $gearboxRatingFields = $schema['gearbox_rating_fields'];
    $gearboxTestFields = $schema['gearbox_test_fields'];
    $approvalDefaults = $schema['approval_defaults'] ?? FixedCommissioningTemplate::defaultApprovalDefaults();
    $headerRows = [
        ['doc_number', 'plant', 'tag_num'],
        ['functional_location', 'area', 'name_equipment'],
        ['id_equipment', 'date_time', 'inspector_commissioning'],
    ];
    $headerFieldMap = collect(FixedCommissioningTemplate::headerFields())->keyBy('key');
@endphp

<div class="qc-user-form" data-commissioning-form>
    <section class="inspector-panel qc-form-card">
        <div class="qc-form-card-head">
            <div>
                <span>Commissioning</span>
                <h2>{{ $selectedTemplate->name }}</h2>
                @if ($selectedTemplate->description)<p>{{ $selectedTemplate->description }}</p>@endif
            </div>
            <div class="qc-form-code"><strong>{{ $selectedTemplate->code ?: 'Tanpa kode' }}</strong><span>Versi {{ $selectedTemplate->version }}</span></div>
        </div>
    </section>

    <section class="inspector-panel qc-form-card">
        <div class="qc-form-section-title"><h3>Informasi Umum</h3></div>
        <div class="qc-user-field-grid">
            @foreach ($headerRows as $row)
                @foreach ($row as $fieldKey)
                @php
                    $field = $headerFieldMap[$fieldKey];
                    $value = $oldHeader[$fieldKey] ?? ($fieldKey === 'doc_number' ? ($autoDocNumber ?? '') : ($fieldKey === 'inspector_commissioning' ? $signerName : ''));
                    $autoKeys = ['plant', 'tag_num', 'functional_location', 'id_equipment'];
                @endphp
                <label class="qc-user-field">
                    <span>{{ $field['label'] }}</span>
                    @if ($fieldKey === 'name_equipment')
                        <input type="hidden" name="header[name_equipment]" value="{{ $value }}" data-header-input="name_equipment">
                        <select name="header[master_data_record_id]" class="form-select" data-master-data-select required>
                            <option value="">Pilih Area terlebih dahulu</option>
                        </select>
                    @elseif ($fieldKey === 'area')
                        <input type="{{ $field['type'] }}"
                               name="header[{{ $fieldKey }}]"
                               value="{{ $selectedArea }}"
                               class="form-control"
                               data-header-input="{{ $fieldKey }}"
                               placeholder="Pilih area di bagian atas"
                               readonly
                               required>
                    @else
                        <input type="{{ $field['type'] }}" name="header[{{ $fieldKey }}]" value="{{ $value }}" class="form-control" data-header-input="{{ $fieldKey }}" @if ($fieldKey === 'doc_number' || $fieldKey === 'inspector_commissioning' || in_array($fieldKey, $autoKeys, true)) readonly @else required @endif>
                    @endif
                </label>
                @endforeach
            @endforeach
        </div>
    </section>

    <section class="inspector-panel qc-form-card">
        <div class="commissioning-section-title">{{ $labels['motor_title'] }}</div>
        <div class="table-responsive commissioning-mobile-card-wrap mb-3">
            <table class="commissioning-table commissioning-mobile-card-table compact commissioning-rating-table commissioning-motor-rating-table">
                <thead>
                    <tr>
                        @foreach ($motorRatingFields as $field)
                            <th>
                                <div>{{ $field['label'] }}</div>
                                @if ($unitLabel = FixedCommissioningTemplate::fieldUnitLabel($field))
                                    <small>{{ $unitLabel }}</small>
                                @endif
                            </th>
                        @endforeach
                        <th class="bg-dark text-white" colspan="4">RMS Vibration velocity - ISO 10816-1</th>
                    </tr>
                </thead>
                <tbody><tr>
                    @foreach ($motorRatingFields as $field)
                        @php
                            $key = $field['key'];
                            $mobileLabel = $field['label'] . (($unitLabel = FixedCommissioningTemplate::fieldUnitLabel($field)) ? ' (' . $unitLabel . ')' : '');
                        @endphp
                        <td data-label="{{ $mobileLabel }}"><input type="text" name="body[motor_rating][{{ $key }}]" value="{{ $motorRating[$key] ?? '' }}" class="form-control" required></td>
                    @endforeach
                    <td colspan="4" class="text-center small commissioning-rms-info" data-label="RMS Vibration">
                        Power &lt;= 15 kW : &lt; 4.5 mm/s<br>
                        15 kW &lt; Power &lt;= 300 kW : &lt; 7.1 mm/s<br>
                        300 kW &lt; Power &lt;= 10 MW : &lt; 11.2 mm/s
                    </td>
                </tr></tbody>
            </table>
        </div>
        <div class="table-responsive commissioning-mobile-card-wrap">
            <table class="commissioning-table commissioning-mobile-card-table test-table commissioning-motor-test-table">
                <thead>
                    <tr>
                        @foreach ($motorTestFields as $field)
                            <th>
                                <div>{{ $field['label'] }}</div>
                                @if ($unitLabel = FixedCommissioningTemplate::fieldUnitLabel($field))
                                    <small>{{ $unitLabel }}</small>
                                @endif
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody data-motor-row-list>
                    @foreach ($motorRows as $index => $row)
                        <tr data-motor-row>
                            @foreach ($motorTestFields as $field)
                                @php
                                    $key = $field['key'];
                                    $isRemarksField = in_array(strtolower(trim((string) $key)), ['remarks', 'remark'], true);
                                    $mobileLabel = $field['label'] . (($unitLabel = FixedCommissioningTemplate::fieldUnitLabel($field)) ? ' (' . $unitLabel . ')' : '');
                                @endphp
                                <td data-label="{{ $mobileLabel }}"><input type="text" name="body[motor_test_rows][{{ $index }}][{{ $key }}]" value="{{ $row[$key] ?? '' }}" class="form-control form-control-sm" @if (! $isRemarksField) required @endif></td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="inspector-panel qc-form-card">
        <div class="commissioning-section-title">{{ $labels['gearbox_title'] }}</div>
        <div class="table-responsive commissioning-mobile-card-wrap mb-3">
            <table class="commissioning-table commissioning-mobile-card-table compact commissioning-rating-table commissioning-gearbox-rating-table">
                <thead>
                    <tr>
                        @foreach ($gearboxRatingFields as $field)
                            <th>
                                <div>{{ $field['label'] }}</div>
                                @if ($unitLabel = FixedCommissioningTemplate::fieldUnitLabel($field))
                                    <small>{{ $unitLabel }}</small>
                                @endif
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody><tr>
                    @foreach ($gearboxRatingFields as $field)
                        @php
                            $key = $field['key'];
                            $mobileLabel = $field['label'] . (($unitLabel = FixedCommissioningTemplate::fieldUnitLabel($field)) ? ' (' . $unitLabel . ')' : '');
                        @endphp
                        <td data-label="{{ $mobileLabel }}"><input type="text" name="body[gearbox_rating][{{ $key }}]" value="{{ $gearboxRating[$key] ?? '' }}" class="form-control" required></td>
                    @endforeach
                </tr></tbody>
            </table>
        </div>
        <div class="table-responsive commissioning-mobile-card-wrap">
            <table class="commissioning-table commissioning-mobile-card-table test-table commissioning-gearbox-test-table">
                <thead>
                    <tr>
                        @foreach ($gearboxTestFields as $field)
                            @if ($field['key'] === 'horizontal')
                                <th colspan="3">Vibration test</th>
                            @elseif (! in_array($field['key'], ['vertical', 'axial'], true))
                                <th rowspan="2">
                                    <div>{{ $field['label'] }}</div>
                                    @if ($unitLabel = FixedCommissioningTemplate::fieldUnitLabel($field))
                                        <small>{{ $unitLabel }}</small>
                                    @endif
                                </th>
                            @endif
                        @endforeach
                    </tr>
                    <tr>
                        @foreach ($gearboxTestFields as $field)
                            @if (in_array($field['key'], ['horizontal', 'vertical', 'axial'], true))
                                <th>
                                    <div>{{ $field['label'] }}</div>
                                    @if ($unitLabel = FixedCommissioningTemplate::fieldUnitLabel($field))
                                        <small>{{ $unitLabel }}</small>
                                    @endif
                                </th>
                            @endif
                        @endforeach
                    </tr>
                </thead>
                <tbody data-gearbox-row-list>
                    @foreach ($gearboxRows as $index => $row)
                        <tr data-gearbox-row>
                            @foreach ($gearboxTestFields as $field)
                                @php
                                    $key = $field['key'];
                                    $isRemarksField = in_array(strtolower(trim((string) $key)), ['remarks', 'remark'], true);
                                    $mobileLabel = $field['label'] . (($unitLabel = FixedCommissioningTemplate::fieldUnitLabel($field)) ? ' (' . $unitLabel . ')' : '');
                                @endphp
                                <td data-label="{{ $mobileLabel }}"><input type="text" name="body[gearbox_test_rows][{{ $index }}][{{ $key }}]" value="{{ $row[$key] ?? '' }}" class="form-control form-control-sm" @if (! $isRemarksField) required @endif></td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="inspector-panel qc-form-card">
        <div class="qc-form-section-title"><h3>{{ $labels['equipment_check_title'] }}</h3></div>
        <div class="table-responsive commissioning-mobile-card-wrap">
            <table class="commissioning-table commissioning-mobile-card-table check-table commissioning-equipment-check-table">
                <thead><tr><th>No</th><th>Item</th><th>Check</th><th>YES</th><th>NO</th><th>NA</th><th>Remark</th></tr></thead>
                <tbody data-check-row-list>
                    @foreach ($checkRows as $index => $row)
                        <tr data-check-row>
                            <td data-label="No">
                                {{ $row['no'] ?? $loop->iteration }}
                                <input type="hidden" name="body[equipment_check_rows][{{ $index }}][no]" value="{{ $row['no'] ?? $loop->iteration }}">
                            </td>
                            <td data-label="Item">
                                {{ $row['item'] ?? '' }}
                                <input type="hidden" name="body[equipment_check_rows][{{ $index }}][item]" value="{{ $row['item'] ?? '' }}">
                            </td>
                            <td class="text-center" data-label="Check"><input type="checkbox" name="body[equipment_check_rows][{{ $index }}][check]" value="1" @checked(! empty($row['check'])) required></td>
                            @foreach (['YES', 'NO', 'NA'] as $result)
                                <td class="text-center" data-label="{{ $result }}"><input type="radio" name="body[equipment_check_rows][{{ $index }}][result]" value="{{ $result }}" @checked(($row['result'] ?? null) === $result) required></td>
                            @endforeach
                            <td data-label="Remark"><input type="text" name="body[equipment_check_rows][{{ $index }}][remark]" value="{{ $row['remark'] ?? '' }}" class="form-control form-control-sm"></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="inspector-panel qc-form-card">
        <div class="row g-3">
            <div class="col-12 col-lg-7"><label class="qc-user-note-box"><span>{{ $labels['note_label'] }}</span><textarea name="note" rows="5" class="form-control">{{ old('note', $draftSubmission?->note ?? '') }}</textarea></label></div>
            <div class="col-12 col-lg-5">
                @php
                    $temporaryAttachmentTokens = old('temporary_attachments.dokumentasi', []);
                    $temporaryAttachmentMetas = collect($temporaryAttachmentTokens)
                        ->map(fn ($token) => ['token' => $token] + (session("commissioning_temporary_attachments.{$token}") ?? []))
                        ->filter(fn ($attachment) => isset($attachment['original_name']));
                    $hasTemporaryDocumentation = $temporaryAttachmentMetas->isNotEmpty();
                @endphp
                <label class="qc-user-note-box">
                    <span>{{ $labels['documentation_label'] }}</span>
                    <input type="file" name="attachments[dokumentasi][]" class="form-control" accept=".jpg,.jpeg,.png,image/jpeg,image/png" multiple @if (! $hasTemporaryDocumentation && ! ($draftSubmission?->attachments()->exists() ?? false)) required @endif>
                    <small class="text-muted d-block mt-2">Hanya JPG atau PNG. Bisa upload lebih dari satu gambar sekaligus.</small>
                    @if ($temporaryAttachmentMetas->isNotEmpty())
                        <div class="mt-2 small text-muted">
                            <strong class="d-block text-body">Lampiran tersimpan sementara:</strong>
                            @foreach ($temporaryAttachmentMetas as $attachment)
                                <input type="hidden" name="temporary_attachments[dokumentasi][]" value="{{ $attachment['token'] }}">
                                <div><i class="bi bi-check2-circle me-1 text-success"></i>{{ $attachment['original_name'] }}</div>
                            @endforeach
                        </div>
                    @endif
                </label>
            </div>
        </div>
    </section>

    <section class="inspector-panel qc-form-card">
        <div class="qc-form-section-title"><h3>Approval Footer</h3></div>
        <div class="qc-user-approval-grid" style="--qc-approval-columns: 4">
            @foreach (FixedCommissioningTemplate::approvalColumns() as $column)
                @php
                    $approvalName = $approval[$column['key']]['name'] ?? ($approvalDefaults[$column['key']]['name'] ?? '');
                @endphp
                <div class="qc-user-approval-box is-locked">
                    <strong>{{ $column['label'] }}</strong>
                    <small class="text-muted d-block mb-2">{{ $column['group'] }}</small>
                    <input type="text" name="approval[{{ $column['key'] }}][name]" value="{{ $approvalName }}" class="form-control mb-2" placeholder="Nama">
                    <input type="date" name="approval[{{ $column['key'] }}][date]" value="{{ $approval[$column['key']]['date'] ?? '' }}" class="form-control" disabled>
                    <div class="qc-signature-locked mt-2"><i class="bi bi-lock"></i><span>Tanda tangan terkunci.</span></div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="inspector-panel qc-form-actions-card">
        <div><h3>Action Form</h3><p>Simpan draft bisa belum lengkap. Submit wajib semua field terisi dan dokumentasi JPG/PNG terupload.</p></div>
        <div class="qc-form-actions">
            <button type="submit" name="action" value="draft" class="btn btn-primary" formnovalidate>Simpan Draft</button>
            <button type="submit" name="action" value="submit" class="btn btn-success">Submit Form</button>
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
    const master = document.querySelector('[data-master-data-select]');
    const area = document.querySelector('[data-master-area-select]');
    const masterOptions = @json($masterDataOptions);
    const selectedMasterDataId = @json((string) ($selectedMasterDataId ?? ''));
    let masterTomSelect = null;

    const destroyMasterSearch = () => {
        if (masterTomSelect) {
            masterTomSelect.destroy();
            masterTomSelect = null;
        }
    };

    const initMasterSearch = () => {
        if (!master || !window.TomSelect) return;

        masterTomSelect = new TomSelect(master, {
            create: false,
            allowEmptyOption: true,
            maxOptions: 1000,
            searchField: ['text'],
            placeholder: area?.value ? 'Cari equipment...' : 'Pilih area terlebih dahulu',
            render: {
                no_results: () => '<div class="no-results">Equipment tidak ditemukan</div>',
            },
        });
    };

    const setHeader = (key, value) => { const input = document.querySelector(`[data-header-input="${key}"]`); if (input) input.value = value || ''; };
    document.querySelectorAll('[data-commissioning-form] input[name*="[remarks]"], [data-commissioning-form] input[name*="[remark]"]').forEach((input) => {
        input.required = false;
        input.removeAttribute('required');
    });
    const clearMasterHeader = () => {
        ['tahun','plant','tag_num','functional_location','name_equipment','id_equipment'].forEach((key) => setHeader(key, ''));
    };
    const filterMasterOptions = () => {
        const selectedArea = area?.value || '';
        if (!master) return;

        setHeader('area', selectedArea);

        const currentValue = master.value || selectedMasterDataId;
        destroyMasterSearch();
        master.innerHTML = '';

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = selectedArea ? 'Pilih Name Equipment' : 'Pilih Area terlebih dahulu';
        master.appendChild(placeholder);

        masterOptions
            .filter((option) => selectedArea && option.area === selectedArea)
            .forEach((record) => {
                const option = document.createElement('option');
                option.value = record.id;
                option.textContent = record.label;
                option.dataset.tahun = record.tahun;
                option.dataset.plant = record.plant;
                option.dataset.area = record.area;
                option.dataset.tagNum = record.tagNum;
                option.dataset.functionalLocation = record.functionalLocation;
                option.dataset.nameEquipment = record.nameEquipment;
                option.dataset.idEquipment = record.idEquipment;
                master.appendChild(option);
            });

        master.disabled = !selectedArea;

        if (currentValue && master.querySelector(`option[value="${CSS.escape(currentValue)}"]`)) {
            master.value = currentValue;
        } else {
            clearMasterHeader();
        }

        initMasterSearch();
    };
    const syncMaster = () => {
        const option = master?.selectedOptions?.[0];
        if (!option || !option.value) {
            clearMasterHeader();
            return;
        }
        setHeader('tahun', option.dataset.tahun);
        setHeader('plant', option.dataset.plant);
        setHeader('area', option.dataset.area);
        setHeader('tag_num', option.dataset.tagNum);
        setHeader('functional_location', option.dataset.functionalLocation);
        setHeader('name_equipment', option.dataset.nameEquipment);
        setHeader('id_equipment', option.dataset.idEquipment);
    };
    area?.addEventListener('change', () => {
        filterMasterOptions();
        syncMaster();
    });
    master?.addEventListener('change', syncMaster);
    filterMasterOptions();
    syncMaster();

})();
</script>
@endpush

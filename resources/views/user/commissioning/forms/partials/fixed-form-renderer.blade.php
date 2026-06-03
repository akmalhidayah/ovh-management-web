@php
    use App\Support\AreaOwnerLabel;
    use App\Support\Commissioning\FixedCommissioningTemplate;
    use Illuminate\Support\Facades\Storage;

    $draftSubmission = $draftSubmission ?? null;
    $schema = \App\Support\Commissioning\FixedCommissioningTemplate::normalizeSchema($selectedTemplate->body_schema);
    $draftHeader = $draftSubmission?->header_data ?? [];
    $draftBody = $draftSubmission?->body_data ?? [];
    $oldHeader = old('header', $draftHeader);
    $oldBody = old('body', $draftBody);
    $masterDataRecords = collect($activeMasterDataRecords ?? []);
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
        'tahun' => (string) ($record->year ?? ''),
        'plant' => (string) ($record->plant ?? ''),
        'area' => (string) ($record->area ?? ''),
        'tagNum' => (string) ($record->section_no ?? ''),
        'functionalLocation' => (string) ($record->func_location ?? ''),
        'nameEquipment' => (string) ($record->description ?? ''),
        'idEquipment' => (string) ($record->equipment_no ?? ''),
        'organizationSectionId' => (string) ($record->organization_section_id ?? ''),
        'organizationDepartment' => (string) ($record->organizationSection?->department ?? ''),
        'organizationWorkUnit' => (string) ($record->organizationSection?->unit_kerja ?? ''),
        'organizationSection' => (string) ($record->organizationSection?->section ?? ''),
        'label' => trim(($record->section_no ?: '-') . ' - ' . ($record->description ?: '-') . ' (' . ($record->equipment_no ?: '-') . ')'),
    ])->values();
    $organizationSections = collect($activeOrganizationSections ?? []);
    $selectedOrganizationSection = old('header.unit_kerja', $oldHeader['unit_kerja'] ?? ($selectedMasterDataRecord?->organizationSection?->section ?? ''));
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
    $checkRows = old('body.equipment_check_rows', ($draftBody['equipment_check_rows'] ?? []) ?: ($schema['equipment_check_rows'] ?? []));
    $motorRows = old('body.motor_test_rows', ($draftBody['motor_test_rows'] ?? []) ?: ($schema['motor_test_rows'] ?? []));
    $gearboxRows = old('body.gearbox_test_rows', ($draftBody['gearbox_test_rows'] ?? []) ?: ($schema['gearbox_test_rows'] ?? []));
    $motorRating = old('body.motor_rating', $draftBody['motor_rating'] ?? []);
    $gearboxRating = old('body.gearbox_rating', $draftBody['gearbox_rating'] ?? []);
    $approval = old('approval', $draftSubmission?->approval_data ?? []);
    $existingDocumentationAttachments = $draftSubmission
        ? $draftSubmission->attachments->where('field_key', 'dokumentasi')->values()
        : collect();
    $draftApprovalSteps = $draftSubmission?->approvalFlow?->steps?->sortBy('step_order')->values() ?? collect();
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
        ['id_equipment', 'date_time', 'inspector_commissioning', 'unit_kerja'],
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
        </div>
    </section>

    <section class="inspector-panel qc-form-card">
        <div class="qc-form-section-title"><h3>Informasi Umum</h3></div>
        <div class="commissioning-header-grid">
            @foreach ($headerRows as $row)
                <div class="commissioning-header-row {{ count($row) === 4 ? 'is-four-column' : '' }}">
                @foreach ($row as $fieldKey)
                @php
                    $field = $headerFieldMap[$fieldKey];
                    $value = $oldHeader[$fieldKey] ?? ($fieldKey === 'doc_number' ? ($autoDocNumber ?? '') : ($fieldKey === 'inspector_commissioning' ? $signerName : ''));
                    $autoKeys = ['plant', 'functional_location', 'id_equipment', 'name_equipment'];
                @endphp
                <label class="qc-user-field">
                    <span>{{ $field['label'] }}</span>
                    @if ($fieldKey === 'tag_num')
                        <input type="hidden" name="header[tag_num]" value="{{ $value }}" data-header-input="tag_num">
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
                    @elseif ($fieldKey === 'unit_kerja')
                        <input type="hidden" name="header[department]" value="{{ old('header.department', $oldHeader['department'] ?? '') }}" data-header-input="department">
                        <input type="hidden" name="header[work_unit]" value="{{ old('header.work_unit', $oldHeader['work_unit'] ?? '') }}" data-header-input="work_unit">
                        <input type="hidden" name="header[organization_section_id]" value="{{ old('header.organization_section_id', $oldHeader['organization_section_id'] ?? '') }}" data-header-input="organization_section_id">
                        <select name="header[unit_kerja]" class="form-select" data-organization-section-select required>
                            <option value="">Pilih Area Owner</option>
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
                        <input type="{{ $field['type'] }}" name="header[{{ $fieldKey }}]" value="{{ $value }}" class="form-control" data-header-input="{{ $fieldKey }}" @if ($fieldKey === 'doc_number' || $fieldKey === 'inspector_commissioning' || in_array($fieldKey, $autoKeys, true)) readonly @else required @endif>
                    @endif
                </label>
                @endforeach
                </div>
            @endforeach
        </div>
    </section>

    <section class="inspector-panel qc-form-card">
        <div class="commissioning-section-title">{{ $labels['motor_title'] }} (Opsional)</div>
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
                        <td data-label="{{ $mobileLabel }}"><input type="text" name="body[motor_rating][{{ $key }}]" value="{{ $motorRating[$key] ?? '' }}" class="form-control"></td>
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
                                <td data-label="{{ $mobileLabel }}"><input type="text" name="body[motor_test_rows][{{ $index }}][{{ $key }}]" value="{{ $row[$key] ?? '' }}" class="form-control form-control-sm"></td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="inspector-panel qc-form-card">
        <div class="commissioning-section-title">{{ $labels['gearbox_title'] }} (Opsional)</div>
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
                        <td data-label="{{ $mobileLabel }}"><input type="text" name="body[gearbox_rating][{{ $key }}]" value="{{ $gearboxRating[$key] ?? '' }}" class="form-control"></td>
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
                                <td data-label="{{ $mobileLabel }}"><input type="text" name="body[gearbox_test_rows][{{ $index }}][{{ $key }}]" value="{{ $row[$key] ?? '' }}" class="form-control form-control-sm"></td>
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
            <div class="col-12 col-lg-7">
                <label class="qc-user-note-box">
                    <span>{{ $labels['note_label'] }} (Opsional)</span>
                    <textarea name="note" rows="5" class="form-control" placeholder="Tulis notes/finding jika diperlukan">{{ old('note', $draftSubmission?->note ?? '') }}</textarea>
                </label>
            </div>
            <div class="col-12 col-lg-5">
                @php
                    $temporaryAttachmentTokens = old('temporary_attachments.dokumentasi', []);
                    $temporaryAttachmentMetas = collect($temporaryAttachmentTokens)
                        ->map(fn ($token) => ['token' => $token] + (session("commissioning_temporary_attachments.{$token}") ?? []))
                        ->filter(fn ($attachment) => isset($attachment['original_name']));
                    $hasTemporaryDocumentation = $temporaryAttachmentMetas->isNotEmpty();
                    $hasExistingDocumentation = $existingDocumentationAttachments->isNotEmpty();
                @endphp
                <div class="qc-user-note-box">
                    <span>{{ $labels['documentation_label'] }}</span>
                    <input
                        id="commissioning-upload-dokumentasi"
                        type="file"
                        name="attachments[dokumentasi][]"
                        class="visually-hidden"
                        accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                        multiple
                        data-commissioning-upload-input
                    >
                    <input
                        id="commissioning-camera-dokumentasi"
                        type="file"
                        class="visually-hidden"
                        accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                        capture="environment"
                        data-commissioning-camera-input
                    >
                    <div class="qc-upload-actions mt-2" data-commissioning-upload-box>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-camera me-1"></i>Upload Dokumentasi
                            </button>
                            <div class="dropdown-menu">
                                <label class="dropdown-item" for="commissioning-camera-dokumentasi">
                                    <i class="bi bi-camera me-2"></i>Ambil Foto
                                </label>
                                <label class="dropdown-item" for="commissioning-upload-dokumentasi">
                                    <i class="bi bi-images me-2"></i>Pilih dari Galeri/File
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="qc-upload-preview mt-2" data-commissioning-upload-preview></div>
                    <small class="text-muted d-block mt-2">Hanya JPG atau PNG. Format HEIC/HEIF belum didukung.</small>
                    @if (! $hasTemporaryDocumentation && ! $hasExistingDocumentation)
                        <small class="text-muted d-block">Wajib diisi saat submit.</small>
                    @endif
                    @if ($existingDocumentationAttachments->isNotEmpty())
                        <div class="mt-2">
                            <strong class="d-block text-body small mb-2">Dokumentasi tersimpan:</strong>
                            <div class="qc-upload-preview">
                                @foreach ($existingDocumentationAttachments as $attachment)
                                    <div class="qc-upload-thumb">
                                        <img src="{{ route('user.commissioning.attachments.show', $attachment) }}" alt="{{ $attachment->original_name ?: 'Dokumentasi tersimpan' }}">
                                        <span>{{ $attachment->original_name ?: 'Dokumentasi tersimpan' }}</span>
                                        <a href="{{ route('user.commissioning.attachments.show', $attachment) }}" class="btn btn-sm btn-light border" target="_blank" rel="noopener">Buka</a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    @if ($temporaryAttachmentMetas->isNotEmpty())
                        <div class="mt-2 small text-muted">
                            <strong class="d-block text-body">Lampiran tersimpan sementara:</strong>
                            @foreach ($temporaryAttachmentMetas as $attachment)
                                <input type="hidden" name="temporary_attachments[dokumentasi][]" value="{{ $attachment['token'] }}">
                                <div><i class="bi bi-check2-circle me-1 text-success"></i>{{ $attachment['original_name'] }}</div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="inspector-panel qc-form-card">
        <div class="qc-form-section-title"><h3>Approval Footer</h3></div>
        <div class="qc-user-approval-grid" style="--qc-approval-columns: 4">
            @foreach (FixedCommissioningTemplate::approvalColumns() as $approvalIndex => $column)
                @php
                    $approvalData = is_array($approval[$column['key']] ?? null) ? $approval[$column['key']] : [];
                    $storedApprovalStep = $draftApprovalSteps->firstWhere('step_order', $approvalIndex + 1);
                    $storedSignatureUrl = '';
                    if (! blank($approvalData['signature'] ?? null)) {
                        $storedSignatureUrl = (string) $approvalData['signature'];
                    } elseif (! blank($storedApprovalStep?->signature_path) && Storage::disk('public')->exists($storedApprovalStep->signature_path)) {
                        $storedSignatureUrl = Storage::disk('public')->url($storedApprovalStep->signature_path);
                    } elseif (! blank($storedApprovalStep?->signature_data)) {
                        $storedSignatureUrl = (string) $storedApprovalStep->signature_data;
                    }
                    $approvalName = $approvalData['name']
                        ?? ($storedApprovalStep?->approver_name ?: ($approvalDefaults[$column['key']]['name'] ?? ''));
                    $approvalDate = $approvalData['date']
                        ?? ($storedApprovalStep?->acted_at?->format('Y-m-d') ?: '');
                    $approvalSignedAt = $approvalData['signed_at']
                        ?? ($storedApprovalStep?->acted_at?->toISOString() ?: '');
                    $approvalRole = $approvalData['role']
                        ?? ($storedApprovalStep?->approver_position ?: $column['label']);
                    $isUnitKerjaApprover = $column['key'] === 'unit_kerja';
                    $approvalLabel = $isUnitKerjaApprover
                        ? AreaOwnerLabel::approvalLabel($oldHeader['unit_kerja'] ?? $selectedOrganizationSection, $column['label'])
                        : $column['label'];
                @endphp
                <div class="qc-user-approval-box is-locked">
                    <strong @if ($isUnitKerjaApprover) data-unit-kerja-approval-label @endif>{{ $approvalLabel }}</strong>
                    @if ($isUnitKerjaApprover)
                        <input type="hidden" name="approval[{{ $column['key'] }}][label]" value="{{ $approvalLabel }}" data-unit-kerja-approval-label-input>
                    @endif
                    <small class="text-muted d-block mb-2">{{ $column['group'] }}</small>
                    <input
                        type="text"
                        name="approval[{{ $column['key'] }}][name]"
                        value="{{ $approvalName }}"
                        class="form-control mb-2"
                        placeholder="Nama"
                    >
                    <input type="hidden" name="approval[{{ $column['key'] }}][date]" value="{{ $approvalDate }}">
                    <input type="hidden" name="approval[{{ $column['key'] }}][signature]" value="{{ $storedSignatureUrl }}">
                    <input type="hidden" name="approval[{{ $column['key'] }}][signed_at]" value="{{ $approvalSignedAt }}">
                    <input type="hidden" name="approval[{{ $column['key'] }}][role]" value="{{ $approvalRole }}">
                    <input type="date" value="{{ $approvalDate }}" class="form-control" disabled>
                    @if ($storedSignatureUrl !== '')
                        <div class="qc-signature-result mt-2">
                            <img src="{{ $storedSignatureUrl }}" alt="TTD tersimpan">
                            <div>
                                <strong>TTD tersimpan</strong>
                                <span>{{ $approvalRole }}</span>
                                <small>{{ $approvalSignedAt ?: ($approvalDate ? 'Date : '.$approvalDate : '') }}</small>
                            </div>
                        </div>
                    @else
                        <div class="qc-signature-locked mt-2"><i class="bi bi-lock"></i><span>Tanda tangan terkunci.</span></div>
                    @endif
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
    const organizationSection = document.querySelector('[data-organization-section-select]');
    const unitKerjaApprovalLabel = document.querySelector('[data-unit-kerja-approval-label]');
    const unitKerjaApprovalLabelInput = document.querySelector('[data-unit-kerja-approval-label-input]');
    const masterOptions = @json($masterDataOptions);
    const selectedMasterDataId = @json((string) ($selectedMasterDataId ?? ''));
    const formStateKey = `ovh:commissioning-form:${document.querySelector('input[name="template_id"]')?.value || 'default'}`;
    const savedFormState = (() => {
        try {
            return JSON.parse(window.sessionStorage?.getItem(formStateKey) || '{}') || {};
        } catch (error) {
            return {};
        }
    })();
    const preferredMasterDataId = selectedMasterDataId || savedFormState.masterDataId || '';
    let masterTomSelect = null;
    let organizationTomSelect = null;

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
            placeholder: area?.value ? 'Cari section...' : 'Pilih area terlebih dahulu',
            render: {
                no_results: () => '<div class="no-results">Section tidak ditemukan</div>',
            },
        });
    };

    const setHeader = (key, value) => { const input = document.querySelector(`[data-header-input="${key}"]`); if (input) input.value = value || ''; };
    const persistMasterState = () => {
        try {
            window.sessionStorage?.setItem(formStateKey, JSON.stringify({
                area: area?.value || '',
                masterDataId: master?.value || '',
            }));
        } catch (error) {
            // Browser storage is optional; form restore still works from old input.
        }
    };
    const setMasterValue = (value) => {
        if (!master || !value) return;

        if (masterTomSelect) {
            masterTomSelect.setValue(value, true);
            return;
        }

        master.value = value;
    };
    const initOrganizationSearch = () => {
        if (!organizationSection || !window.TomSelect) return;

        organizationTomSelect = new TomSelect(organizationSection, {
            create: false,
            allowEmptyOption: true,
            maxOptions: 1000,
            searchField: ['text'],
            placeholder: 'Cari Area Owner...',
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
                no_results: () => '<div class="no-results">Area Owner tidak ditemukan</div>',
            },
        });
    };

    const areaOwnerApprovalLabel = (section) => {
        const value = (section || '').trim();

        return value ? `Mgr of ${value}` : 'Area Owner';
    };

    const syncOrganizationSection = () => {
        const option = organizationSection?.selectedOptions?.[0];
        const section = option?.value || '';
        setHeader('organization_section_id', option?.dataset.id || '');
        setHeader('department', option?.dataset.department || '');
        setHeader('work_unit', option?.dataset.workUnit || '');
        if (unitKerjaApprovalLabel) {
            unitKerjaApprovalLabel.textContent = areaOwnerApprovalLabel(section);
        }
        if (unitKerjaApprovalLabelInput) {
            unitKerjaApprovalLabelInput.value = areaOwnerApprovalLabel(section);
        }
    };

    document.querySelectorAll('[data-commissioning-form] input[name*="[remarks]"], [data-commissioning-form] input[name*="[remark]"]').forEach((input) => {
        input.required = false;
        input.removeAttribute('required');
    });
    const clearMasterHeader = () => {
        ['tahun','plant','tag_num','functional_location','name_equipment','id_equipment'].forEach((key) => setHeader(key, ''));
        setOrganizationValue('');
    };
    const filterMasterOptions = () => {
        const selectedArea = area?.value || '';
        if (!master) return;

        setHeader('area', selectedArea);

        const currentValue = master.value || preferredMasterDataId;
        destroyMasterSearch();
        master.innerHTML = '';

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = selectedArea ? 'Pilih Section' : 'Pilih Area terlebih dahulu';
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
                option.dataset.organizationSectionId = record.organizationSectionId;
                option.dataset.organizationDepartment = record.organizationDepartment;
                option.dataset.organizationWorkUnit = record.organizationWorkUnit;
                option.dataset.organizationSection = record.organizationSection;
                master.appendChild(option);
            });

        master.disabled = !selectedArea;

        const canRestoreCurrentValue = currentValue && master.querySelector(`option[value="${CSS.escape(currentValue)}"]`);

        if (canRestoreCurrentValue) {
            master.value = currentValue;
        } else {
            clearMasterHeader();
        }

        initMasterSearch();

        if (canRestoreCurrentValue) {
            setMasterValue(currentValue);
        }
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
        setOrganizationValue(option.dataset.organizationSectionId || '');
        persistMasterState();
    };
    const setOrganizationValue = (value) => {
        if (!organizationSection) return;

        const requestedValue = String(value || '');
        const selectedOption = requestedValue
            ? Array.from(organizationSection.options).find((option) => option.dataset.id === requestedValue)
                || Array.from(organizationSection.options).find((option) => option.value === requestedValue)
            : null;
        const optionValue = selectedOption?.value || '';

        if (organizationTomSelect) {
            organizationTomSelect.setValue(optionValue, true);
        } else {
            organizationSection.value = optionValue;
        }

        syncOrganizationSection();
    };
    area?.addEventListener('change', () => {
        filterMasterOptions();
        syncMaster();
        persistMasterState();
    });
    master?.addEventListener('change', () => {
        syncMaster();
        persistMasterState();
    });
    organizationSection?.addEventListener('change', syncOrganizationSection);
    if (!selectedMasterDataId && savedFormState.area && area?.querySelector(`option[value="${CSS.escape(savedFormState.area)}"]`)) {
        area.value = savedFormState.area;
    }
    filterMasterOptions();
    syncMaster();
    initOrganizationSearch();
    syncOrganizationSection();

})();
</script>
@endpush

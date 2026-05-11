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
    $checkRows = old('body.equipment_check_rows', ($draftBody['equipment_check_rows'] ?? []) ?: ($schema['equipment_check_rows'] ?? []));
    $motorRows = old('body.motor_test_rows', ($draftBody['motor_test_rows'] ?? []) ?: ($schema['motor_test_rows'] ?? []));
    $gearboxRows = old('body.gearbox_test_rows', ($draftBody['gearbox_test_rows'] ?? []) ?: ($schema['gearbox_test_rows'] ?? []));
    $motorRating = old('body.motor_rating', $draftBody['motor_rating'] ?? []);
    $gearboxRating = old('body.gearbox_rating', $draftBody['gearbox_rating'] ?? []);
    $approval = old('approval', $draftSubmission?->approval_data ?? []);
    $labels = $schema['labels'];
    $motorRatingFields = $schema['motor_rating_fields'];
    $motorTestFields = $schema['motor_test_fields'];
    $gearboxRatingFields = $schema['gearbox_rating_fields'];
    $gearboxTestFields = $schema['gearbox_test_fields'];
    $headerFields = collect(FixedCommissioningTemplate::headerFields())
        ->sortBy(fn ($field) => match ($field['key']) {
            'doc_number' => 10,
            'functional_location' => 20,
            'tahun' => 30,
            'area' => 40,
            'date_time' => 50,
            'tag_num' => 60,
            'name_equipment' => 70,
            'id_equipment' => 80,
            default => 90,
        })
        ->values();
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
            @foreach ($headerFields as $field)
                @php
                    $value = $oldHeader[$field['key']] ?? ($field['key'] === 'doc_number' ? ($autoDocNumber ?? '') : '');
                    $autoKeys = ['tahun', 'area', 'tag_num', 'functional_location', 'name_equipment', 'id_equipment'];
                @endphp
                <label class="qc-user-field">
                    <span>{{ $field['label'] }}</span>
                    @if ($field['key'] === 'functional_location')
                        <input type="hidden" name="header[functional_location]" value="{{ $value }}" data-header-input="functional_location">
                        <select name="header[master_data_record_id]" class="form-select" data-master-data-select required>
                            <option value="">Pilih Functional Location</option>
                            @foreach ($masterDataRecords as $record)
                                <option value="{{ $record->id }}"
                                        @selected((string) $selectedMasterDataId === (string) $record->id)
                                        data-tahun="{{ $record->year }}"
                                        data-area="{{ $record->area }}"
                                        data-tag-num="{{ $record->section_no }}"
                                        data-functional-location="{{ $record->func_location }}"
                                        data-name-equipment="{{ $record->description }}"
                                        data-id-equipment="{{ $record->equipment_no }}">
                                    {{ $record->func_location }} - {{ $record->equipment_no }} - {{ $record->description }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        <input type="{{ $field['type'] }}" name="header[{{ $field['key'] }}]" value="{{ $value }}" class="form-control" data-header-input="{{ $field['key'] }}" @if ($field['key'] === 'doc_number' || in_array($field['key'], $autoKeys, true)) readonly @else required @endif>
                    @endif
                </label>
            @endforeach
        </div>
    </section>

    <section class="inspector-panel qc-form-card">
        <div class="commissioning-section-title">{{ $labels['motor_title'] }}</div>
        <div class="table-responsive mb-3">
            <table class="commissioning-table compact">
                <thead>
                    <tr>
                        @foreach ($motorRatingFields as $field)<th>{{ $field['label'] }}</th>@endforeach
                        <th class="bg-dark text-white" colspan="4">RMS Vibration velocity - ISO 10816-1</th>
                    </tr>
                </thead>
                <tbody><tr>
                    @foreach ($motorRatingFields as $field)
                        @php($key = $field['key'])
                        <td><input type="text" name="body[motor_rating][{{ $key }}]" value="{{ $motorRating[$key] ?? '' }}" class="form-control" required></td>
                    @endforeach
                    <td colspan="4" class="text-center small">
                        Power &lt;= 15 kW : &lt; 4.5 mm/s<br>
                        15 kW &lt; Power &lt;= 300 kW : &lt; 7.1 mm/s<br>
                        300 kW &lt; Power &lt;= 10 MW : &lt; 11.2 mm/s
                    </td>
                </tr></tbody>
            </table>
        </div>
        <div class="table-responsive">
            <table class="commissioning-table test-table">
                <thead><tr>@foreach ($motorTestFields as $field)<th>{{ $field['label'] }}</th>@endforeach</tr></thead>
                <tbody data-motor-row-list>
                    @foreach ($motorRows as $index => $row)
                        <tr data-motor-row>
                            @foreach ($motorTestFields as $field)
                                @php($key = $field['key'])
                                <td><input type="text" name="body[motor_test_rows][{{ $index }}][{{ $key }}]" value="{{ $row[$key] ?? '' }}" class="form-control form-control-sm" required></td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="inspector-panel qc-form-card">
        <div class="commissioning-section-title">{{ $labels['gearbox_title'] }}</div>
        <div class="table-responsive mb-3">
            <table class="commissioning-table compact">
                <thead><tr>@foreach ($gearboxRatingFields as $field)<th>{{ $field['label'] }}</th>@endforeach</tr></thead>
                <tbody><tr>
                    @foreach ($gearboxRatingFields as $field)
                        @php($key = $field['key'])
                        <td><input type="text" name="body[gearbox_rating][{{ $key }}]" value="{{ $gearboxRating[$key] ?? '' }}" class="form-control" required></td>
                    @endforeach
                </tr></tbody>
            </table>
        </div>
        <div class="table-responsive">
            <table class="commissioning-table test-table">
                <thead>
                    <tr>
                        @foreach ($gearboxTestFields as $field)
                            @if ($field['key'] === 'horizontal')
                                <th colspan="3">Vibration test</th>
                            @elseif (! in_array($field['key'], ['vertical', 'axial'], true))
                                <th rowspan="2">
                                    {{ $field['label'] }}
                                    @if ($field['key'] === 'time')
                                        <br><small>(Interval 10 minutes)</small>
                                    @endif
                                </th>
                            @endif
                        @endforeach
                    </tr>
                    <tr>
                        @foreach ($gearboxTestFields as $field)
                            @if (in_array($field['key'], ['horizontal', 'vertical', 'axial'], true))
                                <th>{{ $field['label'] }}</th>
                            @endif
                        @endforeach
                    </tr>
                </thead>
                <tbody data-gearbox-row-list>
                    @foreach ($gearboxRows as $index => $row)
                        <tr data-gearbox-row>
                            @foreach ($gearboxTestFields as $field)
                                @php($key = $field['key'])
                                <td><input type="text" name="body[gearbox_test_rows][{{ $index }}][{{ $key }}]" value="{{ $row[$key] ?? '' }}" class="form-control form-control-sm" required></td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="inspector-panel qc-form-card">
        <div class="qc-form-section-title"><h3>{{ $labels['equipment_check_title'] }}</h3></div>
        <div class="table-responsive">
            <table class="commissioning-table check-table">
                <thead><tr><th>No</th><th>Item</th><th>Check</th><th>YES</th><th>NO</th><th>NA</th><th>Remark</th></tr></thead>
                <tbody data-check-row-list>
                    @foreach ($checkRows as $index => $row)
                        <tr data-check-row>
                            <td>
                                {{ $row['no'] ?? $loop->iteration }}
                                <input type="hidden" name="body[equipment_check_rows][{{ $index }}][no]" value="{{ $row['no'] ?? $loop->iteration }}">
                            </td>
                            <td>
                                {{ $row['item'] ?? '' }}
                                <input type="hidden" name="body[equipment_check_rows][{{ $index }}][item]" value="{{ $row['item'] ?? '' }}">
                            </td>
                            <td class="text-center"><input type="checkbox" name="body[equipment_check_rows][{{ $index }}][check]" value="1" @checked(! empty($row['check'])) required></td>
                            @foreach (['YES', 'NO', 'NA'] as $result)
                                <td class="text-center"><input type="radio" name="body[equipment_check_rows][{{ $index }}][result]" value="{{ $result }}" @checked(($row['result'] ?? null) === $result) required></td>
                            @endforeach
                            <td><input type="text" name="body[equipment_check_rows][{{ $index }}][remark]" value="{{ $row['remark'] ?? '' }}" class="form-control form-control-sm" required></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="inspector-panel qc-form-card">
        <div class="row g-3">
            <div class="col-12 col-lg-7"><label class="qc-user-note-box"><span>{{ $labels['note_label'] }}</span><textarea name="note" rows="5" class="form-control" required>{{ old('note', $draftSubmission?->note ?? '') }}</textarea></label></div>
            <div class="col-12 col-lg-5">
                <label class="qc-user-note-box">
                    <span>{{ $labels['documentation_label'] }}</span>
                    <input type="file" name="attachments[dokumentasi][]" class="form-control" accept=".jpg,.jpeg,.png,image/jpeg,image/png" multiple @if (! ($draftSubmission?->attachments()->exists() ?? false)) required @endif>
                    <small class="text-muted d-block mt-2">Hanya JPG atau PNG. Bisa upload lebih dari satu gambar sekaligus.</small>
                </label>
            </div>
        </div>
    </section>

    <section class="inspector-panel qc-form-card">
        <div class="qc-form-section-title"><h3>Approval Footer</h3></div>
        <div class="qc-user-approval-grid" style="--qc-approval-columns: 4">
            @foreach (FixedCommissioningTemplate::approvalColumns() as $column)
                <div class="qc-user-approval-box is-locked">
                    <strong>{{ $column['label'] }}</strong>
                    <small class="text-muted d-block mb-2">{{ $column['group'] }}</small>
                    <input type="text" name="approval[{{ $column['key'] }}][name]" value="{{ $approval[$column['key']]['name'] ?? '' }}" class="form-control mb-2" placeholder="Nama" required>
                    <input type="date" name="approval[{{ $column['key'] }}][date]" value="{{ $approval[$column['key']]['date'] ?? '' }}" class="form-control" required>
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

@push('scripts')
<script>
(() => {
    const master = document.querySelector('[data-master-data-select]');
    const setHeader = (key, value) => { const input = document.querySelector(`[data-header-input="${key}"]`); if (input) input.value = value || ''; };
    const syncMaster = () => {
        const option = master?.selectedOptions?.[0];
        if (!option || !option.value) return;
        ['tahun','area','tagNum','functionalLocation','nameEquipment','idEquipment'].forEach(() => {});
        setHeader('tahun', option.dataset.tahun);
        setHeader('area', option.dataset.area);
        setHeader('tag_num', option.dataset.tagNum);
        setHeader('functional_location', option.dataset.functionalLocation);
        setHeader('name_equipment', option.dataset.nameEquipment);
        setHeader('id_equipment', option.dataset.idEquipment);
    };
    master?.addEventListener('change', syncMaster);
    syncMaster();

})();
</script>
@endpush

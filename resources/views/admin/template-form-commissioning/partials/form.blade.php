@php
    use App\Support\Commissioning\FixedCommissioningTemplate;

    $statusOptions = ['draft' => 'Draft', 'active' => 'Aktif', 'inactive' => 'Nonaktif'];
    $schema = FixedCommissioningTemplate::normalizeSchema(old() ? [
        'labels' => old('labels', []),
        'motor_rating_fields' => old('motor_rating_fields', []),
        'motor_test_fields' => old('motor_test_fields', []),
        'motor_test_rows' => old('motor_test_rows', []),
        'gearbox_rating_fields' => old('gearbox_rating_fields', []),
        'gearbox_test_fields' => old('gearbox_test_fields', []),
        'gearbox_test_rows' => old('gearbox_test_rows', []),
        'equipment_check_rows' => old('equipment_check_rows', []),
    ] : ($template->body_schema ?? FixedCommissioningTemplate::defaultSchema()));
    $labels = $schema['labels'];
    $motorRatingFields = $schema['motor_rating_fields'];
    $motorTestFields = $schema['motor_test_fields'];
    $motorRows = $schema['motor_test_rows'];
    $gearboxRatingFields = $schema['gearbox_rating_fields'];
    $gearboxTestFields = $schema['gearbox_test_fields'];
    $gearboxRows = $schema['gearbox_test_rows'];
    $rows = $schema['equipment_check_rows'] ?: FixedCommissioningTemplate::defaultSchema()['equipment_check_rows'];
    $approvalDefaults = $schema['approval_defaults'] ?? FixedCommissioningTemplate::defaultApprovalDefaults();
    $codeInputValue = old('code', $template->code);
    $codeManualValue = $codeInputValue;
    $codeNumberValue = '001';

    if (preg_match('/^COM-(.+)-(\d+)$/i', (string) $codeInputValue, $codeMatches) === 1) {
        $codeManualValue = $codeMatches[1];
        $codeNumberValue = $codeMatches[2];
    } elseif (preg_match('/^COM-.+-(\d+)$/i', (string) $template->code, $templateCodeMatches) === 1) {
        $codeNumberValue = $templateCodeMatches[1];
    }
@endphp

<form method="POST" action="{{ $action }}">
    @csrf
    @if ($method)
        @method($method)
    @endif

    <div class="content-card">
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <label class="form-label">Kode Form</label>
                <div class="input-group">
                    <span class="input-group-text">COM-</span>
                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ $codeManualValue }}" placeholder="MTR">
                    <span class="input-group-text">-{{ $codeNumberValue }}</span>
                </div>
                @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-8">
                <label class="form-label">Nama Template</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $template->name) }}" required placeholder="Contoh: Motor & Gearbox Commissioning">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-4"><label class="form-label">Kategori</label><input type="text" name="category" class="form-control" value="{{ old('category', $template->category ?? 'Commissioning') }}"></div>
            <div class="col-6 col-md-4"><label class="form-label">Versi</label><input type="text" name="version" class="form-control" value="{{ old('version', $template->version ?? '1.0') }}"></div>
            <div class="col-6 col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $template->status ?? 'draft') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12"><label class="form-label">Deskripsi</label><textarea name="description" rows="3" class="form-control">{{ old('description', $template->description) }}</textarea></div>
        </div>
    </div>

    <div class="content-card">
        <div class="card-heading">
            <div>
                <h2>Informasi Umum / Header</h2>
                <div class="text-muted small">Urutan mengikuti form QC. Name Equipment dipilih user dari master data, field lain otomatis terisi.</div>
            </div>
        </div>
        <div class="row g-3">
            @foreach (array_chunk(FixedCommissioningTemplate::headerFields(), 3) as $row)
                @foreach ($row as $field)
                <div class="col-12 col-md-4">
                    <label class="form-label">{{ $field['label'] }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="text" class="form-control" value="{{ $field['label'] }}" readonly>
                    </div>
                </div>
                @endforeach
            @endforeach
        </div>
    </div>

    <div class="content-card">
        <div class="card-heading">
            <div>
                <h2>Motor Test Report</h2>
                <div class="text-muted small">Susunan mengikuti tabel form commissioning. Label kolom bisa diedit, row test bisa ditambah atau dihapus.</div>
            </div>
        </div>
        <input type="text" name="labels[motor_title]" class="form-control fw-semibold mb-2" value="{{ $labels['motor_title'] }}">
        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center mb-0">
                <thead class="table-light">
                    <tr>
                        @foreach ($motorRatingFields as $index => $field)
                            <th>
                                <input type="hidden" name="motor_rating_fields[{{ $index }}][key]" value="{{ $field['key'] }}">
                                <input type="text" name="motor_rating_fields[{{ $index }}][label]" class="form-control form-control-sm text-center fw-semibold" value="{{ $field['label'] }}">
                                <input type="text" name="motor_rating_fields[{{ $index }}][unit]" class="form-control form-control-sm text-center mt-1" value="{{ $field['unit'] ?? '' }}" placeholder="Satuan">
                            </th>
                        @endforeach
                        <th class="bg-dark text-white" colspan="4">RMS Vibration velocity - ISO 10816-1</th>
                    </tr>
                    <tr>
                        @foreach ($motorRatingFields as $field)
                            <td style="height: 54px;"></td>
                        @endforeach
                        <td colspan="4" class="small">
                            Power &lt;= 15 kW : &lt; 4.5 mm/s<br>
                            15 kW &lt; Power &lt;= 300 kW : &lt; 7.1 mm/s<br>
                            300 kW &lt; Power &lt;= 10 MW : &lt; 11.2 mm/s
                        </td>
                    </tr>
                    <tr>
                        @foreach ($motorTestFields as $index => $field)
                            @if ($field['key'] === 'r')
                                <th colspan="3">P H A S E</th>
                            @elseif ($field['key'] === 'horizontal')
                                <th colspan="3">Vibration test</th>
                            @elseif (! in_array($field['key'], ['s', 't', 'vertical', 'axial'], true))
                                <th rowspan="2">
                                    <input type="hidden" name="motor_test_fields[{{ $index }}][key]" value="{{ $field['key'] }}">
                                    <input type="text" name="motor_test_fields[{{ $index }}][label]" class="form-control form-control-sm text-center fw-semibold" value="{{ $field['label'] }}">
                                    <input type="text" name="motor_test_fields[{{ $index }}][unit]" class="form-control form-control-sm text-center mt-1" value="{{ $field['unit'] ?? '' }}" placeholder="Satuan">
                                </th>
                            @endif
                        @endforeach
                        <th rowspan="2" style="width: 96px;">Aksi</th>
                    </tr>
                    <tr>
                        @foreach ($motorTestFields as $index => $field)
                            @if (in_array($field['key'], ['r', 's', 't', 'horizontal', 'vertical', 'axial'], true))
                                <th>
                                    <input type="hidden" name="motor_test_fields[{{ $index }}][key]" value="{{ $field['key'] }}">
                                    <input type="text" name="motor_test_fields[{{ $index }}][label]" class="form-control form-control-sm text-center fw-semibold" value="{{ $field['label'] }}">
                                    <input type="text" name="motor_test_fields[{{ $index }}][unit]" class="form-control form-control-sm text-center mt-1" value="{{ $field['unit'] ?? '' }}" placeholder="Satuan">
                                </th>
                            @elseif (in_array($field['key'], ['starting_current', 'time', 'remarks'], true))
                                <input type="hidden" name="motor_test_fields[{{ $index }}][key]" value="{{ $field['key'] }}">
                            @endif
                        @endforeach
                    </tr>
                </thead>
                <tbody data-motor-row-list>
                    @foreach ($motorRows as $index => $row)
                        <tr data-motor-row>
                            @foreach ($motorTestFields as $field)
                                <td><input type="hidden" name="motor_test_rows[{{ $index }}][no]" value="{{ $row['no'] ?? $loop->parent->iteration }}"></td>
                            @endforeach
                            <td><button type="button" class="btn btn-sm btn-outline-danger" data-remove-row>Hapus</button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="commissioning-template-table-action d-flex justify-content-between align-items-center gap-2">
            <div class="text-muted small">Tambah baris jika form membutuhkan pengukuran motor lebih banyak.</div>
            <button type="button" class="btn btn-primary" data-add-motor-row><i class="bi bi-plus-lg me-1"></i>Tambah Row Motor</button>
        </div>
    </div>

    <div class="content-card">
        <div class="card-heading">
            <div>
                <h2>Gearbox Test Report</h2>
                <div class="text-muted small">Susunan mengikuti tabel gearbox pada form. Label kolom bisa diedit, row test bisa ditambah atau dihapus.</div>
            </div>
        </div>
        <input type="text" name="labels[gearbox_title]" class="form-control fw-semibold mb-2" value="{{ $labels['gearbox_title'] }}">
        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center mb-0">
                <thead class="table-light">
                    <tr>
                        @foreach ($gearboxRatingFields as $index => $field)
                            <th>
                                <input type="hidden" name="gearbox_rating_fields[{{ $index }}][key]" value="{{ $field['key'] }}">
                                <input type="text" name="gearbox_rating_fields[{{ $index }}][label]" class="form-control form-control-sm text-center fw-semibold" value="{{ $field['label'] }}">
                                <input type="text" name="gearbox_rating_fields[{{ $index }}][unit]" class="form-control form-control-sm text-center mt-1" value="{{ $field['unit'] ?? '' }}" placeholder="Satuan">
                            </th>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach ($gearboxRatingFields as $field)
                            <td style="height: 54px;"></td>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach ($gearboxTestFields as $index => $field)
                            @if ($field['key'] === 'horizontal')
                                <th colspan="3">Vibration test</th>
                            @elseif (! in_array($field['key'], ['vertical', 'axial'], true))
                                <th rowspan="2">
                                    <input type="hidden" name="gearbox_test_fields[{{ $index }}][key]" value="{{ $field['key'] }}">
                                    <input type="text" name="gearbox_test_fields[{{ $index }}][label]" class="form-control form-control-sm text-center fw-semibold" value="{{ $field['label'] }}">
                                    <input type="text" name="gearbox_test_fields[{{ $index }}][unit]" class="form-control form-control-sm text-center mt-1" value="{{ $field['unit'] ?? '' }}" placeholder="Satuan">
                                </th>
                            @endif
                        @endforeach
                        <th rowspan="2" style="width: 96px;">Aksi</th>
                    </tr>
                    <tr>
                        @foreach ($gearboxTestFields as $index => $field)
                            @if (in_array($field['key'], ['horizontal', 'vertical', 'axial'], true))
                                <th>
                                    <input type="hidden" name="gearbox_test_fields[{{ $index }}][key]" value="{{ $field['key'] }}">
                                    <input type="text" name="gearbox_test_fields[{{ $index }}][label]" class="form-control form-control-sm text-center fw-semibold" value="{{ $field['label'] }}">
                                    <input type="text" name="gearbox_test_fields[{{ $index }}][unit]" class="form-control form-control-sm text-center mt-1" value="{{ $field['unit'] ?? '' }}" placeholder="Satuan">
                                </th>
                            @elseif (in_array($field['key'], ['time', 'temperature', 'remarks'], true))
                                <input type="hidden" name="gearbox_test_fields[{{ $index }}][key]" value="{{ $field['key'] }}">
                            @endif
                        @endforeach
                    </tr>
                </thead>
                <tbody data-gearbox-row-list>
                    @foreach ($gearboxRows as $index => $row)
                        <tr data-gearbox-row>
                            @foreach ($gearboxTestFields as $field)
                                <td><input type="hidden" name="gearbox_test_rows[{{ $index }}][no]" value="{{ $row['no'] ?? $loop->parent->iteration }}"></td>
                            @endforeach
                            <td><button type="button" class="btn btn-sm btn-outline-danger" data-remove-row>Hapus</button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="commissioning-template-table-action d-flex justify-content-between align-items-center gap-2">
            <div class="text-muted small">Tambah baris jika form membutuhkan pengukuran gearbox lebih banyak.</div>
            <button type="button" class="btn btn-primary" data-add-gearbox-row><i class="bi bi-plus-lg me-1"></i>Tambah Row Gearbox</button>
        </div>
    </div>

    <div class="content-card">
        <div class="card-heading">
            <div>
                <h2>Equipment Check Data</h2>
                <div class="text-muted small">Item check bisa diedit dari tabel ini. Kolom Check, Result, dan Remark akan diisi oleh user.</div>
            </div>
        </div>
        <input type="text" name="labels[equipment_check_title]" class="form-control fw-semibold mb-2 text-center" value="{{ $labels['equipment_check_title'] }}">
        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center mb-0">
                <thead class="table-light">
                    <tr>
                        <th rowspan="2" style="width: 80px;">No.</th>
                        <th rowspan="2">Item</th>
                        <th rowspan="2" style="width: 90px;">Check</th>
                        <th colspan="3">Result</th>
                        <th rowspan="2">Remark</th>
                        <th rowspan="2" style="width: 96px;">Aksi</th>
                    </tr>
                    <tr><th>YES</th><th>NO</th><th>NA</th></tr>
                </thead>
                <tbody data-check-row-list>
                    @foreach ($rows as $index => $row)
                        <tr data-check-row>
                            <td><input type="text" name="equipment_check_rows[{{ $index }}][no]" class="form-control form-control-sm" value="{{ $row['no'] ?? $loop->iteration }}"></td>
                            <td><input type="text" name="equipment_check_rows[{{ $index }}][item]" class="form-control form-control-sm" value="{{ $row['item'] ?? '' }}" required></td>
                            <td><input type="checkbox" disabled></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger" data-remove-row>Hapus</button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="commissioning-template-table-action d-flex justify-content-between align-items-center gap-2">
            <div class="text-muted small">Tambah item checklist sesuai kebutuhan template.</div>
            <button type="button" class="btn btn-primary" data-add-check-row><i class="bi bi-plus-lg me-1"></i>Tambah Row Equipment Check</button>
        </div>
    </div>

    <div class="content-card">
        <div class="card-heading">
            <div>
                <h2>Notes & Dokumentasi</h2>
                <div class="text-muted small">Area isian user di bagian bawah form commissioning.</div>
            </div>
        </div>
        <div class="row g-3">
            <div class="col-12 col-lg-7">
                <label class="form-label">Label Notes</label>
                <input type="text" name="labels[note_label]" class="form-control fw-semibold" value="{{ $labels['note_label'] }}">
                <div class="border mt-2" style="min-height: 96px;"></div>
            </div>
            <div class="col-12 col-lg-5">
                <label class="form-label">Label Dokumentasi</label>
                <input type="text" name="labels[documentation_label]" class="form-control fw-semibold" value="{{ $labels['documentation_label'] }}">
                <div class="border mt-2" style="min-height: 96px;"></div>
            </div>
        </div>
    </div>

    <div class="content-card">
        <div class="card-heading">
            <div>
                <h2>Approval Footer</h2>
                <div class="text-muted small">Struktur approval dikunci dan selalu muncul di bagian bawah form commissioning.</div>
            </div>
        </div>
        <div class="row g-3">
            @foreach (FixedCommissioningTemplate::approvalColumns() as $column)
                @php($approvalName = $approvalDefaults[$column['key']]['name'] ?? '')
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="qc-approval-box is-locked h-100">
                        <small>{{ $column['group'] }}</small>
                        <strong>{{ $column['label'] }}</strong>
                        <input type="text" name="approval_defaults[{{ $column['key'] }}][name]" class="form-control mt-3" value="{{ old("approval_defaults.{$column['key']}.name", $approvalName) }}" placeholder="Nama penanda tangan">
                        <span><i class="bi bi-lock me-1"></i>Terkunci</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('admin.template-form-commissioning.index') }}" class="btn btn-outline-secondary">Batal</a>
        <button class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</form>

@push('scripts')
<script>
(() => {
    const list = document.querySelector('[data-check-row-list]');
    const addSimpleRow = (selector, rowAttr, inputName) => {
        const target = document.querySelector(selector);
        const index = target.querySelectorAll(`[${rowAttr}]`).length;
        const columns = inputName === 'motor_test_rows' ? {{ count($motorTestFields) }} : {{ count($gearboxTestFields) }};
        const cells = Array.from({ length: columns }, () => `<td><input type="hidden" name="${inputName}[${index}][no]" value="${index + 1}"></td>`).join('');
        target.insertAdjacentHTML('beforeend', `<tr ${rowAttr}>
            ${cells}
            <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger" data-remove-row>Hapus</button></td>
        </tr>`);
    };
    document.querySelector('[data-add-motor-row]')?.addEventListener('click', () => addSimpleRow('[data-motor-row-list]', 'data-motor-row', 'motor_test_rows'));
    document.querySelector('[data-add-gearbox-row]')?.addEventListener('click', () => addSimpleRow('[data-gearbox-row-list]', 'data-gearbox-row', 'gearbox_test_rows'));
    document.querySelector('[data-add-check-row]')?.addEventListener('click', () => {
        const index = list.querySelectorAll('[data-check-row]').length;
        list.insertAdjacentHTML('beforeend', `<tr data-check-row>
            <td><input type="text" name="equipment_check_rows[${index}][no]" class="form-control form-control-sm" value="${index + 1}"></td>
            <td><input type="text" name="equipment_check_rows[${index}][item]" class="form-control form-control-sm" required></td>
            <td><input type="checkbox" disabled></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><button type="button" class="btn btn-sm btn-outline-danger" data-remove-row>Hapus</button></td>
        </tr>`);
    });
    document.addEventListener('click', (event) => {
        const remove = event.target.closest('[data-remove-row]');
        if (remove) remove.closest('tr')?.remove();
    });
})();
</script>
@endpush

@push('styles')
<style>
    .commissioning-template-table-action {
        border-top: 1px solid #e2e8f0;
        margin-top: 1rem;
        padding-top: 1rem;
    }

    @media (max-width: 767.98px) {
        .commissioning-template-table-action {
            align-items: stretch !important;
            flex-direction: column;
        }

        .commissioning-template-table-action .btn {
            width: 100%;
        }
    }
</style>
@endpush

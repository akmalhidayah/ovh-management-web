@php
    use App\Support\QcTemplates\FixedQcTemplate;

    $statusOptions = ['draft' => 'Draft', 'active' => 'Aktif', 'inactive' => 'Nonaktif'];
    $typeOptions = FixedQcTemplate::types();
    $selectedType = old('template_type', $template->template_type ?: FixedQcTemplate::TYPE_GENERAL);
    $schema = FixedQcTemplate::normalizeSchema($selectedType, old() ? [
        'rows' => old('general_rows', []),
        'welder_rows' => old('welding_welder_rows', []),
        'result_rows' => old('welding_result_rows', []),
    ] : ($template->body_schema ?? FixedQcTemplate::defaultSchema($selectedType)));
    $generalRows = $schema['rows'] ?? FixedQcTemplate::defaultSchema(FixedQcTemplate::TYPE_GENERAL)['rows'];
    $welderRows = $schema['welder_rows'] ?? [];
    $resultRows = $schema['result_rows'] ?? FixedQcTemplate::defaultSchema(FixedQcTemplate::TYPE_WELDING)['result_rows'];

    if ($generalRows === []) {
        $generalRows = FixedQcTemplate::defaultSchema(FixedQcTemplate::TYPE_GENERAL)['rows'];
    }

    if ($resultRows === []) {
        $resultRows = FixedQcTemplate::defaultSchema(FixedQcTemplate::TYPE_WELDING)['result_rows'];
    }
@endphp

<form action="{{ $action }}" method="POST">
    @csrf
    @isset($method)
        @method($method)
    @endisset

    <div class="content-card mb-3">
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <label class="form-label">Kode Form</label>
                <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $template->code) }}" placeholder="QCR-BC-001">
                @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-8">
                <label class="form-label">Nama Template</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $template->name) }}" required placeholder="Standard QCR Penggantian Belt Conveyor">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">Kategori</label>
                <input type="text" name="category" class="form-control" value="{{ old('category', $template->category ?? 'QC') }}" placeholder="QC">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label">Versi</label>
                <input type="text" name="version" class="form-control" value="{{ old('version', $template->version ?? '1.0') }}">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $template->status ?? 'draft') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">Template Type / Jenis Template</label>
                <select name="template_type" class="form-select" data-template-type>
                    @foreach ($typeOptions as $value => $label)
                        <option value="{{ $value }}" @selected($selectedType === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" rows="3" class="form-control" placeholder="Ringkasan penggunaan template">{{ old('description', $template->description) }}</textarea>
            </div>
        </div>
    </div>

    <div class="content-card mb-3">
        <div class="card-heading">
            <h2>Header</h2>
            <span class="text-muted small">Terkunci</span>
        </div>
        <div class="qc-info-grid">
            @foreach (FixedQcTemplate::headerFields() as $field)
                <div class="qc-info-field">
                    <label>{{ $field['label'] }}</label>
                    <input type="{{ $field['type'] }}" class="form-control" disabled>
                </div>
            @endforeach
        </div>
    </div>

    <div class="content-card" data-general-editor>
        <div class="card-heading align-items-center">
            <div>
                <h2>Body QC Umum</h2>
                <div class="text-muted small">Atur row default tabel QC Umum.</div>
            </div>
            <button type="button" class="btn btn-primary btn-sm" data-add-general-row>
                <i class="bi bi-plus-lg me-1"></i>Tambah Row
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light text-center">
                    <tr>
                        <th style="width: 70px;">Urutan</th>
                        <th>Item Pengecekan</th>
                        <th>Standar</th>
                        <th style="width: 180px;">Actual</th>
                        <th colspan="2">Status</th>
                        <th>Catatan</th>
                        <th style="width: 90px;">Aksi</th>
                    </tr>
                    <tr>
                        <th></th>
                        <th class="fw-normal fst-italic">Mengikuti Jenis Alat</th>
                        <th class="fw-normal fst-italic">Mengikuti Jenis Alat</th>
                        <th class="fw-normal fst-italic">Manual</th>
                        <th>Ok</th>
                        <th>Not Ok</th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody data-general-row-list>
                    @foreach ($generalRows as $index => $row)
                        <tr data-general-row>
                            <td><input type="number" name="general_rows[{{ $index }}][urutan]" class="form-control form-control-sm" value="{{ $row['urutan'] ?? $loop->iteration }}"></td>
                            <td><input type="text" name="general_rows[{{ $index }}][item_pengecekan]" class="form-control form-control-sm" value="{{ $row['item_pengecekan'] ?? '' }}" placeholder="Item Pengecekan"></td>
                            <td><input type="text" name="general_rows[{{ $index }}][standar]" class="form-control form-control-sm" value="{{ $row['standar'] ?? '' }}" placeholder="Standar"></td>
                            <td><input type="text" name="general_rows[{{ $index }}][actual_default]" class="form-control form-control-sm" value="{{ $row['actual_default'] ?? '' }}" placeholder="Manual"></td>
                            <td class="text-center"><input type="checkbox" disabled></td>
                            <td class="text-center"><input type="checkbox" disabled></td>
                            <td><input type="text" class="form-control form-control-sm" disabled placeholder="Diisi user"></td>
                            <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm" data-remove-row>Hapus</button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="content-card" data-welding-editor>
        <div class="card-heading">
            <div>
                <h2>Body QC Welding</h2>
                <div class="text-muted small">Metode QC dan pengecekan ke tampil otomatis di form user. Admin hanya menyiapkan row default tabel.</div>
            </div>
        </div>

        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle mb-0">
                <tbody>
                    <tr>
                        <td class="fw-semibold" style="width: 140px;">Metode QC</td>
                        @foreach (FixedQcTemplate::defaultMethods() as $method)
                            <td class="text-center fw-semibold">{{ $method }}</td>
                        @endforeach
                        <td class="border-0" style="width: 80px;"></td>
                        <td class="fw-semibold" style="width: 160px;">Pengecekan ke:</td>
                        @foreach (FixedQcTemplate::defaultCheckSteps() as $step)
                            <td class="text-center fw-semibold">{{ $step }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td></td>
                        @foreach (FixedQcTemplate::defaultMethods() as $method)
                            <td class="text-center"><input type="checkbox" disabled></td>
                        @endforeach
                        <td class="border-0"></td>
                        <td></td>
                        @foreach (FixedQcTemplate::defaultCheckSteps() as $step)
                            <td class="text-center"><input type="checkbox" disabled></td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
            <strong>Tabel Welder</strong>
            <button type="button" class="btn btn-outline-primary btn-sm" data-add-welder-row>Tambah Row Welder</button>
        </div>
        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light text-center">
                    <tr>
                        <th style="width: 70px;">No</th>
                        <th>Nama Welder</th>
                        <th>Posisi Pengelasan</th>
                        <th>Diameter Electrode</th>
                        <th>Electrode/Filter</th>
                        <th>Amper</th>
                        <th>Keterangan</th>
                        <th style="width: 90px;">Aksi</th>
                    </tr>
                </thead>
                <tbody data-welder-row-list>
                    @foreach ($welderRows as $index => $row)
                        <tr data-welder-row>
                            <td><input type="text" name="welding_welder_rows[{{ $index }}][no]" class="form-control form-control-sm" value="{{ $row['no'] ?? $loop->iteration }}"></td>
                            <td><input type="text" name="welding_welder_rows[{{ $index }}][nama_welder]" class="form-control form-control-sm" value="{{ $row['nama_welder'] ?? '' }}"></td>
                            <td><input type="text" name="welding_welder_rows[{{ $index }}][posisi_pengelasan]" class="form-control form-control-sm" value="{{ $row['posisi_pengelasan'] ?? '' }}"></td>
                            <td><input type="text" name="welding_welder_rows[{{ $index }}][diameter_electrode]" class="form-control form-control-sm" value="{{ $row['diameter_electrode'] ?? '' }}"></td>
                            <td><input type="text" name="welding_welder_rows[{{ $index }}][electrode_filter]" class="form-control form-control-sm" value="{{ $row['electrode_filter'] ?? '' }}"></td>
                            <td><input type="text" name="welding_welder_rows[{{ $index }}][amper]" class="form-control form-control-sm" value="{{ $row['amper'] ?? '' }}"></td>
                            <td><input type="text" name="welding_welder_rows[{{ $index }}][keterangan]" class="form-control form-control-sm" value="{{ $row['keterangan'] ?? '' }}"></td>
                            <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm" data-remove-row>Hapus</button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
            <strong>Tabel Hasil QC Welding</strong>
            <button type="button" class="btn btn-outline-primary btn-sm" data-add-result-row>Tambah Row Hasil</button>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light text-center">
                    <tr>
                        <th style="width: 70px;">No</th>
                        <th>Deskripsi</th>
                        <th>Baik</th>
                        <th>Perlu Perbaikan</th>
                        <th>Tidak Layak</th>
                        <th>Keterangan</th>
                        <th style="width: 90px;">Aksi</th>
                    </tr>
                </thead>
                <tbody data-result-row-list>
                    @foreach ($resultRows as $index => $row)
                        <tr data-result-row>
                            <td><input type="text" name="welding_result_rows[{{ $index }}][no]" class="form-control form-control-sm" value="{{ $row['no'] ?? $loop->iteration }}"></td>
                            <td><input type="text" name="welding_result_rows[{{ $index }}][deskripsi]" class="form-control form-control-sm" value="{{ $row['deskripsi'] ?? '' }}"></td>
                            <td class="text-center"><input type="checkbox" disabled></td>
                            <td class="text-center"><input type="checkbox" disabled></td>
                            <td class="text-center"><input type="checkbox" disabled></td>
                            <td><input type="text" name="welding_result_rows[{{ $index }}][keterangan]" class="form-control form-control-sm" value="{{ $row['keterangan'] ?? '' }}"></td>
                            <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm" data-remove-row>Hapus</button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="content-card mt-3">
        <div class="card-heading">
            <h2>Catatan</h2>
            <span class="text-muted small">Terkunci</span>
        </div>
        <textarea class="form-control" rows="4" placeholder="Catatan diisi oleh user QC" disabled></textarea>
    </div>

    <div class="content-card mt-3">
        <div class="card-heading">
            <h2>Lampiran Foto/Gambar</h2>
            <span class="text-muted small">Terkunci</span>
        </div>
        <div class="row g-3">
            @foreach (['Foto Before', 'Foto After', 'Dokumen Pendukung'] as $label)
                <div class="col-12 col-md-4">
                    <div class="qc-preview-attachment-box h-100">
                        <i class="bi bi-images"></i>
                        <strong>{{ $label }}</strong>
                        <span>Upload oleh user QC</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="content-card mt-3">
        <div class="card-heading">
            <h2>Approval Footer</h2>
            <span class="text-muted small">Terkunci</span>
        </div>
        <p class="text-muted">Baru bisa ter approve jika form sudah terisi semua & Final Check sudah tercentang:</p>
        <label class="d-inline-flex align-items-center gap-2 mb-3">
            <input type="checkbox" disabled>
            <strong>Final Check</strong>
        </label>
        <div class="qc-approval-grid">
            @foreach (FixedQcTemplate::approvalColumns() as $column)
                <div class="qc-approval-box">
                    <small>{{ $column['group'] }}</small>
                    <strong>{{ $column['label'] }}</strong>
                    <input type="text" class="form-control mt-2" placeholder="Nama" disabled>
                    <input type="date" class="form-control mt-2" disabled>
                    <span>{{ $column['key'] === 'qc_inspector_qc_inspektor' ? 'Tanda tangan user QC' : 'Tanda tangan terkunci' }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="d-flex flex-wrap justify-content-end gap-2">
        <a href="{{ route('admin.template-form-qc.index') }}" class="btn btn-outline-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</form>

@push('scripts')
    <script>
        (() => {
            const typeSelect = document.querySelector('[data-template-type]');
            const generalEditor = document.querySelector('[data-general-editor]');
            const weldingEditor = document.querySelector('[data-welding-editor]');
            const generalList = document.querySelector('[data-general-row-list]');
            const welderList = document.querySelector('[data-welder-row-list]');
            const resultList = document.querySelector('[data-result-row-list]');

            const syncType = () => {
                const isWelding = typeSelect?.value === 'welding';
                generalEditor?.classList.toggle('d-none', isWelding);
                weldingEditor?.classList.toggle('d-none', !isWelding);
            };

            const nextIndex = (list, selector) => list.querySelectorAll(selector).length;

            document.querySelector('[data-add-general-row]')?.addEventListener('click', () => {
                const index = nextIndex(generalList, '[data-general-row]');
                generalList.insertAdjacentHTML('beforeend', `<tr data-general-row>
                    <td><input type="number" name="general_rows[${index}][urutan]" class="form-control form-control-sm" value="${index + 1}"></td>
                    <td><input type="text" name="general_rows[${index}][item_pengecekan]" class="form-control form-control-sm" placeholder="Item Pengecekan"></td>
                    <td><input type="text" name="general_rows[${index}][standar]" class="form-control form-control-sm" placeholder="Standar"></td>
                    <td><input type="text" name="general_rows[${index}][actual_default]" class="form-control form-control-sm" placeholder="Manual"></td>
                    <td class="text-center"><input type="checkbox" disabled></td>
                    <td class="text-center"><input type="checkbox" disabled></td>
                    <td><input type="text" class="form-control form-control-sm" disabled placeholder="Diisi user"></td>
                    <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm" data-remove-row>Hapus</button></td>
                </tr>`);
            });

            document.querySelector('[data-add-welder-row]')?.addEventListener('click', () => {
                const index = nextIndex(welderList, '[data-welder-row]');
                welderList.insertAdjacentHTML('beforeend', `<tr data-welder-row>
                    <td><input type="text" name="welding_welder_rows[${index}][no]" class="form-control form-control-sm" value="${index + 1}"></td>
                    <td><input type="text" name="welding_welder_rows[${index}][nama_welder]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="welding_welder_rows[${index}][posisi_pengelasan]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="welding_welder_rows[${index}][diameter_electrode]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="welding_welder_rows[${index}][electrode_filter]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="welding_welder_rows[${index}][amper]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="welding_welder_rows[${index}][keterangan]" class="form-control form-control-sm"></td>
                    <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm" data-remove-row>Hapus</button></td>
                </tr>`);
            });

            document.querySelector('[data-add-result-row]')?.addEventListener('click', () => {
                const index = nextIndex(resultList, '[data-result-row]');
                resultList.insertAdjacentHTML('beforeend', `<tr data-result-row>
                    <td><input type="text" name="welding_result_rows[${index}][no]" class="form-control form-control-sm" value="${index + 1}"></td>
                    <td><input type="text" name="welding_result_rows[${index}][deskripsi]" class="form-control form-control-sm"></td>
                    <td class="text-center"><input type="checkbox" disabled></td>
                    <td class="text-center"><input type="checkbox" disabled></td>
                    <td class="text-center"><input type="checkbox" disabled></td>
                    <td><input type="text" name="welding_result_rows[${index}][keterangan]" class="form-control form-control-sm"></td>
                    <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm" data-remove-row>Hapus</button></td>
                </tr>`);
            });

            document.addEventListener('click', (event) => {
                const remove = event.target.closest('[data-remove-row]');
                if (remove) {
                    remove.closest('tr')?.remove();
                }
            });

            typeSelect?.addEventListener('change', syncType);
            syncType();
        })();
    </script>
@endpush

@php
    $statusOptions = ['draft' => 'Draft', 'active' => 'Aktif', 'inactive' => 'Nonaktif'];
    $layoutOptions = ['block_based' => 'Block Based', 'excel_grid' => 'Excel Grid'];
    $blockOptions = [
        'general_info' => 'Informasi Umum',
        'checklist_table' => 'Checklist Table',
        'measurement_table' => 'Measurement Table',
        'note' => 'Note',
        'attachment' => 'Attachment',
        'approval' => 'Approval',
    ];

    $existingBlocks = old('blocks');
    if ($existingBlocks === null) {
        $existingBlocks = $blocks->map(function ($block) {
            $columns = $block->config['columns'] ?? [];

            return [
                'type' => $block->type,
                'title' => $block->title,
                'columns' => implode(', ', array_map(fn ($column) => is_array($column) ? ($column['label'] ?? $column['key'] ?? '') : $column, $columns)),
                'fields' => $block->fields->map(fn ($field) => [
                    'name' => $field->field_name,
                    'label' => $field->label,
                    'type' => $field->type,
                ])->values()->all(),
                'rows' => $block->tableRows->map(fn ($row) => $row->row_data ?? [])->values()->all(),
            ];
        })->values()->all();
    }

    $existingBlocks = array_values($existingBlocks ?: [
        ['type' => 'general_info', 'title' => 'Informasi Umum', 'columns' => 'Label, Input', 'rows' => []],
        ['type' => 'checklist_table', 'title' => 'Item Pengecekan', 'columns' => 'Kategori, Item Pengecekan, Standar, Status, Catatan', 'rows' => [
            ['kategori' => '', 'item' => '', 'standar' => '', 'catatan' => ''],
        ]],
        ['type' => 'approval', 'title' => 'Approval', 'columns' => 'Tanggal, *1 Diisi, *2 Disetujui, *3 Disetujui', 'rows' => []],
    ]);
@endphp

<form action="{{ $action }}" method="POST">
    @csrf
    @isset($method)
        @method($method)
    @endisset

    <div class="content-card template-manual-guide">
        <div class="manual-guide-head">
            <div>
                <span>Panduan Manual</span>
                <h2>Cara Membuat Template Form QC</h2>
                <p>Isi template dari atas ke bawah. Simpan sebagai Draft dulu, cek hasilnya lewat Preview, lalu aktifkan setelah formatnya sudah sesuai.</p>
            </div>
            <div class="manual-guide-badge">
                <i class="bi bi-ui-checks-grid"></i>
                <strong>Block Based</strong>
            </div>
        </div>

        <div class="manual-guide-grid">
            <div class="manual-guide-step">
                <span>1</span>
                <strong>Lengkapi Identitas</strong>
                <p>Pakai kode unik, nama template yang jelas, kategori QC, versi, dan status Draft untuk proses review.</p>
            </div>
            <div class="manual-guide-step">
                <span>2</span>
                <strong>Susun Bagian</strong>
                <p>Gunakan Informasi Umum, Checklist/Measurement, Attachment, Note, dan Approval sesuai urutan dokumen QC.</p>
            </div>
            <div class="manual-guide-step">
                <span>3</span>
                <strong>Isi Kolom & Row</strong>
                <p>Tulis nama kolom dipisahkan koma. Untuk checklist, isi kategori, item, standar, dan catatan default.</p>
            </div>
            <div class="manual-guide-step">
                <span>4</span>
                <strong>Preview & Aktifkan</strong>
                <p>Simpan template, buka Preview untuk cek tampilan form/PDF, lalu publish agar bisa dipakai user QC.</p>
            </div>
        </div>

        <div class="manual-guide-notes">
            <div>
                <strong>Contoh kolom checklist</strong>
                <code>Kategori, Item Pengecekan, Standar, Status, Catatan</code>
            </div>
            <div>
                <strong>Contoh kolom measurement</strong>
                <code>No, Parameter, Standar, Aktual, Unit, Keterangan</code>
            </div>
            <div>
                <strong>Approval</strong>
                <code>Tanggal, *1 Diisi, *2 Disetujui, *3 Disetujui</code>
            </div>
        </div>
    </div>

    <div class="content-card mb-3">
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <label class="form-label">Kode Form</label>
                <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $template->code) }}" placeholder="QCR-BC-001">
                <div class="form-text">Contoh: QCR-BC-001. Harus unik agar mudah dicari.</div>
                @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-8">
                <label class="form-label">Nama Template</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $template->name) }}" required placeholder="Standard QCR Penggantian Belt Conveyor">
                <div class="form-text">Gunakan nama pekerjaan/equipment yang spesifik, misalnya Standard QCR Penggantian Belt Conveyor.</div>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">Kategori</label>
                <input type="text" name="category" class="form-control" value="{{ old('category', $template->category ?? 'QC') }}" placeholder="QC">
            </div>
            <div class="col-6 col-md-4">
                <label class="form-label">Versi</label>
                <input type="text" name="version" class="form-control" value="{{ old('version', $template->version ?? '1.0') }}">
            </div>
            <div class="col-6 col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $template->status ?? 'draft') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">Layout Mode</label>
                <select name="layout_mode" class="form-select" data-layout-mode>
                    @foreach ($layoutOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('layout_mode', $template->layout_mode === 'excel_like' ? 'block_based' : ($template->layout_mode ?? 'block_based')) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" rows="3" class="form-control" placeholder="Ringkasan penggunaan template">{{ old('description', $template->description) }}</textarea>
                <div class="form-text">Isi ringkasan kapan template ini digunakan dan batasan pemeriksaannya.</div>
            </div>
            <div class="col-12">
                <div class="alert alert-info mb-0 {{ old('layout_mode', $template->layout_mode ?? 'block_based') === 'excel_grid' ? '' : 'd-none' }}" data-excel-grid-note>
                    Mode Excel Grid saat ini disarankan dibuat melalui import Excel atau seed/template khusus. Editor visual grid akan dikembangkan berikutnya.
                </div>
            </div>
        </div>
    </div>

    <div class="content-card">
        <div class="card-heading align-items-center">
            <div>
                <h2>Bagian Template</h2>
                <div class="text-muted small">Susun template memakai bagian agar format lama yang mirip Excel tetap fleksibel.</div>
            </div>
            <button type="button" class="btn btn-primary btn-sm" data-add-block>
                <i class="bi bi-plus-lg me-1"></i>Tambah Bagian
            </button>
        </div>

        <div class="template-builder" data-block-list>
            @foreach ($existingBlocks as $blockIndex => $block)
                @php
                    $type = $block['type'] ?? 'note';
                    $rows = array_values($block['rows'] ?? []);
                    if (in_array($type, ['checklist_table', 'measurement_table'], true) && $rows === []) {
                        $rows[] = $type === 'checklist_table'
                            ? ['activity' => '', 'standard' => '', 'actual_type' => 'text', 'note' => '']
                            : ['parameter' => '', 'standard' => '', 'actual' => '', 'unit' => '', 'note' => ''];
                    }
                @endphp
                <div class="builder-block" data-block data-block-index="{{ $blockIndex }}">
                    <div class="builder-block-head">
                        <div class="row g-2 flex-grow-1">
                            <div class="col-12 col-md-3">
                                <label class="form-label">Jenis Bagian</label>
                                <select name="blocks[{{ $blockIndex }}][type]" class="form-select" data-block-type>
                                    @foreach ($blockOptions as $value => $label)
                                        <option value="{{ $value }}" @selected($type === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">Pilih jenis sesuai isi bagian dokumen.</div>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Judul Bagian</label>
                                <input type="text" name="blocks[{{ $blockIndex }}][title]" class="form-control" value="{{ $block['title'] ?? '' }}" placeholder="Judul block">
                                <div class="form-text">Contoh: Informasi Umum, Item Pengecekan, Lampiran Foto.</div>
                            </div>
                            <div class="col-12 col-md-5">
                                <label class="form-label">Kolom Tabel</label>
                                <input type="text" name="blocks[{{ $blockIndex }}][columns]" class="form-control" value="{{ $block['columns'] ?? '' }}" placeholder="No, Aktivitas, Standar, Aktual, Keterangan">
                                <div class="form-text">Pisahkan nama kolom dengan koma. Urutan kolom mengikuti teks ini.</div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-danger btn-sm" data-remove-block title="Hapus block">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>

                    <div class="builder-rows {{ in_array($type, ['checklist_table', 'measurement_table'], true) ? '' : 'd-none' }}" data-row-area>
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                            <strong class="small">Item Pemeriksaan</strong>
                            <button type="button" class="btn btn-outline-primary btn-sm" data-add-row>
                                <i class="bi bi-plus-lg me-1"></i>Tambah Row
                            </button>
                        </div>
                        <div data-row-list>
                            @foreach ($rows as $rowIndex => $row)
                                @if ($type === 'measurement_table')
                                    <div class="builder-row" data-row>
                                        <input type="text" name="blocks[{{ $blockIndex }}][rows][{{ $rowIndex }}][parameter]" class="form-control" value="{{ $row['parameter'] ?? '' }}" placeholder="Parameter">
                                        <input type="text" name="blocks[{{ $blockIndex }}][rows][{{ $rowIndex }}][standard]" class="form-control" value="{{ $row['standard'] ?? $row['standar'] ?? '' }}" placeholder="Standar">
                                        <input type="text" name="blocks[{{ $blockIndex }}][rows][{{ $rowIndex }}][actual]" class="form-control" value="{{ $row['actual'] ?? $row['aktual'] ?? '' }}" placeholder="Aktual">
                                        <input type="text" name="blocks[{{ $blockIndex }}][rows][{{ $rowIndex }}][unit]" class="form-control" value="{{ $row['unit'] ?? '' }}" placeholder="Unit">
                                        <input type="text" name="blocks[{{ $blockIndex }}][rows][{{ $rowIndex }}][note]" class="form-control" value="{{ $row['note'] ?? $row['keterangan'] ?? '' }}" placeholder="Keterangan">
                                        <button type="button" class="btn btn-outline-danger btn-sm" data-remove-row><i class="bi bi-x-lg"></i></button>
                                    </div>
                                @else
                                    <div class="builder-row" data-row>
                                        <input type="text" name="blocks[{{ $blockIndex }}][rows][{{ $rowIndex }}][kategori]" class="form-control" value="{{ $row['kategori'] ?? '' }}" placeholder="Kategori">
                                        <input type="text" name="blocks[{{ $blockIndex }}][rows][{{ $rowIndex }}][item]" class="form-control" value="{{ $row['item'] ?? $row['activity'] ?? $row['aktivitas'] ?? '' }}" placeholder="Item">
                                        <input type="text" name="blocks[{{ $blockIndex }}][rows][{{ $rowIndex }}][standar]" class="form-control" value="{{ $row['standar'] ?? $row['standard'] ?? '' }}" placeholder="Standar">
                                        <input type="text" name="blocks[{{ $blockIndex }}][rows][{{ $rowIndex }}][catatan]" class="form-control" value="{{ $row['catatan'] ?? $row['note'] ?? $row['keterangan'] ?? '' }}" placeholder="Catatan">
                                        <button type="button" class="btn btn-outline-danger btn-sm" data-remove-row><i class="bi bi-x-lg"></i></button>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <div class="builder-fields {{ in_array($type, ['checklist_table', 'measurement_table'], true) ? 'd-none' : '' }}" data-field-area>
                        <strong class="small d-block mb-2">Field</strong>
                        @foreach (array_values($block['fields'] ?? []) as $fieldIndex => $field)
                            <div class="builder-field-row">
                                <input type="text" name="blocks[{{ $blockIndex }}][fields][{{ $fieldIndex }}][name]" class="form-control" value="{{ $field['name'] ?? '' }}" placeholder="Nama field">
                                <input type="text" name="blocks[{{ $blockIndex }}][fields][{{ $fieldIndex }}][label]" class="form-control" value="{{ $field['label'] ?? '' }}" placeholder="Label">
                                <select name="blocks[{{ $blockIndex }}][fields][{{ $fieldIndex }}][type]" class="form-select">
                                    @foreach (['text' => 'Text', 'number' => 'Number', 'date' => 'Date', 'textarea' => 'Textarea', 'signature' => 'Signature', 'signature_locked' => 'Signature Locked'] as $value => $label)
                                        <option value="{{ $value }}" @selected(($field['type'] ?? 'text') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                    </div>
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
            const blockList = document.querySelector('[data-block-list]');
            const layoutMode = document.querySelector('[data-layout-mode]');
            const excelNote = document.querySelector('[data-excel-grid-note]');
            const blockOptions = @json($blockOptions);
            let blockIndex = blockList.querySelectorAll('[data-block]').length;

            const optionHtml = (selected = 'note') => Object.entries(blockOptions)
                .map(([value, label]) => `<option value="${value}" ${value === selected ? 'selected' : ''}>${label}</option>`)
                .join('');

            const defaultColumns = (type) => {
                if (type === 'checklist_table') return 'Kategori, Item Pengecekan, Standar, Status, Catatan';
                if (type === 'measurement_table') return 'No, Parameter, Standar, Aktual, Unit, Keterangan';
                if (type === 'approval') return 'Tanggal, *1 Diisi, *2 Disetujui, *3 Disetujui';
                return 'Label, Input';
            };

            const rowHtml = (block, row, type) => {
                if (type === 'measurement_table') {
                    return `<div class="builder-row" data-row>
                        <input type="text" name="blocks[${block}][rows][${row}][parameter]" class="form-control" placeholder="Parameter">
                        <input type="text" name="blocks[${block}][rows][${row}][standard]" class="form-control" placeholder="Standar">
                        <input type="text" name="blocks[${block}][rows][${row}][actual]" class="form-control" placeholder="Aktual">
                        <input type="text" name="blocks[${block}][rows][${row}][unit]" class="form-control" placeholder="Unit">
                        <input type="text" name="blocks[${block}][rows][${row}][note]" class="form-control" placeholder="Keterangan">
                        <button type="button" class="btn btn-outline-danger btn-sm" data-remove-row><i class="bi bi-x-lg"></i></button>
                    </div>`;
                }

                return `<div class="builder-row" data-row>
                    <input type="text" name="blocks[${block}][rows][${row}][kategori]" class="form-control" placeholder="Kategori">
                    <input type="text" name="blocks[${block}][rows][${row}][item]" class="form-control" placeholder="Item">
                    <input type="text" name="blocks[${block}][rows][${row}][standar]" class="form-control" placeholder="Standar">
                    <input type="text" name="blocks[${block}][rows][${row}][catatan]" class="form-control" placeholder="Catatan">
                    <button type="button" class="btn btn-outline-danger btn-sm" data-remove-row><i class="bi bi-x-lg"></i></button>
                </div>`;
            };

            const blockHtml = (index) => `<div class="builder-block" data-block data-block-index="${index}">
                <div class="builder-block-head">
                    <div class="row g-2 flex-grow-1">
                        <div class="col-12 col-md-3">
                            <label class="form-label">Jenis Bagian</label>
                            <select name="blocks[${index}][type]" class="form-select" data-block-type>${optionHtml('note')}</select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Judul Bagian</label>
                            <input type="text" name="blocks[${index}][title]" class="form-control" placeholder="Judul block">
                        </div>
                        <div class="col-12 col-md-5">
                            <label class="form-label">Kolom Tabel</label>
                            <input type="text" name="blocks[${index}][columns]" class="form-control" value="${defaultColumns('note')}">
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-danger btn-sm" data-remove-block title="Hapus block"><i class="bi bi-trash"></i></button>
                </div>
                <div class="builder-rows d-none" data-row-area>
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                        <strong class="small">Item Pemeriksaan</strong>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-add-row><i class="bi bi-plus-lg me-1"></i>Tambah Row</button>
                    </div>
                    <div data-row-list></div>
                </div>
                <div class="builder-fields" data-field-area></div>
            </div>`;

            document.querySelector('[data-add-block]')?.addEventListener('click', () => {
                blockList.insertAdjacentHTML('beforeend', blockHtml(blockIndex++));
            });

            const syncLayoutNote = () => {
                excelNote?.classList.toggle('d-none', layoutMode?.value !== 'excel_grid');
            };

            layoutMode?.addEventListener('change', syncLayoutNote);
            syncLayoutNote();

            blockList.addEventListener('click', (event) => {
                const removeBlock = event.target.closest('[data-remove-block]');
                if (removeBlock) {
                    removeBlock.closest('[data-block]').remove();
                    return;
                }

                const addRow = event.target.closest('[data-add-row]');
                if (addRow) {
                    const block = addRow.closest('[data-block]');
                    const blockPosition = block.dataset.blockIndex;
                    const type = block.querySelector('[data-block-type]').value;
                    const rowList = block.querySelector('[data-row-list]');
                    rowList.insertAdjacentHTML('beforeend', rowHtml(blockPosition, rowList.querySelectorAll('[data-row]').length, type));
                    return;
                }

                const removeRow = event.target.closest('[data-remove-row]');
                if (removeRow) {
                    removeRow.closest('[data-row]').remove();
                }
            });

            blockList.addEventListener('change', (event) => {
                if (! event.target.matches('[data-block-type]')) return;

                const block = event.target.closest('[data-block]');
                const type = event.target.value;
                block.querySelector('[name$="[columns]"]').value = defaultColumns(type);
                block.querySelector('[data-row-area]').classList.toggle('d-none', ! ['checklist_table', 'measurement_table'].includes(type));
                block.querySelector('[data-field-area]').classList.toggle('d-none', ['checklist_table', 'measurement_table'].includes(type));
            });
        })();
    </script>
@endpush

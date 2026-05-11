@php
    $fieldDefault = fn ($field) => is_array($field->options ?? null) ? ($field->options['default'] ?? '') : '';
    $fieldType = fn ($field) => in_array($field->type, ['number', 'date'], true) ? $field->type : 'text';
    $columnLabel = fn ($column) => is_array($column) ? ($column['label'] ?? $column['key'] ?? '') : $column;
    $columnKey = fn ($column) => is_array($column) ? ($column['key'] ?? Str::snake($column['label'] ?? 'kolom')) : Str::snake($column);
    $columnType = fn ($column) => is_array($column) ? ($column['type'] ?? 'text') : 'text';
    $columnOptions = fn ($column) => is_array($column) ? ($column['options'] ?? ['OK', 'Not OK']) : ['OK', 'Not OK'];
    $attachmentKey = fn ($field) => is_array($field) ? ($field['key'] ?? $field['name'] ?? Str::snake($field['label'] ?? 'lampiran')) : $field->field_name;
    $attachmentLabel = fn ($field) => is_array($field) ? ($field['label'] ?? $field['key'] ?? 'Lampiran') : $field->label;
    $attachmentType = fn ($field) => is_array($field) ? ($field['type'] ?? 'file') : $field->type;
    $attachmentAccept = fn ($field) => is_array($field) ? ($field['accept'] ?? null) : ($field->options['accept'] ?? null);
    $attachmentMultiple = fn ($field) => is_array($field) ? (bool) ($field['multiple'] ?? false) : (bool) ($field->options['multiple'] ?? false);
    $attachmentMaxFiles = fn ($field) => is_array($field) ? ($field['max_files'] ?? null) : ($field->options['max_files'] ?? null);
    $signerName = auth()->user()?->name ?: 'User QC';
    $signerPosition = 'Quality Control Personil';
@endphp

@if ($selectedTemplate->template_type)
    @include('user.qc.forms.partials.fixed-form-renderer', ['selectedTemplate' => $selectedTemplate, 'draftSubmission' => $draftSubmission ?? null])
@else
<div class="qc-user-form">
    <section class="inspector-panel qc-form-card">
        <div class="qc-form-card-head">
            <div>
                <span>Template Aktif</span>
                <h2>{{ $selectedTemplate->name }}</h2>
                <p>{{ $selectedTemplate->description ?: 'Form QC dibuat berdasarkan template block-based yang sudah dipublish admin.' }}</p>
            </div>
            <div class="qc-form-code">
                <strong>{{ $selectedTemplate->code ?: 'Tanpa kode' }}</strong>
                <span>Versi {{ $selectedTemplate->version }}</span>
            </div>
        </div>
    </section>

    @foreach ($selectedTemplate->blocks as $block)
        @php
            $columns = $block->config['columns'] ?? [];
        @endphp

        <section class="inspector-panel qc-form-card">
            <div class="qc-form-section-title">
                <h3>{{ $block->title ?: Str::headline($block->type) }}</h3>
                <span>{{ Str::headline(str_replace('_', ' ', $block->type)) }}</span>
            </div>

            @if ($block->type === 'general_info')
                <div class="qc-user-field-grid">
                    @foreach ($block->fields as $field)
                        <label class="qc-user-field {{ in_array($field->field_name, ['pekerjaan', 'description'], true) ? 'wide' : '' }}">
                            <span>{{ $field->label }}</span>
                            <input
                                type="{{ $fieldType($field) }}"
                                name="general_info[{{ $field->field_name }}]"
                                value="{{ $fieldDefault($field) }}"
                                class="form-control"
                                @readonly($field->readonly)
                            >
                        </label>
                    @endforeach
                </div>
            @elseif (in_array($block->type, ['checklist_table', 'measurement_table'], true))
                @if ($block->tableRows->isEmpty())
                    <div class="qc-empty-inline">
                        {{ $block->type === 'measurement_table' ? 'Belum ada item pengukuran.' : 'Belum ada item checklist.' }}
                    </div>
                @else
                    <div class="qc-user-table-wrap">
                        <table class="qc-user-checklist-table">
                            <thead>
                                <tr>
                                    @foreach ($columns as $column)
                                        <th>{{ $columnLabel($column) }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $previousKategori = null;
                                @endphp
                                @foreach ($block->tableRows as $row)
                                    @php
                                        $data = $row->row_data ?? [];
                                        $currentKategori = trim((string) ($data['kategori'] ?? ''));
                                        $kategoriChanged = $currentKategori !== '' && $currentKategori !== $previousKategori;
                                        if ($currentKategori !== '') {
                                            $previousKategori = $currentKategori;
                                        }
                                    @endphp
                                    <tr class="{{ $kategoriChanged ? 'qc-user-category-break' : '' }}">
                                        @foreach ($columns as $column)
                                            @php
                                                $key = $columnKey($column);
                                                $type = $columnType($column);
                                            @endphp
                                            <td class="{{ $key === 'kategori' ? 'qc-user-category-cell' : '' }}">
                                                @if ($type === 'radio')
                                                    <div class="qc-user-status-inline">
                                                        @foreach ($columnOptions($column) as $option)
                                                            <label>
                                                                <input type="radio" name="rows[{{ $block->id }}][{{ $row->id }}][status_value]" value="{{ $option }}">
                                                                <span>{{ $option }}</span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                @elseif ($type === 'textarea')
                                                    <textarea class="form-control qc-user-table-note" name="rows[{{ $block->id }}][{{ $row->id }}][catatan]">{{ $data[$key] ?? '' }}</textarea>
                                                @elseif (in_array($key, ['kategori', 'item', 'standar', 'aktivitas', 'no'], true))
                                                    <input type="hidden" name="rows[{{ $block->id }}][{{ $row->id }}][{{ $key }}]" value="{{ $data[$key] ?? '' }}">
                                                    <span class="qc-static-cell">{{ $data[$key] ?? '' }}</span>
                                                @else
                                                    @php
                                                        $inputName = $key === 'status' ? 'status_value' : ($key === 'actual' ? 'aktual' : $key);
                                                    @endphp
                                                    <input type="{{ $type === 'number' ? 'number' : 'text' }}" class="form-control form-control-sm" name="rows[{{ $block->id }}][{{ $row->id }}][{{ $inputName }}]" value="{{ $data[$key] ?? '' }}">
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @elseif ($block->type === 'note')
                @php
                    $field = $block->fields->first();
                @endphp
                <label class="qc-user-note-box">
                    <span>{{ $field?->label ?: 'Catatan' }}</span>
                    <textarea class="form-control" name="note" rows="4" placeholder="Tulis catatan umum pemeriksaan"></textarea>
                </label>
            @elseif ($block->type === 'attachment')
                @php
                    $attachmentFields = $block->config['fields'] ?? $block->fields;
                @endphp
                @if (! empty($block->config['description']))
                    <p class="qc-block-description">{{ $block->config['description'] }}</p>
                @endif
                <div class="qc-attachment-grid">
                    @forelse ($attachmentFields as $field)
                        @php
                            $key = $attachmentKey($field);
                            $label = $attachmentLabel($field);
                            $type = $attachmentType($field);
                            $accept = '.jpg,.jpeg,.png,image/jpeg,image/png';
                            $multiple = $attachmentMultiple($field);
                            $maxFiles = $attachmentMaxFiles($field);
                        @endphp
                        <div class="qc-upload-box" data-upload-box data-upload-type="{{ $type }}" data-max-files="{{ $maxFiles }}">
                            <div class="qc-upload-box-head">
                                <div>
                                    <strong>{{ $label }}</strong>
                                    <span>Hanya JPG atau PNG. {{ $multiple ? 'Bisa memilih beberapa file' : 'Pilih satu file' }}{{ $maxFiles ? ' - maks. '.$maxFiles.' file' : '' }}</span>
                                </div>
                                <i class="bi {{ $type === 'image' ? 'bi-images' : 'bi-paperclip' }}"></i>
                            </div>
                            <input
                                type="file"
                                class="form-control"
                                name="attachments[{{ $key }}][]"
                                data-upload-input
                                accept="{{ $accept }}"
                                @if ($multiple) multiple @endif
                            >
                            <div class="qc-upload-message" data-upload-message></div>
                            <div class="qc-upload-preview" data-upload-preview></div>
                        </div>
                    @empty
                        <div class="qc-empty-inline">Belum ada konfigurasi lampiran.</div>
                    @endforelse
                </div>
            @elseif ($block->type === 'approval')
                <div class="qc-user-approval-grid" style="--qc-approval-columns: {{ max(count($columns ?: $block->fields), 1) }}">
                    @foreach (($columns ?: $block->fields) as $column)
                        @php
                            $normalizedColumn = $column instanceof \App\Models\QcFormTemplateField
                                ? ['key' => $column->field_name, 'label' => $column->label, 'type' => $column->type, 'readonly' => $column->readonly]
                                : \App\Support\QcTemplates\TemplateBuilder::normalizeApprovalColumn($column);
                            $label = $normalizedColumn['label'];
                            $key = $normalizedColumn['key'];
                            $type = $normalizedColumn['type'];
                            $locked = str_contains($type, 'locked');
                            $readonly = $type === 'readonly' || (bool) ($normalizedColumn['readonly'] ?? false);
                        @endphp
                        <div class="qc-user-approval-box {{ $locked || $readonly ? 'is-locked' : '' }}" data-signature-card="{{ $key }}">
                            <div class="qc-approval-label-row">
                                <strong>{{ $label }}</strong>
                                @if ($locked)
                                    <span class="qc-approval-badge">Menunggu Persetujuan</span>
                                @elseif ($readonly)
                                    <span class="qc-approval-badge">Readonly</span>
                                @endif
                            </div>
                            @if ($type === 'date' || $key === 'tanggal')
                                <input type="date" class="form-control" name="approval[{{ $key }}]">
                            @elseif ($type === 'text')
                                <input type="text" class="form-control" name="approval[{{ $key }}]" placeholder="{{ $label }}">
                            @elseif ($readonly)
                                <div class="qc-readonly-box">{{ is_array($column) ? ($column['value'] ?? '-') : '-' }}</div>
                            @elseif ($locked)
                                <div class="qc-signature-locked">
                                    <i class="bi bi-lock"></i>
                                    <span>Terkunci sampai tahap approval.</span>
                                </div>
                                <button type="button" class="btn btn-outline-secondary" disabled>Tanda tangan</button>
                            @else
                                <input type="hidden" name="approval[{{ $key }}][signature]" data-signature-input>
                                <input type="hidden" name="approval[{{ $key }}][name]" value="{{ $signerName }}">
                                <input type="hidden" name="approval[{{ $key }}][role]" value="{{ $signerPosition }}">
                                <input type="hidden" name="approval[{{ $key }}][signed_at]" data-signature-time-input>
                                <div class="qc-signature-empty" data-signature-empty>
                                    <i class="bi bi-pen"></i>
                                    <span>Belum ditandatangani</span>
                                </div>
                                <div class="qc-signature-result d-none" data-signature-result>
                                    <img alt="Preview tanda tangan" data-signature-preview>
                                    <div>
                                        <strong data-signature-signer>{{ $signerName }}</strong>
                                        <span>{{ $signerPosition }}</span>
                                        <small data-signature-time></small>
                                    </div>
                                </div>
                                <div class="qc-signature-actions">
                                    <button
                                        type="button"
                                        class="btn btn-outline-primary"
                                        data-signature-open
                                        data-signature-key="{{ $key }}"
                                        data-signature-label="{{ $label }}"
                                    >
                                        <i class="bi bi-pen me-1"></i><span data-signature-button-label>Tanda Tangan</span>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger d-none" data-signature-remove>Hapus</button>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if (! empty($block->config['notes']))
                    <ul class="qc-user-approval-notes">
                        @foreach ($block->config['notes'] as $note)
                            <li>{{ $note }}</li>
                        @endforeach
                    </ul>
                @endif
            @endif
        </section>
    @endforeach

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
            <p>Tahap ini masih render form. Penyimpanan draft dan submit akan dibuat berikutnya.</p>
        </div>
        <div class="qc-form-actions">
            <button type="submit" name="action" value="draft" class="btn btn-primary">Simpan Draft</button>
            <button type="submit" name="action" value="submit" class="btn btn-success">Submit QC</button>
        </div>
    </section>
</div>
@endif

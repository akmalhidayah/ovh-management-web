@php
    $fieldDefault = fn ($field) => is_array($field->options ?? null) ? ($field->options['default'] ?? '') : '';
    $columnLabel = fn ($column) => is_array($column) ? ($column['label'] ?? $column['key'] ?? '') : $column;
    $columnKey = fn ($column) => is_array($column) ? ($column['key'] ?? Str::snake($column['label'] ?? 'kolom')) : Str::snake($column);
    $columnType = fn ($column) => is_array($column) ? ($column['type'] ?? 'text') : 'text';
@endphp

<div class="qc-block-preview">
    @foreach ($template->blocks as $block)
        @php
            $columns = $block->config['columns'] ?? [];
        @endphp
        <section class="qc-preview-section">
            <div class="qc-preview-section-head">
                <h2>{{ $block->title ?: Str::headline($block->type) }}</h2>
                <span>{{ Str::headline(str_replace('_', ' ', $block->type)) }}</span>
            </div>

            @if ($block->type === 'general_info')
                <div class="qc-info-grid">
                    @foreach ($block->fields as $field)
                        <div class="qc-info-field">
                            <label>{{ $field->label }}</label>
                            <input type="{{ $field->type === 'number' ? 'number' : ($field->type === 'date' ? 'date' : 'text') }}" value="{{ $fieldDefault($field) }}" disabled>
                        </div>
                    @endforeach
                </div>
            @elseif (in_array($block->type, ['checklist_table', 'measurement_table'], true))
                <div class="table-responsive">
                    <table class="table qc-modern-table align-middle">
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
                            @forelse ($block->tableRows as $row)
                                @php
                                    $data = $row->row_data ?? [];
                                    $currentKategori = trim((string) ($data['kategori'] ?? ''));
                                    $kategoriChanged = $currentKategori !== '' && $currentKategori !== $previousKategori;
                                    if ($currentKategori !== '') {
                                        $previousKategori = $currentKategori;
                                    }
                                @endphp
                                <tr class="{{ $kategoriChanged ? 'qc-category-break' : '' }}">
                                    @foreach ($columns as $column)
                                        @php
                                            $key = $columnKey($column);
                                            $type = $columnType($column);
                                        @endphp
                                        <td class="{{ $key === 'kategori' ? 'qc-category-cell' : '' }}">
                                            @if ($type === 'radio')
                                                <div class="qc-radio-inline">
                                                    @foreach (($column['options'] ?? ['OK', 'Not OK']) as $option)
                                                        <label class="qc-radio-option">
                                                            <input type="radio" disabled>
                                                            <span>{{ $option }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @elseif ($type === 'textarea')
                                                <textarea class="form-control form-control-sm qc-table-textarea" disabled>{{ $data[$key] ?? '' }}</textarea>
                                            @else
                                                {{ $data[$key] ?? '' }}
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ max(count($columns), 1) }}" class="text-center text-muted py-3">Item checklist belum diisi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @elseif ($block->type === 'note')
                <textarea class="form-control qc-note-preview" disabled placeholder="Catatan"></textarea>
            @elseif ($block->type === 'attachment')
                <div class="qc-attachment-preview">
                    @if (! empty($block->config['description']))
                        <p>{{ $block->config['description'] }}</p>
                    @endif
                    <div class="qc-preview-attachment-grid">
                        @forelse (($block->config['fields'] ?? $block->fields) as $field)
                            @php
                                $label = is_array($field) ? ($field['label'] ?? $field['key'] ?? 'Lampiran') : $field->label;
                                $type = is_array($field) ? ($field['type'] ?? 'file') : $field->type;
                                $maxFiles = is_array($field) ? ($field['max_files'] ?? null) : ($field->options['max_files'] ?? null);
                            @endphp
                            <div class="qc-preview-attachment-box">
                                <i class="bi {{ $type === 'image' ? 'bi-images' : 'bi-paperclip' }}"></i>
                                <strong>{{ $label }}</strong>
                                <span>{{ $type === 'image' ? 'Upload gambar' : 'Upload file' }}{{ $maxFiles ? ' - maks. '.$maxFiles.' file' : '' }}</span>
                            </div>
                        @empty
                            <span>Area lampiran foto/dokumen</span>
                        @endforelse
                    </div>
                </div>
            @elseif ($block->type === 'approval')
                <div class="qc-approval-grid">
                    @foreach (($columns ?: $block->fields) as $column)
                        @php
                            $normalizedColumn = $column instanceof \App\Models\QcFormTemplateField
                                ? ['key' => $column->field_name, 'label' => $column->label, 'type' => $column->type]
                                : \App\Support\QcTemplates\TemplateBuilder::normalizeApprovalColumn($column);
                            $approvalLabel = $normalizedColumn['label'];
                            $approvalKey = $normalizedColumn['key'];
                            $approvalType = $normalizedColumn['type'];
                            $isDateColumn = $approvalType === 'date' || $approvalKey === 'tanggal' || Str::lower($approvalLabel) === 'tanggal';
                            $isLockedColumn = str_contains($approvalType, 'locked');
                        @endphp
                        <div class="qc-approval-box {{ $isDateColumn ? 'is-date' : '' }} {{ $isLockedColumn ? 'is-locked' : '' }}">
                            <strong>{{ $approvalLabel }}</strong>
                            @if ($isDateColumn)
                                <input type="date" class="qc-approval-date" disabled>
                            @elseif ($isLockedColumn)
                                <span>Menunggu persetujuan</span>
                            @else
                                <span>Tanda tangan</span>
                            @endif
                        </div>
                    @endforeach
                </div>
                @if (! empty($block->config['notes']))
                    <ul class="qc-approval-notes">
                        @foreach ($block->config['notes'] as $note)
                            <li>{{ $note }}</li>
                        @endforeach
                    </ul>
                @endif
            @endif
        </section>
    @endforeach
</div>

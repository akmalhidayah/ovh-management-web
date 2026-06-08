@php
    use App\Support\QcTemplates\FixedQcTemplate;

    $type = FixedQcTemplate::normalizeType($template->template_type);
    $schema = FixedQcTemplate::schemaForTemplate($template);
    $approvalDefaults = $schema['approval_defaults'] ?? FixedQcTemplate::defaultApprovalDefaults($type);
    $headerRows = FixedQcTemplate::headerRows($type);
    $headerFieldMap = collect(FixedQcTemplate::headerFields($type))->keyBy('key');
@endphp

<div class="qc-block-preview">
    <section class="qc-preview-section">
        <div class="qc-preview-section-head">
            <h2>Header</h2>
            <span>Input manual user</span>
        </div>
        <div class="qc-info-grid">
            @foreach ($headerRows as $row)
                @foreach ($row as $fieldKey)
                    @php
                        $field = $headerFieldMap[$fieldKey];
                    @endphp
                    <div class="qc-info-field">
                        <label>{{ $field['label'] }}</label>
                        <input type="{{ $field['type'] }}" disabled>
                    </div>
                @endforeach
            @endforeach
        </div>
    </section>

    @if ($type === FixedQcTemplate::TYPE_WELDING)
        <section class="qc-preview-section">
            <div class="qc-preview-section-head">
                <h2>Metode QC dan Pengecekan ke</h2>
                <span>Checkbox user</span>
            </div>
            <div class="d-flex flex-wrap gap-3">
                @foreach (FixedQcTemplate::defaultMethods() as $method)
                    <label><input type="checkbox" disabled> {{ $method }}</label>
                @endforeach
            </div>
            <div class="d-flex flex-wrap gap-3 mt-2">
                @foreach (FixedQcTemplate::defaultCheckSteps() as $step)
                    <label><input type="checkbox" disabled> {{ $step }}</label>
                @endforeach
            </div>
        </section>

        <section class="qc-preview-section">
            <div class="qc-preview-section-head">
                <h2>Tabel Welder</h2>
                <span>Row bisa tambah/hapus user</span>
            </div>
            <div class="table-responsive">
                <table class="table qc-modern-table align-middle">
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
                    <tbody>
                        @forelse ($schema['welder_rows'] ?? [] as $row)
                            <tr>
                                <td>{{ $row['no'] ?? $loop->iteration }}</td>
                                <td>{{ $row['nama_welder'] ?? '' }}</td>
                                <td>{{ $row['posisi_pengelasan'] ?? '' }}</td>
                                <td>{{ $row['diameter_electrode'] ?? '' }}</td>
                                <td>{{ $row['electrode_filter'] ?? '' }}</td>
                                <td>{{ $row['amper'] ?? '' }}</td>
                                <td>{{ $row['keterangan'] ?? '' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-3">Belum ada row default.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="qc-preview-section">
            <div class="qc-preview-section-head">
                <h2>Tabel Hasil QC Welding</h2>
                <span>Status pilih salah satu</span>
            </div>
            <div class="table-responsive">
                <table class="table qc-modern-table align-middle">
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
                    <tbody>
                        @forelse ($schema['result_rows'] ?? [] as $row)
                            <tr>
                                <td>{{ $row['no'] ?? $loop->iteration }}</td>
                                <td>{{ $row['deskripsi'] ?? '' }}</td>
                                <td><input type="radio" disabled></td>
                                <td><input type="radio" disabled></td>
                                <td><input type="radio" disabled></td>
                                <td>{{ $row['keterangan'] ?? '' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">Belum ada row default.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @elseif ($type === FixedQcTemplate::TYPE_CASTABLE)
        <section class="qc-preview-section">
            <div class="qc-preview-section-head">
                <h2>Customer Data</h2>
                <span>Fixed</span>
            </div>
            <div class="table-responsive">
                <table class="table qc-modern-table align-middle">
                    <tbody>
                        @foreach (FixedQcTemplate::castableCustomerRows() as $row)
                            <tr>
                                <td style="width: 60px;">{{ $row['no'] }}</td>
                                <td>{{ $row['label'] }}</td>
                                <td class="text-muted">{{ $row['hint'] ?: 'Diisi user QC' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="qc-preview-section">
            <div class="qc-preview-section-head">
                <h2>Installation Record / Inspection Check List</h2>
                <span>Fixed</span>
            </div>
            <div class="table-responsive">
                <table class="table qc-modern-table align-middle">
                    <tbody>
                        @foreach (FixedQcTemplate::castableInspectionRows() as $row)
                            <tr>
                                <td style="width: 60px;">{{ $row['no'] }}</td>
                                <td>{{ $row['label'] }}</td>
                                <td>{{ isset($row['options']) ? implode(' / ', $row['options']) : ($row['unit'] ?? 'Manual') }}</td>
                                <td>{{ $row['detail_label'] ?? '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="qc-preview-section">
            <div class="qc-preview-section-head">
                <h2>Monitoring Installation Castable</h2>
                <span>Fixed layout, row dinamis di user</span>
            </div>
            <div class="table-responsive">
                <table class="table qc-modern-table align-middle">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Quantity</th>
                            <th>Batch Number</th>
                            <th>Temp. Material</th>
                            <th>Temp. Ruangan</th>
                            <th>Waktu Aduk</th>
                            <th>Air %</th>
                            <th>PH</th>
                            <th>Temp. Air</th>
                            <th>Lokasi</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (FixedQcTemplate::defaultCastableMonitoringRows() as $row)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                @foreach (FixedQcTemplate::castableMonitoringColumns() as $column)
                                    <td class="text-muted">{{ $column['placeholder'] }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @elseif ($type === FixedQcTemplate::TYPE_BRICS)
        <section class="qc-preview-section">
            <div class="qc-preview-section-head">
                <h2>Customer Data dan Kiln Technical Information</h2>
                <span>Fixed</span>
            </div>
            <div class="table-responsive">
                <table class="table qc-modern-table align-middle">
                    <tbody>
                        @foreach (FixedQcTemplate::bricsCustomerRows() as $row)
                            <tr>
                                <td style="width: 60px;">{{ $row['no'] }}</td>
                                <td>{{ $row['label'] }}</td>
                                <td>{{ $row['default'] ?? 'Diisi user QC' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="qc-preview-section">
            <div class="qc-preview-section-head">
                <h2>Installation Record / Inspection Check List</h2>
                <span>Fixed</span>
            </div>
            <div class="table-responsive">
                <table class="table qc-modern-table align-middle">
                    <tbody>
                        @foreach (FixedQcTemplate::bricsInspectionSections() as $section)
                            <tr class="table-light">
                                <th colspan="5">{{ trim(($section['number'] ?? '').' '.$section['title']) }}</th>
                            </tr>
                            @foreach ($section['items'] as $row)
                                <tr>
                                    <td style="width: 60px;">{{ $row['no'] }}</td>
                                    <td>{{ $row['label'] }}</td>
                                    <td>OK</td>
                                    <td>NO</td>
                                    <td>Remark</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @elseif ($type === FixedQcTemplate::TYPE_ELECTRICAL)
        @foreach ([
            'stator_rows' => ['title' => 'Pengukuran Insulation Resistance & Polarization Index (Stator)', 'columns' => ['item' => 'Parameter']],
            'rotor_rows' => ['title' => 'Pengukuran Insulation Resistance & Polarization Index (Rotor)', 'columns' => ['item' => 'Parameter']],
            'ovality_rows' => ['title' => 'Pengukuran Ovality', 'columns' => ['ring' => 'Ring', 'standard' => 'Standar']],
            'installation_rows' => ['title' => 'Checklist Instalasi', 'columns' => ['activity' => 'Aktivitas', 'standard' => 'Standar']],
            'uncouple_rows' => ['title' => 'Uncouple Testing', 'columns' => ['item' => 'Item', 'label_1' => 'Label 1', 'label_2' => 'Label 2', 'label_3' => 'Label 3']],
        ] as $sectionKey => $section)
            <section class="qc-preview-section">
                <div class="qc-preview-section-head">
                    <h2>{{ $section['title'] }}</h2>
                    <span>Row diatur admin</span>
                </div>
                <div class="table-responsive">
                    <table class="table qc-modern-table align-middle">
                        <thead><tr><th>No</th>@foreach ($section['columns'] as $label)<th>{{ $label }}</th>@endforeach @if ($sectionKey === 'installation_rows')<th>OK/TIDAK</th><th>Keterangan / Remarks</th>@endif</tr></thead>
                        <tbody>
                            @foreach ($schema[$sectionKey] ?? [] as $row)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    @foreach ($section['columns'] as $field => $label)<td>{{ $row[$field] ?? '' }}</td>@endforeach
                                    @if ($sectionKey === 'installation_rows')<td>OK / NOT OK</td><td>Wajib jika NOT OK</td>@endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endforeach
    @else
        <section class="qc-preview-section">
            <div class="qc-preview-section-head">
                <h2>Body QC Umum</h2>
                <span>Status pilih salah satu</span>
            </div>
            <div class="table-responsive">
                <table class="table qc-modern-table align-middle">
                    <thead>
                        <tr>
                            <th>Item Pengecekan</th>
                            <th>Standar</th>
                            <th>Status</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($schema['rows'] ?? [] as $row)
                            <tr>
                                <td>{{ $row['item_pengecekan'] ?? '' }}</td>
                                <td>{{ $row['standar'] ?? '' }}</td>
                                <td>
                                    <label><input type="radio" disabled> Ok</label>
                                    <label class="ms-2"><input type="radio" disabled> Not Ok</label>
                                </td>
                                <td></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">Belum ada row default.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    <section class="qc-preview-section">
        <div class="qc-preview-section-head">
            <h2>Catatan</h2>
            <span>Terkunci</span>
        </div>
        <textarea class="form-control qc-note-preview" disabled placeholder="Catatan diisi oleh user QC"></textarea>
    </section>

    <section class="qc-preview-section">
        <div class="qc-preview-section-head">
            <h2>Lampiran Foto/Gambar</h2>
            <span>Terkunci</span>
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
    </section>

    <section class="qc-preview-section">
        <div class="qc-preview-section-head">
            <h2>Approval Footer</h2>
            <span>Terkunci</span>
        </div>
        <p class="text-muted">Baru bisa ter approve jika form sudah terisi semua & Final Check sudah tercentang:</p>
        <label class="d-inline-flex align-items-center gap-2 mb-3">
            <input type="checkbox" disabled>
            <strong>Final Check</strong>
        </label>
        <div class="qc-approval-grid">
            @foreach (FixedQcTemplate::approvalColumnsWithDefaults($type, $approvalDefaults) as $column)
                @php
                    $approvalName = $approvalDefaults[$column['key']]['name'] ?? '';
                @endphp
                <div class="qc-approval-box">
                    @if ($type === FixedQcTemplate::TYPE_CASTABLE)
                        <strong>{{ $column['label'] }}</strong>
                        <small>{{ $column['group'] }}</small>
                    @else
                        <small>{{ $column['group'] }}</small>
                        <strong>{{ $column['label'] }}</strong>
                    @endif
                    <input type="text" class="form-control mt-2" value="{{ $approvalName }}" placeholder="Nama" disabled>
                    <input type="date" class="form-control mt-2" disabled>
                    <span>{{ ($column['role'] ?? null) === 'QC Inspektor' ? 'Tanda tangan user QC' : 'Tanda tangan terkunci' }}</span>
                </div>
            @endforeach
        </div>
    </section>
</div>

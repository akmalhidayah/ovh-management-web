@php
    use App\Support\QcTemplates\FixedQcTemplate;

    $castableCustomer = $oldBody['castable_customer'] ?? [];
    $castableChecks = $oldBody['castable_checks'] ?? [];
    $castableSample = $oldBody['castable_sample'] ?? [];
    $monitoringRows = collect(old('body.castable_monitoring_rows', $oldBody['castable_monitoring_rows'] ?? []))->values();
    $monitoringRows = $monitoringRows->isNotEmpty() ? $monitoringRows : collect(FixedQcTemplate::defaultCastableMonitoringRows())->take(1);
    $monitoringType = old('body.castable_monitoring_type', $oldBody['castable_monitoring_type'] ?? '');
    $monitoringColumns = FixedQcTemplate::castableMonitoringColumns();
    $castableSignerName = $signerName ?? auth()->user()?->name ?? 'User QC';
    $castableToday = $today ?? now()->toDateString();
    $sampleSignature = is_array($castableSample['qc_sign_date'] ?? null) ? $castableSample['qc_sign_date'] : [];
    $sampleSignatureData = $sampleSignature['signature'] ?? '';
    $sampleSignatureDate = $sampleSignature['date'] ?? $castableToday;
    $sampleSignatureName = $sampleSignature['name'] ?? $castableSignerName;
@endphp

<section class="inspector-panel qc-form-card">
    <div class="qc-form-section-title"><h3>Customer Data</h3></div>
    <div class="qc-user-table-wrap">
        <table class="qc-user-checklist-table qc-user-fixed-table">
            <colgroup>
                <col style="width: 7%">
                <col style="width: 31%">
                <col style="width: 62%">
            </colgroup>
            <tbody>
                @foreach (FixedQcTemplate::castableCustomerRows() as $row)
                    <tr>
                        <td class="text-center">{{ $row['no'] }}</td>
                        <td>{{ $row['label'] }}</td>
                        <td>
                            <input type="text"
                                   name="body[castable_customer][{{ $row['key'] }}]"
                                   value="{{ $castableCustomer[$row['key']] ?? '' }}"
                                   class="form-control form-control-sm"
                                   placeholder="{{ $row['hint'] }}">
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>

<section class="inspector-panel qc-form-card">
    <div class="qc-form-card-head align-items-start">
        <div>
            <span>Monitoring Installation Castable</span>
            <h2>Monitoring Installation Castable</h2>
        </div>
        <button type="button" class="btn btn-outline-primary" data-castable-add-row>
            <i class="bi bi-plus-lg me-1"></i>Tambah Row Monitoring
        </button>
    </div>

    <label class="qc-castable-monitoring-type">
        <span>Type Material / Mixing</span>
        <input type="text"
               class="form-control form-control-sm"
               name="body[castable_monitoring_type]"
               value="{{ $monitoringType }}"
               data-castable-monitoring-type-input
               placeholder="Contoh: Castable LC-16">
    </label>

    <div class="qc-user-table-wrap qc-castable-monitoring-wrap">
        <table class="qc-user-checklist-table qc-user-fixed-table qc-castable-monitoring-table">
            <thead>
                <tr>
                    <th rowspan="3">No.</th>
                    <th colspan="2">Quantity Material/Mixing</th>
                    <th rowspan="3">Temperatur Material<br>(kering)</th>
                    <th rowspan="3">Temperatur Ruangan<br>C</th>
                    <th rowspan="3">Waktu Aduk<br>(... Standard...)<br>Menit</th>
                    <th colspan="3">Air</th>
                    <th rowspan="3">Lokasi Pemasangan</th>
                    <th rowspan="3">Keterangan</th>
                    <th rowspan="3">Tambah/Hapus</th>
                </tr>
                <tr>
                    <th colspan="2" class="text-start">Type : <span data-castable-monitoring-type-label>{{ $monitoringType ?: '....................' }}</span></th>
                    <th rowspan="2">Persentase<br>(... Standard...)<br>(%)</th>
                    <th rowspan="2">(... Standard...)<br>PH</th>
                    <th rowspan="2">Temperatur<br>(... Standard...)<br>(C)</th>
                </tr>
                <tr>
                    <th>Quantity<br>(kg)</th>
                    <th>Batch number</th>
                </tr>
            </thead>
            <tbody data-castable-monitoring-body>
                @foreach ($monitoringRows as $index => $row)
                    <tr data-castable-monitoring-row>
                        <td class="text-center">
                            <span data-castable-row-number>{{ $index + 1 }}</span>
                            <input type="hidden" name="body[castable_monitoring_rows][{{ $index }}][no]" value="{{ $index + 1 }}" data-castable-row-no>
                        </td>
                        @foreach ($monitoringColumns as $column)
                            <td>
                                <input type="text"
                                       class="form-control form-control-sm"
                                       name="body[castable_monitoring_rows][{{ $index }}][{{ $column['key'] }}]"
                                       value="{{ $row[$column['key']] ?? '' }}"
                                       placeholder="{{ $column['placeholder'] ?? $column['label'] }}">
                            </td>
                        @endforeach
                        <td class="text-center">
                            <button type="button" class="btn btn-outline-danger btn-sm qc-castable-row-remove" data-castable-remove-row title="Hapus row">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>

<section class="inspector-panel qc-form-card">
    <div class="qc-form-section-title"><h3>Installation Record / Inspection Check List</h3></div>
    <div class="qc-user-table-wrap">
        <table class="qc-user-checklist-table qc-user-fixed-table">
            <colgroup>
                <col style="width: 5%">
                <col style="width: 28%">
                <col style="width: 21%">
                <col style="width: 22%">
                <col style="width: 24%">
            </colgroup>
            <tbody>
                @foreach (FixedQcTemplate::castableInspectionRows() as $row)
                    @php
                        $saved = $castableChecks[$row['key']] ?? [];
                    @endphp
                    <tr>
                        <td class="text-center">{{ $row['no'] }}</td>
                        <td>{{ $row['label'] }}</td>
                        <td>
                            @if (! empty($row['options']))
                                <div class="qc-user-status-inline">
                                    @foreach ($row['options'] as $option)
                                        <label>
                                            <input type="radio"
                                                   name="body[castable_checks][{{ $row['key'] }}][status]"
                                                   value="{{ $option }}"
                                                   data-final-check-ok="1"
                                                   @checked(($saved['status'] ?? null) === $option)>
                                            <span>{{ $option }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @elseif (($row['input'] ?? null) === 'dimension')
                                @php
                                    $dimensions = $saved['dimensions'] ?? [];
                                @endphp
                                <div class="qc-castable-dimension-input">
                                    <span>(</span>
                                    <input type="number" step="any" inputmode="decimal" name="body[castable_checks][{{ $row['key'] }}][dimensions][length]" value="{{ $dimensions['length'] ?? '' }}" class="form-control form-control-sm" placeholder="P">
                                    <span>x</span>
                                    <input type="number" step="any" inputmode="decimal" name="body[castable_checks][{{ $row['key'] }}][dimensions][width]" value="{{ $dimensions['width'] ?? '' }}" class="form-control form-control-sm" placeholder="L">
                                    <span>x</span>
                                    <input type="number" step="any" inputmode="decimal" name="body[castable_checks][{{ $row['key'] }}][dimensions][height]" value="{{ $dimensions['height'] ?? '' }}" class="form-control form-control-sm" placeholder="T">
                                    <span>)</span>
                                </div>
                            @else
                                <input type="{{ ($row['input'] ?? null) === 'number' ? 'number' : 'text' }}"
                                       @if (($row['input'] ?? null) === 'number') step="any" inputmode="decimal" @endif
                                       name="body[castable_checks][{{ $row['key'] }}][value]"
                                       value="{{ $saved['value'] ?? '' }}"
                                       class="form-control form-control-sm">
                            @endif
                        </td>
                        <td class="text-muted">{{ $row['unit'] ?? '' }}</td>
                        <td>
                            @if (! empty($row['detail_label']))
                                <label class="d-flex align-items-center gap-2 mb-0">
                                    <span class="small text-muted text-nowrap">{{ $row['detail_label'] }}</span>
                                    <input type="text"
                                           name="body[castable_checks][{{ $row['key'] }}][detail]"
                                           value="{{ $saved['detail'] ?? '' }}"
                                           class="form-control form-control-sm">
                                </label>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="small text-muted mt-2">*Sample For Laboratory test by QC</div>
</section>

<section class="inspector-panel qc-form-card">
    <div class="qc-form-section-title"><h3>Sample Data</h3></div>
    <div class="qc-user-field-grid">
        @foreach (FixedQcTemplate::castableSampleRows() as $row)
            @if ($row['key'] === 'qc_sign_date')
                <div class="qc-user-field wide">
                    <span>{{ $row['label'] }}</span>
                    <div class="qc-user-approval-box qc-castable-sample-sign" data-signature-card="castable-sample-qc-sign-date">
                        <input type="hidden" name="body[castable_sample][qc_sign_date][name]" value="{{ $sampleSignatureName }}">
                        <input type="date" class="form-control form-control-sm mb-2" name="body[castable_sample][qc_sign_date][date]" value="{{ $sampleSignatureDate }}">
                        <input type="hidden" name="body[castable_sample][qc_sign_date][signature]" value="{{ $sampleSignatureData }}" data-signature-input>
                        <input type="hidden" name="body[castable_sample][qc_sign_date][signed_at]" value="{{ $sampleSignature['signed_at'] ?? '' }}" data-signature-time-input>
                        <div class="qc-signature-empty {{ $sampleSignatureData ? 'd-none' : '' }}" data-signature-empty>
                            <i class="bi bi-pen"></i>
                            <span>Belum ditandatangani</span>
                        </div>
                        <div class="qc-signature-result {{ $sampleSignatureData ? '' : 'd-none' }}" data-signature-result>
                            <img alt="Preview tanda tangan" data-signature-preview @if ($sampleSignatureData) src="{{ $sampleSignatureData }}" @endif>
                            <div>
                                <strong data-signature-signer>{{ $sampleSignatureName }}</strong>
                                <span>QC SIGN / DATE</span>
                                <small data-signature-time></small>
                            </div>
                        </div>
                        <div class="qc-signature-actions mt-2">
                            <button type="button" class="btn btn-outline-primary" data-signature-open data-signature-label="QC SIGN / DATE">
                                <i class="bi bi-pen me-1"></i><span data-signature-button-label>{{ $sampleSignatureData ? 'Ubah' : 'Tanda Tangan' }}</span>
                            </button>
                            <button type="button" class="btn btn-outline-danger {{ $sampleSignatureData ? '' : 'd-none' }}" data-signature-remove>Hapus</button>
                        </div>
                    </div>
                </div>
            @else
                <label class="qc-user-field">
                    <span>{{ $row['label'] }}</span>
                    <input type="{{ $row['key'] === 'quantity' ? 'number' : 'text' }}"
                           @if ($row['key'] === 'quantity') step="any" inputmode="decimal" @endif
                           name="body[castable_sample][{{ $row['key'] }}]"
                           value="{{ $castableSample[$row['key']] ?? '' }}"
                           class="form-control">
                </label>
            @endif
        @endforeach
    </div>
</section>

@push('scripts')
    <script>
        (() => {
            const body = document.querySelector('[data-castable-monitoring-body]');
            const addButton = document.querySelector('[data-castable-add-row]');
            const typeInput = document.querySelector('[data-castable-monitoring-type-input]');
            const typeLabel = document.querySelector('[data-castable-monitoring-type-label]');
            const columns = @json($monitoringColumns);

            if (!body || !addButton) return;

            typeInput?.addEventListener('input', () => {
                if (typeLabel) typeLabel.textContent = typeInput.value || '....................';
            });

            const escapeAttribute = (value) => String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/"/g, '&quot;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');

            const renumber = () => {
                body.querySelectorAll('[data-castable-monitoring-row]').forEach((row, index) => {
                    row.querySelector('[data-castable-row-number]').textContent = index + 1;
                    row.querySelector('[data-castable-row-no]').name = `body[castable_monitoring_rows][${index}][no]`;
                    row.querySelector('[data-castable-row-no]').value = index + 1;
                    columns.forEach((column) => {
                        const input = row.querySelector(`[data-castable-column="${column.key}"]`);
                        if (input) input.name = `body[castable_monitoring_rows][${index}][${column.key}]`;
                    });
                });
            };

            const createRow = () => {
                const row = document.createElement('tr');
                row.dataset.castableMonitoringRow = '';
                row.innerHTML = `
                    <td class="text-center">
                        <span data-castable-row-number></span>
                        <input type="hidden" data-castable-row-no>
                    </td>
                    ${columns.map((column) => `
                        <td>
                            <input type="text"
                                   class="form-control form-control-sm"
                                   data-castable-column="${escapeAttribute(column.key)}"
                                   placeholder="${escapeAttribute(column.placeholder || column.label)}">
                        </td>
                    `).join('')}
                    <td class="text-center">
                        <button type="button" class="btn btn-outline-danger btn-sm qc-castable-row-remove" data-castable-remove-row title="Hapus row">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </td>
                `;

                return row;
            };

            body.querySelectorAll('[data-castable-monitoring-row]').forEach((row) => {
                columns.forEach((column) => {
                    const input = row.querySelector(`input[name$="[${column.key}]"]`);
                    if (input) input.dataset.castableColumn = column.key;
                });
            });

            addButton.addEventListener('click', () => {
                body.appendChild(createRow());
                renumber();
            });

            body.addEventListener('click', (event) => {
                const removeButton = event.target.closest('[data-castable-remove-row]');
                if (!removeButton) return;

                removeButton.closest('[data-castable-monitoring-row]')?.remove();
                if (!body.querySelector('[data-castable-monitoring-row]')) {
                    body.appendChild(createRow());
                }
                renumber();
            });

            renumber();
        })();
    </script>
@endpush

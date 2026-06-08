@php
    $electricalStatorRows = old('body.electrical_stator_rows', $draftBody['electrical_stator_rows'] ?? $schema['stator_rows'] ?? []);
    $electricalRotorRows = old('body.electrical_rotor_rows', $draftBody['electrical_rotor_rows'] ?? $schema['rotor_rows'] ?? []);
    $electricalOvalityRows = old('body.electrical_ovality_rows', $draftBody['electrical_ovality_rows'] ?? $schema['ovality_rows'] ?? []);
    $electricalInstallationRows = old('body.electrical_installation_rows', $draftBody['electrical_installation_rows'] ?? $schema['installation_rows'] ?? []);
    $electricalUncoupleRows = old('body.electrical_uncouple_rows', $draftBody['electrical_uncouple_rows'] ?? $schema['uncouple_rows'] ?? []);
@endphp

@foreach ([
    'stator' => ['title' => 'PENGUKURAN INSULATION RESISTANCE & POLARIZATION INDEX (STATOR)', 'rows' => $electricalStatorRows],
    'rotor' => ['title' => 'PENGUKURAN INSULATION RESISTANCE & POLARIZATION INDEX (ROTOR)', 'rows' => $electricalRotorRows],
] as $sectionKey => $section)
    <section class="inspector-panel qc-form-card">
        <div class="qc-form-section-title"><h3>{{ $section['title'] }}</h3></div>
        <div class="qc-user-table-wrap">
            <table class="qc-user-checklist-table">
                <thead>
                    <tr><th>No</th><th>Parameter</th><th>Nilai</th><th>30 detik</th><th>1 Menit</th><th>10 Menit</th><th>PI</th></tr>
                </thead>
                <tbody>
                    @foreach ($section['rows'] as $index => $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <input type="hidden" name="body[electrical_{{ $sectionKey }}_rows][{{ $index }}][item]" value="{{ $row['item'] ?? '' }}">
                                <strong>{{ $row['item'] ?? '' }}</strong>
                            </td>
                            @foreach (['value', 'second_30', 'minute_1', 'minute_10', 'pi'] as $field)
                                <td><input type="text" class="form-control form-control-sm" name="body[electrical_{{ $sectionKey }}_rows][{{ $index }}][{{ $field }}]" value="{{ $row[$field] ?? '' }}"></td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between gap-3 small fw-semibold mt-2">
            <span>*Standar IR 1+MΩ/kV</span>
            <span>*Standar PI &gt; 2</span>
        </div>
    </section>
@endforeach

<section class="inspector-panel qc-form-card">
    <div class="qc-form-section-title"><h3>PENGUKURAN OVALITY</h3></div>
    <div class="qc-user-table-wrap">
        <table class="qc-user-checklist-table">
            <thead><tr><th>No</th><th>Ring</th><th>TIR</th><th>Standar</th></tr></thead>
            <tbody>
                @foreach ($electricalOvalityRows as $index => $row)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $row['ring'] ?? '' }}</td>
                        <td><input type="text" class="form-control form-control-sm" name="body[electrical_ovality_rows][{{ $index }}][tir]" value="{{ $row['tir'] ?? '' }}" required></td>
                        <td>{{ $row['standard'] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>

<section class="inspector-panel qc-form-card">
    <div class="qc-form-section-title"><h3>CHECKLIST INSTALASI</h3></div>
    <div class="qc-user-table-wrap">
        <table class="qc-user-checklist-table">
            <thead><tr><th>No</th><th>Aktivitas</th><th>Standar</th><th>OK/TIDAK</th><th>Keterangan / Remarks</th></tr></thead>
            <tbody>
                @foreach ($electricalInstallationRows as $index => $row)
                    <tr data-electrical-installation-row>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $row['activity'] ?? '' }}</td>
                        <td>{{ $row['standard'] ?? '' }}</td>
                        <td>
                            <div class="qc-user-status-inline">
                                <label><input type="radio" name="body[electrical_installation_rows][{{ $index }}][status]" value="OK" @checked(($row['status'] ?? '') === 'OK') required> <span>OK</span></label>
                                <label><input type="radio" name="body[electrical_installation_rows][{{ $index }}][status]" value="NOT OK" @checked(($row['status'] ?? '') === 'NOT OK') required> <span>NOT OK</span></label>
                            </div>
                        </td>
                        <td>
                            <textarea name="body[electrical_installation_rows][{{ $index }}][remark]"
                                      class="form-control qc-user-table-note"
                                      data-electrical-remark
                                      placeholder="Opsional jika status OK">{{ $row['remark'] ?? '' }}</textarea>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>

<section class="inspector-panel qc-form-card">
    <div class="qc-form-section-title"><h3>UNCOUPLE TESTING</h3></div>
    <div class="qc-user-table-wrap">
        <table class="qc-user-checklist-table">
            <thead><tr><th>No</th><th>Item</th><th>Hasil 1</th><th>Hasil 2</th><th>Hasil 3</th></tr></thead>
            <tbody>
                @foreach ($electricalUncoupleRows as $index => $row)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $row['item'] ?? '' }}</strong></td>
                        @foreach ([1, 2, 3] as $column)
                            <td>
                                <div class="input-group input-group-sm">
                                    @if (filled($row["label_{$column}"] ?? ''))
                                        <span class="input-group-text">{{ $row["label_{$column}"] }} :</span>
                                    @endif
                                    <input type="text" class="form-control" name="body[electrical_uncouple_rows][{{ $index }}][value_{{ $column }}]" value="{{ $row["value_{$column}"] ?? '' }}">
                                </div>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>

@push('scripts')
    <script>
        (() => {
            const syncRemark = (row) => {
                const remark = row.querySelector('[data-electrical-remark]');
                const notOk = row.querySelector('input[type="radio"][value="NOT OK"]')?.checked;
                if (!remark) return;
                remark.required = Boolean(notOk);
                remark.placeholder = notOk ? 'Wajib diisi karena status NOT OK' : 'Opsional jika status OK';
            };

            document.querySelectorAll('[data-electrical-installation-row]').forEach((row) => {
                row.querySelectorAll('input[type="radio"]').forEach((input) => input.addEventListener('change', () => syncRemark(row)));
                syncRemark(row);
            });
        })();
    </script>
@endpush

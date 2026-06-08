@php
    $electricalTables = [
        'stator' => [
            'title' => 'Pengukuran Insulation Resistance & Polarization Index (Stator) (Opsional)',
            'rows' => $electricalStatorRows,
            'fields' => ['item' => 'Parameter'],
        ],
        'rotor' => [
            'title' => 'Pengukuran Insulation Resistance & Polarization Index (Rotor) (Opsional)',
            'rows' => $electricalRotorRows,
            'fields' => ['item' => 'Parameter'],
        ],
        'ovality' => [
            'title' => 'Pengukuran Ovality (Opsional)',
            'rows' => $electricalOvalityRows,
            'fields' => ['ring' => 'Ring', 'standard' => 'Standar'],
        ],
        'installation' => [
            'title' => 'Checklist Instalasi',
            'rows' => $electricalInstallationRows,
            'fields' => ['activity' => 'Aktivitas', 'standard' => 'Standar'],
        ],
        'uncouple' => [
            'title' => 'Uncouple Testing (Opsional)',
            'rows' => $electricalUncoupleRows,
            'fields' => ['item' => 'Item', 'label_1' => 'Label 1', 'label_2' => 'Label 2', 'label_3' => 'Label 3'],
        ],
    ];
@endphp

<div class="content-card" data-electrical-editor>
    <div class="card-heading">
        <div>
            <h2>Body QC Electrical</h2>
            <div class="text-muted small">Admin dapat menambah dan menghapus row pada seluruh tabel.</div>
        </div>
    </div>

    @foreach ($electricalTables as $tableKey => $table)
        <div class="{{ $loop->first ? '' : 'mt-4' }}">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                <strong>{{ $table['title'] }}</strong>
                <button type="button" class="btn btn-outline-primary btn-sm" data-add-electrical-row="{{ $tableKey }}">Tambah Row</button>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 70px;">No</th>
                            @foreach ($table['fields'] as $label)
                                <th>{{ $label }}</th>
                            @endforeach
                            @if ($tableKey === 'installation')
                                <th style="width: 110px;">OK/TIDAK</th>
                                <th>Keterangan / Remarks</th>
                            @endif
                            <th style="width: 90px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody data-electrical-row-list="{{ $tableKey }}">
                        @foreach ($table['rows'] as $index => $row)
                            <tr data-electrical-row>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                @foreach ($table['fields'] as $field => $label)
                                    <td>
                                        <input type="text"
                                               name="electrical_{{ $tableKey }}_rows[{{ $index }}][{{ $field }}]"
                                               class="form-control form-control-sm"
                                               value="{{ $row[$field] ?? '' }}"
                                               placeholder="{{ $label }}">
                                    </td>
                                @endforeach
                                @if ($tableKey === 'installation')
                                    <td class="text-center"><input type="checkbox" disabled></td>
                                    <td><input type="text" class="form-control form-control-sm" disabled placeholder="Diisi user; wajib jika NOT OK"></td>
                                @endif
                                <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm" data-remove-row>Hapus</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if (in_array($tableKey, ['stator', 'rotor'], true))
                <div class="d-flex justify-content-between small fw-semibold mt-1">
                    <span>*Standar IR 1+MΩ/kV</span>
                    <span>*Standar PI &gt; 2</span>
                </div>
            @endif
        </div>
    @endforeach
</div>

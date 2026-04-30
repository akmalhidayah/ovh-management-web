@props(['record'])

@php
    $signatureModalId = 'commissioningSignatureModal-' . uniqid();
    $currentUser = auth()->user();
    $profilePhotoUrl = $currentUser?->profilePhotoUrl();
@endphp

<section {{ $attributes->class(['commissioning-sheet']) }}>
    <div class="commissioning-personnel-row">
        <span>Commissioning Personil</span>
        <div class="personnel-field">
            <span class="personnel-avatar">
                @if ($profilePhotoUrl)
                    <img src="{{ $profilePhotoUrl }}" alt="{{ $currentUser->name }}">
                @else
                    {{ strtoupper(substr($currentUser->name ?? 'U', 0, 1)) }}
                @endif
            </span>
            <div>
                <strong>{{ $currentUser->name ?? ($record['personnel'] ?? '-') }}</strong>
                <small>{{ $currentUser->email ?? 'Commissioning Personil' }}</small>
            </div>
            <input type="hidden" value="{{ $currentUser->name ?? ($record['personnel'] ?? '') }}">
        </div>
    </div>

    <div class="commissioning-grid">
        <label><span>Doc. Number</span><input type="text" class="form-control" value="{{ $record['document']['doc_number'] }}"></label>
        <label><span>Process/Area</span><input type="text" class="form-control" value="{{ $record['document']['process_area'] }}"></label>
        <label><span>Drwg. Reference</span><input type="text" class="form-control" value="{{ $record['document']['drwg_reference'] }}"></label>
        <label><span>Date</span><input type="date" class="form-control" value="{{ $record['document']['date'] }}"></label>
        <label><span>Time</span><input type="time" class="form-control" value="{{ $record['document']['time'] }}"></label>
        <label class="wide"><span>Discipline</span><input type="text" class="form-control" value="{{ $record['document']['discipline'] }}"></label>
    </div>

    <div class="commissioning-section-title">Motor Data</div>
    <div class="commissioning-grid">
        <label><span>Equipment Name</span><input type="text" class="form-control" value="{{ $record['motor_data']['equipment_name'] }}"></label>
        <label><span>Model/Type</span><input type="text" class="form-control" value="{{ $record['motor_data']['model_type'] }}"></label>
        <label><span>Tag Number</span><input type="text" class="form-control" value="{{ $record['motor_data']['tag_number'] }}"></label>
        <label><span>IP</span><input type="text" class="form-control" value="{{ $record['motor_data']['ip'] }}"></label>
        <label><span>Function Of</span><input type="text" class="form-control" value="{{ $record['motor_data']['function_of'] }}"></label>
        <label><span>Brand</span><input type="text" class="form-control" value="{{ $record['motor_data']['brand'] }}"></label>
    </div>

    <div class="commissioning-section-title">Motor Rating (Name Plate Data)</div>
    <div class="table-responsive">
        <table class="commissioning-table compact">
            <thead>
                <tr>
                    <th>Tag. Number</th>
                    <th>Power (kW)</th>
                    <th>Current (A)</th>
                    <th>Voltage (V)</th>
                    <th>Freq. (Hz)</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="text" class="form-control" value="{{ $record['motor_rating']['tag_number'] }}"></td>
                    <td><input type="text" class="form-control" value="{{ $record['motor_rating']['power_kw'] }}"></td>
                    <td><input type="text" class="form-control" value="{{ $record['motor_rating']['current_a'] }}"></td>
                    <td><input type="text" class="form-control" value="{{ $record['motor_rating']['voltage_v'] }}"></td>
                    <td><input type="text" class="form-control" value="{{ $record['motor_rating']['freq_hz'] }}"></td>
                    <td><input type="text" class="form-control" value="{{ $record['motor_rating']['remarks'] }}"></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="commissioning-section-title">Equipment Check Data</div>
    <div class="table-responsive">
        <table class="commissioning-table check-table" data-commissioning-check-table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Item To Check</th>
                    <th>Check</th>
                    <th colspan="3">Accepted</th>
                    <th>Remarks</th>
                </tr>
                <tr>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th>Yes</th>
                    <th>No</th>
                    <th>NA</th>
                    <th></th>
                </tr>
            </thead>
            <tbody data-commissioning-check-body>
                @foreach ($record['checks'] as $check)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td><input type="text" class="form-control" value="{{ $check }}"></td>
                        <td class="text-center"><input type="checkbox"></td>
                        <td class="text-center"><input type="checkbox"></td>
                        <td class="text-center"><input type="checkbox"></td>
                        <td class="text-center"><input type="checkbox"></td>
                        <td><input type="text" class="form-control" placeholder="Remarks..."></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="qc-add-row-area commissioning-add-row-area">
        <button type="button" class="btn btn-primary" data-commissioning-add-check>
            <i class="bi bi-plus-lg me-2"></i>Tambah Item Check
        </button>
    </div>

    <div class="commissioning-section-title">Motor Test Report: Load / No Load</div>
    <div class="table-responsive">
        <table class="commissioning-table test-table" data-commissioning-test-table>
            <colgroup>
                <col class="com-test-current">
                <col class="com-test-time">
                <col class="com-test-phase">
                <col class="com-test-phase">
                <col class="com-test-phase">
                <col class="com-test-remarks">
            </colgroup>
            <thead>
                <tr>
                    <th rowspan="2">Starting Current (Ampere)</th>
                    <th rowspan="2">Time (Interval 10 minutes)</th>
                    <th colspan="3">P H A S E</th>
                    <th rowspan="2">Remarks</th>
                </tr>
                <tr>
                    <th>R</th>
                    <th>S</th>
                    <th>T</th>
                </tr>
            </thead>
            <tbody data-commissioning-test-body>
                @for ($i = 0; $i < $record['motor_test_rows']; $i++)
                    <tr>
                        <td><input type="text" class="form-control"></td>
                        <td><input type="text" class="form-control"></td>
                        <td><input type="text" class="form-control"></td>
                        <td><input type="text" class="form-control"></td>
                        <td><input type="text" class="form-control"></td>
                        @if ($i === 0)
                            <td rowspan="{{ $record['motor_test_rows'] }}" class="commissioning-merged-remarks">
                                <textarea class="form-control" rows="8">Tools/Brand/Model</textarea>
                            </td>
                        @endif
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>
    <div class="qc-add-row-area commissioning-add-row-area">
        <button type="button" class="btn btn-primary" data-commissioning-add-test>
            <i class="bi bi-plus-lg me-2"></i>Tambah Row Test
        </button>
    </div>

    <div class="commissioning-section-title">Vibration Measurement (mm/s)</div>
    <div class="table-responsive">
        <table class="commissioning-table vibration-table">
            <thead>
                <tr>
                    <th>Test Point</th>
                    <th>0</th>
                    <th>5</th>
                    <th>10</th>
                    <th>20</th>
                    <th>40</th>
                    <th>60</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($record['vibration_points'] as $point)
                    @php($isFirstPoint = $loop->first)
                    <tr>
                        <td>{{ $loop->iteration }}. {{ $point }}</td>
                        @for ($i = 0; $i < 6; $i++)
                            <td><input type="text" class="form-control"></td>
                        @endfor
                        <td><input type="text" class="form-control" placeholder="{{ $isFirstPoint ? 'Tools/Brand/Model' : '' }}"></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="commissioning-bottom-grid">
        <label class="qc-record-note-box">
            <span>Notes/Finding</span>
            <textarea class="form-control" rows="4"></textarea>
        </label>
        <div class="commissioning-rms-box">
            <strong>RMS Vibration Velocity - ISO 10816-1</strong>
            @foreach ($record['rms_standard'] as $standard)
                <span>{{ $standard }}</span>
            @endforeach
        </div>
    </div>

    <div class="commissioning-approval-grid">
        @foreach ([
            'Prepared by / Dibuat Oleh' => $record['prepared_by'],
            'Checked by / Diperiksa Oleh' => $record['checked_by'],
            'Area Leader' => $record['area_leader'],
            'Witnessed and Approved by / Disaksikan dan disetujui oleh' => $record['approved_by'],
        ] as $label => $value)
            <div class="commissioning-sign-box">
                <strong>{{ $label }}</strong>
                <span>{{ $value }}</span>
                @if ($loop->first)
                    <button type="button" class="btn btn-outline-primary btn-sm mt-3" data-bs-toggle="modal" data-bs-target="#{{ $signatureModalId }}">
                        <i class="bi bi-vector-pen me-1"></i>Tanda Tangan
                    </button>
                @else
                    <em>Terkunci</em>
                @endif
                <label>Date : <input type="date" class="form-control"></label>
            </div>
        @endforeach
    </div>

    <div class="modal fade" id="{{ $signatureModalId }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content qc-signature-modal" data-signature-pad>
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">Tanda Tangan Commissioning</h5>
                        <small class="text-muted">Canvas dummy untuk simulasi tanda tangan.</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <canvas class="qc-signature-canvas" width="760" height="260" data-signature-canvas></canvas>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-signature-clear>Hapus</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Simpan Dummy</button>
                </div>
            </div>
        </div>
    </div>
</section>

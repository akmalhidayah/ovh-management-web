@php
    use App\Support\QcTemplates\FixedQcTemplate;
@endphp

<section class="inspector-panel qc-form-card">
    <div class="qc-form-section-title">
        <h3>Metode QC dan Pengecekan ke</h3>
    </div>
    <div class="qc-method-check-grid">
        <div class="qc-user-table-wrap qc-mobile-card-wrap qc-welding-method-wrap">
            <table class="qc-user-checklist-table qc-user-fixed-table qc-mobile-card-table qc-method-table qc-welding-method-table">
                <thead>
                    <tr><th colspan="{{ count(FixedQcTemplate::defaultMethods()) }}">Metode QC</th></tr>
                    <tr>
                        @foreach (FixedQcTemplate::defaultMethods() as $method)
                            <th>{{ $method }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @foreach (FixedQcTemplate::defaultMethods() as $method)
                            <td class="text-center" data-label="{{ $method }}">
                                <input type="checkbox" name="body[methods][]" value="{{ $method }}" @checked(in_array($method, $methodValues, true))>
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="qc-user-table-wrap qc-mobile-card-wrap qc-welding-check-step-wrap">
            <table class="qc-user-checklist-table qc-user-fixed-table qc-mobile-card-table qc-check-step-table qc-welding-check-step-table">
                <thead>
                    <tr><th colspan="{{ count(FixedQcTemplate::defaultCheckSteps()) }}">Pengecekan ke</th></tr>
                    <tr>
                        @foreach (FixedQcTemplate::defaultCheckSteps() as $step)
                            <th>{{ $step }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @foreach (FixedQcTemplate::defaultCheckSteps() as $step)
                            <td class="text-center" data-label="{{ $step }}">
                                <input type="checkbox" name="body[check_steps][]" value="{{ $step }}" @checked(in_array($step, $checkStepValues, true))>
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="inspector-panel qc-form-card">
    <div class="qc-form-section-title">
        <h3>Tabel Welder</h3>
    </div>
    <div class="qc-user-table-wrap qc-mobile-card-wrap qc-welding-welder-wrap">
        <table class="qc-user-checklist-table qc-user-fixed-table qc-mobile-card-table qc-welding-welder-table">
            <colgroup>
                <col style="width: 6%">
                <col style="width: 15%">
                <col style="width: 16%">
                <col style="width: 15%">
                <col style="width: 15%">
                <col style="width: 13%">
                <col style="width: 20%">
            </colgroup>
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
            <tbody data-user-welder-list>
                @forelse ($welderRows as $index => $row)
                    <tr data-user-welder-row>
                        <td data-label="No"><input type="text" name="body[welder_rows][{{ $index }}][no]" value="{{ $row['no'] ?? $loop->iteration }}" class="form-control form-control-sm text-center" readonly></td>
                        <td data-label="Nama Welder"><input type="text" name="body[welder_rows][{{ $index }}][nama_welder]" value="{{ $row['nama_welder'] ?? '' }}" class="form-control form-control-sm" required></td>
                        <td data-label="Posisi Pengelasan"><input type="text" name="body[welder_rows][{{ $index }}][posisi_pengelasan]" value="{{ $row['posisi_pengelasan'] ?? '' }}" class="form-control form-control-sm" required></td>
                        <td data-label="Diameter Electrode"><input type="text" name="body[welder_rows][{{ $index }}][diameter_electrode]" value="{{ $row['diameter_electrode'] ?? '' }}" class="form-control form-control-sm" required></td>
                        <td data-label="Electrode/Filter"><input type="text" name="body[welder_rows][{{ $index }}][electrode_filter]" value="{{ $row['electrode_filter'] ?? '' }}" class="form-control form-control-sm" required></td>
                        <td data-label="Amper"><input type="text" name="body[welder_rows][{{ $index }}][amper]" value="{{ $row['amper'] ?? '' }}" class="form-control form-control-sm" required></td>
                        <td data-label="Keterangan"><input type="text" name="body[welder_rows][{{ $index }}][keterangan]" value="{{ $row['keterangan'] ?? '' }}" class="form-control form-control-sm"></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-3" data-label="Info">Belum ada row welder dari template admin.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<section class="inspector-panel qc-form-card">
    <div class="qc-form-section-title">
        <h3>Tabel Hasil QC Welding</h3>
    </div>
    <div class="qc-user-table-wrap qc-mobile-card-wrap qc-welding-result-wrap">
        <table class="qc-user-checklist-table qc-user-fixed-table qc-mobile-card-table qc-welding-result-table">
            <colgroup>
                <col style="width: 6%">
                <col style="width: 28%">
                <col style="width: 12%">
                <col style="width: 15%">
                <col style="width: 14%">
                <col style="width: 25%">
            </colgroup>
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
            <tbody data-user-result-list>
                @forelse ($resultRows as $index => $row)
                    <tr data-user-result-row>
                        <td data-label="No"><input type="text" name="body[result_rows][{{ $index }}][no]" value="{{ $row['no'] ?? $loop->iteration }}" class="form-control form-control-sm text-center" readonly></td>
                        <td data-label="Deskripsi">
                            <input type="hidden" name="body[result_rows][{{ $index }}][deskripsi]" value="{{ $row['deskripsi'] ?? '' }}">
                            <div class="qc-readonly-template-text">{{ $row['deskripsi'] ?? '' }}</div>
                        </td>
                        @foreach (['Baik', 'Perlu Perbaikan', 'Tidak Layak'] as $status)
                            <td class="text-center" data-label="{{ $status }}"><input type="radio" name="body[result_rows][{{ $index }}][status]" value="{{ $status }}" @checked(($row['status'] ?? null) === $status) @if ($status === 'Baik') data-qc-ok-status @else data-qc-not-ok-status @endif required></td>
                        @endforeach
                        <td data-label="Keterangan"><input type="text" name="body[result_rows][{{ $index }}][keterangan]" value="{{ $row['keterangan'] ?? '' }}" class="form-control form-control-sm"></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-3" data-label="Info">Belum ada row hasil QC dari template admin.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

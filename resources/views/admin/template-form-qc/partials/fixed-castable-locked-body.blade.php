@php
    use App\Support\QcTemplates\FixedQcTemplate;
@endphp

<div class="table-responsive mb-3">
    <table class="table table-bordered align-middle mb-0">
        <tbody>
            <tr class="table-light"><th colspan="3">CUSTOMER DATA</th></tr>
            @foreach (FixedQcTemplate::castableCustomerRows() as $row)
                <tr>
                    <td class="text-center" style="width: 64px;">{{ $row['no'] }}</td>
                    <td class="fw-semibold" style="width: 34%;">{{ $row['label'] }}</td>
                    <td>
                        <input type="text" class="form-control form-control-sm" value="{{ $row['hint'] }}" disabled>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="d-flex align-items-center justify-content-between gap-3 mt-4 mb-2">
    <div>
        <h5 class="mb-1">Monitoring Installation Castable</h5>
        <p class="text-muted mb-0 small">Struktur monitoring fixed dan terkunci di template admin. User QC dapat menambah row saat pengisian form.</p>
    </div>
    <span class="badge text-bg-secondary">Locked</span>
</div>

<div class="row g-2 mb-3">
    <div class="col-12 col-md-5">
        <label class="form-label small fw-semibold">Type Material / Mixing</label>
        <input type="text" class="form-control form-control-sm" placeholder="Diisi user QC" disabled>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered align-middle mb-0">
        <thead>
            <tr class="table-light">
                <th rowspan="3">No.</th>
                <th colspan="2">Quantity Material/Mixing</th>
                <th rowspan="3">Temperatur Material<br>(kering)</th>
                <th rowspan="3">Temperatur Ruangan<br>C</th>
                <th rowspan="3">Waktu Aduk<br>(... Standard...)<br>Menit</th>
                <th colspan="3">Air</th>
                <th rowspan="3">Lokasi Pemasangan</th>
                <th rowspan="3">Keterangan</th>
            </tr>
            <tr class="table-light">
                <th colspan="2" class="text-start">Type : ....................</th>
                <th rowspan="2">Persentase<br>(... Standard...)<br>(%)</th>
                <th rowspan="2">(... Standard...)<br>PH</th>
                <th rowspan="2">Temperatur<br>(... Standard...)<br>(C)</th>
            </tr>
            <tr class="table-light">
                <th>Quantity<br>(kg)</th>
                <th>Batch number</th>
            </tr>
        </thead>
        <tbody>
            @foreach (collect(FixedQcTemplate::defaultCastableMonitoringRows())->take(1) as $row)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    @foreach (FixedQcTemplate::castableMonitoringColumns() as $column)
                        <td><input type="text" class="form-control form-control-sm" placeholder="{{ $column['placeholder'] }}" disabled></td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="table-responsive">
    <table class="table table-bordered align-middle mb-0">
        <tbody>
            <tr class="table-light"><th colspan="5">INSTALLATION RECORD / INSPECTION CHECK LIST</th></tr>
            @foreach (FixedQcTemplate::castableInspectionRows() as $row)
                <tr>
                    <td class="text-center" style="width: 64px;">{{ $row['no'] }}</td>
                    <td class="fw-semibold" style="width: 29%;">{{ $row['label'] }}</td>
                    <td style="width: 26%;">
                        @if (! empty($row['options']))
                            @foreach ($row['options'] as $option)
                                <label class="me-3"><input type="checkbox" disabled> {{ $option }}</label>
                            @endforeach
                        @elseif (($row['input'] ?? null) === 'dimension')
                            <div class="d-flex align-items-center gap-2">
                                <span>(</span>
                                <input type="number" class="form-control form-control-sm" disabled>
                                <span>x</span>
                                <input type="number" class="form-control form-control-sm" disabled>
                                <span>x</span>
                                <input type="number" class="form-control form-control-sm" disabled>
                                <span>)</span>
                            </div>
                        @else
                            <input type="{{ ($row['input'] ?? null) === 'number' ? 'number' : 'text' }}" class="form-control form-control-sm" disabled>
                        @endif
                    </td>
                    <td class="text-center" style="width: 13%;">{{ $row['unit'] ?? '' }}</td>
                    <td>
                        @if (! empty($row['detail_label']))
                            <div class="d-flex align-items-center gap-2">
                                <span class="small text-muted text-nowrap">{{ $row['detail_label'] }} :</span>
                                <input type="text" class="form-control form-control-sm" disabled>
                            </div>
                        @endif
                    </td>
                </tr>
            @endforeach
            <tr>
                <td colspan="5" class="small text-muted">*Sample For Laboratory test by QC</td>
            </tr>
            <tr class="table-light"><th colspan="5" class="text-center">SAMPLE DATA</th></tr>
            @foreach (FixedQcTemplate::castableSampleRows() as $row)
                <tr>
                    <td colspan="2" class="fw-semibold">{{ $row['label'] }}</td>
                    <td colspan="3">
                        @if ($row['key'] === 'qc_sign_date')
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm" disabled>Tanda Tangan</button>
                                <input type="date" class="form-control form-control-sm" disabled>
                            </div>
                        @else
                            <input type="{{ $row['key'] === 'quantity' ? 'number' : 'text' }}" class="form-control form-control-sm" disabled>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

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
                        @else
                            <input type="text" class="form-control form-control-sm" disabled>
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
                    <td colspan="3"><input type="text" class="form-control form-control-sm" disabled></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

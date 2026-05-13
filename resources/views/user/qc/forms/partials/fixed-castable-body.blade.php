@php
    use App\Support\QcTemplates\FixedQcTemplate;

    $castableCustomer = $oldBody['castable_customer'] ?? [];
    $castableChecks = $oldBody['castable_checks'] ?? [];
    $castableSample = $oldBody['castable_sample'] ?? [];
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
                            @else
                                <input type="text"
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
            <label class="qc-user-field">
                <span>{{ $row['label'] }}</span>
                <input type="text"
                       name="body[castable_sample][{{ $row['key'] }}]"
                       value="{{ $castableSample[$row['key']] ?? '' }}"
                       class="form-control">
            </label>
        @endforeach
    </div>
</section>

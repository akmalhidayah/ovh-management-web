@php
    use App\Support\Commissioning\FixedCommissioningTemplate;

    $schema = FixedCommissioningTemplate::normalizeSchema($template->body_schema ?? FixedCommissioningTemplate::defaultSchema());
    $labels = $schema['labels'];
    $motorRows = $schema['motor_test_rows'];
    $gearboxRows = $schema['gearbox_test_rows'];
    $rows = $schema['equipment_check_rows'] ?? [];
@endphp

<div class="accordion" id="commissioningPreviewAccordion">
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#commissioningHeaderPreview">
                Header Fixed
            </button>
        </h2>
        <div id="commissioningHeaderPreview" class="accordion-collapse collapse show" data-bs-parent="#commissioningPreviewAccordion">
            <div class="accordion-body">
                <div class="row g-3">
                    @foreach (FixedCommissioningTemplate::headerFields() as $field)
                        <div class="col-12 col-md-4">
                            <label class="form-label">{{ $field['label'] }}</label>
                            <input type="text" class="form-control" disabled>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#commissioningEquipmentPreview">
                {{ $labels['equipment_check_title'] }}
            </button>
        </h2>
        <div id="commissioningEquipmentPreview" class="accordion-collapse collapse" data-bs-parent="#commissioningPreviewAccordion">
            <div class="accordion-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 90px;">No</th>
                                <th>Item Check</th>
                                <th style="width: 140px;">OK</th>
                                <th style="width: 140px;">Not OK</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr>
                                    <td>{{ $row['no'] ?? $loop->iteration }}</td>
                                    <td>{{ $row['item'] ?? '-' }}</td>
                                    <td class="text-center"><input type="radio" disabled></td>
                                    <td class="text-center"><input type="radio" disabled></td>
                                    <td><input type="text" class="form-control form-control-sm" disabled></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">Belum ada item check.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#commissioningFixedPreview">
                Body Form Lainnya
            </button>
        </h2>
        <div id="commissioningFixedPreview" class="accordion-collapse collapse" data-bs-parent="#commissioningPreviewAccordion">
            <div class="accordion-body">
                <div class="row g-3">
                    @foreach ([
                        $labels['motor_title'].' ('.count($motorRows).' row default)',
                        $labels['gearbox_title'].' ('.count($gearboxRows).' row default)',
                        $labels['note_label'],
                        $labels['documentation_label'],
                    ] as $label)
                        <div class="col-12 col-md-4">
                            <div class="qc-preview-attachment-box h-100">
                                <i class="bi bi-pencil-square"></i>
                                <strong>{{ $label }}</strong>
                                <span>Dikonfigurasi dari template admin</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

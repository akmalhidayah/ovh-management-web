@php
    use App\Support\QcTemplates\FixedQcTemplate;
    use Illuminate\Support\Str;

    $bricsCustomer = $oldBody['brics_customer'] ?? [];
    $bricsMeta = $oldBody['brics_meta'] ?? [];
    $bricsTechnical = $oldBody['brics_technical'] ?? [];
    $bricsManpower = $oldBody['brics_manpower'] ?? [];
    $bricsManpowerRows = collect($oldBody['brics_manpower_rows'] ?? [])
        ->map(fn ($row) => [
            'left_label' => $row['left_label'] ?? '',
            'left_value' => $row['left_value'] ?? '',
            'right_label' => $row['right_label'] ?? '',
            'right_value' => $row['right_value'] ?? '',
        ])
        ->filter(fn ($row) => collect($row)->filter()->isNotEmpty())
        ->values();

    if ($bricsManpowerRows->isEmpty()) {
        $bricsManpowerRows = collect(FixedQcTemplate::bricsManpowerRows())
            ->map(fn ($row) => [
                'left_label' => $row['left'],
                'left_value' => $bricsManpower[Str::slug($row['left'], '_')] ?? $bricsManpower[str($row['left'])->snake()->toString()] ?? '',
                'right_label' => $row['right'],
                'right_value' => $bricsManpower[Str::slug($row['right'], '_')] ?? $bricsManpower[str($row['right'])->snake()->toString()] ?? '',
            ]);
    }

    $bricsWeather = $oldBody['brics_weather'] ?? [];
    $bricsChecks = $oldBody['brics_checks'] ?? [];
@endphp

<section class="inspector-panel qc-form-card">
    <div class="qc-form-section-title"><h3>Customer Data</h3></div>
    <div class="qc-user-table-wrap qc-mobile-card-wrap qc-brics-customer-wrap">
        <table class="qc-user-checklist-table qc-user-fixed-table qc-mobile-card-table qc-brics-customer-table">
            <colgroup>
                <col style="width: 7%">
                <col style="width: 25%">
                <col style="width: 48%">
                <col style="width: 10%">
                <col style="width: 10%">
            </colgroup>
            <tbody>
                @foreach (FixedQcTemplate::bricsCustomerRows() as $row)
                    <tr>
                        <td class="text-center" data-label="No">{{ $row['no'] }}</td>
                        <td data-label="Item">{{ $row['label'] }}</td>
                        <td data-label="Data">
                            <input type="text"
                                   name="body[brics_customer][{{ $row['key'] }}]"
                                   value="{{ $bricsCustomer[$row['key']] ?? ($row['default'] ?? '') }}"
                                   class="form-control form-control-sm">
                        </td>
                        @if ($loop->first)
                            <td rowspan="2" class="text-center fw-semibold" data-label="Meta">OWNER</td>
                            <td rowspan="2" data-label="Owner">
                                <input type="text"
                                       name="body[brics_meta][owner]"
                                       value="{{ $bricsMeta['owner'] ?? '' }}"
                                       class="form-control form-control-sm">
                            </td>
                        @elseif ($loop->iteration === 3)
                            <td rowspan="2" class="text-center fw-semibold" data-label="Meta">TYPE<br>INSPECT</td>
                            <td rowspan="2" data-label="Type Inspect">
                                <input type="text"
                                       name="body[brics_meta][type_inspect]"
                                       value="{{ $bricsMeta['type_inspect'] ?? '' }}"
                                       class="form-control form-control-sm">
                            </td>
                        @elseif ($loop->iteration === 5)
                            <td rowspan="2" class="text-center fw-semibold" data-label="Meta">NO.<br>REPORT</td>
                            <td rowspan="2" data-label="No. Report">
                                <input type="text"
                                       name="body[brics_meta][no_report]"
                                       value="{{ $bricsMeta['no_report'] ?? '' }}"
                                       class="form-control form-control-sm">
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>

<section class="inspector-panel qc-form-card">
    <div class="qc-form-section-title"><h3>Kiln Technical Information</h3></div>
    <div class="qc-user-field-grid">
        @foreach (FixedQcTemplate::bricsTechnicalRows() as $row)
            <label class="qc-user-field">
                <span>{{ $row['label'] }}</span>
                <input type="{{ $row['type'] ?? 'text' }}"
                       name="body[brics_technical][{{ $row['key'] }}]"
                       value="{{ $bricsTechnical[$row['key']] ?? '' }}"
                       class="form-control">
            </label>
        @endforeach
    </div>
</section>

<section class="inspector-panel qc-form-card">
    <div class="qc-form-section-title"><h3>Manpower & Weather</h3></div>
    <div class="qc-user-table-wrap qc-mobile-card-wrap qc-brics-manpower-wrap mb-3">
        <table class="qc-user-checklist-table qc-user-fixed-table qc-mobile-card-table qc-brics-manpower-table">
            <colgroup>
                <col style="width: 22%">
                <col style="width: 28%">
                <col style="width: 22%">
                <col style="width: 23%">
                <col style="width: 5%">
            </colgroup>
            <thead>
                <tr>
                    <th>Manpower</th>
                    <th>Nama / Jumlah</th>
                    <th>Manpower</th>
                    <th>Nama / Jumlah</th>
                    <th></th>
                </tr>
            </thead>
            <tbody data-brics-manpower-list>
                @foreach ($bricsManpowerRows as $index => $row)
                    <tr data-brics-manpower-row>
                        <td data-label="Manpower">
                            <input type="text"
                                   name="body[brics_manpower_rows][{{ $index }}][left_label]"
                                   value="{{ $row['left_label'] }}"
                                   class="form-control form-control-sm"
                                   placeholder="Contoh: SPV">
                        </td>
                        <td data-label="Nama / Jumlah">
                            <input type="text"
                                   name="body[brics_manpower_rows][{{ $index }}][left_value]"
                                   value="{{ $row['left_value'] }}"
                                   class="form-control form-control-sm"
                                   placeholder="Contoh: Andi / 4 orang">
                        </td>
                        <td data-label="Manpower">
                            <input type="text"
                                   name="body[brics_manpower_rows][{{ $index }}][right_label]"
                                   value="{{ $row['right_label'] }}"
                                   class="form-control form-control-sm"
                                   placeholder="Contoh: ME">
                        </td>
                        <td data-label="Nama / Jumlah">
                            <input type="text"
                                   name="body[brics_manpower_rows][{{ $index }}][right_value]"
                                   value="{{ $row['right_value'] }}"
                                   class="form-control form-control-sm"
                                   placeholder="Contoh: Budi / 2 orang">
                        </td>
                        <td class="text-center" style="width: 56px;" data-label="Aksi">
                            <button type="button" class="btn btn-outline-danger btn-sm" data-brics-manpower-remove title="Hapus row">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mb-4">
        <button type="button" class="btn btn-outline-primary btn-sm" data-brics-manpower-add>
            <i class="bi bi-plus-lg me-1"></i>Tambah Row Manpower
        </button>
    </div>
    <div class="qc-user-table-wrap qc-mobile-card-wrap qc-brics-weather-wrap">
        <table class="qc-user-checklist-table qc-user-fixed-table qc-mobile-card-table qc-brics-weather-table">
            <thead><tr><th>Weather</th><th>Rainy</th><th>Clear</th></tr></thead>
            <tbody>
                @foreach (['day' => 'DAY', 'night' => 'NIGHT'] as $key => $label)
                    <tr>
                        <td data-label="Waktu">{{ $label }}</td>
                        @foreach (['Rainy', 'Clear'] as $weather)
                            <td class="text-center" data-label="{{ $weather }}">
                                <input type="radio"
                                       name="body[brics_weather][{{ $key }}]"
                                       value="{{ $weather }}"
                                       data-final-check-ok="1"
                                       @checked(($bricsWeather[$key] ?? null) === $weather)>
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
            const list = document.querySelector('[data-brics-manpower-list]');
            const addButton = document.querySelector('[data-brics-manpower-add]');

            if (!list || !addButton) return;

            const rowTemplate = (index) => `
                <tr data-brics-manpower-row>
                    <td data-label="Manpower"><input type="text" name="body[brics_manpower_rows][${index}][left_label]" class="form-control form-control-sm" placeholder="Contoh: SPV"></td>
                    <td data-label="Nama / Jumlah"><input type="text" name="body[brics_manpower_rows][${index}][left_value]" class="form-control form-control-sm" placeholder="Contoh: Andi / 4 orang"></td>
                    <td data-label="Manpower"><input type="text" name="body[brics_manpower_rows][${index}][right_label]" class="form-control form-control-sm" placeholder="Contoh: ME"></td>
                    <td data-label="Nama / Jumlah"><input type="text" name="body[brics_manpower_rows][${index}][right_value]" class="form-control form-control-sm" placeholder="Contoh: Budi / 2 orang"></td>
                    <td class="text-center" style="width: 56px;" data-label="Aksi">
                        <button type="button" class="btn btn-outline-danger btn-sm" data-brics-manpower-remove title="Hapus row">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;

            const reindexRows = () => {
                list.querySelectorAll('[data-brics-manpower-row]').forEach((row, index) => {
                    ['left_label', 'left_value', 'right_label', 'right_value'].forEach((key) => {
                        const input = row.querySelector(`[name$="[${key}]"]`);
                        if (input) input.name = `body[brics_manpower_rows][${index}][${key}]`;
                    });
                });
            };

            addButton.addEventListener('click', () => {
                list.insertAdjacentHTML('beforeend', rowTemplate(list.querySelectorAll('[data-brics-manpower-row]').length));
            });

            list.addEventListener('click', (event) => {
                const button = event.target.closest('[data-brics-manpower-remove]');
                if (!button) return;

                button.closest('[data-brics-manpower-row]')?.remove();
                reindexRows();
            });
        })();
    </script>
@endpush

<section class="inspector-panel qc-form-card">
    <div class="qc-form-section-title"><h3>Installation Record / Inspection Check List</h3></div>
    <div class="qc-user-table-wrap qc-mobile-card-wrap qc-brics-check-wrap">
        <table class="qc-user-checklist-table qc-user-fixed-table qc-mobile-card-table qc-brics-check-table">
            <colgroup>
                <col style="width: 7%">
                <col style="width: 29%">
                <col style="width: 12%">
                <col style="width: 12%">
                <col style="width: 40%">
            </colgroup>
            <thead>
                <tr>
                    <th colspan="2">Item</th>
                    <th class="text-center">OK</th>
                    <th class="text-center">NO</th>
                    <th>Remark</th>
                </tr>
            </thead>
            <tbody>
                @foreach (FixedQcTemplate::bricsInspectionSections() as $section)
                    <tr class="qc-mobile-section-row">
                        <th colspan="5">{{ trim(($section['number'] ?? '').' '.$section['title']) }}</th>
                    </tr>
                    @foreach ($section['items'] as $row)
                        @php
                            $saved = $bricsChecks[$row['key']] ?? [];
                        @endphp
                        <tr>
                            <td class="text-center" data-label="No">{{ $row['no'] }}</td>
                            <td data-label="Item">{{ $row['label'] }}</td>
                            @foreach (['OK', 'NO'] as $status)
                                <td class="text-center" data-label="{{ $status }}">
                                    <input type="radio"
                                           name="body[brics_checks][{{ $row['key'] }}][status]"
                                           value="{{ $status }}"
                                           @if ($status === 'OK') data-final-check-ok="1" @endif
                                           @checked(($saved['status'] ?? null) === $status)>
                                </td>
                            @endforeach
                            <td data-label="Remark">
                                <input type="text"
                                       name="body[brics_checks][{{ $row['key'] }}][remark]"
                                       value="{{ $saved['remark'] ?? '' }}"
                                       class="form-control form-control-sm">
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</section>

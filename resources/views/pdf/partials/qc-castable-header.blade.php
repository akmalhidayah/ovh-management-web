@php
    $headerValue = fn (string $key, string $fallback = '') => $generalInfo[$key] ?? $fallback;
    $castableHeaderGroups = [
        [
            ['label' => 'Doc.Number', 'value' => $headerValue('doc_number', $submission->report_no ?: $submission->form_number)],
            ['label' => 'Functional Loc', 'value' => $headerValue('functional_location')],
            ['label' => 'Area', 'value' => $headerValue('area', $submission->area ?: '')],
            ['label' => 'Pekerjaan', 'value' => $headerValue('pekerjaan', $submission->pekerjaan ?: '')],
        ],
        [
            ['label' => 'Plant', 'value' => $headerValue('plant', $submission->plant ?: '')],
            ['label' => 'ID Equipment', 'value' => $headerValue('id_equipment')],
            ['label' => 'Date & Time', 'value' => $dateTime ?: ''],
            ['label' => 'Unit kerja', 'value' => $headerValue('unit_kerja', $submission->unit ?: '')],
        ],
        [
            ['label' => 'Section No.', 'value' => $headerValue('tag_num', $submission->tag_num ?: '')],
            ['label' => 'Name Equipment', 'value' => $headerValue('name_equipment', $submission->equipment ?: '')],
            ['label' => 'Inspector QC', 'value' => $headerValue('inspector_qc', $submission->user?->name ?: '')],
            ['label' => 'Durasi (Menit)', 'value' => $headerValue('durasi', $submission->durasi ?: '')],
        ],
    ];

    $castableHeaderRows = collect(range(0, 3))
        ->map(fn ($rowIndex) => collect($castableHeaderGroups)->map(fn ($group) => $group[$rowIndex])->all())
        ->all();
@endphp

<table class="top-table castable-title-table">
    <tr>
        <td class="logo-cell">
            @if (file_exists($logoSig))
                <img src="{{ $logoSig }}" class="sig-logo" alt="SIG">
            @endif
        </td>
        <td class="title-cell">
            FORM QUALITY CONTROL
            <span class="title-work">{{ $pdfTitlePekerjaan }}</span>
        </td>
        <td class="logo-cell">
            @if (file_exists($logoSt))
                <img src="{{ $logoSt }}" class="st-logo" alt="ST">
            @endif
        </td>
    </tr>
</table>

@include('pdf.partials.qc-info-table', ['rows' => $castableHeaderRows])

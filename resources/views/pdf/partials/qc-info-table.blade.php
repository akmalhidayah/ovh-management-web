@php
    $infoRows = $rows ?? $headerRows ?? [];
    $normalizedInfoLabel = static function (string $label): string {
        return [
            'Functional Location' => 'Functional Loc',
            'Section' => 'Section No.',
            'Durasi (menit)' => 'Durasi (Menit)',
            'Unit Kerja' => 'Unit kerja',
        ][$label] ?? $label;
    };
@endphp

<table class="info-table">
    @foreach ($infoRows as $row)
        <tr>
            @foreach ($row as $cell)
                @php $label = $normalizedInfoLabel((string) ($cell['label'] ?? '')); @endphp
                <td class="info-label-cell">
                    <span class="info-label">{!! str_replace(' ', '&nbsp;', e($label)) !!}&nbsp;:</span>
                </td>
                <td class="info-value-cell">
                    <span class="info-value">{{ $cell['value'] }}</span>
                </td>
            @endforeach
        </tr>
    @endforeach
</table>

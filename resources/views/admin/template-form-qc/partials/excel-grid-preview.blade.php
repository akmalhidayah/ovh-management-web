@php
    $cells = $template->gridCells->sortBy('order_no')->values();
    $maxRow = max(1, $cells->max(fn ($cell) => $cell->row_start + $cell->row_span - 1) ?? 1);
    $maxCol = max(1, $cells->max(fn ($cell) => $cell->col_start + $cell->col_span - 1) ?? 1);
    $cellMap = [];
    $covered = [];

    foreach ($cells as $cell) {
        $cellMap[$cell->row_start][$cell->col_start] = $cell;

        for ($row = $cell->row_start; $row < $cell->row_start + $cell->row_span; $row++) {
            for ($col = $cell->col_start; $col < $cell->col_start + $cell->col_span; $col++) {
                if ($row === $cell->row_start && $col === $cell->col_start) {
                    continue;
                }

                $covered[$row][$col] = true;
            }
        }
    }

    $styleToString = function (?array $style): string {
        if (! $style) {
            return '';
        }

        return collect($style)
            ->map(fn ($value, $key) => $key.': '.$value)
            ->implode('; ');
    };
@endphp

@if ($cells->isEmpty())
    <div class="alert alert-warning mb-0">
        Belum ada layout grid untuk template ini.
    </div>
@else
<div class="qc-excel-wrapper">
    <table class="qc-excel-sheet" style="--qc-grid-columns: {{ $maxCol }};">
        <colgroup>
            @for ($col = 1; $col <= $maxCol; $col++)
                <col>
            @endfor
        </colgroup>
        <tbody>
            @for ($row = 1; $row <= $maxRow; $row++)
                <tr>
                    @for ($col = 1; $col <= $maxCol; $col++)
                        @continue(isset($covered[$row][$col]))

                        @php($cell = $cellMap[$row][$col] ?? null)

                        @if (! $cell)
                            <td class="qc-excel-cell"></td>
                            @continue
                        @endif

                        <td
                            class="qc-excel-cell {{ $cell->css_class }}"
                            rowspan="{{ $cell->row_span }}"
                            colspan="{{ $cell->col_span }}"
                            style="{{ $styleToString($cell->style) }}"
                        >
                            @switch($cell->cell_type)
                                @case('logo')
                                    @php($logoPath = $cell->value_default ?: 'assets/images/logo/logo-sig.png')
                                    @if (file_exists(public_path($logoPath)))
                                        <img src="{{ asset($logoPath) }}" alt="{{ $cell->label ?: 'SIG' }}" class="qc-excel-logo">
                                    @else
                                        <strong>SIG</strong>
                                    @endif
                                    @break

                                @case('input')
                                    @if ($cell->label)
                                        <span class="qc-excel-label-inline">{{ $cell->label }}</span>
                                    @endif
                                    <input type="{{ $cell->input_type ?: 'text' }}" class="qc-excel-input-control" value="{{ $cell->value_default }}" disabled>
                                    @break

                                @case('textarea')
                                    @if ($cell->label)
                                        <span class="qc-excel-label-inline">{{ $cell->label }}</span>
                                    @endif
                                    <textarea class="qc-excel-textarea-control" disabled>{{ $cell->value_default }}</textarea>
                                    @break

                                @case('select')
                                    @if ($cell->label)
                                        <span class="qc-excel-label-inline">{{ $cell->label }}</span>
                                    @endif
                                    <select class="qc-excel-input-control" disabled>
                                        @foreach (($cell->options ?: ['OK', 'Not OK']) as $option)
                                            <option @selected($cell->value_default === $option)>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                    @break

                                @case('checkbox')
                                @case('radio')
                                    <label class="qc-excel-check">
                                        <input type="{{ $cell->cell_type }}" disabled>
                                        @if ($cell->label)
                                            <span>{{ $cell->label }}</span>
                                        @endif
                                    </label>
                                    @break

                                @case('empty')
                                    &nbsp;
                                    @break

                                @case('label')
                                @case('static')
                                @default
                                    {!! nl2br(e($cell->value_default ?: $cell->label)) !!}
                            @endswitch
                        </td>
                    @endfor
                </tr>
            @endfor
        </tbody>
    </table>
</div>
@endif

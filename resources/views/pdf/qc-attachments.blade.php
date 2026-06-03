@php
    use App\Support\QcTemplates\FixedQcTemplate;

    $attachmentPath = static function ($attachment): ?string {
        if (\Illuminate\Support\Facades\Storage::disk('local')->exists($attachment->file_path)) {
            return \Illuminate\Support\Facades\Storage::disk('local')->path($attachment->file_path);
        }

        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($attachment->file_path)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->path($attachment->file_path);
        }

        $publicPath = storage_path('app/public/'.$attachment->file_path);

        return file_exists($publicPath) ? $publicPath : null;
    };

    $attachmentImages = static function (string $key) use ($attachments, $attachmentPath) {
        return ($attachments[$key] ?? collect())
            ->take(6)
            ->map(function ($attachment) use ($attachmentPath) {
                $path = $attachmentPath($attachment);

                if ($attachment->type !== 'image' || ! $path || ! file_exists($path)) {
                    return null;
                }

                return [
                    'name' => $attachment->original_name,
                    'source' => $path,
                ];
            })
            ->filter()
            ->values();
    };

    $beforeItems = $attachmentImages('foto_before');
    $afterItems = $attachmentImages('foto_after');
    $supportItems = $attachmentImages('dokumen_pendukung');
    $beforeAfterRows = max($beforeItems->count(), $afterItems->count());
@endphp

<div class="attachment-page">
    @if ($type === FixedQcTemplate::TYPE_CASTABLE)
        @include('pdf.partials.qc-castable-header')
    @else
        <table class="top-table">
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
    @endif

    @if ($type !== FixedQcTemplate::TYPE_BRICS && $type !== FixedQcTemplate::TYPE_CASTABLE)
        @include('pdf.partials.qc-info-table', ['rows' => $headerRows])
    @endif

    <div class="section-gap"></div>
    <div class="attachment-label">Lampiran</div>

    @if ($beforeAfterRows === 0 && $supportItems->isEmpty())
        <div class="attachment-empty">Tidak ada lampiran gambar.</div>
    @else
        @if ($beforeAfterRows > 0)
            <table class="attachment-grid">
                <tr>
                    <th>Foto Before</th>
                    <th>Foto After</th>
                </tr>
                @for ($index = 0; $index < $beforeAfterRows; $index++)
                    <tr>
                        <td>
                            @if ($item = $beforeItems->get($index))
                                <img src="{{ $item['source'] }}" class="attachment-img" alt="{{ $item['name'] }}">
                            @endif
                        </td>
                        <td>
                            @if ($item = $afterItems->get($index))
                                <img src="{{ $item['source'] }}" class="attachment-img" alt="{{ $item['name'] }}">
                            @endif
                        </td>
                    </tr>
                @endfor
            </table>
        @endif

        @if ($supportItems->isNotEmpty())
            <table class="attachment-grid attachment-support-grid">
                <tr>
                    <th colspan="2">Dokumen Pendukung</th>
                </tr>
                @foreach ($supportItems->chunk(2) as $row)
                    <tr>
                        @foreach ($row as $item)
                            <td>
                                <img src="{{ $item['source'] }}" class="attachment-img" alt="{{ $item['name'] }}">
                            </td>
                        @endforeach
                        @if ($row->count() === 1)
                            <td>&nbsp;</td>
                        @endif
                    </tr>
                @endforeach
            </table>
        @endif
    @endif

</div>

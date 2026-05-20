@php
    use App\Support\QcTemplates\FixedQcTemplate;

    $attachmentItems = collect(['foto_before', 'foto_after', 'dokumen_pendukung'])
        ->flatMap(function ($key) use ($attachments) {
            return ($attachments[$key] ?? collect())
                ->take(6)
                ->map(function ($attachment) use ($key) {
                    $path = null;

                    if (\Illuminate\Support\Facades\Storage::disk('local')->exists($attachment->file_path)) {
                        $path = \Illuminate\Support\Facades\Storage::disk('local')->path($attachment->file_path);
                    } elseif (\Illuminate\Support\Facades\Storage::disk('public')->exists($attachment->file_path)) {
                        $path = \Illuminate\Support\Facades\Storage::disk('public')->path($attachment->file_path);
                    } else {
                        $publicPath = storage_path('app/public/'.$attachment->file_path);
                        $path = file_exists($publicPath) ? $publicPath : null;
                    }

                    if ($attachment->type !== 'image' || ! $path || ! file_exists($path)) {
                        return null;
                    }

                    $labels = [
                        'foto_before' => 'Foto Before',
                        'foto_after' => 'Foto After',
                        'dokumen_pendukung' => 'Dokumen Pendukung',
                    ];
                    $mime = $attachment->mime_type ?: 'image/jpeg';

                    return [
                        'label' => $labels[$key] ?? ($attachment->label ?: 'Lampiran'),
                        'name' => $attachment->original_name,
                        'source' => 'data:'.$mime.';base64,'.base64_encode(file_get_contents($path)),
                    ];
                })
                ->filter();
        })
        ->values();
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

    <div class="section-gap"></div>
    <div class="attachment-label">Lampiran</div>

    @if ($attachmentItems->isEmpty())
        <div class="attachment-empty">Tidak ada lampiran gambar.</div>
    @else
        <table class="attachment-grid">
            @foreach ($attachmentItems->chunk(2) as $row)
                <tr>
                    @foreach ($row as $item)
                        <td>
                            <div class="attachment-title">{{ $item['label'] }}</div>
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
</div>

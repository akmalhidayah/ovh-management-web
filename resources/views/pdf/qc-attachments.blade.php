@php
    use App\Support\QcTemplates\FixedQcTemplate;
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

    <table class="attachment-grid">
        <tr>
            <th>Foto Before</th>
            <th>Foto After</th>
        </tr>
        <tr>
            @foreach (['foto_before', 'foto_after'] as $key)
                <td>
                @foreach (($attachments[$key] ?? collect())->take(2) as $attachment)
                    @php
                        $path = null;

                        if (\Illuminate\Support\Facades\Storage::disk('local')->exists($attachment->file_path)) {
                            $path = \Illuminate\Support\Facades\Storage::disk('local')->path($attachment->file_path);
                        } elseif (\Illuminate\Support\Facades\Storage::disk('public')->exists($attachment->file_path)) {
                            $path = \Illuminate\Support\Facades\Storage::disk('public')->path($attachment->file_path);
                        } else {
                            $publicPath = storage_path('app/public/'.$attachment->file_path);
                            $path = file_exists($publicPath) ? $publicPath : null;
                        }

                        $imageSource = null;
                        if ($attachment->type === 'image' && $path && file_exists($path)) {
                            $mime = $attachment->mime_type ?: 'image/jpeg';
                            $imageSource = 'data:'.$mime.';base64,'.base64_encode(file_get_contents($path));
                        }
                    @endphp
                    @if ($imageSource)
                        <img src="{{ $imageSource }}" class="attachment-img" alt="{{ $attachment->original_name }}">
                    @endif
                @endforeach
                </td>
            @endforeach
        </tr>
    </table>
</div>

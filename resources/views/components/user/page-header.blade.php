@props([
    'title',
    'subtitle' => null,
    'eyebrow' => null,
])

<div {{ $attributes->class(['inspector-page-header']) }}>
    <div>
        @if ($eyebrow)
            <span class="inspector-eyebrow">{{ $eyebrow }}</span>
        @endif
        <h1>{{ $title }}</h1>
        @if ($subtitle)
            <p>{{ $subtitle }}</p>
        @endif
    </div>

    @if (trim((string) $slot) !== '')
        <div class="inspector-page-header-actions">
            {{ $slot }}
        </div>
    @endif
</div>

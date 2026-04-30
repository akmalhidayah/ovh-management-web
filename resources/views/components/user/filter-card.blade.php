@props([
    'title' => 'Filter',
    'subtitle' => null,
])

<section {{ $attributes->class(['inspector-panel']) }}>
    <div class="mb-3">
        <h2 class="inspector-section-title mb-2">{{ $title }}</h2>
        @if ($subtitle)
            <p class="text-muted mb-0">{{ $subtitle }}</p>
        @endif
    </div>
    {{ $slot }}
</section>

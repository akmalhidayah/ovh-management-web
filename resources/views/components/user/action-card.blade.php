@props([
    'title',
    'description' => null,
    'icon' => null,
    'dynamicTitle' => false,
])

<section {{ $attributes->class(['inspector-panel']) }}>
    <div class="d-flex align-items-start gap-3 mb-3">
        @if ($icon)
            <span class="inspector-panel-icon"><i class="bi {{ $icon }}"></i></span>
        @endif
        <div>
            <h3 class="inspector-section-title mb-2" @if ($dynamicTitle) data-selected-template-label @endif>{{ $title }}</h3>
            @if ($description)
                <p class="text-muted mb-0">{{ $description }}</p>
            @endif
        </div>
    </div>
    {{ $slot }}
</section>

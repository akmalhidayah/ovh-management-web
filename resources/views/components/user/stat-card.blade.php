@props([
    'label',
    'value',
    'icon' => 'bi-bar-chart',
    'accent' => 'primary',
    'subtitle' => null,
])

<div {{ $attributes->class(['inspector-stat-card', "accent-{$accent}"]) }}>
    <div class="inspector-stat-icon">
        <i class="bi {{ $icon }}"></i>
    </div>
    <div>
        <p class="inspector-stat-label">{{ $label }}</p>
        <h3 class="inspector-stat-value">{{ $value }}</h3>
        @if ($subtitle)
            <p class="inspector-stat-subtitle">{{ $subtitle }}</p>
        @endif
    </div>
</div>

@props(['title', 'value', 'icon' => 'bi-graph-up', 'tone' => 'primary', 'subtitle' => null])

<div class="stat-card">
    <div class="stat-icon text-bg-{{ $tone }}">
        <i class="bi {{ $icon }}"></i>
    </div>
    <div class="min-w-0">
        <div class="stat-title">{{ $title }}</div>
        <div class="stat-value">{{ $value }}</div>
        @if ($subtitle)
            <div class="stat-subtitle">{{ $subtitle }}</div>
        @endif
    </div>
</div>

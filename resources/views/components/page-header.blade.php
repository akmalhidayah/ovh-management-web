@props(['title', 'subtitle' => null])

<div class="page-header">
    <div>
        <h1>{{ $title }}</h1>
        @if ($subtitle)
            <p>{{ $subtitle }}</p>
        @endif
    </div>
    @if (trim($slot))
        <div class="page-actions">{{ $slot }}</div>
    @endif
</div>

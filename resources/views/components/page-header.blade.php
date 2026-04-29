@props(['title', 'subtitle' => null])

@if (trim($slot))
    <div class="page-header page-header-actions-only">
        <div class="page-actions">{{ $slot }}</div>
    </div>
@endif

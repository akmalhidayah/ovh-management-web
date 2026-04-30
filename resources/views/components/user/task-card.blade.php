@props(['task', 'actionLabel' => 'Buka'])

<article {{ $attributes->class(['inspector-task-card']) }}>
    <div class="d-flex justify-content-between gap-3 align-items-start mb-3">
        <div>
            <h3>{{ $task['equipment'] ?? $task['job'] }}</h3>
            <p class="mb-1">{{ $task['plant'] ?? ($task['date'] ?? '-') }}</p>
            <small>{{ $task['area'] ?? ($task['pic'] ?? '-') }}</small>
        </div>
        <x-user.status-badge :status="$task['status']" />
    </div>

    <div class="inspector-meta-list">
        @foreach (['type' => 'bi-ui-checks', 'schedule' => 'bi-calendar-event', 'progress' => 'bi-bar-chart', 'date' => 'bi-calendar-event', 'pic' => 'bi-person-badge'] as $key => $icon)
            @if (isset($task[$key]))
                <span><i class="bi {{ $icon }}"></i>{{ $task[$key] }}</span>
            @endif
        @endforeach
    </div>

    @if (trim((string) $slot) !== '')
        <div class="mt-3">{{ $slot }}</div>
    @endif
</article>

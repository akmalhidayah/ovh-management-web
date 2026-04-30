<x-user.page-header :title="$title" :subtitle="$subtitle" eyebrow="Riwayat PGO" />

<section class="inspector-panel">
    <div class="row g-3">
        @foreach ($rows as $row)
            <div class="col-lg-4">
                <article class="inspector-mobile-card h-100">
                    <small class="text-muted d-block mb-2">{{ $row['time'] }}</small>
                    <h3 class="mb-3">{{ $row['activity'] }}</h3>
                    <x-user.status-badge :status="$row['status']" />
                </article>
            </div>
        @endforeach
    </div>
</section>

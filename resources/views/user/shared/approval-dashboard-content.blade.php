<section class="inspector-hero-card">
    <div class="row align-items-center g-4">
        <div class="col-xl-7">
            <span class="inspector-chip chip-soft">{{ $hero['note'] }}</span>
            <h2>{{ $hero['title'] }}</h2>
            <p>{{ $hero['subtitle'] }}</p>
        </div>
        <div class="col-xl-5 text-center">
            <img src="{{ asset($roleUi['hero_asset']) }}" alt="{{ $hero['title'] }}" class="inspector-hero-illustration">
        </div>
    </div>
</section>

<section class="mt-4">
    <div class="row g-3 g-lg-4">
        @foreach ($stats as $stat)
            <div class="col-6 col-xl-3">
                <x-user.stat-card :label="$stat['label']" :value="$stat['value']" :icon="$stat['icon']" :accent="$stat['accent']" />
            </div>
        @endforeach
    </div>
</section>

<div class="row g-4 mt-1">
    <div class="col-xl-6">
        <x-user.action-card title="Form Menunggu Approval" description="Form yang perlu direview hari ini." icon="bi-hourglass-split">
            <x-user.history-table :rows="$pending" type="pending" />
        </x-user.action-card>
    </div>
    <div class="col-xl-6">
        <x-user.action-card title="Riwayat Approval Terbaru" description="Keputusan approval terakhir." icon="bi-clock-history">
            <div class="d-grid gap-3">
                @foreach ($history as $row)
                    <article class="inspector-mobile-card">
                        <small class="text-muted d-block mb-2">{{ $row['time'] }}</small>
                        <h3 class="mb-3">{{ $row['activity'] }}</h3>
                        <x-user.status-badge :status="$row['status']" />
                    </article>
                @endforeach
            </div>
        </x-user.action-card>
    </div>
    <div class="col-12">
        <x-user.action-card title="Dokumen Terkait" description="Contoh preview PDF dummy tersedia dari daftar dokumen berikut." icon="bi-file-earmark-pdf">
            <x-user.document-list :rows="$documents" />
        </x-user.action-card>
    </div>
</div>

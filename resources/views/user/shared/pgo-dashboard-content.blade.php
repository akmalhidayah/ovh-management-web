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
        <x-user.action-card title="Tugas PGO Hari Ini" description="Daftar tugas operasional yang perlu ditindaklanjuti tim PGO." icon="bi-list-check">
            <div class="d-grid gap-3">
                @foreach ($tasks as $task)
                    <x-user.task-card :task="['job' => $task['job'], 'plant' => $task['plant'], 'area' => $task['area'], 'date' => $task['date'], 'pic' => $task['pic'], 'status' => $task['status']]">
                        <a href="{{ route('user.pgo.tasks.index') }}" class="btn btn-primary w-100">Lihat Detail</a>
                    </x-user.task-card>
                @endforeach
            </div>
        </x-user.action-card>
    </div>
    <div class="col-xl-6">
        <x-user.action-card title="Monitoring Pekerjaan" description="Snapshot progres pekerjaan dari perspektif PGO." icon="bi-activity">
            <div class="table-responsive">
                <table class="table align-middle inspector-table">
                    <thead><tr><th>Pekerjaan</th><th>Plant</th><th>Area</th><th>Progress</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach ($monitoring as $row)
                            <tr>
                                <td>{{ $row['job'] }}</td>
                                <td>{{ $row['plant'] }}</td>
                                <td>{{ $row['area'] }}</td>
                                <td>{{ $row['progress'] }}</td>
                                <td><x-user.status-badge :status="$row['status']" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-user.action-card>
    </div>
    <div class="col-12">
        <x-user.action-card title="Riwayat Aktivitas PGO" description="Aktivitas dan tindak lanjut terbaru." icon="bi-clock-history">
            <div class="row g-3">
                @foreach ($history as $item)
                    <div class="col-lg-4">
                        <article class="inspector-mobile-card h-100">
                            <small class="text-muted d-block mb-2">{{ $item['time'] }}</small>
                            <h3 class="mb-3">{{ $item['activity'] }}</h3>
                            <x-user.status-badge :status="$item['status']" />
                        </article>
                    </div>
                @endforeach
            </div>
        </x-user.action-card>
    </div>
</div>

<section class="inspector-hero-card">
    <div class="row align-items-center g-4">
        <div class="col-xl-7">
            <span class="inspector-chip chip-soft">{{ $hero['note'] }}</span>
            <h2>{{ $hero['title'] }}</h2>
            <p>{{ $hero['subtitle'] }}</p>
            <div class="d-flex flex-column flex-sm-row gap-3 mt-4">
                @foreach ($hero['actions'] as $action)
                    <a href="{{ route($action['route']) }}" class="btn {{ $loop->first ? 'btn-primary' : 'btn-light' }}">
                        <i class="bi {{ $action['icon'] }} me-2"></i>{{ $action['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
        <div class="col-xl-5 text-center">
            <img src="{{ asset($roleUi['hero_asset']) }}" alt="{{ $hero['title'] }}" class="inspector-hero-illustration">
        </div>
    </div>
</section>

<section class="mt-4">
    <div class="row g-3 g-lg-4">
        @foreach ($stats as $stat)
            <div class="col-6 col-xl-4 col-xxl-2">
                <x-user.stat-card :label="$stat['label']" :value="$stat['value']" :icon="$stat['icon']" :accent="$stat['accent']" />
            </div>
        @endforeach
    </div>
</section>

@if (! in_array(($roleUi['role'] ?? null), ['qc', 'commissioning'], true))
    <section class="inspector-panel mt-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h2 class="inspector-section-title mb-2">{{ $tasksTitle }}</h2>
                <p class="text-muted mb-0">Tinjau pekerjaan yang terjadwal hari ini dan lanjutkan dari topbar action yang sesuai.</p>
            </div>
            <a href="{{ route($hero['actions'][0]['route']) }}" class="btn btn-outline-primary">Buka Halaman Utama</a>
        </div>

        <div class="table-responsive d-none d-lg-block">
            <table class="table align-middle inspector-table">
                <thead>
                    <tr>
                        <th>Equipment</th>
                        <th>Plant</th>
                        <th>Area</th>
                        <th>Jenis Form</th>
                        <th>Jadwal</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tasks as $task)
                        <tr>
                            <td>{{ $task['equipment'] }}</td>
                            <td>{{ $task['plant'] }}</td>
                            <td>{{ $task['area'] }}</td>
                            <td>{{ $task['type'] }}</td>
                            <td>{{ $task['schedule'] }}</td>
                            <td><x-user.status-badge :status="$task['status']" /></td>
                            <td class="text-end"><a href="{{ route($hero['actions'][0]['route']) }}" class="btn btn-sm btn-primary">Mulai</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-grid gap-3 d-lg-none">
            @foreach ($tasks as $task)
                <x-user.task-card :task="$task">
                    <a href="{{ route($hero['actions'][0]['route']) }}" class="btn btn-primary w-100">Mulai</a>
                </x-user.task-card>
            @endforeach
        </div>
    </section>
@endif

<div class="row g-4 mt-1">
    <div class="col-xl-5">
        <section class="inspector-panel h-100">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                <div>
                    <h2 class="inspector-section-title mb-2">{{ $draftsTitle }}</h2>
                    <p class="text-muted mb-0">Lanjutkan item yang belum selesai atau perlu diperbaiki.</p>
                </div>
                <a href="{{ route($hero['actions'][1]['route']) }}" class="btn btn-sm btn-outline-secondary">Lihat Semua</a>
            </div>
            <div class="d-grid gap-3">
                @foreach ($drafts as $draft)
                    <article class="inspector-draft-card">
                        <div class="d-flex justify-content-between gap-3 mb-3">
                            <div>
                                <span class="inspector-chip">{{ $draft['category'] }}</span>
                                <h3 class="mt-3">{{ $draft['title'] }}</h3>
                            </div>
                            <x-user.status-badge :status="$draft['status']" />
                        </div>
                        <dl class="inspector-detail-list">
                            <div><dt>Equipment</dt><dd>{{ $draft['equipment'] }}</dd></div>
                            <div><dt>Plant</dt><dd>{{ $draft['plant'] }}</dd></div>
                            <div><dt>Area</dt><dd>{{ $draft['area'] }}</dd></div>
                            <div><dt>Terakhir Diubah</dt><dd>{{ $draft['updated_at'] }}</dd></div>
                        </dl>
                        <a href="{{ route($hero['actions'][0]['route']) }}" class="btn btn-outline-primary w-100">Lanjutkan</a>
                    </article>
                @endforeach
            </div>
        </section>
    </div>

    <div class="col-xl-7">
        <section class="inspector-panel h-100">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                <div>
                    <h2 class="inspector-section-title mb-2">{{ $historyTitle }}</h2>
                    <p class="text-muted mb-0">Pantau form terakhir yang sudah dikirim dan status review terbarunya.</p>
                </div>
                <a href="{{ route($hero['actions'][2]['route']) }}" class="btn btn-sm btn-outline-secondary">Buka Riwayat</a>
            </div>

            <div class="table-responsive d-none d-lg-block">
                <table class="table align-middle inspector-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Kategori</th>
                            <th>Equipment</th>
                            <th>Plant</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($history as $row)
                            <tr>
                                <td>{{ $row['submitted_at'] }}</td>
                                <td>{{ $row['category'] }}</td>
                                <td>{{ $row['equipment'] }}</td>
                                <td>{{ $row['plant'] }}</td>
                                <td><x-user.status-badge :status="$row['status']" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-grid gap-3 d-lg-none">
                @foreach ($history as $row)
                    <article class="inspector-mobile-card">
                        <div class="d-flex justify-content-between gap-3 mb-2">
                            <div>
                                <h3>{{ $row['equipment'] }}</h3>
                                <p class="mb-1">{{ $row['plant'] }}</p>
                                <small>{{ $row['submitted_at'] }}</small>
                            </div>
                            <x-user.status-badge :status="$row['status']" />
                        </div>
                        <div class="inspector-meta-list">
                            <span><i class="bi bi-ui-checks-grid"></i>{{ $row['category'] }}</span>
                            <span><i class="bi bi-geo-alt"></i>{{ $row['area'] }}</span>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    </div>
</div>

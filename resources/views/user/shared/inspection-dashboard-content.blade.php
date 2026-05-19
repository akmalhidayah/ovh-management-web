@php
    $primaryAction = $hero['actions'][0] ?? null;
    $draftAction = $hero['actions'][1] ?? $primaryAction;
    $historyAction = $hero['actions'][2] ?? $primaryAction;
    $roleLabel = $roleUi['role_label'] ?? strtoupper($roleUi['role'] ?? 'QC');
@endphp

<div class="inspection-workspace">
    <section class="inspection-command-bar">
        <div class="inspection-command-copy">
            <span class="inspection-overline">{{ $hero['note'] }}</span>
            <h1>{{ $hero['title'] }}</h1>
            <p>{{ $hero['subtitle'] }}</p>
        </div>

        <div class="inspection-action-strip">
            @foreach ($hero['actions'] as $action)
                <a href="{{ route($action['route']) }}" class="btn {{ $loop->first ? 'btn-primary' : 'btn-outline-secondary' }}">
                    <i class="bi {{ $action['icon'] }}"></i>
                    <span>{{ $action['label'] }}</span>
                </a>
            @endforeach
        </div>
    </section>

    <section class="inspection-kpi-grid" aria-label="Ringkasan {{ $roleLabel }}">
        @foreach ($stats as $stat)
            <article class="inspection-kpi-card accent-{{ $stat['accent'] }}">
                <span class="inspection-kpi-icon"><i class="bi {{ $stat['icon'] }}"></i></span>
                <div>
                    <p>{{ $stat['label'] }}</p>
                    <strong>{{ $stat['value'] }}</strong>
                </div>
            </article>
        @endforeach
    </section>

    <div class="inspection-bottom-grid">
        <section class="inspection-panel">
            <div class="inspection-panel-head">
                <div>
                    <span class="inspection-overline">Draft</span>
                    <h2>{{ $draftsTitle }}</h2>
                </div>
                @if ($draftAction)
                    <a href="{{ route($draftAction['route']) }}" class="btn btn-sm btn-light">
                        <i class="bi bi-journal-text"></i>
                        <span>Lihat Semua</span>
                    </a>
                @endif
            </div>

            <div class="inspection-list-stack">
                @foreach ($drafts as $draft)
                    <article class="inspection-list-row">
                        <div>
                            <span class="inspection-row-kicker">{{ $draft['category'] }} / {{ $draft['updated_at'] }}</span>
                            <h3>{{ $draft['title'] }}</h3>
                            <p>{{ $draft['plant'] }} / {{ $draft['area'] }}</p>
                        </div>
                        <x-user.status-badge :status="$draft['status']" />
                    </article>
                @endforeach
            </div>
        </section>

        <section class="inspection-panel">
            <div class="inspection-panel-head">
                <div>
                    <span class="inspection-overline">Riwayat</span>
                    <h2>{{ $historyTitle }}</h2>
                </div>
                @if ($historyAction)
                    <a href="{{ route($historyAction['route']) }}" class="btn btn-sm btn-light">
                        <i class="bi bi-clock-history"></i>
                        <span>Buka Riwayat</span>
                    </a>
                @endif
            </div>

            <div class="inspection-list-stack">
                @foreach ($history as $row)
                    <article class="inspection-list-row">
                        <div>
                            <span class="inspection-row-kicker">{{ $row['submitted_at'] }} / {{ $row['category'] }}</span>
                            <h3>{{ $row['equipment'] }}</h3>
                            <p>{{ $row['type'] }} / {{ $row['plant'] }}</p>
                        </div>
                        <x-user.status-badge :status="$row['status']" />
                    </article>
                @endforeach
            </div>
        </section>
    </div>
</div>

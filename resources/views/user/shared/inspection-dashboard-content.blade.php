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

    @if (($equipmentRows ?? null) !== null)
        <section class="inspection-panel commissioning-equipment-panel">
            <div class="inspection-panel-head">
                <div>
                    <span class="inspection-overline">Equipment</span>
                    <h2>Daftar Equipment Commissioning</h2>
                </div>
                @if ($primaryAction)
                    <a href="{{ route($primaryAction['route']) }}" class="btn btn-sm btn-light">
                        <i class="bi bi-plus-circle"></i>
                        <span>Buat Manual</span>
                    </a>
                @endif
            </div>

            <div class="table-responsive">
                <table class="table align-middle commissioning-equipment-table">
                    <thead>
                        <tr>
                            <th>Section</th>
                            <th>Equipment</th>
                            <th>Lokasi</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($equipmentRows as $row)
                            <tr>
                                <td>
                                    <strong>{{ $row['section_no'] }}</strong>
                                    <small>{{ $row['functional_location'] }}</small>
                                </td>
                                <td>
                                    <strong>{{ $row['equipment'] }}</strong>
                                    <small>{{ $row['equipment_no'] }}</small>
                                </td>
                                <td>
                                    <strong>{{ $row['plant'] }}</strong>
                                    <small>{{ $row['area'] }}</small>
                                </td>
                                <td>
                                    <span class="commissioning-work-badge is-{{ $row['status_accent'] }}">{{ $row['status_label'] }}</span>
                                    @if (! empty($row['form_number']))
                                        <small class="d-block text-muted mt-1">{{ $row['form_number'] }}</small>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if ($row['create_url'])
                                        <a href="{{ $row['create_url'] }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-plus-circle"></i>
                                            <span>Buat Commissioning</span>
                                        </a>
                                    @else
                                        <span class="text-muted small">Readonly</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">Belum ada equipment commissioning aktif dari master data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @else
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
    @endif
</div>

@push('styles')
<style>
    .commissioning-equipment-panel {
        overflow: hidden;
    }

    .commissioning-equipment-table {
        margin-bottom: 0;
    }

    .commissioning-equipment-table th {
        border-top: 0;
        color: #475467;
        font-size: .76rem;
        letter-spacing: .04em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .commissioning-equipment-table td {
        border-color: #eef2f7;
        vertical-align: middle;
    }

    .commissioning-equipment-table strong,
    .commissioning-equipment-table small {
        display: block;
    }

    .commissioning-equipment-table strong {
        color: #172033;
        font-size: .92rem;
    }

    .commissioning-equipment-table small {
        color: #667085;
        line-height: 1.4;
    }

    .commissioning-work-badge {
        border-radius: 999px;
        display: inline-flex;
        font-size: .76rem;
        font-weight: 700;
        padding: .35rem .65rem;
    }

    .commissioning-work-badge.is-success {
        background: #dcfce7;
        color: #166534;
    }

    .commissioning-work-badge.is-warning {
        background: #fef3c7;
        color: #92400e;
    }

    .commissioning-work-badge.is-secondary {
        background: #eef2f7;
        color: #475467;
    }

    @media (max-width: 767.98px) {
        .commissioning-equipment-table th,
        .commissioning-equipment-table td {
            white-space: nowrap;
        }
    }
</style>
@endpush

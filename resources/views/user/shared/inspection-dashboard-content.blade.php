@php
    $primaryAction = $hero['actions'][0] ?? null;
    $draftAction = $hero['actions'][1] ?? $primaryAction;
    $historyAction = $hero['actions'][2] ?? $primaryAction;
    $roleLabel = $roleUi['role_label'] ?? strtoupper($roleUi['role'] ?? 'QC');
    $equipmentFilters = $equipmentFilters ?? [];
    $equipmentPlants = collect($equipmentFilters['plants'] ?? []);
    $equipmentAreas = collect($equipmentFilters['areas'] ?? []);
    $equipmentStatuses = collect($equipmentFilters['statuses'] ?? []);
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
                    @if (! empty($stat['meta']))
                        <small class="inspection-kpi-meta">{{ $stat['meta'] }}</small>
                    @endif
                </div>
            </article>
        @endforeach
    </section>

    @if (session('warning'))
        <div class="alert alert-warning mb-0">{{ session('warning') }}</div>
    @endif

    @if (($equipmentRows ?? null) !== null)
        <section class="inspection-panel commissioning-equipment-panel">
            <div class="inspection-panel-head">
                <div>
                    <span class="inspection-overline">Equipment</span>
                    <h2>Daftar Equipment {{ $roleLabel }}</h2>
                </div>
            </div>

            <form method="GET" action="{{ url()->current() }}" class="commissioning-equipment-filters">
                <label>
                    <span>Plant</span>
                    <select name="equipment_plant" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Plant</option>
                        @foreach ($equipmentPlants as $plant)
                            <option value="{{ $plant }}" @selected(($equipmentFilters['plant'] ?? '') === $plant)>{{ $plant }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span>Area</span>
                    <select name="equipment_area" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Area</option>
                        @foreach ($equipmentAreas as $area)
                            <option value="{{ $area }}" @selected(($equipmentFilters['area'] ?? '') === $area)>{{ $area }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span>Status</span>
                    <select name="equipment_status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        @foreach ($equipmentStatuses as $status)
                            <option value="{{ $status['value'] }}" @selected(($equipmentFilters['status'] ?? '') === $status['value'])>{{ $status['label'] }}</option>
                        @endforeach
                    </select>
                </label>
                @foreach (request()->except(['equipment_plant', 'equipment_area', 'equipment_status', 'equipment_page']) as $name => $value)
                    @if (is_scalar($value))
                        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                    @endif
                @endforeach
            </form>

            <div class="table-responsive">
                <table class="table align-middle commissioning-equipment-table">
                    <thead>
                        <tr>
                            <th>Section</th>
                            <th>Equipment</th>
                            <th>Lokasi</th>
                            <th class="text-end">Status & Aksi</th>
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
                                <td class="text-end">
                                    <div class="commissioning-status-action">
                                        @if (empty($row['create_url']))
                                            <span class="commissioning-work-badge is-{{ $row['status_accent'] }}">{{ $row['status_label'] }}</span>
                                        @endif
                                        @if (! empty($row['form_number']))
                                            <small class="d-block text-muted mt-1">{{ $row['form_number'] }}</small>
                                        @endif
                                    </div>
                                    @if ($row['create_url'])
                                        <a href="{{ $row['create_url'] }}" class="btn btn-sm btn-primary mt-2">
                                            <i class="bi bi-plus-circle"></i>
                                            <span>Buat {{ $roleLabel }}</span>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-5">Belum ada equipment {{ $roleLabel }} yang cocok dengan filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($equipmentRows->hasPages())
                <div class="commissioning-equipment-pagination">
                    {{ $equipmentRows->onEachSide(1)->links() }}
                </div>
            @endif
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
    .inspection-workspace .inspection-kpi-grid {
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: .62rem;
    }

    .inspection-workspace .inspection-kpi-card {
        --inspection-kpi-accent: #0d6efd;
        min-height: 66px;
        gap: .58rem;
        padding: .55rem .68rem;
        border: 1px solid #e2e8f0;
        border-left: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 .4rem 1rem rgba(15, 23, 42, .045);
    }

    .inspection-workspace .inspection-kpi-card.accent-primary {
        --inspection-kpi-accent: #0d6efd;
    }

    .inspection-workspace .inspection-kpi-card.accent-info {
        --inspection-kpi-accent: #0dcaf0;
    }

    .inspection-workspace .inspection-kpi-card.accent-success {
        --inspection-kpi-accent: #198754;
    }

    .inspection-workspace .inspection-kpi-card.accent-warning {
        --inspection-kpi-accent: #f59e0b;
    }

    .inspection-workspace .inspection-kpi-card.accent-danger {
        --inspection-kpi-accent: #dc3545;
    }

    .inspection-workspace .inspection-kpi-card.accent-secondary {
        --inspection-kpi-accent: #475467;
    }

    .inspection-workspace .inspection-kpi-icon {
        width: 34px;
        height: 34px;
        flex-basis: 34px;
        border-radius: 8px;
        color: #ffffff;
        background: var(--inspection-kpi-accent);
        font-size: .9rem;
        box-shadow: none;
    }

    .inspection-workspace .inspection-kpi-card p {
        max-width: 9rem;
        color: #64748b;
        font-size: .68rem;
        font-weight: 650;
        line-height: 1.15;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .inspection-workspace .inspection-kpi-card strong {
        margin-top: .08rem;
        color: #0f172a;
        font-size: 1.12rem;
        font-weight: 850;
        line-height: 1.05;
    }

    .commissioning-equipment-panel {
        overflow: hidden;
    }

    .commissioning-equipment-table {
        margin-bottom: 0;
    }

    .commissioning-equipment-filters {
        align-items: end;
        border-top: 1px solid #eef2f7;
        display: grid;
        gap: .85rem;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        margin: 1rem -1.25rem 0;
        padding: 1rem 1.25rem;
    }

    .commissioning-equipment-filters label {
        color: #475467;
        display: grid;
        font-size: .78rem;
        font-weight: 700;
        gap: .35rem;
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

    .commissioning-status-action {
        min-width: 150px;
    }

    .commissioning-equipment-pagination {
        border-top: 1px solid #eef2f7;
        padding: 1rem 0 0;
    }

    .commissioning-equipment-pagination nav {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
        justify-content: space-between;
    }

    .commissioning-equipment-pagination .pagination {
        margin-bottom: 0;
    }

    .inspection-kpi-meta {
        color: #667085;
        display: block;
        font-size: .6rem;
        font-weight: 700;
        line-height: 1.2;
        margin-top: .18rem;
    }

    @media (max-width: 1199.98px) {
        .inspection-workspace .inspection-kpi-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .inspection-workspace .inspection-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .commissioning-equipment-filters {
            grid-template-columns: 1fr;
        }

        .commissioning-equipment-table th,
        .commissioning-equipment-table td {
            white-space: nowrap;
        }
    }

    @media (max-width: 575.98px) {
        .inspection-workspace .inspection-kpi-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

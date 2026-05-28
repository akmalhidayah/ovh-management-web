@php
    $primaryAction = $hero['actions'][0] ?? null;
    $draftAction = $hero['actions'][1] ?? $primaryAction;
    $historyAction = $hero['actions'][2] ?? $primaryAction;
    $roleLabel = $roleUi['role_label'] ?? strtoupper($roleUi['role'] ?? 'QC');
    $equipmentPlants = collect($equipmentRows ?? [])->pluck('plant')->filter(fn ($value) => $value !== '-')->unique()->sort()->values();
    $equipmentAreas = collect($equipmentRows ?? [])->pluck('area')->filter(fn ($value) => $value !== '-')->unique()->sort()->values();
    $equipmentStatuses = collect($equipmentRows ?? [])
        ->map(fn ($row) => ['value' => $row['status'], 'label' => $row['status_label']])
        ->unique('value')
        ->sortBy('label')
        ->values();
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
                    <h2>Daftar Equipment {{ $roleLabel }}</h2>
                </div>
            </div>

            <div class="commissioning-equipment-filters" data-equipment-filters>
                <label>
                    <span>Plant</span>
                    <select class="form-select form-select-sm" data-equipment-filter="plant">
                        <option value="">Semua Plant</option>
                        @foreach ($equipmentPlants as $plant)
                            <option value="{{ $plant }}">{{ $plant }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span>Area</span>
                    <select class="form-select form-select-sm" data-equipment-filter="area">
                        <option value="">Semua Area</option>
                        @foreach ($equipmentAreas as $area)
                            <option value="{{ $area }}">{{ $area }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span>Status</span>
                    <select class="form-select form-select-sm" data-equipment-filter="status">
                        <option value="">Semua Status</option>
                        @foreach ($equipmentStatuses as $status)
                            <option value="{{ $status['value'] }}">{{ $status['label'] }}</option>
                        @endforeach
                    </select>
                </label>
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
                            <tr data-equipment-row data-plant="{{ $row['plant'] }}" data-area="{{ $row['area'] }}" data-status="{{ $row['status'] }}">
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
                                            <span>Buat {{ $roleLabel }}</span>
                                        </a>
                                    @else
                                        <span class="text-muted small">Readonly</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">Belum ada equipment {{ $roleLabel }} aktif dari master data.</td>
                            </tr>
                        @endforelse
                        @if (! empty($equipmentRows))
                            <tr data-equipment-empty-filter hidden>
                                <td colspan="5" class="text-center text-muted py-5">Tidak ada equipment yang cocok dengan filter.</td>
                            </tr>
                        @endif
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

    @media (max-width: 767.98px) {
        .commissioning-equipment-filters {
            grid-template-columns: 1fr;
        }

        .commissioning-equipment-table th,
        .commissioning-equipment-table td {
            white-space: nowrap;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    (() => {
        document.querySelectorAll('[data-equipment-filters]').forEach((filterGroup) => {
            const panel = filterGroup.closest('.commissioning-equipment-panel');
            const rows = Array.from(panel?.querySelectorAll('[data-equipment-row]') || []);
            const emptyRow = panel?.querySelector('[data-equipment-empty-filter]');
            const controls = Array.from(filterGroup.querySelectorAll('[data-equipment-filter]'));

            const applyFilters = () => {
                const filters = Object.fromEntries(controls.map((control) => [control.dataset.equipmentFilter, control.value]));
                let visibleCount = 0;

                rows.forEach((row) => {
                    const visible = (!filters.plant || row.dataset.plant === filters.plant)
                        && (!filters.area || row.dataset.area === filters.area)
                        && (!filters.status || row.dataset.status === filters.status);

                    row.hidden = !visible;
                    if (visible) {
                        visibleCount += 1;
                    }
                });

                if (emptyRow) {
                    emptyRow.hidden = visibleCount > 0;
                }
            };

            controls.forEach((control) => control.addEventListener('change', applyFilters));
            applyFilters();
        });
    })();
</script>
@endpush

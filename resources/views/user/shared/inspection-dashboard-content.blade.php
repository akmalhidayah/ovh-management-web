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

            <div class="table-responsive commissioning-equipment-table-wrap">
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
                                <td data-label="Section">
                                    <strong>{{ $row['section_no'] }}</strong>
                                    <small>{{ $row['functional_location'] }}</small>
                                </td>
                                <td data-label="Equipment">
                                    <strong>{{ $row['equipment'] }}</strong>
                                    <small>{{ $row['equipment_no'] }}</small>
                                </td>
                                <td data-label="Lokasi">
                                    <strong>{{ $row['plant'] }}</strong>
                                    <small>{{ $row['area'] }}</small>
                                </td>
                                <td class="text-end" data-label="Status & Aksi">
                                    <div class="commissioning-status-action">
                                        @if (empty($row['create_url']))
                                            <span class="commissioning-work-badge is-{{ $row['status_accent'] }}">{{ $row['status_label'] }}</span>
                                        @endif
                                        @if (! empty($row['form_type']))
                                            <small class="d-block text-muted mt-1">{{ $row['form_type'] }}</small>
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
    <link href="{{ asset('assets/css/inspection-dashboard.css') }}" rel="stylesheet">
@endpush

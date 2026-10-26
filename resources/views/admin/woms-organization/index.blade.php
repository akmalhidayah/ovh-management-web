@extends('layouts.dashboard')

@section('title', 'Struktur Organisasi')
@section('page_title', 'Struktur Organisasi')
@section('breadcrumb_parent', 'Master Data')

@section('content')
    @foreach (['success' => 'success', 'warning' => 'warning', 'error' => 'danger'] as $flashKey => $alertTone)
        @if (session($flashKey))
            <div class="alert alert-{{ $alertTone }} alert-dismissible fade show" role="alert">
                {{ session($flashKey) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        @endif
    @endforeach

    <div class="page-header woms-page-header">
        <div>
            <h1>Struktur Organisasi</h1>
        </div>
        @if ($canSync)
            <div class="page-actions">
                @if ($latestRun?->status === \App\Models\WomsOrganizationSyncRun::STATUS_NEEDS_REVIEW && $latestRun?->error_category === 'large_reduction')
                    <form method="POST" action="{{ route('admin.woms-organization.sync') }}" onsubmit="return confirm('Perubahan besar akan diterapkan setelah snapshot WOMS divalidasi ulang. Lanjutkan?')">
                        @csrf
                        <input type="hidden" name="confirm_large_reduction" value="1">
                        <input type="hidden" name="expected_checksum" value="{{ $latestRun->source_checksum }}">
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="bi bi-shield-exclamation me-1"></i>Konfirmasi Perubahan Besar
                        </button>
                    </form>
                @endif
                <form method="POST" action="{{ route('admin.woms-organization.sync') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-arrow-repeat me-1"></i>Sinkronkan Sekarang
                    </button>
                </form>
            </div>
        @endif
    </div>

    <div class="content-card woms-status-card is-{{ $connectionStatus['tone'] }}">
        <div class="woms-status-icon"><i class="bi bi-cloud-check"></i></div>
        <div class="woms-status-copy">
            <span>Status Integrasi</span>
            <h2>{{ $connectionStatus['label'] }}</h2>
            <p>{{ $connectionStatus['description'] }}</p>
        </div>
        <div class="woms-status-meta">
            <span>Terakhir berhasil</span>
            <strong>{{ $lastSuccessfulRun?->finished_at?->format('d M Y H:i') ?? 'Belum ada' }}</strong>
            <span>Checksum</span>
            <code title="{{ $lastSuccessfulRun?->source_checksum }}">{{ $lastSuccessfulRun?->source_checksum ? substr($lastSuccessfulRun->source_checksum, 0, 16).'…' : '-' }}</code>
        </div>
    </div>

    <div class="woms-summary-grid">
        @foreach ([
            ['Departemen', $summary['departments'], 'bi-building', 'blue'],
            ['Unit Kerja', $summary['units'], 'bi-diagram-3', 'green'],
            ['Seksi', $summary['sections'], 'bi-people', 'cyan'],
        ] as [$label, $value, $icon, $tone])
            <div class="content-card woms-summary-card is-{{ $tone }}">
                <i class="bi {{ $icon }}"></i>
                <div><span>{{ $label }}</span><strong>{{ number_format($value) }}</strong></div>
            </div>
        @endforeach
    </div>

    <div class="content-card woms-filter-card">
        <form method="GET" action="{{ route('admin.woms-organization.index') }}" class="woms-filter-grid">
            <div>
                <label class="form-label" for="womsDepartmentFilter">Departemen</label>
                <select class="form-select" id="womsDepartmentFilter" name="department_id">
                    <option value="">Semua Departemen</option>
                    @foreach ($departmentOptions as $department)
                        <option value="{{ $department->id }}" @selected($filters['department_id'] === $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" for="womsUnitFilter">Unit Kerja</label>
                <select class="form-select" id="womsUnitFilter" name="unit_id">
                    <option value="">Semua Unit Kerja</option>
                    @foreach ($unitOptions as $unit)
                        <option value="{{ $unit->id }}" @selected($filters['unit_id'] === $unit->id)>
                            {{ $unit->name }}{{ $unit->department ? ' — '.$unit->department->name : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" for="womsSectionFilter">Seksi</label>
                <select class="form-select" id="womsSectionFilter" name="section_id">
                    <option value="">Semua Seksi</option>
                    @foreach ($sectionOptions as $section)
                        <option value="{{ $section->id }}" @selected($filters['section_id'] === $section->id)>
                            {{ $section->name }}{{ $section->unit ? ' — '.$section->unit->name : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" for="womsPersonnelFilter">Nama/Email Pejabat</label>
                <input class="form-control" id="womsPersonnelFilter" name="personnel" value="{{ $filters['personnel'] }}" placeholder="Cari pejabat...">
            </div>
            <div class="woms-filter-actions">
                <button class="btn btn-primary" type="submit"><i class="bi bi-funnel me-1"></i>Terapkan</button>
                <a class="btn btn-outline-secondary" href="{{ route('admin.woms-organization.index') }}">Reset</a>
            </div>
        </form>
    </div>

    <div class="woms-section-heading">
        <h2>Hierarki Organisasi</h2>
    </div>

    <div class="woms-hierarchy">
        @forelse ($departments as $department)
            <article class="content-card woms-department-card">
                <header class="woms-department-header">
                    <div>
                        <div class="woms-entity-kicker">Departemen · WOMS ID {{ $department->woms_id }}</div>
                        <h3>{{ $department->name }}</h3>
                    </div>
                    <div class="woms-department-meta">
                        <div>
                            <span>General Manager</span>
                            <strong>{{ $department->generalManager?->name ?? 'Belum ditetapkan' }}</strong>
                            @if ($department->generalManager?->email)
                                <small>{{ $department->generalManager->email }}</small>
                            @endif
                        </div>
                    </div>
                </header>

                <div class="woms-unit-list">
                    @forelse ($department->units as $unit)
                        <section class="woms-unit-card">
                            <div class="woms-unit-header">
                                <div>
                                    <div class="woms-entity-kicker">Unit Kerja · WOMS ID {{ $unit->woms_id }}</div>
                                    <h4>{{ $unit->name }}</h4>
                                </div>
                                <div class="woms-officer">
                                    <span>Senior Manager</span>
                                    <strong>{{ $unit->seniorManager?->name ?? 'Belum ditetapkan' }}</strong>
                                    @if ($unit->seniorManager?->email)<small>{{ $unit->seniorManager->email }}</small>@endif
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table align-middle woms-section-table">
                                    <thead><tr><th>Seksi</th><th>Manager</th><th>Email</th></tr></thead>
                                    <tbody>
                                        @forelse ($unit->sections as $section)
                                            <tr>
                                                <td><strong>{{ $section->name }}</strong><small>WOMS ID {{ $section->woms_id }}</small></td>
                                                <td>{{ $section->manager?->name ?? 'Belum ditetapkan' }}</td>
                                                <td>{{ $section->manager?->email ?? '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-center text-muted py-3">Unit kerja ini tidak mempunyai seksi.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    @empty
                        <div class="woms-empty-unit">Departemen ini tidak mempunyai unit kerja.</div>
                    @endforelse
                </div>
            </article>
        @empty
            <div class="content-card woms-empty-state">
                <i class="bi bi-diagram-3"></i>
                <h3>Belum ada data organisasi</h3>
                <p>Jalankan sinkronisasi setelah konfigurasi API WOMS tersedia, atau ubah filter pencarian.</p>
            </div>
        @endforelse
    </div>

    @if ($departments->hasPages())
        <div class="mt-3">{{ $departments->links() }}</div>
    @endif

    <div class="content-card woms-history-card">
        <div class="card-heading">
            <div><h2>Riwayat Sinkronisasi</h2><div class="text-muted small">Sepuluh proses terbaru tanpa menyimpan token atau raw JSON.</div></div>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0 woms-history-table">
                <thead><tr><th>Mulai</th><th>Selesai</th><th>Status</th><th>Jumlah Data</th><th>Perubahan</th><th>Pemicu</th><th>Keterangan</th></tr></thead>
                <tbody>
                    @forelse ($recentRuns as $run)
                        @php
                            $runTone = match ($run->status) {
                                \App\Models\WomsOrganizationSyncRun::STATUS_SUCCESS, \App\Models\WomsOrganizationSyncRun::STATUS_NO_CHANGES => 'success',
                                \App\Models\WomsOrganizationSyncRun::STATUS_RUNNING => 'info',
                                \App\Models\WomsOrganizationSyncRun::STATUS_NEEDS_REVIEW => 'warning',
                                default => 'danger',
                            };
                        @endphp
                        <tr>
                            <td>{{ $run->started_at?->format('d M Y H:i:s') }}</td>
                            <td>{{ $run->finished_at?->format('d M Y H:i:s') ?? '-' }}</td>
                            <td><span class="badge text-bg-{{ $runTone }}">{{ $run->statusLabel() }}</span></td>
                            <td>D {{ $run->department_count }} · U {{ $run->unit_count }} · S {{ $run->section_count }}</td>
                            <td>+{{ $run->created_count }} / ~{{ $run->updated_count }} / -{{ $run->marked_missing_count }}</td>
                            <td>{{ ucfirst($run->triggered_by_type) }}{{ $run->triggeredBy ? ' · '.$run->triggeredBy->name : '' }}</td>
                            <td>
                                @if ($run->error_category)
                                    <code class="d-block mb-1">{{ $run->error_category }}</code>
                                @endif
                                {{ $run->sanitized_error_message ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Belum ada riwayat sinkronisasi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .woms-page-header p,.woms-section-heading p{margin:.25rem 0 0;color:#64748b;font-size:.84rem}.woms-page-header .page-actions{display:flex;flex-wrap:wrap;gap:.55rem}.woms-status-card{display:flex;align-items:center;gap:1rem;border-left:4px solid #64748b}.woms-status-card.is-success{border-left-color:#16a34a}.woms-status-card.is-warning{border-left-color:#f59e0b}.woms-status-card.is-danger{border-left-color:#dc2626}.woms-status-card.is-info{border-left-color:#0ea5e9}.woms-status-icon{width:52px;height:52px;display:grid;place-items:center;flex:0 0 auto;border-radius:.75rem;background:#eff6ff;color:#2563eb;font-size:1.4rem}.woms-status-copy{min-width:0;flex:1}.woms-status-copy>span,.woms-status-meta span{display:block;color:#64748b;font-size:.72rem;font-weight:700;text-transform:uppercase}.woms-status-copy h2{margin:.1rem 0;color:#172033;font-size:1.15rem;font-weight:800}.woms-status-copy p{margin:0;color:#64748b;font-size:.82rem}.woms-status-meta{display:grid;grid-template-columns:auto auto;gap:.2rem .7rem;text-align:right}.woms-status-meta code{max-width:170px;color:#334155;overflow:hidden;text-overflow:ellipsis}.woms-summary-grid{display:grid;grid-template-columns:repeat(3,minmax(130px,1fr));gap:.75rem;margin:1rem 0}.woms-summary-card{display:flex;align-items:center;gap:.7rem;margin:0;padding:.85rem}.woms-summary-card>i{width:38px;height:38px;display:grid;place-items:center;flex:0 0 auto;border-radius:.55rem;background:#eaf2ff;color:#2563eb}.woms-summary-card.is-green>i{background:#eaf8ef;color:#168148}.woms-summary-card.is-cyan>i{background:#e8f8fc;color:#0891b2}.woms-summary-card.is-violet>i{background:#f2edff;color:#7c3aed}.woms-summary-card.is-orange>i{background:#fff5e5;color:#d97706}.woms-summary-card.is-slate>i{background:#eef2f7;color:#475569}.woms-summary-card span{display:block;color:#64748b;font-size:.7rem;font-weight:700}.woms-summary-card strong{display:block;color:#172033;font-size:1.25rem;line-height:1.15}.woms-filter-card{margin-bottom:1rem}.woms-filter-grid{display:grid;grid-template-columns:repeat(4,minmax(150px,1fr)) auto;gap:.7rem;align-items:end}.woms-filter-grid .form-label{font-size:.75rem;font-weight:700;color:#475569}.woms-filter-grid .form-control,.woms-filter-grid .form-select{font-size:.8rem}.woms-filter-actions{display:flex;gap:.4rem}.woms-section-heading{display:flex;align-items:end;justify-content:space-between;margin:1.2rem 0 .7rem}.woms-section-heading h2{margin:0;color:#172033;font-size:1.05rem;font-weight:800}.woms-section-heading>span{color:#64748b;font-size:.78rem}.woms-hierarchy{display:grid;gap:1rem}.woms-department-card{padding:0;overflow:hidden}.woms-department-header{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1rem;background:#eef4fb;border-bottom:1px solid #d9e3ef}.woms-entity-kicker{color:#64748b;font-size:.67rem;font-weight:800;text-transform:uppercase;letter-spacing:.04em}.woms-department-header h3,.woms-unit-header h4{margin:.15rem 0 0;color:#172033;font-weight:800}.woms-department-header h3{font-size:1.08rem}.woms-unit-header h4{font-size:.93rem}.woms-department-meta{display:flex;align-items:center;gap:1rem;text-align:right}.woms-department-meta>div span,.woms-officer span{display:block;color:#64748b;font-size:.68rem;font-weight:700;text-transform:uppercase}.woms-department-meta strong,.woms-officer strong{display:block;color:#334155;font-size:.8rem}.woms-department-meta small,.woms-officer small{display:block;color:#64748b;font-size:.7rem}.woms-unit-list{display:grid;gap:.8rem;padding:.8rem}.woms-unit-card{overflow:hidden;border:1px solid #dbe3ee;border-radius:.65rem}.woms-unit-header{display:grid;grid-template-columns:minmax(180px,1fr) minmax(180px,.7fr);align-items:center;gap:.8rem;padding:.75rem .85rem;background:#fff}.woms-officer{text-align:right}.woms-section-table{min-width:720px;margin:0;border-top:1px solid #e2e8f0}.woms-section-table thead th,.woms-history-table thead th{background:#f8fafc;color:#526076;font-size:.7rem;text-transform:uppercase;white-space:nowrap}.woms-section-table td,.woms-history-table td{color:#475569;font-size:.75rem}.woms-section-table td:first-child strong,.woms-section-table td:first-child small{display:block}.woms-section-table td:first-child small{margin-top:.12rem;color:#94a3b8}.woms-empty-unit,.woms-empty-state{padding:1.5rem;text-align:center;color:#64748b}.woms-empty-state i{font-size:2rem}.woms-empty-state h3{margin:.5rem 0 .2rem;color:#334155;font-size:1rem}.woms-empty-state p{margin:0;font-size:.82rem}.woms-history-card{margin-top:1rem}.woms-history-table{min-width:1050px}.woms-history-table td:last-child{max-width:300px;white-space:normal}.woms-history-card .card-heading{margin-bottom:.7rem}@media(max-width:1399.98px){.woms-summary-grid{grid-template-columns:repeat(3,1fr)}.woms-filter-grid{grid-template-columns:repeat(3,1fr)}}@media(max-width:767.98px){.woms-page-header,.woms-department-header,.woms-section-heading{align-items:stretch;flex-direction:column}.woms-page-header .page-actions,.woms-page-header .page-actions form,.woms-page-header .btn{width:100%}.woms-status-card{align-items:flex-start;flex-wrap:wrap}.woms-status-meta{width:100%;padding-top:.7rem;border-top:1px solid #e2e8f0;text-align:left}.woms-summary-grid,.woms-filter-grid{grid-template-columns:1fr 1fr}.woms-filter-actions{grid-column:1/-1}.woms-unit-header{grid-template-columns:1fr}.woms-officer{text-align:left}.woms-department-meta{align-items:flex-start;flex-direction:column;text-align:left}}@media(max-width:479.98px){.woms-summary-grid,.woms-filter-grid{grid-template-columns:1fr}.woms-filter-actions{grid-column:auto}.woms-filter-actions .btn{flex:1}}
    </style>
@endpush

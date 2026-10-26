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
        <div class="page-actions">
            <button type="button" class="btn btn-outline-primary woms-chart-button" data-bs-toggle="modal" data-bs-target="#womsOrganizationChartModal" aria-label="Lihat bagan struktur organisasi" title="Lihat bagan struktur organisasi">
                <i class="bi bi-diagram-3"></i>
            </button>
            <button type="button" class="btn btn-outline-{{ $connectionStatus['tone'] === 'success' ? 'success' : 'danger' }} woms-info-button" data-bs-toggle="modal" data-bs-target="#womsSyncInfoModal" aria-label="Informasi sinkronisasi" title="Informasi sinkronisasi">
                <i class="bi bi-info-circle"></i>
            </button>
            @if ($canSync)
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
            @endif
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

    <div class="woms-hierarchy">
        @forelse ($departments as $department)
            <article class="content-card woms-department-card">
                <header class="woms-department-header">
                    <div>
                        <div class="woms-entity-kicker">Departemen</div>
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
                                    <div class="woms-entity-kicker">Unit Kerja</div>
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
                                                <td><strong>{{ $section->name }}</strong></td>
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

    <div class="modal fade" id="womsOrganizationChartModal" data-chart-url="{{ route('admin.woms-organization.chart') }}" tabindex="-1" aria-labelledby="womsOrganizationChartModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered woms-chart-dialog">
            <div class="modal-content woms-chart-modal-content">
                <div class="modal-header woms-chart-modal-header">
                    <div class="d-flex align-items-center gap-3">
                        <span class="woms-chart-title-icon"><i class="bi bi-diagram-3"></i></span>
                        <div>
                            <h2 class="modal-title" id="womsOrganizationChartModalLabel">Bagan Struktur Organisasi</h2>
                            <p>Departemen, Unit Kerja, dan Seksi</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body woms-chart-modal-body">
                    <div class="woms-chart-toolbar">
                        <div class="woms-chart-levels" role="group" aria-label="Kedalaman bagan">
                            <span>Tampilan</span>
                            <button type="button" class="is-active" data-woms-chart-mode="all">Lengkap</button>
                            <button type="button" data-woms-chart-mode="units">Sampai Unit Kerja</button>
                            <button type="button" data-woms-chart-mode="departments">Departemen Saja</button>
                        </div>
                        <div class="woms-chart-controls">
                            <span>Zoom</span>
                            <button type="button" data-woms-chart-zoom-out aria-label="Perkecil bagan"><i class="bi bi-zoom-out"></i></button>
                            <strong data-woms-chart-zoom-label>50%</strong>
                            <button type="button" data-woms-chart-zoom-in aria-label="Perbesar bagan"><i class="bi bi-zoom-in"></i></button>
                            <button type="button" class="woms-chart-reset" data-woms-chart-reset><i class="bi bi-arrow-counterclockwise"></i> Reset</button>
                            <button type="button" data-woms-chart-left aria-label="Geser bagan ke kiri"><i class="bi bi-arrow-left"></i></button>
                            <button type="button" data-woms-chart-right aria-label="Geser bagan ke kanan"><i class="bi bi-arrow-right"></i></button>
                        </div>
                    </div>

                    <div class="woms-chart-viewport" data-woms-chart-viewport>
                        <div class="woms-chart-stage" data-woms-chart-stage>
                            <div class="woms-chart-canvas" data-woms-chart-canvas data-mode="all">
                                <div class="woms-chart-loading" data-woms-chart-loading>
                                    <span class="spinner-border text-primary" aria-hidden="true"></span>
                                    <strong>Memuat bagan organisasi…</strong>
                                </div>
                                <div class="woms-chart-load-error" data-woms-chart-error hidden>
                                    <i class="bi bi-exclamation-circle"></i>
                                    <strong>Bagan belum dapat dimuat</strong>
                                    <span>Periksa koneksi lalu coba kembali.</span>
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-woms-chart-retry>Coba Lagi</button>
                                </div>
                                <div data-woms-chart-content hidden>
                                    <div class="woms-chart-root">
                                        <span>Struktur Organisasi</span>
                                        <strong>Overhaul PT. Semen Tonasa</strong>
                                        <small data-woms-chart-summary></small>
                                    </div>
                                    <div class="woms-chart-departments" data-woms-chart-departments></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer woms-chart-modal-footer">
                    <span><i class="bi bi-arrows-move"></i> Tarik area bagan atau gunakan tombol panah untuk bergeser.</span>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Selesai</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="womsSyncInfoModal" tabindex="-1" aria-labelledby="womsSyncInfoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title fs-5" id="womsSyncInfoModalLabel">Informasi Sinkronisasi</h2>
                        <p class="mb-0 mt-1 text-muted small">Status integrasi dan riwayat proses sinkronisasi terbaru.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="content-card woms-status-card is-{{ $connectionStatus['tone'] }}">
                        <div class="woms-status-icon {{ $connectionStatus['tone'] === 'success' ? 'is-connected' : 'is-disconnected' }}"><i class="bi bi-cloud-check"></i></div>
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
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .woms-page-header p,.woms-section-heading p{margin:.25rem 0 0;color:#64748b;font-size:.84rem}.woms-page-header .page-actions{display:flex;flex-wrap:wrap;gap:.55rem}.woms-info-button,.woms-chart-button{width:42px;padding-left:0;padding-right:0}.woms-status-card{display:flex;align-items:center;gap:1rem;border-left:4px solid #64748b}.woms-status-card.is-success{border-left-color:#16a34a}.woms-status-card.is-warning{border-left-color:#f59e0b}.woms-status-card.is-danger{border-left-color:#dc2626}.woms-status-card.is-info{border-left-color:#0ea5e9}.woms-status-icon{width:52px;height:52px;display:grid;place-items:center;flex:0 0 auto;border-radius:.75rem;font-size:1.4rem}.woms-status-icon.is-connected{background:#ecfdf3;color:#16a34a}.woms-status-icon.is-disconnected{background:#fef2f2;color:#dc2626}.woms-status-copy{min-width:0;flex:1}.woms-status-copy>span,.woms-status-meta span{display:block;color:#64748b;font-size:.72rem;font-weight:700;text-transform:uppercase}.woms-status-copy h2{margin:.1rem 0;color:#172033;font-size:1.15rem;font-weight:800}.woms-status-copy p{margin:0;color:#64748b;font-size:.82rem}.woms-status-meta{display:grid;grid-template-columns:auto auto;gap:.2rem .7rem;text-align:right}.woms-status-meta code{max-width:170px;color:#334155;overflow:hidden;text-overflow:ellipsis}.woms-summary-grid{display:grid;grid-template-columns:repeat(3,minmax(130px,1fr));gap:.75rem;margin:1rem 0}.woms-summary-card{display:flex;align-items:center;gap:.7rem;margin:0;padding:.85rem}.woms-summary-card>i{width:38px;height:38px;display:grid;place-items:center;flex:0 0 auto;border-radius:.55rem;background:#eaf2ff;color:#2563eb}.woms-summary-card.is-green>i{background:#eaf8ef;color:#168148}.woms-summary-card.is-cyan>i{background:#e8f8fc;color:#0891b2}.woms-summary-card.is-violet>i{background:#f2edff;color:#7c3aed}.woms-summary-card.is-orange>i{background:#fff5e5;color:#d97706}.woms-summary-card.is-slate>i{background:#eef2f7;color:#475569}.woms-summary-card span{display:block;color:#64748b;font-size:.7rem;font-weight:700}.woms-summary-card strong{display:block;color:#172033;font-size:1.25rem;line-height:1.15}.woms-filter-card{margin-bottom:1rem}.woms-filter-grid{display:grid;grid-template-columns:repeat(4,minmax(150px,1fr)) auto;gap:.7rem;align-items:end}.woms-filter-grid .form-label{font-size:.75rem;font-weight:700;color:#475569}.woms-filter-grid .form-control,.woms-filter-grid .form-select{font-size:.8rem}.woms-filter-actions{display:flex;gap:.4rem}.woms-section-heading{display:flex;align-items:end;justify-content:space-between;margin:1.2rem 0 .7rem}.woms-section-heading h2{margin:0;color:#172033;font-size:1.05rem;font-weight:800}.woms-section-heading>span{color:#64748b;font-size:.78rem}.woms-hierarchy{display:grid;gap:1rem}.woms-department-card{padding:0;overflow:hidden}.woms-department-header{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1rem;background:#eef4fb;border-bottom:1px solid #d9e3ef}.woms-entity-kicker{color:#64748b;font-size:.67rem;font-weight:800;text-transform:uppercase;letter-spacing:.04em}.woms-department-header h3,.woms-unit-header h4{margin:.15rem 0 0;color:#172033;font-weight:800}.woms-department-header h3{font-size:1.08rem}.woms-unit-header h4{font-size:.93rem}.woms-department-meta{display:flex;align-items:center;gap:1rem;text-align:right}.woms-department-meta>div span,.woms-officer span{display:block;color:#64748b;font-size:.68rem;font-weight:700;text-transform:uppercase}.woms-department-meta strong,.woms-officer strong{display:block;color:#334155;font-size:.8rem}.woms-department-meta small,.woms-officer small{display:block;color:#64748b;font-size:.7rem}.woms-unit-list{display:grid;gap:.8rem;padding:.8rem}.woms-unit-card{overflow:hidden;border:1px solid #dbe3ee;border-radius:.65rem}.woms-unit-header{display:grid;grid-template-columns:minmax(180px,1fr) minmax(180px,.7fr);align-items:center;gap:.8rem;padding:.75rem .85rem;background:#fff}.woms-officer{text-align:right}.woms-section-table{min-width:720px;margin:0;border-top:1px solid #e2e8f0}.woms-section-table thead th,.woms-history-table thead th{background:#f8fafc;color:#526076;font-size:.7rem;text-transform:uppercase;white-space:nowrap}.woms-section-table td,.woms-history-table td{color:#475569;font-size:.75rem}.woms-section-table td:first-child strong,.woms-section-table td:first-child small{display:block}.woms-section-table td:first-child small{margin-top:.12rem;color:#94a3b8}.woms-empty-unit,.woms-empty-state{padding:1.5rem;text-align:center;color:#64748b}.woms-empty-state i{font-size:2rem}.woms-empty-state h3{margin:.5rem 0 .2rem;color:#334155;font-size:1rem}.woms-empty-state p{margin:0;font-size:.82rem}.woms-history-card{margin-top:1rem}.woms-history-table{min-width:1050px}.woms-history-table td:last-child{max-width:300px;white-space:normal}.woms-history-card .card-heading{margin-bottom:.7rem}@media(max-width:1399.98px){.woms-summary-grid{grid-template-columns:repeat(3,1fr)}.woms-filter-grid{grid-template-columns:repeat(3,1fr)}}@media(max-width:767.98px){.woms-page-header,.woms-department-header,.woms-section-heading{align-items:stretch;flex-direction:column}.woms-page-header .page-actions,.woms-page-header .page-actions form,.woms-page-header .btn{width:100%}.woms-status-card{align-items:flex-start;flex-wrap:wrap}.woms-status-meta{width:100%;padding-top:.7rem;border-top:1px solid #e2e8f0;text-align:left}.woms-summary-grid,.woms-filter-grid{grid-template-columns:1fr 1fr}.woms-filter-actions{grid-column:1/-1}.woms-unit-header{grid-template-columns:1fr}.woms-officer{text-align:left}.woms-department-meta{align-items:flex-start;flex-direction:column;text-align:left}}@media(max-width:479.98px){.woms-summary-grid,.woms-filter-grid{grid-template-columns:1fr}.woms-filter-actions{grid-column:auto}.woms-filter-actions .btn{flex:1}}

        .woms-chart-dialog{width:min(96vw,1680px);max-width:none;height:calc(100vh - 2rem);margin:1rem auto}.woms-chart-modal-content{height:100%;overflow:hidden;border:0;border-radius:1.25rem;box-shadow:0 28px 80px rgba(15,23,42,.3)}.woms-chart-modal-header{padding:1rem 1.25rem;border:0;background:linear-gradient(135deg,#173d82,#1e5fd4);color:#fff}.woms-chart-modal-header .modal-title{font-size:1.08rem;font-weight:800}.woms-chart-modal-header p{margin:.15rem 0 0;color:#dbeafe;font-size:.78rem}.woms-chart-title-icon{display:grid;width:42px;height:42px;place-items:center;border-radius:.8rem;background:rgba(255,255,255,.14);font-size:1.15rem}.woms-chart-modal-body{display:flex;min-height:0;flex:1;flex-direction:column;gap:.8rem;padding:1rem;background:#f1f5f9}.woms-chart-toolbar{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:.65rem .75rem;border:1px solid #d7e1ee;border-radius:.8rem;background:#fff}.woms-chart-levels,.woms-chart-controls{display:flex;align-items:center;gap:.45rem}.woms-chart-levels>span,.woms-chart-controls>span{margin-right:.2rem;color:#64748b;font-size:.67rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em}.woms-chart-levels button,.woms-chart-controls button{height:32px;padding:0 .72rem;border:1px solid #d7e1ee;border-radius:.55rem;background:#fff;color:#334155;font-size:.72rem;font-weight:700}.woms-chart-levels button:hover,.woms-chart-controls button:hover{border-color:#2563eb;color:#1d4ed8}.woms-chart-levels button.is-active{border-color:#2563eb;background:#2563eb;color:#fff}.woms-chart-controls strong{display:grid;height:32px;min-width:54px;place-items:center;border-block:1px solid #d7e1ee;color:#172033;font-size:.75rem}.woms-chart-controls .woms-chart-reset{display:inline-flex;align-items:center;gap:.35rem}.woms-chart-viewport{min-height:0;flex:1;overflow:auto;border:1px solid #d7e1ee;border-radius:.9rem;background-color:#f8fafc;background-image:radial-gradient(#cbd5e1 1px,transparent 1px);background-size:22px 22px;cursor:grab}.woms-chart-viewport.is-dragging{cursor:grabbing;user-select:none}.woms-chart-stage{position:relative;min-width:100%;min-height:100%}.woms-chart-canvas{position:absolute;top:0;left:0;width:max-content;min-width:1450px;padding:56px 44px 90px;transform-origin:top left}.woms-chart-root{position:relative;display:flex;width:390px;min-height:104px;margin:0 auto 64px;padding:1rem 1.2rem;align-items:center;justify-content:center;flex-direction:column;border-radius:1rem;background:linear-gradient(135deg,#6d4ee8,#7c3aed);box-shadow:0 12px 30px rgba(109,78,232,.24);color:#fff;text-align:center}.woms-chart-root::after{position:absolute;bottom:-64px;width:2px;height:64px;background:#94a3b8;content:""}.woms-chart-root span,.woms-chart-node span{font-size:.62rem;font-weight:800;text-transform:uppercase;letter-spacing:.13em;opacity:.82}.woms-chart-root strong{font-size:1.05rem}.woms-chart-root small{margin-top:.25rem;color:#ede9fe;font-size:.7rem}.woms-chart-departments{position:relative;display:flex;width:max-content;min-width:100%;gap:28px;padding-top:38px}.woms-chart-departments::before{position:absolute;top:0;right:160px;left:160px;height:2px;background:#94a3b8;content:""}.woms-chart-branch{position:relative;width:320px;flex:0 0 320px}.woms-chart-branch::before{position:absolute;top:-38px;left:50%;width:2px;height:38px;background:#94a3b8;content:""}.woms-chart-node{padding:.8rem .9rem;border-radius:.8rem;color:#fff;box-shadow:0 6px 16px rgba(15,23,42,.12)}.woms-chart-node strong,.woms-chart-node small{display:block}.woms-chart-node strong{margin-top:.12rem;font-size:.82rem;line-height:1.25}.woms-chart-node small{margin-top:.25rem;font-size:.64rem;line-height:1.35;opacity:.9}.woms-chart-department-node{min-height:90px;background:linear-gradient(135deg,#319363,#47a76f)}.woms-chart-units{position:relative;display:grid;gap:18px;margin-top:24px;padding-left:22px}.woms-chart-units::before{position:absolute;top:-24px;bottom:24px;left:8px;width:2px;background:#60a5fa;content:""}.woms-chart-unit-group{position:relative}.woms-chart-unit-group::before{position:absolute;top:36px;left:-14px;width:14px;height:2px;background:#60a5fa;content:""}.woms-chart-unit-node{min-height:76px;background:linear-gradient(135deg,#1764df,#2563eb)}.woms-chart-sections{position:relative;display:grid;gap:9px;margin-top:12px;padding-left:22px}.woms-chart-sections::before{position:absolute;top:-12px;bottom:19px;left:8px;width:2px;background:#f59e0b;content:""}.woms-chart-section-node{position:relative;min-height:58px;padding:.65rem .75rem;background:linear-gradient(135deg,#ee8d05,#f59e0b);color:#172033;box-shadow:0 4px 12px rgba(245,158,11,.16)}.woms-chart-section-node::before{position:absolute;top:26px;left:-14px;width:14px;height:2px;background:#f59e0b;content:""}.woms-chart-section-node strong{font-size:.74rem}.woms-chart-section-node small{font-size:.6rem}.woms-chart-empty{padding:.65rem;border:1px dashed #cbd5e1;border-radius:.65rem;background:#fff;color:#64748b;font-size:.7rem;text-align:center}.woms-chart-empty-state{display:grid;width:420px;min-height:180px;margin:auto;place-items:center;border:1px dashed #cbd5e1;border-radius:1rem;background:#fff;color:#64748b}.woms-chart-empty-state i{font-size:2rem}.woms-chart-canvas[data-mode="departments"] .woms-chart-units{display:none}.woms-chart-canvas[data-mode="units"] .woms-chart-sections{display:none}.woms-chart-modal-footer{display:flex;justify-content:space-between;padding:.75rem 1.25rem;border-color:#e2e8f0}.woms-chart-modal-footer span{display:flex;align-items:center;gap:.4rem;color:#64748b;font-size:.72rem}@media(max-width:991.98px){.woms-chart-dialog{width:100%;height:100%;margin:0}.woms-chart-modal-content{border-radius:0}.woms-chart-toolbar{align-items:stretch;flex-direction:column}.woms-chart-levels,.woms-chart-controls{flex-wrap:wrap}.woms-chart-modal-footer span{display:none}}@media(max-width:575.98px){.woms-chart-levels>span,.woms-chart-controls>span{width:100%}.woms-chart-levels button{flex:1}.woms-chart-controls strong{min-width:48px}.woms-chart-modal-body{padding:.7rem}}
        .woms-chart-loading,.woms-chart-load-error{display:flex;width:420px;min-height:190px;margin:80px auto;align-items:center;justify-content:center;flex-direction:column;gap:.75rem;border:1px solid #dbe3ee;border-radius:1rem;background:#fff;color:#475569;box-shadow:0 10px 26px rgba(15,23,42,.08)}.woms-chart-loading .spinner-border{width:2rem;height:2rem}.woms-chart-load-error>i{color:#dc2626;font-size:2rem}.woms-chart-load-error>span{color:#64748b;font-size:.75rem}.woms-chart-load-error[hidden],.woms-chart-loading[hidden],[data-woms-chart-content][hidden]{display:none!important}
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('womsOrganizationChartModal');
            if (!modal) return;

            const viewport = modal.querySelector('[data-woms-chart-viewport]');
            const stage = modal.querySelector('[data-woms-chart-stage]');
            const canvas = modal.querySelector('[data-woms-chart-canvas]');
            const zoomLabel = modal.querySelector('[data-woms-chart-zoom-label]');
            const zoomOut = modal.querySelector('[data-woms-chart-zoom-out]');
            const zoomIn = modal.querySelector('[data-woms-chart-zoom-in]');
            const modeButtons = [...modal.querySelectorAll('[data-woms-chart-mode]')];
            const loadingState = modal.querySelector('[data-woms-chart-loading]');
            const errorState = modal.querySelector('[data-woms-chart-error]');
            const content = modal.querySelector('[data-woms-chart-content]');
            const summary = modal.querySelector('[data-woms-chart-summary]');
            const departmentsContainer = modal.querySelector('[data-woms-chart-departments]');
            const initialZoom = 0.5;
            const minimumZoom = 0.3;
            const maximumZoom = 1;
            const zoomStep = 0.1;
            let zoom = initialZoom;
            let dragState = null;
            let chartLoaded = false;
            let chartLoading = false;

            const createElement = (tag, className = '', text = '') => {
                const element = document.createElement(tag);
                if (className) element.className = className;
                if (text) element.textContent = text;
                return element;
            };

            const createNode = (tag, className, label, name, detail) => {
                const node = createElement(tag, `woms-chart-node ${className}`);
                if (label) node.append(createElement('span', '', label));
                node.append(createElement('strong', '', name));
                node.append(createElement('small', '', detail));
                return node;
            };

            const renderChart = (payload) => {
                const departments = Array.isArray(payload.departments) ? payload.departments : [];
                const totals = payload.summary ?? {};
                const number = new Intl.NumberFormat('id-ID');
                summary.textContent = `${number.format(totals.departments ?? 0)} Departemen · ${number.format(totals.units ?? 0)} Unit Kerja · ${number.format(totals.sections ?? 0)} Seksi`;
                departmentsContainer.replaceChildren();

                if (departments.length === 0) {
                    const empty = createElement('div', 'woms-chart-empty-state');
                    empty.append(createElement('i', 'bi bi-diagram-3'));
                    empty.append(createElement('strong', '', 'Belum ada data struktur organisasi'));
                    departmentsContainer.append(empty);
                    return;
                }

                departments.forEach((department) => {
                    const branch = createElement('section', 'woms-chart-branch');
                    branch.append(createNode(
                        'header',
                        'woms-chart-department-node',
                        'Departemen',
                        department.name,
                        `General Manager · ${department.general_manager || 'Belum ditetapkan'}`
                    ));

                    const units = createElement('div', 'woms-chart-units');
                    if (!Array.isArray(department.units) || department.units.length === 0) {
                        units.append(createElement('div', 'woms-chart-empty', 'Belum ada unit kerja'));
                    } else {
                        department.units.forEach((unit) => {
                            const unitGroup = createElement('article', 'woms-chart-unit-group');
                            unitGroup.append(createNode(
                                'div',
                                'woms-chart-unit-node',
                                'Unit Kerja',
                                unit.name,
                                `Senior Manager · ${unit.senior_manager || 'Belum ditetapkan'}`
                            ));

                            const sections = createElement('div', 'woms-chart-sections');
                            if (!Array.isArray(unit.sections) || unit.sections.length === 0) {
                                sections.append(createElement('div', 'woms-chart-empty', 'Belum ada seksi'));
                            } else {
                                unit.sections.forEach((section) => {
                                    sections.append(createNode(
                                        'div',
                                        'woms-chart-section-node',
                                        '',
                                        section.name,
                                        `Manager · ${section.manager || 'Belum ditetapkan'}`
                                    ));
                                });
                            }

                            unitGroup.append(sections);
                            units.append(unitGroup);
                        });
                    }

                    branch.append(units);
                    departmentsContainer.append(branch);
                });
            };

            const applyZoom = (center = false) => {
                canvas.style.transform = `scale(${zoom})`;
                zoomLabel.textContent = `${Math.round(zoom * 100)}%`;
                zoomOut.disabled = zoom <= minimumZoom;
                zoomIn.disabled = zoom >= maximumZoom;

                requestAnimationFrame(() => {
                    const scaledWidth = canvas.scrollWidth * zoom;
                    const scaledHeight = canvas.scrollHeight * zoom;
                    stage.style.width = `${Math.max(viewport.clientWidth, scaledWidth)}px`;
                    stage.style.height = `${Math.max(viewport.clientHeight, scaledHeight)}px`;

                    if (center) {
                        viewport.scrollLeft = Math.max(0, (scaledWidth - viewport.clientWidth) / 2);
                        viewport.scrollTop = 0;
                    }
                });
            };

            const loadChart = async (force = false) => {
                if (chartLoading || (chartLoaded && !force)) {
                    if (chartLoaded) applyZoom(true);
                    return;
                }

                chartLoading = true;
                loadingState.hidden = false;
                errorState.hidden = true;
                content.hidden = true;
                applyZoom(true);

                try {
                    const response = await fetch(modal.dataset.chartUrl, {
                        credentials: 'same-origin',
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!response.ok) throw new Error(`HTTP ${response.status}`);

                    renderChart(await response.json());
                    chartLoaded = true;
                    loadingState.hidden = true;
                    content.hidden = false;
                    applyZoom(true);
                } catch {
                    chartLoaded = false;
                    loadingState.hidden = true;
                    errorState.hidden = false;
                    applyZoom(true);
                } finally {
                    chartLoading = false;
                }
            };

            zoomOut.addEventListener('click', () => {
                zoom = Math.max(minimumZoom, Number((zoom - zoomStep).toFixed(1)));
                applyZoom();
            });

            zoomIn.addEventListener('click', () => {
                zoom = Math.min(maximumZoom, Number((zoom + zoomStep).toFixed(1)));
                applyZoom();
            });

            modal.querySelector('[data-woms-chart-reset]').addEventListener('click', () => {
                zoom = initialZoom;
                canvas.dataset.mode = 'all';
                modeButtons.forEach((button) => button.classList.toggle('is-active', button.dataset.womsChartMode === 'all'));
                applyZoom(true);
            });

            modeButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    canvas.dataset.mode = button.dataset.womsChartMode;
                    modeButtons.forEach((item) => item.classList.toggle('is-active', item === button));
                    applyZoom(true);
                });
            });

            modal.querySelector('[data-woms-chart-left]').addEventListener('click', () => {
                viewport.scrollBy({ left: -420, behavior: 'smooth' });
            });

            modal.querySelector('[data-woms-chart-right]').addEventListener('click', () => {
                viewport.scrollBy({ left: 420, behavior: 'smooth' });
            });

            modal.querySelector('[data-woms-chart-retry]').addEventListener('click', () => loadChart(true));

            viewport.addEventListener('pointerdown', (event) => {
                dragState = {
                    pointerId: event.pointerId,
                    startX: event.clientX,
                    startY: event.clientY,
                    scrollLeft: viewport.scrollLeft,
                    scrollTop: viewport.scrollTop,
                };
                viewport.setPointerCapture(event.pointerId);
                viewport.classList.add('is-dragging');
            });

            viewport.addEventListener('pointermove', (event) => {
                if (!dragState || dragState.pointerId !== event.pointerId) return;
                viewport.scrollLeft = dragState.scrollLeft - (event.clientX - dragState.startX);
                viewport.scrollTop = dragState.scrollTop - (event.clientY - dragState.startY);
            });

            const stopDragging = (event) => {
                if (!dragState || dragState.pointerId !== event.pointerId) return;
                dragState = null;
                viewport.classList.remove('is-dragging');
            };

            viewport.addEventListener('pointerup', stopDragging);
            viewport.addEventListener('pointercancel', stopDragging);
            modal.addEventListener('shown.bs.modal', () => loadChart());
            window.addEventListener('resize', () => {
                if (modal.classList.contains('show')) applyZoom();
            });
        });
    </script>
@endpush

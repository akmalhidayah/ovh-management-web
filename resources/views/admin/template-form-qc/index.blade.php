@extends('layouts.dashboard')

@section('title', 'Template Form QC')
@section('page_title', 'Template Form QC')

@section('content')
    @php
        $statusLabels = ['draft' => 'Perlu Review', 'active' => 'Siap Digunakan', 'inactive' => 'Nonaktif'];
        $statusClasses = ['draft' => 'text-bg-warning', 'active' => 'text-bg-success', 'inactive' => 'text-bg-secondary'];
        $tabs = [
            'all' => 'Semua',
            'active' => 'Siap Digunakan',
            'draft' => 'Perlu Review',
            'inactive' => 'Nonaktif',
        ];
        $currentStatus = request('status', 'all');
    @endphp

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="page-header">
        <div>
            <h1>Template Form QC</h1>
            <p>Kelola template form QC yang akan digunakan user QC untuk membuat laporan inspeksi.</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('admin.template-form-qc.import') }}" class="btn btn-outline-primary">
                <i class="bi bi-file-earmark-excel me-1"></i>Import dari Excel
            </a>
            <a href="{{ route('admin.template-form-qc.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Buat Template Manual
            </a>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon text-bg-success"><i class="bi bi-check2-circle"></i></div>
                <div>
                    <div class="stat-title">Template Siap Digunakan</div>
                    <div class="stat-value">{{ $summary['active'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon text-bg-warning"><i class="bi bi-pencil-square"></i></div>
                <div>
                    <div class="stat-title">Draft Perlu Review</div>
                    <div class="stat-value">{{ $summary['draft'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon text-bg-secondary"><i class="bi bi-pause-circle"></i></div>
                <div>
                    <div class="stat-title">Template Nonaktif</div>
                    <div class="stat-value">{{ $summary['inactive'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon text-bg-primary"><i class="bi bi-files"></i></div>
                <div>
                    <div class="stat-title">Total Template</div>
                    <div class="stat-value">{{ $summary['total'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-card">
        <div class="template-toolbar">
            <div class="template-tabs">
                @foreach ($tabs as $value => $label)
                    <a href="{{ route('admin.template-form-qc.index', array_filter(['status' => $value === 'all' ? null : $value, 'search' => request('search')])) }}"
                       class="template-tab {{ $currentStatus === $value ? 'active' : '' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
            <form method="GET" class="template-search">
                @if ($currentStatus !== 'all')
                    <input type="hidden" name="status" value="{{ $currentStatus }}">
                @endif
                <i class="bi bi-search"></i>
                <input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari kode, nama template, atau kategori">
            </form>
        </div>

        <div class="table-responsive">
            <table class="table align-middle template-table">
                <thead>
                    <tr>
                        <th>Nama Template</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th>Terakhir Diubah</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($templates as $template)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $template->name }}</div>
                                <div class="template-meta">
                                    <span>{{ $template->code ?: 'Tanpa kode' }}</span>
                                    <span>{{ $template->template_type ? \App\Support\QcTemplates\FixedQcTemplate::templateTypeLabel($template->template_type) : $template->blocks_count.' bagian' }}</span>
                                    <span>{{ $template->fields_count }} item</span>
                                    <span>Versi {{ $template->version }}</span>
                                    <span>{{ $template->template_type ? 'Form Terarah' : ($template->layout_mode === 'excel_grid' ? 'Excel Grid' : 'Block Based') }}</span>
                                </div>
                                @if ($template->status === 'draft')
                                    <div class="template-row-note">Perlu direview sebelum digunakan.</div>
                                @endif
                            </td>
                            <td>{{ $template->category ?: '-' }}</td>
                            <td><span class="badge {{ $statusClasses[$template->status] ?? 'text-bg-secondary' }}">{{ $statusLabels[$template->status] ?? $template->status }}</span></td>
                            <td>
                                <div>{{ $template->updated_at?->format('d M Y') }}</div>
                                <small class="text-muted">{{ $template->updated_at?->format('H:i') }}</small>
                            </td>
                            <td>
                                <div class="template-actions">
                                    <a href="{{ route('admin.template-form-qc.preview', $template) }}" class="btn btn-sm btn-outline-primary">Preview</a>
                                    <a href="{{ route('admin.template-form-qc.edit', $template) }}" class="btn btn-sm {{ $template->status === 'draft' ? 'btn-primary' : 'btn-outline-secondary' }}">
                                        {{ $template->status === 'draft' ? 'Review & Rapikan' : 'Review/Edit' }}
                                    </a>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            Lainnya
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="{{ route('admin.template-form-qc.show', $template) }}">Detail</a></li>
                                            <li>
                                                <form action="{{ route('admin.template-form-qc.duplicate', $template) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">Duplicate</button>
                                                </form>
                                            </li>
                                            @if ($template->status === 'active')
                                                <li>
                                                    <form action="{{ route('admin.template-form-qc.toggle-status', $template) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="dropdown-item">Nonaktifkan</button>
                                                    </form>
                                                </li>
                                            @else
                                                <li>
                                                    <form action="{{ route('admin.template-form-qc.publish', $template) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="dropdown-item">Publish / Aktifkan</button>
                                                    </form>
                                                </li>
                                            @endif
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('admin.template-form-qc.destroy', $template) }}" method="POST" onsubmit="return confirm('Hapus template ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">Hapus</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Belum ada template Form QC.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $templates->links() }}
        </div>
    </div>
@endsection

@extends('layouts.dashboard')

@section('title', 'Master Data')
@section('page_title', 'Master Data')

@section('content')
    @php
        $categoryBadge = [
            'qc' => 'text-bg-primary',
            'commissioning' => 'text-bg-info',
        ];
        $statusBadge = [
            'active' => 'text-bg-success',
            'inactive' => 'text-bg-secondary',
        ];
    @endphp

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->has('record_ids'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ $errors->first('record_ids') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="page-header">
        <div>
            <h1>Master Data</h1>
            <p>Kelola referensi equipment berdasarkan kategori dokumen QC dan Commissioning.</p>
        </div>
        <div class="page-actions">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createMasterDataModal">
                <i class="bi bi-plus-lg me-1"></i>Tambah Data
            </button>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon text-bg-primary"><i class="bi bi-database"></i></div>
                <div>
                    <div class="stat-title">Total Data</div>
                    <div class="stat-value">{{ $summary['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon text-bg-primary"><i class="bi bi-shield-check"></i></div>
                <div>
                    <div class="stat-title">Data QC</div>
                    <div class="stat-value">{{ $summary['qc'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon text-bg-info"><i class="bi bi-tools"></i></div>
                <div>
                    <div class="stat-title">Data Commissioning</div>
                    <div class="stat-value">{{ $summary['commissioning'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon text-bg-success"><i class="bi bi-check2-circle"></i></div>
                <div>
                    <div class="stat-title">Aktif</div>
                    <div class="stat-value">{{ $summary['active'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-card">
        <div class="template-toolbar">
            <div class="template-tabs">
                @foreach (['all' => 'Semua Dokumen'] + $categoryOptions as $value => $label)
                    <a href="{{ route('admin.master-data', array_merge(request()->except('page'), ['document_category' => $value])) }}"
                       class="template-tab {{ $filters['document_category'] === $value ? 'active' : '' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        <form method="GET" class="row g-3 align-items-end">
            <div class="col-12 col-md-6 col-xl-2">
                <label class="form-label">Kategori Dokumen</label>
                <select name="document_category" class="form-select">
                    <option value="all">Semua</option>
                    @foreach ($categoryOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['document_category'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-2">
                <label class="form-label">Tahun</label>
                <select name="year" class="form-select">
                    <option value="all">Semua</option>
                    @foreach ($filterOptions['years'] as $year)
                        <option value="{{ $year }}" @selected($filters['year'] === $year)>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-2">
                <label class="form-label">Plant</label>
                <select name="plant" class="form-select">
                    <option value="all">Semua</option>
                    @foreach ($filterOptions['plants'] as $plant)
                        <option value="{{ $plant }}" @selected($filters['plant'] === $plant)>{{ $plant }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-2">
                <label class="form-label">Area</label>
                <select name="area" class="form-select">
                    <option value="all">Semua</option>
                    @foreach ($filterOptions['areas'] as $area)
                        <option value="{{ $area }}" @selected($filters['area'] === $area)>{{ $area }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="all">Semua</option>
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-2">
                <label class="form-label">Cari</label>
                <input type="search" name="search" class="form-control" value="{{ $filters['search'] }}" placeholder="Equipment, area, plant">
            </div>
            <div class="col-12 d-flex flex-wrap justify-content-end gap-2">
                <a href="{{ route('admin.master-data') }}" class="btn btn-outline-secondary">Reset</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
            </div>
        </form>
    </div>

    <div class="content-card">
        <form id="bulkMasterDataForm" action="{{ route('admin.master-data.bulk-status') }}" method="POST">
            @csrf
            @method('PATCH')
        </form>

        <div class="card-heading">
            <div>
                <h2>Data Equipment Per Dokumen</h2>
                <div class="text-muted small">Kolom mengikuti data source: Func. Location, Equipment No., Section No., Descriptions, Plant, Area.</div>
            </div>
            <div class="d-flex flex-wrap align-items-center justify-content-end gap-2">
                <span class="text-muted small" data-bulk-selected-count>0 dipilih</span>
                <button type="submit" form="bulkMasterDataForm" name="status" value="active" class="btn btn-sm btn-success" data-bulk-action disabled>
                    <i class="bi bi-check2-circle me-1"></i>Aktifkan
                </button>
                <button type="submit" form="bulkMasterDataForm" name="status" value="inactive" class="btn btn-sm btn-outline-secondary" data-bulk-action disabled>
                    <i class="bi bi-pause-circle me-1"></i>Nonaktifkan
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle template-table">
                <thead>
                    <tr>
                        <th style="width: 44px;">
                            <input type="checkbox" class="form-check-input" data-master-select-all aria-label="Pilih semua data pada halaman ini">
                        </th>
                        <th>Kategori</th>
                        <th>Func. Location</th>
                        <th>Equipment No.</th>
                        <th>Section No.</th>
                        <th>Descriptions</th>
                        <th>Plant</th>
                        <th>Area</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $record)
                        <tr>
                            <td>
                                <input type="checkbox"
                                       class="form-check-input"
                                       name="record_ids[]"
                                       value="{{ $record->id }}"
                                       form="bulkMasterDataForm"
                                       data-master-row-check
                                       aria-label="Pilih {{ $record->func_location }}">
                            </td>
                            <td>
                                <span class="badge {{ $categoryBadge[$record->document_category] ?? 'text-bg-secondary' }}">
                                    {{ $record->document_category_label }}
                                </span>
                                @if ($record->year)
                                    <div class="text-muted small mt-1">{{ $record->year }}</div>
                                @endif
                            </td>
                            <td class="fw-semibold">{{ $record->func_location }}</td>
                            <td>{{ $record->equipment_no }}</td>
                            <td>{{ $record->section_no ?: '-' }}</td>
                            <td>{{ $record->description }}</td>
                            <td>{{ $record->plant }}</td>
                            <td>{{ $record->area }}</td>
                            <td><span class="badge {{ $statusBadge[$record->status] ?? 'text-bg-secondary' }}">{{ $record->status_label }}</span></td>
                            <td>
                                <div class="template-actions">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editMasterDataModal"
                                            data-update-url="{{ route('admin.master-data.update', $record) }}"
                                            data-document-category="{{ $record->document_category }}"
                                            data-year="{{ $record->year }}"
                                            data-func-location="{{ $record->func_location }}"
                                            data-equipment-no="{{ $record->equipment_no }}"
                                            data-section-no="{{ $record->section_no }}"
                                            data-description="{{ $record->description }}"
                                            data-plant="{{ $record->plant }}"
                                            data-area="{{ $record->area }}"
                                            data-status="{{ $record->status }}"
                                            data-notes="{{ $record->notes }}">
                                        Edit
                                    </button>
                                    <form action="{{ route('admin.master-data.destroy', $record) }}" method="POST" onsubmit="return confirm('Hapus master data ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">Belum ada master data sesuai filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $records->links() }}
        </div>
    </div>

    @include('admin.master-data.partials.form-modal', [
        'modalId' => 'createMasterDataModal',
        'title' => 'Tambah Master Data',
        'action' => route('admin.master-data.store'),
        'method' => null,
        'categoryOptions' => $categoryOptions,
        'statusOptions' => $statusOptions,
        'record' => null,
    ])

    @include('admin.master-data.partials.form-modal', [
        'modalId' => 'editMasterDataModal',
        'title' => 'Edit Master Data',
        'action' => '#',
        'method' => 'PUT',
        'categoryOptions' => $categoryOptions,
        'statusOptions' => $statusOptions,
        'record' => null,
    ])
@endsection

@push('scripts')
    <script>
        (() => {
            const editModal = document.getElementById('editMasterDataModal');
            const selectAll = document.querySelector('[data-master-select-all]');
            const rowChecks = Array.from(document.querySelectorAll('[data-master-row-check]'));
            const bulkActions = document.querySelectorAll('[data-bulk-action]');
            const selectedCount = document.querySelector('[data-bulk-selected-count]');

            const syncBulkControls = () => {
                const checkedCount = rowChecks.filter((checkbox) => checkbox.checked).length;
                bulkActions.forEach((button) => {
                    button.disabled = checkedCount === 0;
                });

                if (selectedCount) {
                    selectedCount.textContent = `${checkedCount} dipilih`;
                }

                if (selectAll) {
                    selectAll.checked = checkedCount > 0 && checkedCount === rowChecks.length;
                    selectAll.indeterminate = checkedCount > 0 && checkedCount < rowChecks.length;
                }
            };

            selectAll?.addEventListener('change', () => {
                rowChecks.forEach((checkbox) => {
                    checkbox.checked = selectAll.checked;
                });
                syncBulkControls();
            });

            rowChecks.forEach((checkbox) => {
                checkbox.addEventListener('change', syncBulkControls);
            });

            syncBulkControls();

            if (!editModal) return;

            editModal.addEventListener('show.bs.modal', (event) => {
                const button = event.relatedTarget;
                if (!button) return;

                const form = editModal.querySelector('form');
                form.action = button.dataset.updateUrl;

                const fields = {
                    document_category: button.dataset.documentCategory,
                    year: button.dataset.year,
                    func_location: button.dataset.funcLocation,
                    equipment_no: button.dataset.equipmentNo,
                    section_no: button.dataset.sectionNo,
                    description: button.dataset.description,
                    plant: button.dataset.plant,
                    area: button.dataset.area,
                    status: button.dataset.status,
                    notes: button.dataset.notes,
                };

                Object.entries(fields).forEach(([name, value]) => {
                    const input = form.querySelector(`[name="${name}"]`);
                    if (input) input.value = value || '';
                });
            });
        })();
    </script>
@endpush

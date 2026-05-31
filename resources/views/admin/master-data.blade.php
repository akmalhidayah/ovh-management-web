@extends('layouts.dashboard')

@section('title', 'MASTER DATA EQUIPMENT')
@section('page_title', 'MASTER DATA EQUIPMENT')

@push('styles')
    <style>
        .master-data-stat-row .stat-card {
            gap: .75rem;
            padding: .8rem;
        }

        .master-data-stat-row .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: .55rem;
            font-size: 1.05rem;
        }

        .master-data-stat-row .stat-title {
            font-size: .78rem;
            line-height: 1.25;
        }

        .master-data-stat-row .stat-value {
            font-size: 1.08rem;
        }

        .master-data-bulk-actions {
            align-items: center;
            gap: .45rem;
        }

        .master-data-bulk-actions .btn {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .35rem .55rem;
            line-height: 1.15;
        }

        .master-data-bulk-actions .btn i {
            margin: 0 !important;
            font-size: .9rem;
            line-height: 1;
        }

        .master-data-location {
            display: grid;
            gap: .15rem;
            line-height: 1.25;
        }

        .master-data-location small {
            color: #64748b;
        }

        .master-data-actions .btn {
            width: 2rem;
            height: 2rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }
    </style>
@endpush

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
        $showBulkSelection = true;
        $bulkFilterInputs = [
            'document_category' => $filters['document_category'],
            'year' => $filters['year'],
            'plant' => $filters['plant'],
            'area' => $filters['area'],
            'current_status' => $filters['status'],
            'search' => $filters['search'],
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
            <h1>MASTER DATA EQUIPMENT</h1>
        </div>
        <div class="page-actions">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createMasterDataModal">
                <i class="bi bi-plus-lg me-1"></i>Tambah Data
            </button>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-5 g-3 mb-3 master-data-stat-row">
        <div class="col">
            <div class="stat-card">
                <div class="stat-icon text-bg-primary"><i class="bi bi-database"></i></div>
                <div>
                    <div class="stat-title">Total Data</div>
                    <div class="stat-value">{{ $summary['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="stat-card">
                <div class="stat-icon text-bg-primary"><i class="bi bi-shield-check"></i></div>
                <div>
                    <div class="stat-title">Data QC</div>
                    <div class="stat-value">{{ $summary['qc'] }}</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="stat-card">
                <div class="stat-icon text-bg-info"><i class="bi bi-tools"></i></div>
                <div>
                    <div class="stat-title">Data Commissioning</div>
                    <div class="stat-value">{{ $summary['commissioning'] }}</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="stat-card">
                <div class="stat-icon text-bg-success"><i class="bi bi-check2-circle"></i></div>
                <div>
                    <div class="stat-title">Aktif QC</div>
                    <div class="stat-value">{{ $summary['active_qc'] }}</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="stat-card">
                <div class="stat-icon text-bg-success"><i class="bi bi-check-all"></i></div>
                <div>
                    <div class="stat-title">Aktif Commissioning</div>
                    <div class="stat-value">{{ $summary['active_commissioning'] }}</div>
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
                <h2>Data Equipment</h2>
            </div>
            @if ($showBulkSelection)
                <div class="d-flex flex-wrap justify-content-end master-data-bulk-actions">
                    <span class="text-muted small" data-bulk-selected-count>0 dipilih</span>
                    <button type="submit" form="bulkMasterDataForm" name="status" value="active" class="btn btn-sm btn-success" data-bulk-action disabled>
                        <i class="bi bi-check2-circle"></i><span>Aktifkan</span>
                    </button>
                    <button type="submit" form="bulkMasterDataForm" name="status" value="inactive" class="btn btn-sm btn-outline-secondary" data-bulk-action disabled>
                        <i class="bi bi-pause-circle"></i><span>Nonaktifkan</span>
                    </button>
                    <form action="{{ route('admin.master-data.bulk-filtered-status') }}" method="POST" class="d-inline" data-filtered-bulk-form data-filtered-bulk-action="aktifkan">
                        @csrf
                        @method('PATCH')
                        @foreach ($bulkFilterInputs as $name => $value)
                            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                        @endforeach
                        <button type="submit" name="status" value="active" class="btn btn-sm btn-success">
                            <i class="bi bi-check-all"></i><span>Aktifkan Semua</span>
                        </button>
                    </form>
                    <form action="{{ route('admin.master-data.bulk-filtered-status') }}" method="POST" class="d-inline" data-filtered-bulk-form data-filtered-bulk-action="nonaktifkan">
                        @csrf
                        @method('PATCH')
                        @foreach ($bulkFilterInputs as $name => $value)
                            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                        @endforeach
                        <button type="submit" name="status" value="inactive" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-slash-circle"></i><span>Nonaktifkan Semua</span>
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <div class="table-responsive">
            <table class="table align-middle template-table">
                <thead>
                    <tr>
                        @if ($showBulkSelection)
                            <th style="width: 44px;">
                                <input type="checkbox" class="form-check-input" data-master-select-all aria-label="Pilih semua data pada halaman ini">
                            </th>
                        @endif
                        <th>Kategori</th>
                        <th>Func. Location</th>
                        <th>Equipment No.</th>
                        <th>Section No.</th>
                        <th>Descriptions</th>
                        <th>Lokasi</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $record)
                        <tr>
                            @if ($showBulkSelection)
                                <td>
                                    <input type="checkbox"
                                           class="form-check-input"
                                           name="record_ids[]"
                                           value="{{ $record->id }}"
                                           form="bulkMasterDataForm"
                                           data-master-row-check
                                           aria-label="Pilih {{ $record->func_location }}">
                                </td>
                            @endif
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
                            <td>
                                <span class="master-data-location">
                                    <strong>{{ $record->plant }}</strong>
                                    <small>{{ $record->area }}</small>
                                    @if ($record->document_category === \App\Models\MasterDataRecord::CATEGORY_COMMISSIONING && $record->organizationSection)
                                        <small>Unit Kerja: {{ $record->organizationSection->section }}</small>
                                    @endif
                                </span>
                            </td>
                            <td><span class="badge {{ $statusBadge[$record->status] ?? 'text-bg-secondary' }}">{{ $record->status_label }}</span></td>
                            <td>
                                <div class="template-actions master-data-actions">
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
                                            data-organization-section-id="{{ $record->organization_section_id }}"
                                            data-status="{{ $record->status }}"
                                            data-notes="{{ $record->notes }}"
                                            title="Edit"
                                            aria-label="Edit {{ $record->func_location }}">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <form action="{{ route('admin.master-data.destroy', $record) }}" method="POST" data-delete-master-data-form data-delete-label="{{ $record->func_location }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus" aria-label="Hapus {{ $record->func_location }}">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $showBulkSelection ? 9 : 8 }}" class="text-center text-muted py-4">Belum ada master data sesuai filter.</td>
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
        'organizationSectionOptions' => $organizationSectionOptions,
        'record' => null,
    ])

    @include('admin.master-data.partials.form-modal', [
        'modalId' => 'editMasterDataModal',
        'title' => 'Edit Master Data',
        'action' => '#',
        'method' => 'PUT',
        'categoryOptions' => $categoryOptions,
        'statusOptions' => $statusOptions,
        'organizationSectionOptions' => $organizationSectionOptions,
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
            const filteredBulkForms = document.querySelectorAll('[data-filtered-bulk-form]');
            const deleteForms = document.querySelectorAll('[data-delete-master-data-form]');
            const syncCommissioningUnitField = (form) => {
                const category = form?.querySelector('[data-master-document-category]')?.value || '';
                const unitField = form?.querySelector('[data-commissioning-unit-field]');
                const unitSelect = form?.querySelector('[name="organization_section_id"]');

                if (!unitField || !unitSelect) {
                    return;
                }

                const isCommissioning = category === 'commissioning';
                unitField.classList.toggle('d-none', !isCommissioning);
                unitSelect.disabled = !isCommissioning;

                if (!isCommissioning) {
                    unitSelect.value = '';
                }
            };

            const confirmAction = async ({title, text, confirmButtonText = 'Lanjutkan', icon = 'warning'}) => {
                if (!window.Swal) {
                    return confirm(text || title);
                }

                const result = await Swal.fire({
                    title,
                    text,
                    icon,
                    showCancelButton: true,
                    confirmButtonText,
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                });

                return result.isConfirmed;
            };

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

            filteredBulkForms.forEach((form) => {
                form.querySelectorAll('button[type="submit"][name]').forEach((button) => {
                    button.addEventListener('click', () => {
                        form.dataset.submitterName = button.name;
                        form.dataset.submitterValue = button.value;
                    });
                });

                const syncSubmitterValue = (submitter) => {
                    const name = submitter?.name || form.dataset.submitterName;
                    const value = submitter?.value || form.dataset.submitterValue;

                    if (!name) {
                        return;
                    }

                    let submitterValue = form.querySelector('[data-confirmed-submit-value]');

                    if (!submitterValue) {
                        submitterValue = document.createElement('input');
                        submitterValue.type = 'hidden';
                        submitterValue.dataset.confirmedSubmitValue = '1';
                        form.appendChild(submitterValue);
                    }

                    submitterValue.name = name;
                    submitterValue.value = value;
                };

                form.addEventListener('submit', async (event) => {
                    const submitter = event.submitter;
                    const action = form.dataset.filteredBulkAction || 'ubah status';
                    const category = form.querySelector('[name="document_category"]')?.value || 'all';
                    const affectsBothCategories = category === 'all';
                    const categoryLabel = category === 'qc' ? 'QC' : (category === 'commissioning' ? 'Commissioning' : '');
                    const message = affectsBothCategories
                        ? `Kategori masih Semua Dokumen. Aksi ini akan ${action} semua master data QC dan Commissioning yang cocok dengan filter saat ini. Lanjutkan?`
                        : `Filter kategori ${categoryLabel} sedang aktif. Aksi ini akan ${action} semua master data ${categoryLabel} yang cocok dengan filter saat ini, termasuk data di halaman pagination lain. Lanjutkan?`;

                    if (form.dataset.confirmed === '1') {
                        return;
                    }

                    event.preventDefault();

                    if (await confirmAction({
                        title: affectsBothCategories ? 'Konfirmasi Semua Dokumen' : `Konfirmasi ${categoryLabel}`,
                        text: message,
                        confirmButtonText: action === 'aktifkan' ? 'Ya, aktifkan' : 'Ya, nonaktifkan',
                    })) {
                        form.dataset.confirmed = '1';
                        syncSubmitterValue(submitter);
                        HTMLFormElement.prototype.submit.call(form);
                    }
                });
            });

            deleteForms.forEach((form) => {
                form.addEventListener('submit', async (event) => {
                    if (form.dataset.confirmed === '1') {
                        return;
                    }

                    event.preventDefault();

                    const label = form.dataset.deleteLabel || 'master data ini';
                    if (await confirmAction({
                        title: 'Hapus Master Data?',
                        text: `Data ${label} akan dihapus permanen.`,
                        confirmButtonText: 'Ya, hapus',
                    })) {
                        form.dataset.confirmed = '1';
                        form.requestSubmit();
                    }
                });
            });

            syncBulkControls();

            document.querySelectorAll('[data-master-document-category]').forEach((select) => {
                const form = select.closest('form');
                syncCommissioningUnitField(form);
                select.addEventListener('change', () => syncCommissioningUnitField(form));
            });

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
                    organization_section_id: button.dataset.organizationSectionId,
                    status: button.dataset.status,
                    notes: button.dataset.notes,
                };

                Object.entries(fields).forEach(([name, value]) => {
                    const input = form.querySelector(`[name="${name}"]`);
                    if (input) input.value = value || '';
                });

                syncCommissioningUnitField(form);
            });
        })();
    </script>
@endpush

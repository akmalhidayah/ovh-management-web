@extends('layouts.dashboard')

@section('title', 'Unit Kerja')
@section('page_title', 'Unit Kerja')

@section('content')
    @php
        $departmentRowspans = $sections->groupBy('department')->map->count();
        $unitRowspans = $sections->groupBy(fn ($section) => $section->department.'|'.$section->unit_kerja)->map->count();
        $printedDepartments = [];
        $printedUnits = [];
    @endphp

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="page-header">
        <div>
            <h1>Unit Kerja</h1>
        </div>
        <div class="page-actions">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createOrganizationSectionModal">
                <i class="bi bi-plus-lg me-1"></i>Tambah Unit Kerja
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Departemen</th>
                    <th>Unit Kerja</th>
                    <th>Seksi</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sections as $section)
                    @php
                        $departmentKey = md5($section->department);
                        $unitKey = md5($section->department.'|'.$section->unit_kerja);
                    @endphp
                    <tr>
                        @if (! isset($printedDepartments[$departmentKey]))
                            <td rowspan="{{ $departmentRowspans[$section->department] }}" class="align-top fw-semibold">
                                {{ $section->department }}
                            </td>
                            @php($printedDepartments[$departmentKey] = true)
                        @endif
                        @if (! isset($printedUnits[$unitKey]))
                            <td rowspan="{{ $unitRowspans[$section->department.'|'.$section->unit_kerja] }}" class="align-top">
                                {{ $section->unit_kerja }}
                            </td>
                            @php($printedUnits[$unitKey] = true)
                        @endif
                        <td class="fw-semibold">{{ $section->section }}</td>
                        <td>
                            <div class="d-flex justify-content-end gap-2">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editOrganizationSectionModal"
                                    data-update-url="{{ route('admin.organization-sections.update', $section) }}"
                                    data-department="{{ $section->department }}"
                                    data-unit-kerja="{{ $section->unit_kerja }}"
                                    data-section="{{ $section->section }}"
                                    aria-label="Edit {{ $section->section }}">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <form action="{{ route('admin.organization-sections.destroy', $section) }}" method="POST" onsubmit="return confirm('Hapus unit kerja ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">Belum ada data unit kerja.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="modal fade" id="createOrganizationSectionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" action="{{ route('admin.organization-sections.store') }}" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Unit Kerja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    @include('admin.organization-sections._form', [
                        'section' => new \App\Models\OrganizationSection(),
                        'fieldPrefix' => 'create-organization-section',
                    ])
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="editOrganizationSectionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" action="#" method="POST" data-organization-section-edit-form>
                <div class="modal-header">
                    <h5 class="modal-title">Edit Unit Kerja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    @include('admin.organization-sections._form', [
                        'section' => new \App\Models\OrganizationSection(),
                        'method' => 'PUT',
                        'fieldPrefix' => 'edit-organization-section',
                    ])
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const editModal = document.getElementById('editOrganizationSectionModal');
            const editForm = document.querySelector('[data-organization-section-edit-form]');

            editModal?.addEventListener('show.bs.modal', (event) => {
                const button = event.relatedTarget;
                if (!button || !editForm) return;

                editForm.action = button.dataset.updateUrl || '#';
                editForm.querySelector('[data-organization-section-field="department"]').value = button.dataset.department || '';
                editForm.querySelector('[data-organization-section-field="unit_kerja"]').value = button.dataset.unitKerja || '';
                editForm.querySelector('[data-organization-section-field="section"]').value = button.dataset.section || '';
            });
        })();
    </script>
@endpush

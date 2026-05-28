@extends('layouts.dashboard')

@section('title', 'Unit Kerja')
@section('page_title', 'Unit Kerja')

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="page-header">
        <div>
            <h1>Unit Kerja</h1>
            <p>Kelola daftar departemen, unit kerja, dan seksi yang muncul di form QC dan Commissioning.</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('admin.organization-sections.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Tambah Unit Kerja
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Departemen</th>
                    <th>Unit Kerja</th>
                    <th>Seksi</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sections as $section)
                    <tr>
                        <td>{{ $section->department }}</td>
                        <td>{{ $section->unit_kerja }}</td>
                        <td class="fw-semibold">{{ $section->section }}</td>
                        <td>
                            <span class="badge {{ $section->status === 'active' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ $section->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.organization-sections.edit', $section) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
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
                        <td colspan="5" class="text-center text-muted py-4">Belum ada data unit kerja.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $sections->links() }}
    </div>
@endsection

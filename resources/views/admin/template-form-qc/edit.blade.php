@extends('layouts.dashboard')

@section('title', 'Edit Template Form QC')
@section('page_title', 'Template Form QC')

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="page-header">
        <div>
            <h1>Edit Template Form QC</h1>
            <p>{{ $template->name }}</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('admin.template-form-qc.index') }}" class="btn btn-outline-secondary">Kembali</a>
            <a href="{{ route('admin.template-form-qc.preview', $template) }}" class="btn btn-outline-primary">
                <i class="bi bi-eye me-1"></i>Preview
            </a>
            <form action="{{ $template->status === 'active' ? route('admin.template-form-qc.toggle-status', $template) : route('admin.template-form-qc.publish', $template) }}" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn {{ $template->status === 'active' ? 'btn-outline-warning' : 'btn-primary' }}">
                    {{ $template->status === 'active' ? 'Nonaktifkan' : 'Publish / Aktifkan' }}
                </button>
            </form>
        </div>
    </div>

    @include('admin.template-form-qc._form', [
        'action' => route('admin.template-form-qc.update', $template),
        'method' => 'PUT',
        'submitLabel' => 'Simpan Perubahan',
    ])
@endsection

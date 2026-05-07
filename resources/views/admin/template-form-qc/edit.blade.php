@extends('layouts.dashboard')

@section('title', 'Edit Template Form QC')
@section('page_title', 'Template Form QC')

@section('content')
    <div class="page-header">
        <div>
            <h1>Edit Template Form QC</h1>
            <p>{{ $template->name }}</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('admin.template-form-qc.preview', $template) }}" class="btn btn-outline-primary">
                <i class="bi bi-eye me-1"></i>Preview
            </a>
        </div>
    </div>

    @include('admin.template-form-qc._form', [
        'action' => route('admin.template-form-qc.update', $template),
        'method' => 'PUT',
        'submitLabel' => 'Simpan Perubahan',
    ])
@endsection

@extends('layouts.dashboard')

@section('title', 'Edit Template Form Commissioning')
@section('page_title', 'Edit Template Form Commissioning')

@section('content')
    <div class="page-header">
        <div>
            <h1>Edit Template Form Commissioning</h1>
            <p>{{ $template->name }}</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('admin.template-form-commissioning.index') }}" class="btn btn-outline-secondary">Kembali</a>
            <a href="{{ route('admin.template-form-commissioning.preview', $template) }}" class="btn btn-outline-primary">
                <i class="bi bi-eye me-1"></i>Preview
            </a>
            <form action="{{ $template->status === 'active' ? route('admin.template-form-commissioning.toggle-status', $template) : route('admin.template-form-commissioning.publish', $template) }}" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn {{ $template->status === 'active' ? 'btn-outline-warning' : 'btn-primary' }}">
                    {{ $template->status === 'active' ? 'Nonaktifkan' : 'Publish / Aktifkan' }}
                </button>
            </form>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @include('admin.template-form-commissioning.partials.form', [
        'action' => route('admin.template-form-commissioning.update', $template),
        'method' => 'PUT',
        'submitLabel' => 'Update Template',
    ])
@endsection

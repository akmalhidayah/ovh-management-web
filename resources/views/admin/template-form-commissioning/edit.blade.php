@extends('layouts.dashboard')

@section('title', 'Edit Template Form Commissioning')
@section('page_title', 'Edit Template Form Commissioning')

@section('content')
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

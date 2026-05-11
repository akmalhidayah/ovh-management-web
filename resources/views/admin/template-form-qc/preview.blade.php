@extends('layouts.dashboard')

@section('title', 'Preview Template Form QC')
@section('page_title', 'Template Form QC')

@section('content')
    @php
        $statusLabels = ['draft' => 'Perlu Review', 'active' => 'Siap Digunakan', 'inactive' => 'Nonaktif'];
        $statusClasses = ['draft' => 'text-bg-warning', 'active' => 'text-bg-success', 'inactive' => 'text-bg-secondary'];
    @endphp

    <div class="page-header template-preview-header">
        <div>
            <h1>{{ $template->name }}</h1>
            <p>Preview template QC {{ $template->template_type ? 'dengan struktur terarah' : 'dalam mode '.($template->layout_mode === 'excel_grid' ? 'Excel Grid' : 'Block Based') }}.</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('admin.template-form-qc.index') }}" class="btn btn-outline-secondary">Kembali</a>
            <a href="{{ route('admin.template-form-qc.edit', $template) }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil me-1"></i>Edit Template
            </a>
            <form action="{{ $template->status === 'active' ? route('admin.template-form-qc.toggle-status', $template) : route('admin.template-form-qc.publish', $template) }}" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn {{ $template->status === 'active' ? 'btn-outline-warning' : 'btn-primary' }}">
                    {{ $template->status === 'active' ? 'Nonaktifkan' : 'Publish' }}
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

    <div class="content-card">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div class="template-meta">
                <span>{{ $template->code ?: 'Tanpa kode' }}</span>
                <span>Versi {{ $template->version }}</span>
                <span>{{ $template->category ?: 'QC' }}</span>
            </div>
            <span class="badge {{ $statusClasses[$template->status] ?? 'text-bg-secondary' }}">{{ $statusLabels[$template->status] ?? $template->status }}</span>
        </div>

        @if ($template->status === 'draft')
            <div class="alert alert-warning">
                Template ini masih draft. Review dan publish agar bisa digunakan user QC.
            </div>
        @endif

        @if ($template->template_type)
            @include('admin.template-form-qc.partials.fixed-preview', ['template' => $template])
        @elseif ($template->layout_mode === 'excel_grid')
            @include('admin.template-form-qc.partials.excel-grid-preview', ['template' => $template])
        @else
            @include('admin.template-form-qc.partials.block-based-preview', ['template' => $template])
        @endif
    </div>
@endsection

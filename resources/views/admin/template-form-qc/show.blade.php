@extends('layouts.dashboard')

@section('title', 'Detail Template Form QC')
@section('page_title', 'Template Form QC')

@section('content')
    @php
        $statusLabels = ['draft' => 'Draft', 'active' => 'Aktif', 'inactive' => 'Nonaktif'];
        $statusClasses = ['draft' => 'text-bg-warning', 'active' => 'text-bg-success', 'inactive' => 'text-bg-secondary'];
    @endphp

    <div class="page-header">
        <div>
            <h1>{{ $template->name }}</h1>
            <p>{{ $template->description ?: 'Template QC berbasis block.' }}</p>
        </div>
        <div class="page-actions">
            <form action="{{ route('admin.template-form-qc.duplicate', $template) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-copy me-1"></i>Duplicate</button>
            </form>
            <a href="{{ route('admin.template-form-qc.preview', $template) }}" class="btn btn-outline-primary"><i class="bi bi-eye me-1"></i>Preview</a>
            <a href="{{ route('admin.template-form-qc.edit', $template) }}" class="btn btn-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
        </div>
    </div>

    <div class="row g-3 mb-3">
        @foreach ([
            'Kode' => $template->code ?: '-',
            'Kategori' => $template->category ?: '-',
            'Versi' => $template->version,
            'Status' => $statusLabels[$template->status] ?? $template->status,
        ] as $label => $value)
            <div class="col-12 col-md-3">
                <div class="content-card h-100">
                    <div class="text-muted small">{{ $label }}</div>
                    <div class="fw-semibold">
                        @if ($label === 'Status')
                            <span class="badge {{ $statusClasses[$template->status] ?? 'text-bg-secondary' }}">{{ $value }}</span>
                        @else
                            {{ $value }}
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="content-card">
        <div class="card-heading">
            <h2>Struktur Template</h2>
            <span class="text-muted small">{{ $template->blocks->count() }} bagian, {{ $template->fields->count() }} item</span>
        </div>

        <div class="accordion" id="templateBlockAccordion">
            @foreach ($template->blocks as $block)
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#block{{ $block->id }}">
                            {{ $block->order_no }}. {{ $block->title ?: Str::headline($block->type) }}
                            <span class="badge text-bg-light ms-2">{{ $block->type }}</span>
                        </button>
                    </h2>
                    <div id="block{{ $block->id }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#templateBlockAccordion">
                        <div class="accordion-body">
                            <div class="row g-3">
                                <div class="col-12 col-lg-6">
                                    <h3 class="h6">Item</h3>
                                    <ul class="list-group">
                                        @forelse ($block->fields as $field)
                                            <li class="list-group-item d-flex justify-content-between gap-2">
                                                <span>{{ $field->label }}</span>
                                                <span class="badge text-bg-secondary">{{ $field->type }}</span>
                                            </li>
                                        @empty
                                            <li class="list-group-item text-muted">Tidak ada item khusus.</li>
                                        @endforelse
                                    </ul>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <h3 class="h6">Baris Tabel</h3>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered mb-0">
                                            <tbody>
                                                @forelse ($block->tableRows as $row)
                                                    <tr>
                                                        <td class="text-muted">{{ $row->order_no }}</td>
                                                        <td>{{ implode(' | ', array_filter($row->row_data ?? [])) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr><td class="text-muted">Tidak ada row table.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection

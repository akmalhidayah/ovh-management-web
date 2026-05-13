@extends('layouts.dashboard')

@section('title', 'Detail Template Form Commissioning')
@section('page_title', 'Template Form Commissioning')

@section('content')
    @php
        $statusLabels = ['draft' => 'Draft', 'active' => 'Aktif', 'inactive' => 'Nonaktif'];
        $statusClasses = ['draft' => 'text-bg-warning', 'active' => 'text-bg-success', 'inactive' => 'text-bg-secondary'];
        $schema = \App\Support\Commissioning\FixedCommissioningTemplate::normalizeSchema($template->body_schema);
        $rows = $schema['equipment_check_rows'] ?? [];
    @endphp

    <div class="page-header">
        <div>
            <h1>{{ $template->name }}</h1>
            <p>{{ $template->description ?: 'Template commissioning manual.' }}</p>
        </div>
        <div class="page-actions">
            <form action="{{ route('admin.template-form-commissioning.duplicate', $template) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-copy me-1"></i>Duplicate</button>
            </form>
            <a href="{{ route('admin.template-form-commissioning.preview', $template) }}" class="btn btn-outline-primary"><i class="bi bi-eye me-1"></i>Preview</a>
            <a href="{{ route('admin.template-form-commissioning.edit', $template) }}" class="btn btn-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
        </div>
    </div>

    <div class="row g-3 mb-3">
        @foreach ([
            'Kode' => $template->code ?: '-',
            'Kategori' => $template->category ?: '-',
            'Versi' => $template->version,
            'Motor Row' => count($schema['motor_test_rows']),
            'Gearbox Row' => count($schema['gearbox_test_rows']),
            'Status' => $statusLabels[$template->status] ?? $template->status,
        ] as $label => $value)
            <div class="col-12 col-md-2">
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
            <h2>Equipment Check Data</h2>
            <span class="text-muted small">{{ count($rows) }} item check</span>
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 90px;">No</th>
                        <th>Item</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td>{{ $row['no'] ?? $loop->iteration }}</td>
                            <td>{{ $row['item'] ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="text-muted">Tidak ada item check.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

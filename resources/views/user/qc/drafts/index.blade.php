@extends('layouts.user')

@section('title', 'Draft Form QC')

@section('content')
    <x-user.page-header title="Draft Form QC" subtitle="Lanjutkan draft form QC yang sudah disimpan." eyebrow="Draft Workspace">
        <a href="{{ route('user.qc.forms.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Buat Form Baru
        </a>
    </x-user.page-header>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <section class="inspector-panel">
        <div class="table-responsive">
            <table class="table align-middle inspector-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kategori</th>
                        <th>Jenis Form</th>
                        <th>Equipment</th>
                        <th>Plant</th>
                        <th>Area</th>
                        <th>Terakhir Diubah</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($submissions as $submission)
                        <tr>
                            <td>{{ $submissions->firstItem() + $loop->index }}</td>
                            <td>{{ $submission->template?->category ?: 'QC' }}</td>
                            <td>
                                <div>{{ $submission->template?->name ?: '-' }}</div>
                                <small class="text-muted">{{ $submission->form_number ?: '-' }}</small>
                            </td>
                            <td>{{ $submission->equipment ?: '-' }}</td>
                            <td>{{ $submission->plant ?: '-' }}</td>
                            <td>{{ $submission->area ?: '-' }}</td>
                            <td>{{ $submission->updated_at?->format('d M Y H:i') }}</td>
                            <td><x-user.status-badge :status="$statusLabels[$submission->status] ?? $submission->status" /></td>
                            <td class="text-end">
                                <div class="d-inline-flex flex-wrap justify-content-end gap-2">
                                    <a href="{{ route('user.qc.submissions.show', $submission) }}" class="btn btn-sm btn-primary">Preview</a>
                                    <a href="{{ route('user.qc.forms.create', ['template' => $submission->qc_form_template_id]) }}" class="btn btn-sm btn-outline-secondary">Lanjutkan</a>
                                    <form method="POST" action="{{ route('user.qc.submissions.destroy', $submission) }}" onsubmit="return confirm('Hapus draft ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">Belum ada draft QC.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $submissions->links() }}
        </div>
    </section>
@endsection

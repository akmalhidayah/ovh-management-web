@extends('layouts.user')

@section('title', 'Riwayat QC')

@section('content')
    <x-user.page-header title="Riwayat QC" subtitle="Daftar form QC yang sudah disubmit." eyebrow="History Workspace" />

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <section class="inspector-panel">
        <div class="table-responsive">
            <table class="table align-middle inspector-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal Submit</th>
                        <th>No Form</th>
                        <th>Kategori</th>
                        <th>Jenis Form</th>
                        <th>Equipment</th>
                        <th>Plant</th>
                        <th>Area</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($submissions as $submission)
                        <tr>
                            <td>{{ $submissions->firstItem() + $loop->index }}</td>
                            <td>{{ $submission->submitted_at?->format('d M Y H:i') ?: '-' }}</td>
                            <td>{{ $submission->form_number }}</td>
                            <td>{{ $submission->template?->category ?: 'QC' }}</td>
                            <td>{{ $submission->template?->name ?: '-' }}</td>
                            <td>{{ $submission->equipment ?: '-' }}</td>
                            <td>{{ $submission->plant ?: '-' }}</td>
                            <td>{{ $submission->area ?: '-' }}</td>
                            <td><x-user.status-badge :status="$statusLabels[$submission->status] ?? $submission->status" /></td>
                            <td class="text-end">
                                <div class="d-inline-flex flex-wrap justify-content-end gap-2">
                                    <a href="{{ route('user.qc.submissions.show', $submission) }}" class="btn btn-sm btn-primary">Detail</a>
                                    <a href="{{ route('user.qc.submissions.pdf', $submission) }}" class="btn btn-sm btn-outline-secondary" target="_blank">PDF</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">Belum ada form QC yang disubmit.</td>
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

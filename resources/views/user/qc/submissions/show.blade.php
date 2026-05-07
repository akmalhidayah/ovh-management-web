@extends('layouts.user')

@section('title', 'Detail Form QC')

@section('content')
    <x-user.page-header title="Detail Form QC" subtitle="Preview data form QC yang tersimpan." eyebrow="Submission Detail">
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('user.qc.submissions.pdf', $submission) }}" target="_blank" class="btn btn-primary">
                <i class="bi bi-file-earmark-pdf me-2"></i>Download PDF
            </a>
            <a href="{{ $submission->status === 'draft' ? route('user.qc.drafts.index') : route('user.qc.history.index') }}" class="btn btn-outline-secondary">Kembali</a>
        </div>
    </x-user.page-header>

    @include('qc-submissions._detail', ['submission' => $submission, 'statusLabels' => $statusLabels])
@endsection

@extends('layouts.dashboard')

@section('title', 'Import Template Form QC')
@section('page_title', 'Template Form QC')

@section('content')
    <div class="page-header">
        <div>
            <h1>Import dari Excel</h1>
            <p>Import Excel hanya membuat draft template. Admin tetap perlu mereview hasil generate sebelum template bisa digunakan.</p>
        </div>
    </div>

    <div class="import-steps content-card">
        @foreach (['Upload Excel', 'Generate Draft', 'Review Template', 'Preview', 'Publish'] as $index => $step)
            <div class="import-step {{ $index === 0 ? 'active' : '' }}">
                <span>{{ $index + 1 }}</span>
                <strong>{{ $step }}</strong>
            </div>
        @endforeach
    </div>

    <div class="content-card">
        <form action="{{ route('admin.template-form-qc.import.process') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="upload-panel upload-panel-large mb-3">
                <i class="bi bi-file-earmark-excel"></i>
                <div>
                    <label class="form-label fw-semibold">Upload File Template QC Lama</label>
                    <input type="file" name="excel_file" class="form-control form-control-lg @error('excel_file') is-invalid @enderror" accept=".xlsx,.xls" required>
                    @error('excel_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Format didukung: .xlsx dan .xls, maksimal 10 MB.</div>
                </div>
            </div>

            <div class="alert alert-warning">
                Format Excel lama bisa memakai merged cell dan susunan tabel yang berbeda. Hasil import mungkin perlu dirapikan di halaman review sebelum dipublish.
            </div>

            <div class="alert alert-info">
                Sistem akan mencoba mendeteksi tabel dengan header No, Aktivitas/Aktifitas, Standar, Aktual, dan Keterangan. Template hasil import selalu dibuat sebagai draft.
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.template-form-qc.index') }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-magic me-1"></i>Generate Draft Template
                </button>
            </div>
        </form>
    </div>
@endsection

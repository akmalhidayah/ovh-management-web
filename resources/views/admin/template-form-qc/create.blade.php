@extends('layouts.dashboard')

@section('title', 'Buat Template Form QC')
@section('page_title', 'Template Form QC')

@section('content')
    <div class="page-header">
        <div>
            <h1>Buat Template Form QC</h1>
            <p>Bangun template berbasis block untuk mengikuti format dokumen QC lama.</p>
        </div>
    </div>

    @include('admin.template-form-qc._form', [
        'action' => route('admin.template-form-qc.store'),
        'submitLabel' => 'Simpan Template',
    ])
@endsection

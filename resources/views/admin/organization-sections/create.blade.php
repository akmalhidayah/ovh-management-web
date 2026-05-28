@extends('layouts.dashboard')

@section('title', 'Tambah Unit Kerja')
@section('page_title', 'Tambah Unit Kerja')

@section('content')
    <div class="page-header">
        <div>
            <h1>Tambah Unit Kerja</h1>
            <p>Tambahkan departemen, unit kerja, dan seksi untuk pilihan form QC dan Commissioning.</p>
        </div>
    </div>

    <form action="{{ route('admin.organization-sections.store') }}" method="POST">
        @include('admin.organization-sections._form')
    </form>
@endsection

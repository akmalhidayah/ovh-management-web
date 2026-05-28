@extends('layouts.dashboard')

@section('title', 'Edit Unit Kerja')
@section('page_title', 'Edit Unit Kerja')

@section('content')
    <div class="page-header">
        <div>
            <h1>Edit Unit Kerja</h1>
            <p>Perbarui departemen, unit kerja, seksi, atau status data.</p>
        </div>
    </div>

    <form action="{{ route('admin.organization-sections.update', $section) }}" method="POST">
        @include('admin.organization-sections._form', ['method' => 'PUT'])
    </form>
@endsection

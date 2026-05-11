@extends('layouts.dashboard')

@section('title', 'Buat Template Form Commissioning')
@section('page_title', 'Buat Template Form Commissioning')

@section('content')
    @include('admin.template-form-commissioning.partials.form', [
        'action' => route('admin.template-form-commissioning.store'),
        'method' => null,
        'submitLabel' => 'Simpan Template',
    ])
@endsection

@extends('layouts.dashboard')
@section('title', 'Pengadaan')
@section('page_title', 'Pengadaan')
@if (in_array(($section ?? 'barang'), ['barang', 'jasa'], true))
    @section('hide_breadcrumb', '1')
@endif
@section('content')
    @include('modules.procurement-content')
@endsection

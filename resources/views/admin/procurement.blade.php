@extends('layouts.dashboard')
@section('title', 'Pengadaan')
@section('page_title', 'Pengadaan')
@if (($section ?? 'barang') === 'barang')
    @section('hide_breadcrumb', '1')
@endif
@section('content')
    @include('modules.procurement-content')
@endsection

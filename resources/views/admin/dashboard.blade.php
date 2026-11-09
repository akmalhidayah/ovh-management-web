@extends('layouts.dashboard')
@section('title', 'Dashboard Admin')
@section('page_title', 'Dashboard')
@section('hide_breadcrumb', true)
@section('content')
    @include('modules.dashboard-content')
@endsection

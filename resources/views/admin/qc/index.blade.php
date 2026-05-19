@extends('layouts.dashboard')

@section('title', $pageTitle ?? 'QC & Commissioning')
@section('page_title', $pageTitle ?? 'QC & Commissioning')

@section('content')
    @include('modules.qc-content')
@endsection

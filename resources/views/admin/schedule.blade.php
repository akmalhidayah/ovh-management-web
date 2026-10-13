@php
    $workType = request()->query('work_type', 'barang');
    $workTypeLabel = match ($workType) {
        'jasa' => 'Rencana Kerja Jasa',
        'capex' => 'Rencana Kerja Capex',
        default => 'Rencana Kerja Barang',
    };
@endphp

@extends('layouts.dashboard')
@section('title', $workTypeLabel)
@section('page_title', $workTypeLabel)
@section('breadcrumb_parent', 'Rencana Kerja')
@section('content')
    @include('modules.schedule-content', [
        'workType' => $workType,
        'workTypeLabel' => $workTypeLabel,
    ])
@endsection

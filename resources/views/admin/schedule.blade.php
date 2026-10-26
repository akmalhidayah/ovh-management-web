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
@if (in_array($workType, ['barang', 'jasa', 'capex'], true))
    @section('hide_breadcrumb', '1')
@endif
@section('content')
    @include('modules.schedule-content', [
        'workType' => $workType,
        'workTypeLabel' => $workTypeLabel,
    ])
@endsection

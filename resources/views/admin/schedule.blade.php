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
@if ($workType === 'barang')
    @section('hide_breadcrumb', '1')
@endif
@if ($workType !== 'barang')
    @section('breadcrumb_actions')
        <button class="btn btn-primary work-plan-update-btn" type="button">
            <i class="bi bi-plus-lg"></i>
            <span>Tambah Rencana</span>
        </button>
    @endsection
@endif
@section('content')
    @include('modules.schedule-content', [
        'workType' => $workType,
        'workTypeLabel' => $workTypeLabel,
    ])
@endsection

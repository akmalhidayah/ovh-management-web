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
@section('content')
    @include('modules.schedule-content', ['workTypeLabel' => $workTypeLabel])
@endsection

@php
    $requestedWorkType = $workType ?? 'barang';
    $workType = in_array($requestedWorkType, ['barang', 'jasa', 'capex'], true) ? $requestedWorkType : 'barang';
@endphp

@if ($workType === 'barang')
    @include('modules.schedule-barang.tabs')
@elseif ($workType === 'jasa')
    @include('modules.schedule-jasa.tabs')
@else
    @include('modules.schedule-capex.tabs')
@endif

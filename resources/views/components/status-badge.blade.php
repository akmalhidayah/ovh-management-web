@props(['status' => 'Draft'])

@php
    $map = [
        'Draft' => 'secondary',
        'Proses' => 'info',
        'Selesai' => 'success',
        'Terlambat' => 'danger',
        'Open' => 'warning',
        'Closed' => 'success',
    ];
@endphp

<span class="badge rounded-pill status-badge text-bg-{{ $map[$status] ?? 'secondary' }}">{{ $status }}</span>

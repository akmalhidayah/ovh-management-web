@props(['status'])

@php
    $classes = match ($status) {
        'Terjadwal', 'Dalam Proses' => 'is-primary',
        'Draft', 'Menunggu' => 'is-muted',
        'Menunggu Data', 'Menunggu Review', 'Menunggu Approval', 'Follow Up', 'Perlu Review' => 'is-warning',
        'Perlu Revisi', 'Ditolak', 'Not OK' => 'is-danger',
        'Disetujui', 'Selesai', 'OK', 'Tersimpan' => 'is-success',
        default => 'is-secondary',
    };
@endphp

<span {{ $attributes->class(['inspector-status-badge', $classes]) }}>{{ $status }}</span>

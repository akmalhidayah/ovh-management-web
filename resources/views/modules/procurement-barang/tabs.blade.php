@php
    $requestedProcurementBarangTab = request()->query('tab', 'dashboard');
    $procurementBarangTabs = [
        'dashboard' => 'Beranda',
        'pengeluaran' => 'Pengeluaran Barang',
        'siap' => 'Siap',
        'pengiriman' => 'Pengiriman',
        'pengadaan' => 'Pengadaan',
        'dokumen' => 'Dokumen & Permintaan Pembelian',
        'tertunda' => 'Tertunda',
        'dibatalkan' => 'Dibatalkan',
        'semua' => 'Tampilkan Semua',
    ];
    $procurementBarangTab = is_string($requestedProcurementBarangTab) && array_key_exists($requestedProcurementBarangTab, $procurementBarangTabs)
        ? $requestedProcurementBarangTab
        : 'dashboard';
@endphp

<div class="procurement-goods" data-procurement-goods>
    <nav class="procurement-goods-tabs" aria-label="Navigasi Pengadaan Barang">
        @foreach ($procurementBarangTabs as $tabKey => $tabLabel)
            <a
                href="{{ route('admin.procurement.barang', ['tab' => $tabKey]) }}"
                class="procurement-goods-tab {{ $procurementBarangTab === $tabKey ? 'is-active' : '' }}"
                @if ($procurementBarangTab === $tabKey) aria-current="page" @endif
            >{{ $tabLabel }}</a>
        @endforeach
    </nav>

    <div class="procurement-goods-content">
        @if ($procurementBarangTab === 'dashboard')
            @include('modules.procurement-barang.dashboard')
        @else
            @include('modules.procurement-barang.list', ['listMode' => $procurementBarangTab])
        @endif
    </div>
</div>

@include('modules.procurement-barang.assets')

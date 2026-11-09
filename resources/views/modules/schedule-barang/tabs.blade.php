@php
    $sb = \App\Support\ScheduleBarangPageData::make();
    $sbTabs = ['dashboard' => 'Dashboard', 'disetujui' => 'Disetujui', 'dibatalkan' => 'Dibatalkan', 'rekap' => 'Rekap Ajuan'];
    $requestedTab = request()->query('tab', 'dashboard');
    $sbTab = is_string($requestedTab) && array_key_exists($requestedTab, $sbTabs) ? $requestedTab : 'dashboard';
@endphp

@push('styles')
    <link href="{{ asset('assets/css/schedule-barang.css') }}?v={{ file_exists(public_path('assets/css/schedule-barang.css')) ? filemtime(public_path('assets/css/schedule-barang.css')) : time() }}" rel="stylesheet">
@endpush

<div class="sb" data-sb data-initial-tab="{{ $sbTab }}">
    <nav class="sb-tabs" role="tablist" aria-label="Navigasi Rencana Kerja Barang">
        @foreach ($sbTabs as $key => $label)
            <button type="button" role="tab" id="sb-tab-{{ $key }}" class="sb-tab {{ $sbTab === $key ? 'is-active' : '' }}"
                    aria-selected="{{ $sbTab === $key ? 'true' : 'false' }}" aria-controls="sb-panel-{{ $key }}" data-sb-tab="{{ $key }}">{{ $label }}</button>
        @endforeach
    </nav>

    <form class="sb-filter" data-sb-filter onsubmit="return false">
        <div class="sb-filter-fields">
            <label class="sb-field">
                <span>Tahun</span>
                <select data-sb-year aria-label="Tahun">
                    @foreach ($sb['years'] as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
            </label>
            <label class="sb-field">
                <span>Plant</span>
                <select data-sb-plant aria-label="Plant">
                    <option value="">Semua Plant</option>
                    @foreach ($sb['plants'] as $plant)
                        <option value="{{ $plant }}">{{ $plant }}</option>
                    @endforeach
                </select>
            </label>
            <label class="sb-field sb-field--search">
                <span>Cari</span>
                <input type="search" data-sb-search placeholder="Cari Unit Kerja/Status Ajuan/Pekerjaan..." autocomplete="off">
            </label>
        </div>

        <div class="sb-filter-actions">
            <span class="sb-updated">Data diperbarui <time data-sb-updated>{{ $sb['updatedAt'] }}</time></span>
            <button type="button" class="sb-btn sb-btn--navy" data-sb-refresh data-sb-show="dashboard"><i class="bi bi-arrow-repeat" aria-hidden="true"></i> Segarkan</button>
            <button type="button" class="sb-btn sb-btn--navy" data-sb-add data-sb-show="list"><i class="bi bi-plus-lg" aria-hidden="true"></i> Tambah Ajuan</button>
            <span class="sb-form-actions" data-sb-show="form" hidden>
                <button type="button" class="sb-btn sb-btn--green" data-sb-export><i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i> Export Excel</button>
                <button type="button" class="sb-btn sb-btn--light" data-sb-import><i class="bi bi-upload" aria-hidden="true"></i> Import</button>
                <button type="button" class="sb-btn sb-btn--light" data-sb-print><i class="bi bi-printer" aria-hidden="true"></i> Cetak</button>
                <button type="button" class="sb-btn sb-btn--navy" data-sb-new><i class="bi bi-plus-lg" aria-hidden="true"></i> Tambahkan</button>
            </span>
            <input type="file" accept=".xlsx,.xls,.csv" data-sb-import-input hidden>
        </div>
    </form>

    <div role="tabpanel" id="sb-panel-dashboard" aria-labelledby="sb-tab-dashboard" data-sb-panel="dashboard" @if ($sbTab !== 'dashboard') hidden @endif>
        @include('modules.schedule-barang.dashboard')
    </div>

    @foreach (['disetujui', 'dibatalkan'] as $key)
        <div role="tabpanel" id="sb-panel-{{ $key }}" aria-labelledby="sb-tab-{{ $key }}" data-sb-panel="{{ $key }}" @if ($sbTab !== $key) hidden @endif>
            @include('modules.schedule-barang.ajuan-list', ['listKey' => $key])
        </div>
    @endforeach

    <div role="tabpanel" id="sb-panel-rekap" aria-labelledby="sb-tab-rekap" data-sb-panel="rekap" @if ($sbTab !== 'rekap') hidden @endif>
        <div data-sb-rekap-list>
            @include('modules.schedule-barang.ajuan-list', ['listKey' => 'rekap'])
        </div>
        <div data-sb-rekap-form hidden>
            @include('modules.schedule-barang.ajuan-form')
        </div>
    </div>

    @include('modules.schedule-barang.modal-edit')
    @include('modules.schedule-barang.modal-note')
</div>

@push('scripts')
    <script>window.SB_DATA = {{ \Illuminate\Support\Js::from(['rows' => $sb['rows']]) }};</script>
    <script src="{{ asset('assets/js/schedule-barang.js') }}?v={{ file_exists(public_path('assets/js/schedule-barang.js')) ? filemtime(public_path('assets/js/schedule-barang.js')) : time() }}"></script>
@endpush

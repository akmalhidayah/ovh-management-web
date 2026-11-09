@php
    $sc = \App\Support\ScheduleCapexPageData::make();
    $scTabs = ['dashboard' => 'Dashboard', 'disetujui' => 'Disetujui', 'dibatalkan' => 'Dibatalkan', 'rekap' => 'Rekap Ajuan'];
    $requestedTab = request()->query('tab', 'dashboard');
    $scTab = is_string($requestedTab) && array_key_exists($requestedTab, $scTabs) ? $requestedTab : 'dashboard';
@endphp

@push('styles')
    <link href="{{ asset('assets/css/schedule-capex.css') }}?v={{ file_exists(public_path('assets/css/schedule-capex.css')) ? filemtime(public_path('assets/css/schedule-capex.css')) : time() }}" rel="stylesheet">
@endpush

<div class="sc" data-sc data-initial-tab="{{ $scTab }}">
    <nav class="sc-tabs" role="tablist" aria-label="Navigasi Rencana Kerja Capex">
        @foreach ($scTabs as $key => $label)
            <button type="button" role="tab" id="sc-tab-{{ $key }}" class="sc-tab {{ $scTab === $key ? 'is-active' : '' }}"
                    aria-selected="{{ $scTab === $key ? 'true' : 'false' }}" aria-controls="sc-panel-{{ $key }}" data-sc-tab="{{ $key }}">{{ $label }}</button>
        @endforeach
    </nav>

    <form class="sc-filter" data-sc-filter onsubmit="return false">
        <div class="sc-filter-fields">
            <label class="sc-field">
                <span>Tahun</span>
                <select data-sc-year aria-label="Tahun">
                    @foreach ($sc['years'] as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
            </label>
            <label class="sc-field">
                <span>Plant</span>
                <select data-sc-plant aria-label="Plant">
                    <option value="">Semua Plant</option>
                    @foreach ($sc['plants'] as $plant)
                        <option value="{{ $plant }}">{{ $plant }}</option>
                    @endforeach
                </select>
            </label>
            <label class="sc-field sc-field--search">
                <span>Cari</span>
                <input type="search" data-sc-search placeholder="Cari Unit Kerja/Status Ajuan/Pekerjaan..." autocomplete="off">
            </label>
        </div>

        <div class="sc-filter-actions">
            <span class="sc-updated">Data diperbarui <time data-sc-updated>{{ $sc['updatedAt'] }}</time></span>
            <button type="button" class="sc-btn sc-btn--navy" data-sc-refresh data-sc-show="dashboard"><i class="bi bi-arrow-repeat" aria-hidden="true"></i> Perbarui</button>
            <button type="button" class="sc-btn sc-btn--navy" data-sc-add data-sc-show="list"><i class="bi bi-plus-lg" aria-hidden="true"></i> Tambah Ajuan</button>
            <span class="sc-form-actions" data-sc-show="form" hidden>
                <button type="button" class="sc-btn sc-btn--green" data-sc-export><i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i> Export Excel</button>
                <button type="button" class="sc-btn sc-btn--light" data-sc-import><i class="bi bi-upload" aria-hidden="true"></i> Import</button>
                <button type="button" class="sc-btn sc-btn--light" data-sc-print><i class="bi bi-printer" aria-hidden="true"></i> Cetak</button>
                <button type="button" class="sc-btn sc-btn--navy" data-sc-new><i class="bi bi-plus-lg" aria-hidden="true"></i> Tambahkan</button>
            </span>
            <input type="file" accept=".xlsx,.xls,.csv" data-sc-import-input hidden>
        </div>
    </form>

    <div role="tabpanel" id="sc-panel-dashboard" aria-labelledby="sc-tab-dashboard" data-sc-panel="dashboard" @if ($scTab !== 'dashboard') hidden @endif>
        @include('modules.schedule-capex.dashboard')
    </div>

    @foreach (['disetujui', 'dibatalkan'] as $key)
        <div role="tabpanel" id="sc-panel-{{ $key }}" aria-labelledby="sc-tab-{{ $key }}" data-sc-panel="{{ $key }}" @if ($scTab !== $key) hidden @endif>
            @include('modules.schedule-capex.ajuan-list', ['listKey' => $key])
        </div>
    @endforeach

    <div role="tabpanel" id="sc-panel-rekap" aria-labelledby="sc-tab-rekap" data-sc-panel="rekap" @if ($scTab !== 'rekap') hidden @endif>
        <div data-sc-rekap-list>
            @include('modules.schedule-capex.ajuan-list', ['listKey' => 'rekap'])
        </div>
        <div data-sc-rekap-form hidden>
            @include('modules.schedule-capex.ajuan-form')
        </div>
    </div>

    @include('modules.schedule-capex.modal-edit')
    @include('modules.schedule-capex.modal-note')
</div>

@push('scripts')
    <script>window.SC_DATA = {{ \Illuminate\Support\Js::from(['rows' => $sc['rows']]) }};</script>
    <script src="{{ asset('assets/js/schedule-capex.js') }}?v={{ file_exists(public_path('assets/js/schedule-capex.js')) ? filemtime(public_path('assets/js/schedule-capex.js')) : time() }}"></script>
@endpush

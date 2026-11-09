@php
    $sj = \App\Support\ScheduleJasaPageData::make();
    $sjTabs = ['dashboard' => 'Dashboard', 'disetujui' => 'Disetujui', 'intern' => 'Dikerjakan Intern', 'dibatalkan' => 'Dibatalkan', 'rekap' => 'Rekap Ajuan'];
    $requestedTab = request()->query('tab', 'dashboard');
    $sjTab = is_string($requestedTab) && array_key_exists($requestedTab, $sjTabs) ? $requestedTab : 'dashboard';
@endphp

@push('styles')
    <link href="{{ asset('assets/css/schedule-jasa.css') }}?v={{ file_exists(public_path('assets/css/schedule-jasa.css')) ? filemtime(public_path('assets/css/schedule-jasa.css')) : time() }}" rel="stylesheet">
@endpush

<div class="sj" data-sj data-initial-tab="{{ $sjTab }}">
    @include('modules.schedule-jasa.hero')

    <nav class="sj-tabs" role="tablist" aria-label="Navigasi Rencana Kerja Jasa">
        @foreach ($sjTabs as $key => $label)
            <button type="button" role="tab" id="sj-tab-{{ $key }}" class="sj-tab {{ $sjTab === $key ? 'is-active' : '' }}"
                    aria-selected="{{ $sjTab === $key ? 'true' : 'false' }}" aria-controls="sj-panel-{{ $key }}" data-sj-tab="{{ $key }}">{{ $label }}</button>
        @endforeach
    </nav>

    <form class="sj-filter" data-sj-filter onsubmit="return false">
        <div class="sj-filter-fields">
            <label class="sj-field">
                <span>Tahun</span>
                <select data-sj-year aria-label="Tahun">
                    @foreach ($sj['years'] as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
            </label>
            <label class="sj-field">
                <span>Plant</span>
                <select data-sj-plant aria-label="Plant">
                    <option value="">Semua Plant</option>
                    @foreach ($sj['plants'] as $plant)
                        <option value="{{ $plant }}">{{ $plant }}</option>
                    @endforeach
                </select>
            </label>
            <label class="sj-field sj-field--search">
                <span>Cari</span>
                <input type="search" data-sj-search placeholder="Cari Unit Kerja/Status Ajuan/Pekerjaan..." autocomplete="off">
            </label>
        </div>

        <div class="sj-filter-actions">
            <span class="sj-updated">Data diperbarui <time data-sj-updated>{{ $sj['updatedAt'] }}</time></span>
            <button type="button" class="sj-btn sj-btn--navy" data-sj-refresh data-sj-show="dashboard"><i class="bi bi-arrow-repeat" aria-hidden="true"></i> Perbarui</button>
            <button type="button" class="sj-btn sj-btn--navy" data-sj-add data-sj-show="list"><i class="bi bi-plus-lg" aria-hidden="true"></i> Tambah Ajuan</button>
            <span class="sj-form-actions" data-sj-show="form" hidden>
                <button type="button" class="sj-btn sj-btn--green" data-sj-export><i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i> Export Excel</button>
                <button type="button" class="sj-btn sj-btn--light" data-sj-import><i class="bi bi-upload" aria-hidden="true"></i> Import</button>
                <button type="button" class="sj-btn sj-btn--light" data-sj-print><i class="bi bi-printer" aria-hidden="true"></i> Cetak</button>
                <button type="button" class="sj-btn sj-btn--navy" data-sj-new><i class="bi bi-plus-lg" aria-hidden="true"></i> Tambahkan</button>
            </span>
            <input type="file" accept=".xlsx,.xls,.csv" data-sj-import-input hidden>
        </div>
    </form>

    <div role="tabpanel" id="sj-panel-dashboard" aria-labelledby="sj-tab-dashboard" data-sj-panel="dashboard" @if ($sjTab !== 'dashboard') hidden @endif>
        @include('modules.schedule-jasa.dashboard')
    </div>

    @foreach (['disetujui', 'intern', 'dibatalkan'] as $key)
        <div role="tabpanel" id="sj-panel-{{ $key }}" aria-labelledby="sj-tab-{{ $key }}" data-sj-panel="{{ $key }}" @if ($sjTab !== $key) hidden @endif>
            @include('modules.schedule-jasa.ajuan-list', ['listKey' => $key])
        </div>
    @endforeach

    <div role="tabpanel" id="sj-panel-rekap" aria-labelledby="sj-tab-rekap" data-sj-panel="rekap" @if ($sjTab !== 'rekap') hidden @endif>
        <div data-sj-rekap-list>
            @include('modules.schedule-jasa.ajuan-list', ['listKey' => 'rekap'])
        </div>
        <div data-sj-rekap-form hidden>
            @include('modules.schedule-jasa.ajuan-form')
        </div>
    </div>

    @include('modules.schedule-jasa.modal-edit')
    @include('modules.schedule-jasa.modal-note')
</div>

@push('scripts')
    <script>window.SJ_DATA = {{ \Illuminate\Support\Js::from(['rows' => $sj['rows']]) }};</script>
    <script src="{{ asset('assets/js/schedule-jasa.js') }}?v={{ file_exists(public_path('assets/js/schedule-jasa.js')) ? filemtime(public_path('assets/js/schedule-jasa.js')) : time() }}"></script>
@endpush

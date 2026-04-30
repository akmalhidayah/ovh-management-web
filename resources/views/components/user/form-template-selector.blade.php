@props([
    'catalog' => [],
    'defaultCategory' => 'QC',
    'defaultTemplate' => null,
    'generalInfo' => [],
    'summary' => [],
])

@php
    $templateOptions = $catalog[$defaultCategory] ?? [];
    $currentUser = auth()->user();
    $profilePhotoUrl = $currentUser?->profilePhotoUrl();
@endphp

<section class="inspector-panel inspector-template-panel" data-template-selector>
    <script type="application/json" data-template-catalog>@json($catalog)</script>
    <input type="hidden" value="{{ $defaultCategory }}" data-template-category>

    @if (count($catalog) > 1)
        <div class="inspector-template-tabs mb-4">
            @foreach (array_keys($catalog) as $category)
                <button type="button" class="btn {{ $category === $defaultCategory ? 'active' : '' }}" data-template-tab data-category="{{ $category }}">
                    {{ $category }}
                </button>
            @endforeach
        </div>
    @endif

    <div class="row g-4">
        <div class="col-xl-5">
            <label class="form-label">Jenis form</label>
            <div class="user-template-select" data-template-combobox>
                <button type="button" class="user-template-control" aria-expanded="false" data-template-toggle>
                    <i class="bi bi-search"></i>
                    <span data-template-current>{{ $defaultTemplate }}</span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </button>
                <div class="user-template-dropdown" hidden data-template-dropdown>
                    <div class="inspector-search search-static">
                        <i class="bi bi-search"></i>
                        <input type="search" class="form-control" placeholder="Cari jenis form..." autocomplete="off" data-template-search>
                    </div>
                    <div class="user-template-options" data-template-options>
                        @foreach ($templateOptions as $option)
                            <button type="button" class="user-template-option {{ $option === $defaultTemplate ? 'active' : '' }}" value="{{ $option }}" data-template-option>
                                {{ $option }}
                            </button>
                        @endforeach
                    </div>
                </div>
                <select class="d-none" data-template-select>
                    @foreach ($templateOptions as $option)
                        <option value="{{ $option }}" @selected($option === $defaultTemplate)>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Tahun</label>
                    <input type="text" class="form-control" value="{{ $generalInfo['year'] ?? '' }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Plant</label>
                    <input type="text" class="form-control" value="{{ $generalInfo['plant'] ?? '' }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Area</label>
                    <input type="text" class="form-control" value="{{ $generalInfo['area'] ?? '' }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Equipment</label>
                    <input type="text" class="form-control" value="{{ $generalInfo['equipment'] ?? '' }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ $generalInfo['date_label'] ?? 'Tanggal' }}</label>
                    <input type="date" class="form-control" value="{{ $generalInfo['inspection_date'] ?? '' }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Quality Control Personil</label>
                    <div class="personnel-field">
                        <span class="personnel-avatar">
                            @if ($profilePhotoUrl)
                                <img src="{{ $profilePhotoUrl }}" alt="{{ $currentUser->name }}">
                            @else
                                {{ strtoupper(substr($currentUser->name ?? 'U', 0, 1)) }}
                            @endif
                        </span>
                        <div>
                            <strong>{{ $currentUser->name ?? ($generalInfo['inspector'] ?? '-') }}</strong>
                            <small>{{ $currentUser->email ?? 'Quality Control Personil' }}</small>
                        </div>
                        <input type="hidden" value="{{ $currentUser->name ?? ($generalInfo['inspector'] ?? '') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-column flex-sm-row gap-3 mt-4">
        <button type="button" class="btn btn-primary btn-lg" data-show-form>
            <i class="bi bi-check2-square me-2"></i>Pilih Form
        </button>
    </div>
</section>

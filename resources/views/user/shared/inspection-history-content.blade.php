<x-user.page-header :title="$title" :subtitle="$subtitle" eyebrow="History Workspace" />

<x-user.filter-card title="Filter Riwayat" subtitle="Filter dummy untuk mensimulasikan pencarian histori berdasarkan tahun, form, plant, area, dan status.">
    <div class="row g-3">
        <div class="col-md-6 col-xl-2"><label class="form-label">Tahun</label><select class="form-select">@foreach ($filters['years'] as $option)<option>{{ $option }}</option>@endforeach</select></div>
        <div class="col-md-6 col-xl-2"><label class="form-label">Kategori</label><select class="form-select">@foreach ($filters['categories'] as $option)<option>{{ $option }}</option>@endforeach</select></div>
        <div class="col-md-6 col-xl-3"><label class="form-label">Jenis Form</label><select class="form-select">@foreach ($filters['form_types'] as $option)<option>{{ $option }}</option>@endforeach</select></div>
        <div class="col-md-6 col-xl-2"><label class="form-label">Plant</label><select class="form-select">@foreach ($filters['plants'] as $option)<option>{{ $option }}</option>@endforeach</select></div>
        <div class="col-md-6 col-xl-2"><label class="form-label">Area</label><select class="form-select">@foreach ($filters['areas'] as $option)<option>{{ $option }}</option>@endforeach</select></div>
        <div class="col-md-6 col-xl-1"><label class="form-label">Status</label><select class="form-select">@foreach ($filters['statuses'] as $option)<option>{{ $option }}</option>@endforeach</select></div>
    </div>
</x-user.filter-card>

<section class="inspector-panel">
    <x-user.history-table :rows="$rows" :pdf-asset="$pdfAsset" type="history" />
</section>

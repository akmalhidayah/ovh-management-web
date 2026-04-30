<x-user.page-header :title="$title" :subtitle="$subtitle" eyebrow="Document Center">
    <a href="{{ route($roleUi['nav'][1]['route'] ?? $roleUi['nav'][0]['route']) }}" class="btn btn-outline-primary">
        <i class="bi bi-cloud-upload me-2"></i>Tambah dari Workspace
    </a>
</x-user.page-header>

<x-user.filter-card title="Filter Dokumen" subtitle="Filter dummy untuk menelusuri dokumen dan foto pendukung.">
    <div class="row g-3">
        <div class="col-md-6 col-xl-2"><label class="form-label">Tahun</label><select class="form-select">@foreach ($filters['years'] as $option)<option>{{ $option }}</option>@endforeach</select></div>
        <div class="col-md-6 col-xl-2"><label class="form-label">Kategori Form</label><select class="form-select">@foreach ($filters['categories'] as $option)<option>{{ $option }}</option>@endforeach</select></div>
        <div class="col-md-6 col-xl-3"><label class="form-label">Jenis Form</label><select class="form-select">@foreach ($filters['form_types'] as $option)<option>{{ $option }}</option>@endforeach</select></div>
        <div class="col-md-6 col-xl-2"><label class="form-label">Equipment</label><select class="form-select">@foreach ($filters['equipment'] as $option)<option>{{ $option }}</option>@endforeach</select></div>
        <div class="col-md-6 col-xl-2"><label class="form-label">Plant</label><select class="form-select">@foreach ($filters['plants'] as $option)<option>{{ $option }}</option>@endforeach</select></div>
        <div class="col-md-6 col-xl-1"><label class="form-label">Tipe</label><select class="form-select">@foreach ($filters['document_types'] as $option)<option>{{ $option }}</option>@endforeach</select></div>
    </div>
</x-user.filter-card>

<section class="inspector-panel">
    <x-user.document-list :rows="$rows" />
</section>

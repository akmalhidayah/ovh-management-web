@extends('layouts.user')

@section('title', 'Buat Form Commissioning')

@section('content')
    <div class="user-simple-form-header qc-template-form-heading">
        <div>
            <h1>Buat Form Commissioning</h1>
            <p>Pilih template aktif, isi header dari master data, lalu lengkapi body commissioning.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Form belum bisa disubmit.</strong>
            <ul class="mb-0 mt-2">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <section class="inspector-panel qc-template-picker-card">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-lg-8">
                <label class="form-label">Jenis Form</label>
                <select id="template-select" class="form-select qc-template-select" {{ $templates->isEmpty() ? 'disabled' : '' }}>
                    @forelse ($templates as $template)
                        <option value="{{ $template->id }}" @selected($selectedTemplate?->is($template))>{{ $template->name }}</option>
                    @empty
                        <option>Belum ada template aktif</option>
                    @endforelse
                </select>
            </div>
            <div class="col-12 col-lg-4">
                <div class="qc-template-selected-meta">
                    <span>{{ $selectedTemplate?->code ?: 'Template belum tersedia' }}</span>
                    <strong>{{ $selectedTemplate?->category ?: 'Commissioning' }}</strong>
                </div>
            </div>
        </div>
    </section>

    @if (! $selectedTemplate)
        <section class="inspector-panel qc-empty-template-state">
            <i class="bi bi-file-earmark-lock"></i>
            <h2>Belum ada template Commissioning yang aktif.</h2>
            <p>Silakan publish template dari halaman admin agar user Commissioning bisa membuat form.</p>
        </section>
    @else
        <form method="POST" action="{{ isset($draftSubmission) ? route('user.commissioning.submissions.update', $draftSubmission) : route('user.commissioning.forms.store') }}" enctype="multipart/form-data">
            @csrf
            @isset($draftSubmission) @method('PATCH') @endisset
            <input type="hidden" name="template_id" value="{{ $selectedTemplate->id }}">
            @include('user.commissioning.forms.partials.fixed-form-renderer')
        </form>
    @endif
@endsection

@push('scripts')
<script>
document.getElementById('template-select')?.addEventListener('change', function () {
    const url = new URL(@json(route('user.commissioning.forms.create')), window.location.origin);
    url.searchParams.set('template', this.value);
    window.location.href = url.toString();
});
</script>
@endpush

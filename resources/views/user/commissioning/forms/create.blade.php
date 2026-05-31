@extends('layouts.user')

@section('title', 'Buat Form Commissioning')

@push('styles')
    <link href="{{ asset('assets/css/commissioning-mobile.css') }}?v=20260522-1" rel="stylesheet">
@endpush

@section('content')
    @php
        $commissioningMasterDataRecords = collect($activeMasterDataRecords ?? []);
        $draftHeader = ($draftSubmission ?? null)?->header_data ?? [];
        $selectedMasterDataId = old('header.master_data_record_id', request('master_data_record_id', $draftHeader['master_data_record_id'] ?? null));
        $selectedMasterDataRecord = $selectedMasterDataId ? $commissioningMasterDataRecords->firstWhere('id', (int) $selectedMasterDataId) : null;
        $areaOptions = $commissioningMasterDataRecords->pluck('area')->filter()->unique()->sort()->values();
        $selectedArea = old('header.area', request('area', $draftHeader['area'] ?? ($selectedMasterDataRecord?->area ?? '')));
        if ($selectedArea && ! $areaOptions->contains($selectedArea)) {
            $areaOptions->push($selectedArea);
            $areaOptions = $areaOptions->sort()->values();
        }
        if (! $selectedArea && $areaOptions->isNotEmpty()) {
            $selectedArea = $areaOptions->first();
        }
    @endphp

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
        <div class="qc-template-picker-grid">
            <div class="qc-template-picker-field">
                <label for="template-select" class="form-label">Jenis Form</label>
                <select id="template-select" class="form-select qc-template-select" {{ $templates->isEmpty() ? 'disabled' : '' }}>
                    @forelse ($templates as $template)
                        <option value="{{ $template->id }}" @selected($selectedTemplate?->is($template))>{{ $template->name }}</option>
                    @empty
                        <option>Belum ada template aktif</option>
                    @endforelse
                </select>
            </div>
            <div class="qc-template-picker-field">
                <label for="master-area-select" class="form-label">Area</label>
                <select id="master-area-select" class="form-select qc-template-select" data-master-area-select {{ $areaOptions->isEmpty() ? 'disabled' : '' }}>
                    <option value="">Pilih Area</option>
                    @foreach ($areaOptions as $areaOption)
                        <option value="{{ $areaOption }}" @selected((string) $selectedArea === (string) $areaOption)>{{ $areaOption }}</option>
                    @endforeach
                </select>
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
        <form method="POST" action="{{ isset($draftSubmission) ? route('user.commissioning.submissions.update', $draftSubmission) : route('user.commissioning.forms.store') }}" enctype="multipart/form-data" data-confirm-submit>
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
    const selectedArea = document.getElementById('master-area-select')?.value;
    const selectedMasterData = document.querySelector('[data-master-data-select]')?.value;
    url.searchParams.set('template', this.value);
    if (selectedArea) {
        url.searchParams.set('area', selectedArea);
    }
    if (selectedMasterData) {
        url.searchParams.set('master_data_record_id', selectedMasterData);
    }
    window.location.href = url.toString();
});

(() => {
    const focusFirstInvalid = (form) => {
        const invalid = form.querySelector(':invalid');
        invalid?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        setTimeout(() => invalid?.focus?.({ preventScroll: true }), 250);
    };

    document.querySelectorAll('form[data-confirm-submit] button[name="action"][value="submit"]').forEach((button) => {
        button.addEventListener('click', (event) => {
            const form = button.form;
            if (!form || form.checkValidity()) return;

            event.preventDefault();
            event.stopImmediatePropagation();
            form.reportValidity();
            focusFirstInvalid(form);
        }, true);
    });
})();

(() => {
    const box = document.querySelector('[data-commissioning-upload-box]');
    const input = document.querySelector('[data-commissioning-upload-input]');
    const cameraInput = document.querySelector('[data-commissioning-camera-input]');
    const preview = document.querySelector('[data-commissioning-upload-preview]');

    if (!box || !input || !cameraInput || !preview) {
        return;
    }

    let selectedFiles = [];

    const syncInputFiles = () => {
        const transfer = new DataTransfer();
        selectedFiles.forEach((file) => transfer.items.add(file));
        input.files = transfer.files;
    };

    const renderPreview = () => {
        preview.innerHTML = '';

        selectedFiles.forEach((file, index) => {
            const item = document.createElement('div');
            item.className = file.type.startsWith('image/') ? 'qc-upload-thumb' : 'qc-file-list-item';

            if (file.type.startsWith('image/')) {
                const image = document.createElement('img');
                image.src = URL.createObjectURL(file);
                image.alt = file.name;
                item.appendChild(image);
            } else {
                const icon = document.createElement('i');
                icon.className = 'bi bi-file-earmark-text';
                item.appendChild(icon);
            }

            const name = document.createElement('span');
            name.textContent = file.name;
            item.appendChild(name);

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'btn btn-sm btn-light border';
            remove.textContent = 'Hapus';
            remove.addEventListener('click', () => {
                selectedFiles.splice(index, 1);
                syncInputFiles();
                renderPreview();
            });
            item.appendChild(remove);
            preview.appendChild(item);
        });
    };

    const addFiles = (files, sourceInput) => {
        if (files.length === 0) {
            return;
        }

        selectedFiles = selectedFiles.concat(files);
        syncInputFiles();
        renderPreview();
        if (sourceInput !== input) {
            sourceInput.value = '';
        }
    };

    input.addEventListener('change', () => addFiles(Array.from(input.files || []), input));
    cameraInput.addEventListener('change', () => addFiles(Array.from(cameraInput.files || []), cameraInput));
})();
</script>
@endpush

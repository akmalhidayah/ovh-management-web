@csrf
@isset($method)
    @method($method)
@endisset

@php($fieldPrefix = $fieldPrefix ?? 'organization-section')

<div class="row g-3">
    <div class="col-12">
        <label class="form-label" for="{{ $fieldPrefix }}-department">Departemen</label>
        <input type="text" id="{{ $fieldPrefix }}-department" name="department" class="form-control @error('department') is-invalid @enderror" value="{{ old('department', $section->department) }}" required data-organization-section-field="department">
        @error('department')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-12">
        <label class="form-label" for="{{ $fieldPrefix }}-unit-kerja">Unit Kerja</label>
        <input type="text" id="{{ $fieldPrefix }}-unit-kerja" name="unit_kerja" class="form-control @error('unit_kerja') is-invalid @enderror" value="{{ old('unit_kerja', $section->unit_kerja) }}" required data-organization-section-field="unit_kerja">
        @error('unit_kerja')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-12">
        <label class="form-label" for="{{ $fieldPrefix }}-section">Seksi</label>
        <input type="text" id="{{ $fieldPrefix }}-section" name="section" class="form-control @error('section') is-invalid @enderror" value="{{ old('section', $section->section) }}" required data-organization-section-field="section">
        @error('section')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="modal-footer px-0 pb-0 mt-4">
    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
    <button type="submit" class="btn btn-primary">Simpan</button>
</div>

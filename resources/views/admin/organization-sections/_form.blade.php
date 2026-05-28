@csrf
@isset($method)
    @method($method)
@endisset

<div class="row g-3">
    <div class="col-12 col-lg-4">
        <label class="form-label" for="department">Departemen</label>
        <input type="text" id="department" name="department" class="form-control @error('department') is-invalid @enderror" value="{{ old('department', $section->department) }}" required>
        @error('department')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-12 col-lg-4">
        <label class="form-label" for="unit_kerja">Unit Kerja</label>
        <input type="text" id="unit_kerja" name="unit_kerja" class="form-control @error('unit_kerja') is-invalid @enderror" value="{{ old('unit_kerja', $section->unit_kerja) }}" required>
        @error('unit_kerja')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-12 col-lg-4">
        <label class="form-label" for="section">Seksi</label>
        <input type="text" id="section" name="section" class="form-control @error('section') is-invalid @enderror" value="{{ old('section', $section->section) }}" required>
        @error('section')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-12 col-lg-4">
        <label class="form-label" for="status">Status</label>
        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
            <option value="active" @selected(old('status', $section->status) === 'active')>Aktif</option>
            <option value="inactive" @selected(old('status', $section->status) === 'inactive')>Nonaktif</option>
        </select>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('admin.organization-sections.index') }}" class="btn btn-outline-secondary">Batal</a>
    <button type="submit" class="btn btn-primary">Simpan</button>
</div>

@php
    $record = $record ?? null;
    $organizationSectionOptions = $organizationSectionOptions ?? [];
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ $action }}" method="POST">
                @csrf
                @if ($method)
                    @method($method)
                @endif
                <div class="modal-header">
                    <h5 class="modal-title">{{ $title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Kategori Dokumen</label>
                            <select name="document_category" class="form-select" required data-master-document-category>
                                @foreach ($categoryOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('document_category', $record?->document_category ?? 'qc') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Tahun</label>
                            <input type="text" name="year" class="form-control" value="{{ old('year', $record?->year ?? now()->year) }}" placeholder="2026">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Func. Location</label>
                            <input type="text" name="func_location" class="form-control" value="{{ old('func_location', $record?->func_location) }}" required placeholder="ST-4302-RM-405-BC02">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Equipment No.</label>
                            <input type="text" name="equipment_no" class="form-control" value="{{ old('equipment_no', $record?->equipment_no) }}" placeholder="20007019 / N/A">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Section No.</label>
                            <input type="text" name="section_no" class="form-control" value="{{ old('section_no', $record?->section_no) }}" placeholder="405BC02M1">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Descriptions</label>
                            <input type="text" name="description" class="form-control" value="{{ old('description', $record?->description) }}" required placeholder="MOTOR BELT CONVEYOR">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Plant</label>
                            <input type="text" name="plant" class="form-control" value="{{ old('plant', $record?->plant) }}" required placeholder="TONASA 4">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Area</label>
                            <input type="text" name="area" class="form-control" value="{{ old('area', $record?->area) }}" required placeholder="RAW MILL">
                        </div>
                        <div class="col-12 col-md-4" data-commissioning-unit-field>
                            <label class="form-label">Unit Kerja</label>
                            <select name="organization_section_id" class="form-select">
                                <option value="">Pilih Unit Kerja</option>
                                @foreach ($organizationSectionOptions as $section)
                                    <option value="{{ $section['id'] }}" @selected((string) old('organization_section_id', $record?->organization_section_id) === (string) $section['id'])>
                                        {{ $section['label'] }}{{ $section['meta'] ? ' - '.$section['meta'] : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                @foreach ($statusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', $record?->status ?? 'active') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Catatan</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Opsional">{{ old('notes', $record?->notes) }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

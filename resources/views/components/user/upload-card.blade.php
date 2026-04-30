@props(['files' => []])

<section {{ $attributes->class(['inspector-panel']) }}>
    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
        <div>
            <h3 class="inspector-section-title mb-2">Upload Foto & Dokumen</h3>
            <p class="text-muted mb-0">Lampirkan bukti pendukung untuk memperkuat hasil pekerjaan.</p>
        </div>
        <span class="inspector-chip">Dummy Upload</span>
    </div>

    <div class="inspector-upload-dropzone">
        <img src="{{ asset('assets/images/placeholders/upload-placeholder.svg') }}" alt="Upload placeholder">
        <strong>Drag & drop file di sini</strong>
        <span>Format JPG, PNG, PDF, DOCX</span>
        <button type="button" class="btn btn-outline-primary btn-sm mt-3">Tambah File</button>
    </div>

    <div class="inspector-upload-preview">
        @foreach ($files as $file)
            <div class="inspector-upload-file">
                <span class="inspector-upload-icon">{{ $file['type'] }}</span>
                <div>
                    <strong>{{ $file['name'] }}</strong>
                    <small>{{ $file['size'] }}</small>
                </div>
            </div>
        @endforeach
    </div>
</section>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Approval {{ $document['type'] }} - {{ $document['number'] }}</title>
    @include('partials.tonasa-meta')
    @include('partials.upload-limits-meta')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f8; color: #182230; font-family: Arial, sans-serif; }
        .approval-shell { max-width: 1240px; margin: 32px auto; padding: 0 16px; }
        .approval-card { background: #fff; border: 1px solid #d9e0e8; border-radius: 8px; box-shadow: 0 14px 35px rgba(15, 23, 42, .08); }
        .approval-head { padding: 24px; border-bottom: 1px solid #e5e7eb; }
        .approval-head h1 { margin: 0; font-size: 24px; font-weight: 800; }
        .approval-body { padding: 24px; }
        .doc-hero { display: grid; gap: 12px; margin-bottom: 18px; }
        .doc-title-card { border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; background: #fbfdff; }
        .doc-title-card span { display: block; color: #64748b; font-size: 12px; font-weight: 700; text-transform: uppercase; }
        .doc-title-card h2 { margin: 4px 0 6px; color: #172033; font-size: 24px; font-weight: 850; line-height: 1.2; }
        .doc-title-card p { margin: 0; color: #475569; }
        .doc-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
        .doc-item { border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px; background: #fbfdff; }
        .doc-item span { display: block; color: #64748b; font-size: 12px; font-weight: 700; text-transform: uppercase; }
        .doc-item strong { display: block; margin-top: 4px; }
        .attachment-preview { margin-bottom: 24px; border: 1px solid #d9e0e8; border-radius: 8px; overflow: hidden; background: #fff; }
        .attachment-preview-head { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 12px 14px; border-bottom: 1px solid #e5e7eb; background: #f8fafc; }
        .attachment-preview-head strong { color: #172033; font-size: 15px; }
        .attachment-preview-head span { color: #64748b; font-size: 12px; }
        .attachment-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .attachment-table th { padding: 10px; border-bottom: 1px solid #e5e7eb; background: #f1f5f9; color: #475569; font-size: 12px; text-transform: uppercase; }
        .attachment-table td { height: 180px; padding: 10px; border: 1px solid #e5e7eb; text-align: center; vertical-align: middle; }
        .attachment-table td:first-child { border-left: 0; }
        .attachment-table td:last-child { border-right: 0; }
        .attachment-table img { max-width: 100%; max-height: 164px; margin: 0 auto; object-fit: contain; }
        .attachment-empty-cell { color: #94a3b8; font-size: 13px; }
        .pdf-panel { border: 1px solid #d9e0e8; border-radius: 8px; overflow: hidden; background: #eef2f6; }
        .pdf-toolbar { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 12px 14px; border-bottom: 1px solid #d9e0e8; background: #fff; }
        .pdf-toolbar strong { font-size: 15px; }
        .pdf-frame { display: block; width: 100%; height: min(74vh, 920px); border: 0; background: #fff; }
        .approval-form-panel { margin-top: 24px; padding-top: 24px; border-top: 1px solid #e5e7eb; }
        .approval-form-panel h2 { margin-bottom: 4px; font-size: 20px; font-weight: 800; }
        .signature-canvas { width: 100%; height: 190px; border: 1px solid #cbd5e1; border-radius: 6px; background: #fff; touch-action: none; }
        .reject-box { display: none; }
        @media (max-width: 900px) {
            .doc-grid { grid-template-columns: 1fr; }
            .doc-title-card h2 { font-size: 20px; }
            .attachment-preview-head { align-items: flex-start; flex-direction: column; }
            .attachment-table,
            .attachment-table tbody,
            .attachment-table tr,
            .attachment-table th,
            .attachment-table td { display: block; width: 100%; }
            .attachment-table thead { display: none; }
            .attachment-table td { height: auto; min-height: 170px; border-left: 0; border-right: 0; }
            .attachment-table td::before { content: attr(data-label); display: block; margin-bottom: 8px; color: #64748b; font-size: 12px; font-weight: 800; text-transform: uppercase; text-align: left; }
            .pdf-toolbar { align-items: flex-start; flex-direction: column; }
            .pdf-frame { height: 64vh; }
        }
    </style>
</head>
<body>
<main class="approval-shell">
    <section class="approval-card">
        <div class="approval-head">
            <h1>Approval {{ $document['type'] }}</h1>
            <p class="mb-0 text-muted">Link approval publik. Pastikan data dokumen sudah sesuai sebelum menandatangani.</p>
        </div>
        <div class="approval-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Approval belum dapat diproses.</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($step->status !== \App\Models\ApprovalStep::STATUS_ACTIVE)
                <div class="alert alert-warning">Link ini sudah tidak aktif.</div>
            @else
                <div class="doc-hero">
                    <div class="doc-title-card">
                        <span>Nama Equipment</span>
                        <h2>{{ $document['equipment'] ?: '-' }}</h2>
                        <p>{{ $document['work_description'] ?: '-' }}</p>
                    </div>
                    <div class="doc-grid">
                        <div class="doc-item"><span>Nomor Dokumen</span><strong>{{ $document['number'] }}</strong></div>
                        <div class="doc-item"><span>Section No.</span><strong>{{ $document['section_no'] ?: '-' }}</strong></div>
                        <div class="doc-item"><span>Functional Location</span><strong>{{ $document['functional_location'] ?: '-' }}</strong></div>
                        <div class="doc-item"><span>Plant / Area</span><strong>{{ $document['plant'] ?: '-' }} / {{ $document['area'] ?: '-' }}</strong></div>
                        <div class="doc-item"><span>Step Approval</span><strong>{{ $step->label }}</strong></div>
                        <div class="doc-item"><span>Jenis Dokumen</span><strong>{{ $document['type'] }}</strong></div>
                    </div>
                </div>

                @php
                    $previewType = $attachmentPreview['type'] ?? null;
                    $beforeImages = $attachmentPreview['before'] ?? collect();
                    $afterImages = $attachmentPreview['after'] ?? collect();
                    $commissioningImages = $attachmentPreview['items'] ?? collect();
                    $beforeAfterRows = max($beforeImages->count(), $afterImages->count());
                @endphp

                @if (($previewType === 'qc' && $beforeAfterRows > 0) || ($previewType === 'commissioning' && $commissioningImages->isNotEmpty()))
                    <div class="attachment-preview">
                        <div class="attachment-preview-head">
                            <strong>{{ $previewType === 'qc' ? 'Lampiran Foto Before / After' : 'Lampiran Dokumentasi' }}</strong>
                            <span>Maksimal 6 gambar ditampilkan untuk ringkasan approval.</span>
                        </div>

                        @if ($previewType === 'qc')
                            <table class="attachment-table">
                                <thead>
                                    <tr>
                                        <th>Foto Before</th>
                                        <th>Foto After</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @for ($index = 0; $index < $beforeAfterRows; $index++)
                                        <tr>
                                            <td data-label="Foto Before">
                                                @if ($item = $beforeImages->get($index))
                                                    <img src="{{ $item['source'] }}" alt="{{ $item['name'] }}">
                                                @else
                                                    <span class="attachment-empty-cell">Tidak ada foto.</span>
                                                @endif
                                            </td>
                                            <td data-label="Foto After">
                                                @if ($item = $afterImages->get($index))
                                                    <img src="{{ $item['source'] }}" alt="{{ $item['name'] }}">
                                                @else
                                                    <span class="attachment-empty-cell">Tidak ada foto.</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        @else
                            <table class="attachment-table">
                                <tbody>
                                    @foreach ($commissioningImages->chunk(min($commissioningImages->count(), 3)) as $row)
                                        <tr>
                                            @foreach ($row as $item)
                                                <td data-label="Dokumentasi">
                                                    <img src="{{ $item['source'] }}" alt="{{ $item['name'] }}">
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                @endif

                <div class="pdf-panel">
                    <div class="pdf-toolbar">
                        <div>
                            <strong>Preview PDF</strong>
                            <div class="text-muted small">{{ $document['equipment'] ?: '-' }} - {{ $document['plant'] ?: '-' }} / {{ $document['area'] ?: '-' }}</div>
                        </div>
                        <a href="{{ route('public.approval.pdf', ['token' => $token]) }}" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener">
                            Buka PDF di Tab Baru
                        </a>
                    </div>
                    <iframe
                        src="{{ route('public.approval.pdf', ['token' => $token]) }}#toolbar=1&navpanes=0"
                        class="pdf-frame"
                        title="Preview PDF {{ $document['number'] }}">
                    </iframe>
                </div>

                <div class="approval-form-panel">
                    <h2>Tanda Tangani Dokumen</h2>
                    <p class="text-muted mb-4">Periksa PDF di atas, lalu isi data approver dan tanda tangan untuk melanjutkan approval.</p>

                    <form method="POST" action="{{ route('public.approval.approve', ['token' => $token]) }}" enctype="multipart/form-data" data-approval-form>
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Approver</label>
                                <input type="text" name="approver_name" class="form-control" value="{{ old('approver_name', $suggestedApproverName) }}" placeholder="Nama Approver" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Jabatan Approver</label>
                                <input type="text" name="approver_position" class="form-control" value="{{ old('approver_position', $suggestedApproverPosition) }}" placeholder="Jabatan Approver" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Tanda Tangan</label>
                                <canvas width="900" height="280" class="signature-canvas" data-signature-canvas></canvas>
                                <input type="file" name="signature_file" accept="image/png,.png" class="d-none" data-signature-input>
                                <div class="form-text">Gunakan mouse/sentuhan layar, atau upload TTD format PNG transparan.</div>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mt-4">
                            <button type="button" class="btn btn-outline-secondary" data-signature-clear>Clear</button>
                            <button type="button" class="btn btn-outline-primary" data-signature-upload>Upload TTD</button>
                            <button type="submit" class="btn btn-success">Approve & Sign</button>
                            <button type="button" class="btn btn-outline-danger" data-show-reject>Reject</button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('public.approval.reject', ['token' => $token]) }}" class="reject-box mt-4" data-reject-form>
                        @csrf
                        <label class="form-label">Alasan Reject</label>
                        <textarea name="reject_reason" rows="4" class="form-control" required>{{ old('reject_reason') }}</textarea>
                        <div class="d-flex gap-2 mt-3">
                            <button type="submit" class="btn btn-danger">Kirim Reject</button>
                            <button type="button" class="btn btn-light" data-hide-reject>Batal</button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </section>
</main>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('assets/js/global-upload-guard.js') }}"></script>
<script>
(() => {
    const canvas = document.querySelector('[data-signature-canvas]');
    const input = document.querySelector('[data-signature-input]');
    if (!canvas || !input) return;
    const context = canvas.getContext('2d');
    let drawing = false;
    let hasDrawing = false;
    let signaturePrepared = false;

    const reset = () => {
        context.clearRect(0, 0, canvas.width, canvas.height);
        context.fillStyle = '#fff';
        context.fillRect(0, 0, canvas.width, canvas.height);
        context.strokeStyle = '#000';
        context.lineWidth = 6;
        context.lineCap = 'round';
        context.lineJoin = 'round';
        hasDrawing = false;
        signaturePrepared = false;
        input.value = '';
    };
    const point = (event) => {
        const source = event.touches?.[0] || event.changedTouches?.[0] || event;
        const rect = canvas.getBoundingClientRect();
        return {
            x: (source.clientX - rect.left) * (canvas.width / rect.width),
            y: (source.clientY - rect.top) * (canvas.height / rect.height),
        };
    };
    const start = (event) => {
        event.preventDefault();
        drawing = true;
        const p = point(event);
        context.beginPath();
        context.moveTo(p.x, p.y);
    };
    const draw = (event) => {
        if (!drawing) return;
        event.preventDefault();
        const p = point(event);
        context.lineTo(p.x, p.y);
        context.stroke();
        hasDrawing = true;
        signaturePrepared = false;
    };
    const stop = () => { drawing = false; };
    reset();
    canvas.addEventListener('mousedown', start);
    canvas.addEventListener('mousemove', draw);
    window.addEventListener('mouseup', stop);
    canvas.addEventListener('touchstart', start, { passive: false });
    canvas.addEventListener('touchmove', draw, { passive: false });
    canvas.addEventListener('touchend', stop);
    document.querySelector('[data-signature-clear]')?.addEventListener('click', reset);
    document.querySelector('[data-signature-upload]')?.addEventListener('click', () => input.click());

    const signatureBounds = (image) => {
        const scratch = document.createElement('canvas');
        scratch.width = image.naturalWidth || image.width;
        scratch.height = image.naturalHeight || image.height;

        const scratchContext = scratch.getContext('2d');
        scratchContext.drawImage(image, 0, 0);

        const { data, width, height } = scratchContext.getImageData(0, 0, scratch.width, scratch.height);
        let minX = width;
        let minY = height;
        let maxX = -1;
        let maxY = -1;
        let hasTransparentPixel = false;

        for (let y = 0; y < height; y += 1) {
            for (let x = 0; x < width; x += 1) {
                const offset = (y * width + x) * 4;
                const alpha = data[offset + 3];
                const red = data[offset];
                const green = data[offset + 1];
                const blue = data[offset + 2];

                if (alpha < 250) {
                    hasTransparentPixel = true;
                }

                if (alpha <= 20 || (red > 245 && green > 245 && blue > 245)) {
                    continue;
                }

                minX = Math.min(minX, x);
                minY = Math.min(minY, y);
                maxX = Math.max(maxX, x);
                maxY = Math.max(maxY, y);
            }
        }

        if (maxX < minX || maxY < minY) {
            return { x: 0, y: 0, width, height, transparent: hasTransparentPixel };
        }

        const padding = Math.max(4, Math.round(Math.max(width, height) * 0.02));

        return {
            x: Math.max(0, minX - padding),
            y: Math.max(0, minY - padding),
            width: Math.min(width - 1, maxX + padding) - Math.max(0, minX - padding) + 1,
            height: Math.min(height - 1, maxY + padding) - Math.max(0, minY - padding) + 1,
            transparent: hasTransparentPixel,
        };
    };

    const drawUploadedSignature = (file) => {
        if (file.type !== 'image/png') {
            alert('File TTD upload harus PNG transparan.');
            input.value = '';
            return;
        }

        if (file.size > 1024 * 1024) {
            alert('Ukuran file TTD maksimal 1MB.');
            input.value = '';
            return;
        }

        const image = new Image();
        const objectUrl = URL.createObjectURL(file);
        image.onload = () => {
            reset();

            const bounds = signatureBounds(image);
            if (!bounds.transparent) {
                input.value = '';
                alert('File TTD harus PNG transparan, bukan PNG dengan background penuh.');
                URL.revokeObjectURL(objectUrl);
                return;
            }

            const padding = 18;
            const maxWidth = canvas.width - (padding * 2);
            const maxHeight = canvas.height - (padding * 2);
            const ratio = Math.min(maxWidth / bounds.width, maxHeight / bounds.height);
            const width = bounds.width * ratio;
            const height = bounds.height * ratio;
            const x = (canvas.width - width) / 2;
            const y = (canvas.height - height) / 2;

            context.drawImage(image, bounds.x, bounds.y, bounds.width, bounds.height, x, y, width, height);
            hasDrawing = true;
            signaturePrepared = false;
            URL.revokeObjectURL(objectUrl);
        };
        image.onerror = () => {
            URL.revokeObjectURL(objectUrl);
            input.value = '';
            alert('File TTD gagal dibaca. Silakan pilih gambar lain.');
        };
        image.src = objectUrl;
    };

    input.addEventListener('change', () => {
        const file = input.files?.[0];
        if (file) {
            drawUploadedSignature(file);
        }
    });

    const setSignatureFile = (blob) => {
        const file = new File([blob], `signature-${Date.now()}.png`, { type: 'image/png' });
        const transfer = new DataTransfer();
        transfer.items.add(file);
        input.files = transfer.files;
    };

    const approvalForm = document.querySelector('[data-approval-form]');
    approvalForm?.addEventListener('submit', (event) => {
        if (signaturePrepared) {
            return;
        }

        event.preventDefault();

        if (!hasDrawing) {
            alert('Silakan buat tanda tangan terlebih dahulu.');
            return;
        }

        canvas.toBlob((blob) => {
            if (!blob) {
                alert('Tanda tangan gagal diproses. Silakan coba lagi.');
                return;
            }

            setSignatureFile(blob);
            signaturePrepared = true;

            if (approvalForm.requestSubmit) {
                approvalForm.requestSubmit(event.submitter || undefined);
            } else {
                approvalForm.submit();
            }
        }, 'image/png');
    });
    const rejectBox = document.querySelector('[data-reject-form]');
    document.querySelector('[data-show-reject]')?.addEventListener('click', () => rejectBox.style.display = 'block');
    document.querySelector('[data-hide-reject]')?.addEventListener('click', () => rejectBox.style.display = 'none');
})();
</script>
</body>
</html>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Approval {{ $document['type'] }} - {{ $document['number'] }}</title>
    @include('partials.tonasa-meta')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f8; color: #182230; font-family: Arial, sans-serif; }
        .approval-shell { max-width: 1240px; margin: 32px auto; padding: 0 16px; }
        .approval-card { background: #fff; border: 1px solid #d9e0e8; border-radius: 8px; box-shadow: 0 14px 35px rgba(15, 23, 42, .08); }
        .approval-head { padding: 24px; border-bottom: 1px solid #e5e7eb; }
        .approval-head h1 { margin: 0; font-size: 24px; font-weight: 800; }
        .approval-body { padding: 24px; }
        .doc-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
        .doc-item { border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px; background: #fbfdff; }
        .doc-item span { display: block; color: #64748b; font-size: 12px; font-weight: 700; text-transform: uppercase; }
        .doc-item strong { display: block; margin-top: 4px; }
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
                <div class="doc-grid mb-4">
                    <div class="doc-item"><span>Nomor Dokumen</span><strong>{{ $document['number'] }}</strong></div>
                    <div class="doc-item"><span>Dokumen</span><strong>{{ $document['template'] ?: '-' }}</strong></div>
                    <div class="doc-item"><span>Step Approval</span><strong>{{ $step->label }}</strong></div>
                </div>

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
                                <input type="file" name="signature_file" accept="image/png,image/jpeg" class="d-none" data-signature-input>
                                <div class="form-text">Gunakan mouse, sentuhan layar, atau upload gambar TTD PNG/JPG.</div>
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

    const drawUploadedSignature = (file) => {
        if (!['image/png', 'image/jpeg'].includes(file.type)) {
            alert('File TTD harus PNG atau JPG.');
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

            const padding = 18;
            const maxWidth = canvas.width - (padding * 2);
            const maxHeight = canvas.height - (padding * 2);
            const ratio = Math.min(maxWidth / image.width, maxHeight / image.height, 1);
            const width = image.width * ratio;
            const height = image.height * ratio;
            const x = (canvas.width - width) / 2;
            const y = (canvas.height - height) / 2;

            context.drawImage(image, x, y, width, height);
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

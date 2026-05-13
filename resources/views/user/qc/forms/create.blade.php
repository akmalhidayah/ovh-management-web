@extends('layouts.user')

@section('title', 'Buat Form QC')

@section('content')
    <div class="user-simple-form-header qc-template-form-heading">
        <div>
            <h1>Buat Form Quality Control</h1>
            <p>Pilih template QC aktif, lalu isi form berdasarkan bagian yang sudah dipublish admin.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Form belum bisa disubmit.</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="inspector-panel qc-template-picker-card">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-lg-8">
                <label for="template-select" class="form-label">Jenis Form</label>
                <select id="template-select" class="form-select qc-template-select" {{ $templates->isEmpty() ? 'disabled' : '' }}>
                    @forelse ($templates as $template)
                        <option value="{{ $template->id }}" @selected($selectedTemplate?->is($template))>
                            {{ $template->name }}
                        </option>
                    @empty
                        <option>Belum ada template aktif</option>
                    @endforelse
                </select>
            </div>
            <div class="col-12 col-lg-4">
                <div class="qc-template-selected-meta">
                    <span>{{ $selectedTemplate?->code ?: 'Template belum tersedia' }}</span>
                    <strong>{{ $selectedTemplate?->category ?: 'QC' }}</strong>
                </div>
            </div>
        </div>
    </section>

    @if (! $selectedTemplate)
        <section class="inspector-panel qc-empty-template-state">
            <i class="bi bi-file-earmark-lock"></i>
            <h2>Belum ada template QC yang aktif.</h2>
            <p>Silakan publish template dari halaman admin agar user QC bisa membuat form berdasarkan template tersebut.</p>
        </section>
    @else
        <form method="POST" action="{{ isset($draftSubmission) ? route('user.qc.submissions.update', $draftSubmission) : route('user.qc.forms.store') }}" enctype="multipart/form-data" data-confirm-submit>
            @csrf
            @isset($draftSubmission)
                @method('PATCH')
            @endisset
            <input type="hidden" name="template_id" value="{{ $selectedTemplate->id }}">
            @include('user.qc.forms.partials.block-form-renderer', ['selectedTemplate' => $selectedTemplate, 'draftSubmission' => $draftSubmission ?? null])
        </form>
    @endif
@endsection

@push('scripts')
    <script>
        document.getElementById('template-select')?.addEventListener('change', function () {
            const url = new URL(@json(route('user.qc.forms.create')), window.location.origin);
            url.searchParams.set('template', this.value);
            window.location.href = url.toString();
        });

        (() => {
            const modalElement = document.getElementById('qcSignatureModal');
            const canvas = modalElement?.querySelector('[data-signature-canvas]');

            if (!modalElement || !canvas || !window.bootstrap) {
                return;
            }

            const modal = new bootstrap.Modal(modalElement);
            const context = canvas.getContext('2d');
            let activeCard = null;
            let drawing = false;
            let hasDrawing = false;
            let signatureBounds = null;

            const includePointInBounds = (point) => {
                signatureBounds = signatureBounds
                    ? {
                        minX: Math.min(signatureBounds.minX, point.x),
                        minY: Math.min(signatureBounds.minY, point.y),
                        maxX: Math.max(signatureBounds.maxX, point.x),
                        maxY: Math.max(signatureBounds.maxY, point.y),
                    }
                    : { minX: point.x, minY: point.y, maxX: point.x, maxY: point.y };
            };

            const resetCanvas = () => {
                context.clearRect(0, 0, canvas.width, canvas.height);
                context.fillStyle = '#ffffff';
                context.fillRect(0, 0, canvas.width, canvas.height);
                context.strokeStyle = '#000000';
                context.lineWidth = 6;
                context.lineCap = 'round';
                context.lineJoin = 'round';
                hasDrawing = false;
                signatureBounds = null;
            };

            const signatureDataUrl = () => {
                if (!signatureBounds) {
                    return canvas.toDataURL('image/png');
                }

                const padding = 26;
                const minX = Math.max(Math.floor(signatureBounds.minX - padding), 0);
                const minY = Math.max(Math.floor(signatureBounds.minY - padding), 0);
                const maxX = Math.min(Math.ceil(signatureBounds.maxX + padding), canvas.width);
                const maxY = Math.min(Math.ceil(signatureBounds.maxY + padding), canvas.height);
                const cropped = document.createElement('canvas');

                cropped.width = Math.max(maxX - minX, 1);
                cropped.height = Math.max(maxY - minY, 1);

                const croppedContext = cropped.getContext('2d');
                croppedContext.fillStyle = '#ffffff';
                croppedContext.fillRect(0, 0, cropped.width, cropped.height);
                croppedContext.drawImage(
                    canvas,
                    minX,
                    minY,
                    cropped.width,
                    cropped.height,
                    0,
                    0,
                    cropped.width,
                    cropped.height
                );

                return cropped.toDataURL('image/png');
            };

            const pointFromEvent = (event) => {
                const source = event.touches?.[0] || event.changedTouches?.[0] || event;
                const rect = canvas.getBoundingClientRect();

                return {
                    x: (source.clientX - rect.left) * (canvas.width / rect.width),
                    y: (source.clientY - rect.top) * (canvas.height / rect.height),
                };
            };

            const startDrawing = (event) => {
                event.preventDefault();
                drawing = true;
                const point = pointFromEvent(event);
                context.beginPath();
                context.moveTo(point.x, point.y);
                includePointInBounds(point);
            };

            const draw = (event) => {
                if (!drawing) {
                    return;
                }

                event.preventDefault();
                const point = pointFromEvent(event);
                context.lineTo(point.x, point.y);
                context.stroke();
                hasDrawing = true;
                includePointInBounds(point);
            };

            const stopDrawing = () => {
                drawing = false;
            };

            canvas.addEventListener('mousedown', startDrawing);
            canvas.addEventListener('mousemove', draw);
            window.addEventListener('mouseup', stopDrawing);
            canvas.addEventListener('touchstart', startDrawing, { passive: false });
            canvas.addEventListener('touchmove', draw, { passive: false });
            canvas.addEventListener('touchend', stopDrawing);

            document.querySelectorAll('[data-signature-open]').forEach((button) => {
                button.addEventListener('click', () => {
                    activeCard = button.closest('[data-signature-card]');
                    modalElement.querySelector('[data-signature-modal-label]').textContent = button.dataset.signatureLabel || '';
                    resetCanvas();
                    modal.show();
                });
            });

            modalElement.querySelector('[data-signature-clear]')?.addEventListener('click', resetCanvas);

            modalElement.querySelector('[data-signature-save]')?.addEventListener('click', () => {
                if (!activeCard || !hasDrawing) {
                    window.alert('Silakan buat tanda tangan terlebih dahulu.');
                    return;
                }

                const dataUrl = signatureDataUrl();
                const signedAt = new Date();
                const signedText = signedAt.toLocaleString('id-ID', {
                    dateStyle: 'medium',
                    timeStyle: 'short',
                });

                activeCard.querySelector('[data-signature-input]').value = dataUrl;
                activeCard.querySelector('[data-signature-time-input]').value = signedAt.toISOString();
                activeCard.querySelector('[data-signature-preview]').src = dataUrl;
                activeCard.querySelector('[data-signature-time]').textContent = signedText;
                activeCard.querySelector('[data-signature-empty]').classList.add('d-none');
                activeCard.querySelector('[data-signature-result]').classList.remove('d-none');
                activeCard.querySelector('[data-signature-remove]').classList.remove('d-none');
                activeCard.querySelector('[data-signature-button-label]').textContent = 'Ubah';
                modal.hide();
            });

            document.querySelectorAll('[data-signature-remove]').forEach((button) => {
                button.addEventListener('click', () => {
                    const card = button.closest('[data-signature-card]');
                    card.querySelector('[data-signature-input]').value = '';
                    card.querySelector('[data-signature-time-input]').value = '';
                    card.querySelector('[data-signature-preview]').removeAttribute('src');
                    card.querySelector('[data-signature-time]').textContent = '';
                    card.querySelector('[data-signature-empty]').classList.remove('d-none');
                    card.querySelector('[data-signature-result]').classList.add('d-none');
                    button.classList.add('d-none');
                    card.querySelector('[data-signature-button-label]').textContent = 'Tanda Tangan';
                });
            });
        })();

        (() => {
            document.querySelectorAll('[data-upload-box]').forEach((box) => {
                const input = box.querySelector('[data-upload-input]');
                const preview = box.querySelector('[data-upload-preview]');
                const message = box.querySelector('[data-upload-message]');
                const maxFiles = Number(box.dataset.maxFiles || 0);
                const uploadType = box.dataset.uploadType || 'file';
                let selectedFiles = [];

                const syncInputFiles = () => {
                    const dataTransfer = new DataTransfer();
                    selectedFiles.forEach((file) => dataTransfer.items.add(file));
                    input.files = dataTransfer.files;
                };

                const renderPreview = () => {
                    preview.innerHTML = '';
                    message.textContent = '';

                    selectedFiles.forEach((file, index) => {
                        const item = document.createElement('div');
                        item.className = uploadType === 'image' && file.type.startsWith('image/')
                            ? 'qc-upload-thumb'
                            : 'qc-file-list-item';

                        if (uploadType === 'image' && file.type.startsWith('image/')) {
                            const img = document.createElement('img');
                            img.src = URL.createObjectURL(file);
                            img.alt = file.name;
                            item.appendChild(img);
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

                input?.addEventListener('change', () => {
                    const files = Array.from(input.files || []);
                    const nextFiles = input.multiple ? selectedFiles.concat(files) : files.slice(0, 1);

                    if (maxFiles && nextFiles.length > maxFiles) {
                        selectedFiles = nextFiles.slice(0, maxFiles);
                        message.textContent = `Maksimal ${maxFiles} file. Sebagian file tidak ditambahkan.`;
                    } else {
                        selectedFiles = nextFiles;
                    }

                    syncInputFiles();
                    renderPreview();
                });
            });
        })();
    </script>
@endpush

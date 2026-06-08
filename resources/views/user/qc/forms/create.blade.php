@extends('layouts.user')

@section('title', 'Buat Form QC')

@push('styles')
    <link href="{{ asset('assets/css/qc-fixed-mobile.css') }}?v={{ file_exists(public_path('assets/css/qc-fixed-mobile.css')) ? filemtime(public_path('assets/css/qc-fixed-mobile.css')) : time() }}" rel="stylesheet">
@endpush

@section('content')
    @php
        $qcMasterDataRecords = collect($activeQcMasterDataRecords ?? []);
        $draftHeader = ($draftSubmission ?? null)?->general_info ?? [];
        $selectedMasterDataId = old('header.master_data_record_id', request('master_data_record_id', $draftHeader['master_data_record_id'] ?? null));
        $selectedMasterDataRecord = $selectedMasterDataId ? $qcMasterDataRecords->firstWhere('id', (int) $selectedMasterDataId) : null;
        $areaOptions = $qcMasterDataRecords->pluck('area')->filter()->unique()->sort()->values();
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
        <div class="qc-template-picker-grid">
            <div class="qc-template-picker-field">
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
        @if ($errors->has('form_number'))
            (() => {
                const duplicateFormNumberMessage = @json($errors->first('form_number'));

                const showDuplicateFormNumberAlert = () => {
                    if (!duplicateFormNumberMessage || !window.Swal) {
                        return;
                    }

                    window.Swal.fire({
                        title: 'Nomor Form Bentrok',
                        text: duplicateFormNumberMessage,
                        icon: 'warning',
                        confirmButtonText: 'Submit Ulang',
                        confirmButtonColor: '#2563eb',
                    });
                };

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', showDuplicateFormNumberAlert, { once: true });
                } else {
                    showDuplicateFormNumberAlert();
                }
            })();
        @endif

        document.getElementById('template-select')?.addEventListener('change', function () {
            const url = new URL(@json(route('user.qc.forms.create')), window.location.origin);
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
            const validateCheckboxGroup = (form, selector, message) => {
                const inputs = Array.from(form.querySelectorAll(selector));
                if (inputs.length === 0) return;

                const isChecked = inputs.some((input) => input.checked);
                inputs[0].setCustomValidity(isChecked ? '' : message);
                inputs.forEach((input) => {
                    input.addEventListener('change', () => {
                        inputs[0].setCustomValidity(inputs.some((item) => item.checked) ? '' : message);
                    }, { once: true });
                });
            };

            const focusFirstInvalid = (form) => {
                const invalid = form.querySelector(':invalid');
                invalid?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                setTimeout(() => invalid?.focus?.({ preventScroll: true }), 250);
            };

            document.querySelectorAll('form[data-confirm-submit] button[name="action"][value="submit"]').forEach((button) => {
                button.addEventListener('click', (event) => {
                    const form = button.form;
                    if (!form) return;

                    validateCheckboxGroup(form, 'input[name="body[methods][]"]', 'Pilih minimal satu metode QC.');
                    validateCheckboxGroup(form, 'input[name="body[check_steps][]"]', 'Pilih minimal satu pengecekan ke.');

                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopImmediatePropagation();
                        form.reportValidity();
                        focusFirstInvalid(form);
                    }
                }, true);
            });
        })();

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

            const dataUrlToFile = (dataUrl, filename) => {
                const [header, encoded] = dataUrl.split(',');
                const mime = header.match(/data:(.*?);base64/)?.[1] || 'image/png';
                const binary = atob(encoded || '');
                const bytes = new Uint8Array(binary.length);

                for (let i = 0; i < binary.length; i += 1) {
                    bytes[i] = binary.charCodeAt(i);
                }

                return new File([bytes], filename, { type: mime });
            };

            const setFileInput = (input, file) => {
                if (!input || !file) {
                    return;
                }

                const transfer = new DataTransfer();
                transfer.items.add(file);
                input.files = transfer.files;
            };

            const revokeSignatureObjectUrl = (card) => {
                if (card?.dataset.signatureObjectUrl) {
                    URL.revokeObjectURL(card.dataset.signatureObjectUrl);
                    delete card.dataset.signatureObjectUrl;
                }
            };

            const applySignatureToCard = (card, previewSource, options = {}) => {
                if (!card) {
                    return;
                }

                const signedAt = new Date();
                const signedText = signedAt.toLocaleString('id-ID', {
                    dateStyle: 'medium',
                    timeStyle: 'short',
                });
                const signatureInput = card.querySelector('[data-signature-input]');
                const fileInput = card.querySelector('[data-signature-file-input]');

                revokeSignatureObjectUrl(card);

                if (options.file) {
                    setFileInput(fileInput, options.file);
                }

                if (options.objectUrl) {
                    card.dataset.signatureObjectUrl = options.objectUrl;
                }

                if (signatureInput) {
                    signatureInput.value = options.hiddenValue || '';
                }

                card.querySelector('[data-signature-time-input]').value = signedAt.toISOString();
                card.querySelector('[data-signature-preview]').src = previewSource;
                card.querySelector('[data-signature-time]').textContent = signedText;
                card.querySelector('[data-signature-empty]').classList.add('d-none');
                card.querySelector('[data-signature-result]').classList.remove('d-none');
                card.querySelector('[data-signature-remove]').classList.remove('d-none');
                card.querySelector('[data-signature-button-label]').textContent = 'Ubah';
            };

            const uploadedSignatureHasTransparency = (file) => new Promise((resolve) => {
                const image = new Image();
                const objectUrl = URL.createObjectURL(file);

                image.onload = () => {
                    const scratch = document.createElement('canvas');
                    scratch.width = image.naturalWidth || image.width;
                    scratch.height = image.naturalHeight || image.height;

                    try {
                        const scratchContext = scratch.getContext('2d');
                        scratchContext.drawImage(image, 0, 0);
                        const pixels = scratchContext.getImageData(0, 0, scratch.width, scratch.height).data;

                        for (let index = 3; index < pixels.length; index += 4) {
                            if (pixels[index] < 250) {
                                URL.revokeObjectURL(objectUrl);
                                resolve(true);
                                return;
                            }
                        }
                    } catch (error) {
                        URL.revokeObjectURL(objectUrl);
                        resolve(false);
                        return;
                    }

                    URL.revokeObjectURL(objectUrl);
                    resolve(false);
                };

                image.onerror = () => {
                    URL.revokeObjectURL(objectUrl);
                    resolve(false);
                };

                image.src = objectUrl;
            });

            const showWarning = (message, callback = null) => {
                if (!window.Swal) {
                    window.alert(message);
                    callback?.();
                    return;
                }

                window.Swal.fire({
                    title: 'Periksa Form',
                    text: message,
                    icon: 'warning',
                    confirmButtonText: 'Mengerti',
                    confirmButtonColor: '#2563eb',
                }).then(() => callback?.());
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

            document.querySelectorAll('[data-signature-upload]').forEach((button) => {
                button.addEventListener('click', () => {
                    button.closest('[data-signature-card]')?.querySelector('[data-signature-file-input]')?.click();
                });
            });

            document.querySelectorAll('[data-signature-file-input]').forEach((input) => {
                input.addEventListener('change', async () => {
                    const file = input.files?.[0];
                    const card = input.closest('[data-signature-card]');

                    if (!file || !card) {
                        return;
                    }

                    if (file.type !== 'image/png' && !file.name.toLowerCase().endsWith('.png')) {
                        input.value = '';
                        showWarning('File TTD upload harus PNG transparan.');
                        return;
                    }

                    if (file.size > 1024 * 1024) {
                        input.value = '';
                        showWarning('Ukuran file TTD maksimal 1 MB.');
                        return;
                    }

                    const hasTransparency = await uploadedSignatureHasTransparency(file);
                    if (!hasTransparency) {
                        input.value = '';
                        showWarning('File TTD harus PNG transparan, bukan PNG dengan background penuh.');
                        return;
                    }

                    const objectUrl = URL.createObjectURL(file);
                    applySignatureToCard(card, objectUrl, { objectUrl });
                });
            });

            modalElement.querySelector('[data-signature-clear]')?.addEventListener('click', resetCanvas);

            modalElement.querySelector('[data-signature-save]')?.addEventListener('click', () => {
                if (!activeCard || !hasDrawing) {
                    showWarning('Silakan buat tanda tangan terlebih dahulu.');
                    return;
                }

                const dataUrl = signatureDataUrl();
                applySignatureToCard(activeCard, dataUrl, {
                    file: dataUrlToFile(dataUrl, `signature-${Date.now()}.png`),
                    hiddenValue: dataUrl,
                });
                modal.hide();
            });

            document.querySelectorAll('[data-signature-remove]').forEach((button) => {
                button.addEventListener('click', () => {
                    const card = button.closest('[data-signature-card]');
                    revokeSignatureObjectUrl(card);
                    card.querySelector('[data-signature-input]').value = '';
                    const fileInput = card.querySelector('[data-signature-file-input]');
                    if (fileInput) fileInput.value = '';
                    card.querySelector('[data-signature-time-input]').value = '';
                    card.querySelector('[data-signature-preview]').removeAttribute('src');
                    card.querySelector('[data-signature-time]').textContent = '';
                    card.querySelector('[data-signature-empty]').classList.remove('d-none');
                    card.querySelector('[data-signature-result]').classList.add('d-none');
                    button.classList.add('d-none');
                    card.querySelector('[data-signature-button-label]').textContent = 'Tanda Tangan';
                });
            });

            document.querySelectorAll('form[data-confirm-submit]').forEach((form) => {
                form.addEventListener('submit', (event) => {
                    const submitter = event.submitter;
                    const isSubmitAction = submitter?.name === 'action' && submitter?.value === 'submit';
                    const inspectorCard = form.querySelector('[data-qc-inspector-approval]');

                    if (isSubmitAction && inspectorCard) {
                        const signatureInput = inspectorCard.querySelector('[data-signature-input]');
                        const fileInput = inspectorCard.querySelector('[data-signature-file-input]');
                        const hasSignature = Boolean(signatureInput?.value || fileInput?.files?.length);

                        if (!hasSignature) {
                            event.preventDefault();
                            showWarning(
                                'Tanda tangan QC Inspektor wajib diisi sebelum submit final.',
                                () => inspectorCard.querySelector('[data-signature-open]')?.focus()
                            );
                            return;
                        }
                    }

                    form.querySelectorAll('[data-signature-input]').forEach((input) => {
                        if (!input.value.startsWith('data:image/')) {
                            return;
                        }

                        const card = input.closest('[data-signature-card]');
                        const fileInput = card?.querySelector('[data-signature-file-input]');

                        if (fileInput && !fileInput.files.length) {
                            setFileInput(fileInput, dataUrlToFile(input.value, `signature-${Date.now()}.png`));
                        }

                        if (fileInput?.files?.length) {
                            input.value = '';
                        }
                    });
                });
            });
        })();

        (() => {
            const syncGeneralNotePlaceholder = (row) => {
                const note = row.querySelector('[data-qc-general-note]');
                if (!note) return;

                const status = row.querySelector('input[name*="[status]"]:checked')?.value;
                const isNotOk = status === 'Not Ok';

                note.placeholder = isNotOk
                    ? 'Wajib diisi karena status Not Ok'
                    : 'Opsional jika status Ok';
                note.toggleAttribute('required', isNotOk);
            };

            document.querySelectorAll('.qc-general-check-table tbody tr').forEach((row) => {
                syncGeneralNotePlaceholder(row);

                row.querySelectorAll('input[name*="[status]"]').forEach((input) => {
                    input.addEventListener('change', () => syncGeneralNotePlaceholder(row));
                });
            });
        })();

        (() => {
            document.querySelectorAll('[data-existing-attachment-remove]').forEach((button) => {
                button.addEventListener('click', () => {
                    const item = button.closest('[data-existing-attachment-item]');
                    const input = item?.querySelector('[data-existing-attachment-remove-input]');

                    if (!item || !input) {
                        return;
                    }

                    input.disabled = false;
                    item.classList.add('d-none');
                });
            });
        })();

        (() => {
            const MAX_IMAGE_BYTES = 1.4 * 1024 * 1024;
            const MAX_TOTAL_UPLOAD_BYTES = 24 * 1024 * 1024;
            const MAX_IMAGE_DIMENSION = 1800;
            const SUPPORTED_IMAGE_MIME = /^image\/(jpeg|png)$/;
            const UNSUPPORTED_HEIC_EXTENSION = /\.(heic|heif)$/i;

            const showUploadWarning = (message) => {
                if (window.Swal) {
                    window.Swal.fire({
                        title: 'Upload terlalu besar',
                        text: message,
                        icon: 'warning',
                        confirmButtonText: 'Mengerti',
                        confirmButtonColor: '#2563eb',
                    });
                    return;
                }

                window.alert(message);
            };

            const isUnsupportedHeic = (file) => {
                const type = (file.type || '').toLowerCase();

                return type === 'image/heic'
                    || type === 'image/heif'
                    || UNSUPPORTED_HEIC_EXTENSION.test(file.name || '');
            };

            const fileExtension = (name) => {
                const parts = String(name || '').split('.');
                return parts.length > 1 ? parts.pop() : '';
            };

            const compressedFileName = (name) => {
                const extension = fileExtension(name);
                const base = extension ? name.slice(0, -(extension.length + 1)) : name;

                return `${base || 'foto'}-compressed.jpg`;
            };

            const loadImage = (file) => new Promise((resolve, reject) => {
                const url = URL.createObjectURL(file);
                const image = new Image();

                image.onload = () => {
                    URL.revokeObjectURL(url);
                    resolve(image);
                };
                image.onerror = () => {
                    URL.revokeObjectURL(url);
                    reject(new Error('Gambar tidak bisa dibaca.'));
                };
                image.src = url;
            });

            const canvasBlob = (canvas, quality) => new Promise((resolve) => {
                canvas.toBlob(resolve, 'image/jpeg', quality);
            });

            const compressImageFile = async (file) => {
                if (!SUPPORTED_IMAGE_MIME.test(file.type) || file.size <= MAX_IMAGE_BYTES) {
                    return file;
                }

                const image = await loadImage(file);
                const scale = Math.min(1, MAX_IMAGE_DIMENSION / Math.max(image.width, image.height));
                const canvas = document.createElement('canvas');
                canvas.width = Math.max(Math.round(image.width * scale), 1);
                canvas.height = Math.max(Math.round(image.height * scale), 1);

                const context = canvas.getContext('2d');
                context.drawImage(image, 0, 0, canvas.width, canvas.height);

                let quality = 0.78;
                let blob = await canvasBlob(canvas, quality);

                while (blob && blob.size > MAX_IMAGE_BYTES && quality > 0.48) {
                    quality -= 0.1;
                    blob = await canvasBlob(canvas, quality);
                }

                if (!blob || blob.size >= file.size) {
                    return file;
                }

                return new File([blob], compressedFileName(file.name), {
                    type: 'image/jpeg',
                    lastModified: Date.now(),
                });
            };

            document.querySelectorAll('[data-upload-box]').forEach((box) => {
                const input = box.querySelector('[data-upload-input]');
                const cameraInput = box.querySelector('[data-camera-input]');
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

                const addFiles = async (files, sourceInput = input) => {
                    if (!sourceInput || files.length === 0) {
                        return;
                    }

                    const unsupportedFiles = files.filter(isUnsupportedHeic);
                    const supportedFiles = files.filter((file) => !isUnsupportedHeic(file));

                    if (unsupportedFiles.length > 0) {
                        const names = unsupportedFiles.map((file) => file.name || 'foto HEIC').join(', ');
                        showUploadWarning(`Format HEIC/HEIF belum didukung: ${names}. Ubah pengaturan kamera ke Most Compatible/JPG atau pilih foto JPG/PNG.`);
                    }

                    if (supportedFiles.length === 0) {
                        if (sourceInput === input) {
                            syncInputFiles();
                        } else {
                            sourceInput.value = '';
                        }

                        return;
                    }

                    sourceInput.dataset.uploadProcessing = '1';
                    if (input) {
                        input.dataset.uploadProcessing = '1';
                    }

                    if (message) {
                        message.textContent = supportedFiles.some((file) => SUPPORTED_IMAGE_MIME.test(file.type) && file.size > MAX_IMAGE_BYTES)
                            ? 'Mengompres foto agar ukuran upload aman...'
                            : '';
                    }

                    const compressedFiles = [];

                    for (const file of supportedFiles) {
                        try {
                            compressedFiles.push(await compressImageFile(file));
                        } catch (error) {
                            compressedFiles.push(file);
                        }
                    }

                    sourceInput.dataset.uploadProcessing = '0';
                    if (input) {
                        input.dataset.uploadProcessing = '0';
                    }

                    const reducedCount = compressedFiles.filter((file, index) => file.size < supportedFiles[index].size).length;
                    let statusMessage = '';
                    if (reducedCount > 0) {
                        statusMessage = `${reducedCount} foto dikompres otomatis sebelum upload.`;
                    }

                    const nextFiles = input?.multiple ? selectedFiles.concat(compressedFiles) : compressedFiles.slice(0, 1);

                    if (maxFiles && nextFiles.length > maxFiles) {
                        selectedFiles = nextFiles.slice(0, maxFiles);
                        statusMessage = `Maksimal ${maxFiles} file. Sebagian file tidak ditambahkan.`;
                    } else {
                        selectedFiles = nextFiles;
                    }

                    syncInputFiles();
                    renderPreview();
                    if (message) {
                        message.textContent = statusMessage;
                    }

                    if (sourceInput !== input) {
                        sourceInput.value = '';
                    }
                };

                input?.addEventListener('change', async () => {
                    await addFiles(Array.from(input.files || []), input);
                });

                cameraInput?.addEventListener('change', async () => {
                    await addFiles(Array.from(cameraInput.files || []), cameraInput);
                });
            });

            document.querySelectorAll('form[data-confirm-submit]').forEach((form) => {
                form.addEventListener('submit', (event) => {
                    const processingInput = form.querySelector('[data-upload-input][data-upload-processing="1"]');

                    if (processingInput) {
                        event.preventDefault();
                        showUploadWarning('Foto masih diproses. Tunggu beberapa detik lalu submit ulang.');
                        return;
                    }

                    const totalUploadBytes = Array.from(form.querySelectorAll('input[type="file"]'))
                        .flatMap((input) => Array.from(input.files || []))
                        .reduce((total, file) => total + file.size, 0);

                    if (totalUploadBytes > MAX_TOTAL_UPLOAD_BYTES) {
                        event.preventDefault();
                        showUploadWarning('Total ukuran lampiran masih terlalu besar. Kurangi jumlah lampiran atau pilih file yang lebih kecil.');
                        return;
                    }
                });
            });
        })();
    </script>
@endpush

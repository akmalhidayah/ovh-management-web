@once
    @push('scripts')
        <script>
        (() => {
            const notify = (icon, title, text = '') => {
                if (window.Swal) {
                    window.Swal.fire({
                        icon,
                        title,
                        text,
                        timer: icon === 'success' ? 1800 : 2600,
                        showConfirmButton: false,
                    });
                    return;
                }

                alert([title, text].filter(Boolean).join('\n'));
            };

            const copyText = async (text) => {
                if (window.isSecureContext && navigator.clipboard?.writeText) {
                    try {
                        await navigator.clipboard.writeText(text);
                        return;
                    } catch (error) {
                        // Fall back below for HTTP/IP access where Clipboard API is blocked.
                    }
                }

                const textarea = document.createElement('textarea');
                textarea.value = text;
                textarea.setAttribute('readonly', 'readonly');
                textarea.style.position = 'fixed';
                textarea.style.top = '-1000px';
                textarea.style.left = '-1000px';
                document.body.appendChild(textarea);
                textarea.focus();
                textarea.select();

                const copied = document.execCommand('copy');
                document.body.removeChild(textarea);

                if (!copied) {
                    window.prompt('Browser menolak copy otomatis. Salin link approval ini:', text);
                    return;
                }
            };

            const parseResponse = async (response) => {
                const contentType = response.headers.get('content-type') || '';
                if (contentType.includes('application/json')) {
                    return response.json();
                }

                return { message: await response.text() };
            };

            document.querySelectorAll('[data-copy-approval-link-url]').forEach((button) => {
                if (button.dataset.copyApprovalBound === '1') return;
                button.dataset.copyApprovalBound = '1';

                button.addEventListener('click', async () => {
                    const original = button.innerHTML;
                    const originalClassName = button.className;
                    button.disabled = true;
                    button.innerHTML = '<i class="bi bi-hourglass-split"></i>';

                    try {
                        const response = await fetch(button.dataset.copyApprovalLinkUrl, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                        });
                        const payload = await parseResponse(response);

                        if (!response.ok || !payload.url) {
                            throw new Error(payload.message || 'Link approval gagal dibuat.');
                        }

                        await copyText(payload.url);
                        button.classList.remove('btn-warning', 'btn-danger', 'is-copy-error');
                        button.classList.add(button.classList.contains('approval-copy-link-btn') ? 'is-copy-success' : 'btn-success');
                        button.innerHTML = '<i class="bi bi-check-lg"></i>';
                        notify('success', 'Berhasil disalin', 'Link approval sudah tersalin.');
                    } catch (error) {
                        button.classList.remove('btn-warning', 'btn-success', 'is-copy-success');
                        button.classList.add(button.classList.contains('approval-copy-link-btn') ? 'is-copy-error' : 'btn-danger');
                        button.innerHTML = '<i class="bi bi-exclamation-triangle"></i>';
                        notify('error', 'Gagal salin link', error.message || 'Silakan coba lagi.');
                    } finally {
                        setTimeout(() => {
                            button.innerHTML = original;
                            button.className = originalClassName;
                            button.disabled = false;
                        }, 2200);
                    }
                });
            });

            document.querySelectorAll('[data-delete-submission-form]').forEach((form) => {
                if (form.dataset.deleteBound === '1') return;
                form.dataset.deleteBound = '1';

                form.addEventListener('submit', async (event) => {
                    if (!window.Swal) {
                        if (!confirm('Hapus form ini? Link approval aktif akan dibatalkan.')) {
                            event.preventDefault();
                        }
                        return;
                    }

                    event.preventDefault();
                    const result = await window.Swal.fire({
                        icon: 'warning',
                        title: 'Hapus form?',
                        text: 'Link approval aktif akan dibatalkan dan form dihapus dari riwayat.',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, hapus',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#dc2626',
                    });

                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        })();
        </script>
    @endpush
@endonce

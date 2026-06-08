(() => {
    const readLimit = (name) => {
        const value = Number(document.querySelector(`meta[name="${name}"]`)?.content || 0);

        return Number.isFinite(value) && value > 0 ? value : 0;
    };

    const maxFileBytes = readLimit('upload-max-file-bytes');
    const maxTotalBytes = readLimit('upload-max-request-bytes');

    if (!maxFileBytes && !maxTotalBytes) {
        return;
    }

    const formatBytes = (bytes) => {
        if (bytes >= 1024 * 1024 * 1024) {
            return `${(bytes / (1024 * 1024 * 1024)).toFixed(1)} GB`;
        }

        if (bytes >= 1024 * 1024) {
            return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
        }

        return `${Math.ceil(bytes / 1024)} KB`;
    };

    const showWarning = (title, message) => {
        if (window.Swal) {
            window.Swal.fire({
                title,
                text: message,
                icon: 'warning',
                confirmButtonText: 'Pilih File Lebih Kecil',
                confirmButtonColor: '#2563eb',
            });
            return;
        }

        window.alert(message);
    };

    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        const files = Array.from(form.querySelectorAll('input[type="file"]'))
            .flatMap((input) => Array.from(input.files || []));

        if (files.length === 0) {
            return;
        }

        const oversizedFile = maxFileBytes
            ? files.find((file) => file.size > maxFileBytes)
            : null;

        if (oversizedFile) {
            event.preventDefault();
            event.stopImmediatePropagation();
            showWarning(
                'File terlalu besar',
                `${oversizedFile.name} berukuran ${formatBytes(oversizedFile.size)}. Batas server per file adalah ${formatBytes(maxFileBytes)}.`
            );
            return;
        }

        const totalBytes = files.reduce((total, file) => total + file.size, 0);

        if (maxTotalBytes && totalBytes > maxTotalBytes) {
            event.preventDefault();
            event.stopImmediatePropagation();
            showWarning(
                'Total upload terlalu besar',
                `Total file yang dipilih ${formatBytes(totalBytes)}, sedangkan batas aman server ${formatBytes(maxTotalBytes)}. Kurangi jumlah foto atau pilih file yang lebih kecil.`
            );
        }
    }, true);

    window.OvhUploadGuard = {
        maxFileBytes,
        maxTotalBytes,
        formatBytes,
    };
})();

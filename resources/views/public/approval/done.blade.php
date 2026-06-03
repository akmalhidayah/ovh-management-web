<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <style>
        body { margin: 0; background: #f4f6f8; color: #182230; font-family: Arial, sans-serif; }
        .box { max-width: 560px; margin: 80px auto; padding: 28px; background: #fff; border: 1px solid #d9e0e8; border-radius: 8px; text-align: center; }
        h1 { margin: 0 0 12px; font-size: 24px; }
        p { margin: 0; color: #475569; line-height: 1.6; }
        .actions { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; margin-top: 22px; }
        .btn { display: inline-flex; align-items: center; justify-content: center; min-height: 42px; padding: 0 18px; border-radius: 7px; border: 1px solid #cbd5e1; color: #334155; text-decoration: none; font-weight: 700; }
        .btn-primary { border-color: #2563eb; background: #2563eb; color: #fff; }
    </style>
</head>
<body>
    <main class="box">
        <h1>{{ $title }}</h1>
        <p>{{ $message }}</p>
        @if (! empty($signedPdfUrl))
            <div class="actions">
                <a href="{{ $signedPdfUrl }}" class="btn btn-primary" target="_blank" rel="noopener">{{ $signedPdfLabel ?? 'Lihat PDF' }}</a>
            </div>
        @endif
    </main>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        (() => {
            if (!window.Swal) {
                return;
            }

            window.Swal.fire({
                title: @json($title),
                text: @json($message),
                icon: @json($icon ?? 'success'),
                confirmButtonText: @json($confirmButtonText ?? 'Mengerti'),
                confirmButtonColor: '#2563eb',
            });
        })();
    </script>
</body>
</html>

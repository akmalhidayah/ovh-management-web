<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Upload Terlalu Besar - Overhaul PT. Semen Tonasa</title>
    @include('partials.tonasa-meta')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: grid;
            place-items: center;
            margin: 0;
            padding: 1.25rem;
            background: #f2f5f7;
            color: #172033;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .error-card {
            width: min(100%, 520px);
            padding: 1.4rem;
            border: 1px solid #dbe3ef;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 18px 42px rgba(15, 23, 42, .12);
        }

        .error-icon {
            display: inline-grid;
            place-items: center;
            width: 48px;
            height: 48px;
            margin-bottom: 1rem;
            border-radius: 12px;
            background: #fff1f0;
            color: #b42318;
            font-size: 1.35rem;
        }

        h1 {
            margin: 0 0 .55rem;
            font-size: 1.35rem;
            font-weight: 800;
        }

        p {
            margin: 0;
            color: #667085;
            line-height: 1.55;
        }

        .error-actions {
            display: flex;
            flex-wrap: wrap;
            gap: .65rem;
            margin-top: 1.2rem;
        }
    </style>
</head>
<body>
    <main class="error-card">
        <span class="error-icon"><i class="bi bi-images"></i></span>
        <h1>Upload foto terlalu besar</h1>
        <p>Total ukuran foto yang dikirim melebihi batas server. Kurangi jumlah foto pendukung atau pilih foto yang lebih kecil, lalu coba submit ulang.</p>
        <div class="error-actions">
            <button type="button" class="btn btn-primary" onclick="history.back()">Kembali ke form</button>
            <a href="{{ url('/') }}" class="btn btn-outline-secondary">Ke beranda</a>
        </div>
    </main>
</body>
</html>

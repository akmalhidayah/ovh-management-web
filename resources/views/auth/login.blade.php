<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - OVH Management</title>
    <link rel="icon" href="{{ asset('assets/images/logo/favicon.svg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/ovh-dashboard.css') }}" rel="stylesheet">
</head>
<body class="login-body">
    <main class="login-shell">
        <section class="login-visual">
            <img src="{{ asset('assets/images/illustrations/login-illustration.svg') }}" alt="Overhaul Management">
            <h1>Sistem Overhaul Management</h1>
            <p>Monitoring overhaul, procurement, schedule, commissioning, QC, equipment, MoM, dan dokumentasi dalam satu dashboard internal.</p>
        </section>

        <section class="login-card">
            <img src="{{ asset('assets/images/logo/logo-ovh.svg') }}" alt="OVH" class="login-logo">
            <h2>Masuk</h2>
            <p class="text-muted mb-4">Gunakan akun OVH Management.</p>

            @if ($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login.attempt') }}" class="vstack gap-3">
                @csrf
                <div>
                    <label class="form-label" for="email">Email</label>
                    <input class="form-control" id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
                </div>
                <div>
                    <label class="form-label" for="password">Password</label>
                    <input class="form-control" id="password" type="password" name="password" required>
                </div>
                <div class="d-flex justify-content-between align-items-center gap-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label" for="remember">Ingat saya</label>
                    </div>
                </div>
                <button class="btn btn-primary w-100" type="submit">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Login
                </button>
            </form>
        </section>
    </main>
</body>
</html>

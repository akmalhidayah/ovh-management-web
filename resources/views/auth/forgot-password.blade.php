<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lupa Password - Overhaul PT. Semen Tonasa</title>
    @include('partials.tonasa-meta')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/ovh-dashboard.css') }}" rel="stylesheet">
</head>
<body class="login-body">
    <main class="login-shell">
        <section class="login-visual">
            <h1>Reset Password<br>Unit Overhaul</h1>
        </section>

        <section class="login-card">
            <div class="login-logo-group">
                <img src="{{ asset('assets/images/logo/logo-sig.png') }}" alt="SIG" class="login-logo">
                <img src="{{ asset('assets/images/logo/logo-st2.png') }}" alt="Semen Tonasa" class="login-logo">
            </div>
            <h2>Lupa Password</h2>
            <p class="text-muted mb-4">Masukkan email akun Anda. Sistem akan mengirim link reset password ke email tersebut.</p>

            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="vstack gap-3">
                @csrf
                <div>
                    <label class="form-label" for="email">Email</label>
                    <input class="form-control" id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
                </div>
                <button class="btn btn-primary w-100" type="submit">
                    <i class="bi bi-envelope-paper me-2"></i>Kirim Link Reset
                </button>
                <a class="btn btn-outline-secondary w-100" href="{{ route('login') }}">
                    <i class="bi bi-arrow-left me-2"></i>Kembali ke Login
                </a>
            </form>
        </section>
    </main>
</body>
</html>

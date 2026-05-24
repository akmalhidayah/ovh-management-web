<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password - Overhaul PT. Semen Tonasa</title>
    @include('partials.tonasa-meta')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/ovh-dashboard.css') }}" rel="stylesheet">
</head>
<body class="login-body">
    <main class="login-shell">
        <section class="login-visual">
            <h1>Password Baru<br>Unit Overhaul</h1>
        </section>

        <section class="login-card">
            <div class="login-logo-group">
                <img src="{{ asset('assets/images/logo/logo-sig.png') }}" alt="SIG" class="login-logo">
                <img src="{{ asset('assets/images/logo/logo-st2.png') }}" alt="Semen Tonasa" class="login-logo">
            </div>
            <h2>Reset Password</h2>

            @if ($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="vstack gap-3">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label class="form-label" for="email">Email</label>
                    <input class="form-control" id="email" type="email" name="email" value="{{ old('email', $email) }}" required autofocus>
                </div>
                <div>
                    <label class="form-label" for="password">Password Baru</label>
                    <input class="form-control" id="password" type="password" name="password" required autocomplete="new-password">
                </div>
                <div>
                    <label class="form-label" for="password_confirmation">Konfirmasi Password Baru</label>
                    <input class="form-control" id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
                </div>

                <button class="btn btn-primary w-100" type="submit">
                    <i class="bi bi-key me-2"></i>Simpan Password Baru
                </button>
            </form>
        </section>
    </main>
</body>
</html>

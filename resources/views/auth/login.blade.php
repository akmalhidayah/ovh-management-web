<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Overhaul PT. Semen Tonasa</title>
    @include('partials.tonasa-meta')
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/ovh-dashboard.css') }}" rel="stylesheet">
</head>
<body class="login-body">
    <main class="login-shell">
        <section class="login-visual">
            <h1>Unit Overhaul<br>PT. Semen Tonasa</h1>
        </section>

        <section class="login-card">
            <div class="login-logo-group">
                <img src="{{ asset('assets/images/logo/logo-sig.png') }}" alt="SIG" class="login-logo">
                <img src="{{ asset('assets/images/logo/logo-st2.png') }}" alt="Semen Tonasa" class="login-logo">
            </div>
            <h2>Masuk</h2>

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

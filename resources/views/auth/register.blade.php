<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register - Overhaul PT. Semen Tonasa</title>
    @include('partials.tonasa-meta')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/ovh-dashboard.css') }}" rel="stylesheet">
</head>
<body class="login-body">
    <main class="login-shell register-shell">
        <section class="login-visual">
            <h1>Unit Overhaul<br>PT. Semen Tonasa</h1>
        </section>

        <section class="login-card register-card">
            <div class="login-logo-group">
                <img src="{{ asset('assets/images/logo/logo-sig.png') }}" alt="SIG" class="login-logo">
                <img src="{{ asset('assets/images/logo/logo-st2.png') }}" alt="Semen Tonasa" class="login-logo">
            </div>
            <h2>Register User</h2>
            <p class="text-muted mb-4">Buat akun user operasional dan pilih role akses dashboard.</p>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Registrasi belum valid.</strong>
                    <ul class="mb-0 mt-2 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register.store') }}" class="vstack gap-3">
                @csrf
                <div>
                    <label class="form-label" for="name">Nama Lengkap</label>
                    <input class="form-control @error('name') is-invalid @enderror" id="name" type="text" name="name" value="{{ old('name') }}" required autofocus>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row g-3">
                    <div class="col-md-7">
                        <label class="form-label" for="email">Email</label>
                        <input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email') }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="phone">Nomor HP</label>
                        <input class="form-control @error('phone') is-invalid @enderror" id="phone" type="text" name="phone" value="{{ old('phone') }}" placeholder="0812-xxxx-xxxx">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div>
                    <label class="form-label" for="role">Role User</label>
                    <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                        <option value="" disabled @selected(! old('role'))>Pilih role</option>
                        @foreach ($roles as $value => $label)
                            <option value="{{ $value }}" @selected(old('role') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="password">Password</label>
                        <input class="form-control @error('password') is-invalid @enderror" id="password" type="password" name="password" required autocomplete="new-password">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="password_confirmation">Konfirmasi Password</label>
                        <input class="form-control" id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
                    </div>
                </div>

                <button class="btn btn-primary w-100 mt-1" type="submit">
                    <i class="bi bi-person-plus me-2"></i>Buat Akun
                </button>
            </form>
        </section>
    </main>
</body>
</html>

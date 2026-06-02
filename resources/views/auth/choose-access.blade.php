<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pilih Akses - Overhaul PT. Semen Tonasa</title>
    @include('partials.tonasa-meta')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/ovh-dashboard.css') }}?v={{ file_exists(public_path('assets/css/ovh-dashboard.css')) ? filemtime(public_path('assets/css/ovh-dashboard.css')) : time() }}" rel="stylesheet">
    <style>
        .access-choice-body { min-height: 100vh; display: grid; place-items: center; padding: 1.25rem; background: #eef3f8; }
        .access-choice-card { width: min(100%, 720px); padding: 1.5rem; border: 1px solid #dbe3ef; border-radius: .95rem; background: #fff; box-shadow: 0 1.25rem 3rem rgba(15, 23, 42, .12); }
        .access-choice-head { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.25rem; }
        .access-choice-logo { width: 52px; height: 52px; object-fit: contain; }
        .access-choice-head h1 { margin: 0; color: #172033; font-size: 1.35rem; font-weight: 850; }
        .access-choice-head p { margin: .2rem 0 0; color: #64748b; }
        .access-choice-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .9rem; }
        .access-choice-option { width: 100%; min-height: 142px; display: flex; align-items: flex-start; gap: .8rem; padding: 1rem; border: 1px solid #dbe3ef; border-radius: .8rem; background: #f8fafc; text-align: left; transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease; }
        .access-choice-option:hover,
        .access-choice-option:focus { border-color: #2563eb; box-shadow: 0 .75rem 1.8rem rgba(37, 99, 235, .16); transform: translateY(-1px); }
        .access-choice-icon { width: 42px; height: 42px; display: grid; place-items: center; flex: 0 0 auto; border-radius: .65rem; color: #fff; background: #2563eb; font-size: 1.1rem; }
        .access-choice-copy strong { display: block; color: #172033; font-size: .98rem; }
        .access-choice-copy span { display: block; margin-top: .28rem; color: #64748b; font-size: .86rem; line-height: 1.42; }
        .access-choice-footer { display: flex; justify-content: flex-end; margin-top: 1rem; }
        @media (max-width: 640px) {
            .access-choice-card { padding: 1.1rem; }
            .access-choice-grid { grid-template-columns: 1fr; }
            .access-choice-head { align-items: flex-start; flex-direction: column; }
        }
    </style>
</head>
<body class="access-choice-body">
    <main class="access-choice-card">
        <div class="access-choice-head">
            <div>
                <h1>Pilih Akses</h1>
                <p>{{ $user->name }} memiliki lebih dari satu akses.</p>
            </div>
            <img src="{{ asset('assets/images/logo/logo-st2.png') }}" alt="Semen Tonasa" class="access-choice-logo">
        </div>

        <div class="access-choice-grid">
            @foreach ($modes as $mode => $item)
                <form method="POST" action="{{ route('access.choose.store') }}">
                    @csrf
                    <input type="hidden" name="mode" value="{{ $mode }}">
                    <button type="submit" class="access-choice-option">
                        <span class="access-choice-icon"><i class="bi {{ $item['icon'] }}"></i></span>
                        <span class="access-choice-copy">
                            <strong>{{ $item['label'] }}</strong>
                            <span>{{ $item['description'] }}</span>
                        </span>
                    </button>
                </form>
            @endforeach
        </div>

        <div class="access-choice-footer">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-secondary">
                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                </button>
            </form>
        </div>
    </main>
</body>
</html>

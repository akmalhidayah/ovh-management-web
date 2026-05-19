<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') - Overhaull PT. Semen Tonasa</title>
    @include('partials.tonasa-meta')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/ovh-dashboard.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
    <div class="ovh-shell">
        @include('partials.sidebar')

        <div class="sidebar-backdrop" data-sidebar-close></div>

        <div class="ovh-main">
            @include('partials.topbar')

            <main class="ovh-content">
                @include('partials.breadcrumb')
                @yield('content')
            </main>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script src="{{ asset('assets/js/ovh-dashboard.js') }}"></script>
    @stack('scripts')
</body>
</html>

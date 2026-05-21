<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Overhaul PT. Semen Tonasa</title>
    @include('partials.tonasa-meta')
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">
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

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script src="{{ asset('assets/js/ovh-dashboard.js') }}"></script>
    @stack('scripts')
</body>
</html>

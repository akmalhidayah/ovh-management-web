@php
    $roleUi = $roleUi ?? \App\Support\UserRoleUiData::layout(auth()->user()?->role ?? 'qc');
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $roleUi['brand_title']) - OVH Management</title>
    <link rel="icon" href="{{ asset('assets/images/logo/logo-user.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/user-role.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="inspector-body">
    @include('partials.user.topbar', ['roleUi' => $roleUi])
    @include('partials.user.mobile-menu', ['roleUi' => $roleUi])

    <main class="inspector-main">
        <div class="container-xxl inspector-container">
            @yield('content')
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/user-role.js') }}"></script>
    @stack('scripts')
</body>
</html>

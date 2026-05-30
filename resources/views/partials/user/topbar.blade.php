@php
    $currentUser = auth()->user();
    $profilePhotoUrl = $currentUser?->profilePhotoUrl();
    $userNotifications = \App\Support\UserTopbarNotifications::make($roleUi['role'] ?? '');
    $notificationCount = (int) ($userNotifications['count'] ?? 0);
    $notificationTotal = (int) ($userNotifications['total'] ?? $notificationCount);
    $notificationItems = collect($userNotifications['items'] ?? []);
@endphp

<header class="inspector-topbar">
    <div class="container-xxl">
        <div class="inspector-topbar-shell">
            <div class="d-flex align-items-center gap-3 min-w-0">
                <button class="btn inspector-icon-btn d-xl-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#userRoleMobileMenu" aria-controls="userRoleMobileMenu" aria-label="Buka menu user">
                    <i class="bi bi-list"></i>
                </button>

                <a href="{{ route($roleUi['nav'][0]['route'] ?? 'login') }}" class="inspector-brand">
                    <span class="user-brand-mark">
                        <img src="{{ asset('assets/images/logo/logo-st2.png') }}" alt="Semen Tonasa">
                    </span>
                    <div class="min-w-0 user-brand-copy">
                        <span class="inspector-brand-title">{{ $roleUi['brand_title'] }}</span>
                        <span class="inspector-brand-subtitle">{{ $roleUi['brand_subtitle'] ?? 'Sistem Overhaul Management' }}</span>
                    </div>
                </a>
            </div>

            <nav class="inspector-nav d-none d-xl-flex" aria-label="Navigasi role user">
                @foreach ($roleUi['nav'] as $item)
                    <a href="{{ route($item['route']) }}" class="inspector-nav-link {{ request()->routeIs($item['route']) ? 'active' : '' }}">
                        <i class="bi {{ $item['icon'] }}"></i>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="inspector-topbar-tools">
                <div class="inspector-search user-topbar-search d-none d-xxl-flex">
                    <i class="bi bi-search"></i>
                    <input type="search" class="form-control" placeholder="Cari...">
                </div>

                <div class="dropdown">
                    <button class="btn inspector-icon-btn position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifikasi">
                        <i class="bi bi-bell"></i>
                        @if ($notificationCount > 0)
                            <span class="user-notification-badge">{{ $notificationCount > 99 ? '99+' : $notificationCount }}</span>
                        @endif
                    </button>
                    <div class="dropdown-menu dropdown-menu-end user-notification-menu">
                        <div class="user-notification-head">
                            <div>
                                <strong>Notifikasi</strong>
                                <span>{{ $notificationCount }} belum dibaca dari {{ $notificationTotal }} equipment aktif</span>
                            </div>
                            @if ($notificationCount > 0)
                                <form method="POST" action="{{ route('user.'.$roleUi['role'].'.notifications.read-all') }}">
                                    @csrf
                                    <button type="submit" class="user-notification-read-all">Tandai dibaca</button>
                                </form>
                            @endif
                        </div>
                        @forelse ($notificationItems as $item)
                            <a class="user-notification-item {{ ! empty($item['is_read']) ? 'is-read' : 'is-unread' }}" href="{{ $item['url'] }}">
                                <span class="user-notification-type">{{ $item['type'] }}</span>
                                <span class="user-notification-body">
                                    <strong>{{ $item['title'] }}</strong>
                                    @if (! empty($item['description']))
                                        <small>{{ $item['description'] }}</small>
                                    @endif
                                    @if ($item['meta'])
                                        <small>{{ $item['meta'] }}</small>
                                    @endif
                                </span>
                                <span class="user-notification-state">{{ ! empty($item['is_read']) ? 'Dibaca' : 'Baru' }}</span>
                            </a>
                        @empty
                            <div class="user-notification-empty">Belum ada equipment aktif baru.</div>
                        @endforelse
                    </div>
                </div>

                <div class="dropdown">
                    <button class="btn inspector-user-chip dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="inspector-avatar">
                            @if ($profilePhotoUrl)
                                <img src="{{ $profilePhotoUrl }}" alt="{{ $currentUser->name }}">
                            @else
                                {{ strtoupper(substr($currentUser->name ?? 'U', 0, 1)) }}
                            @endif
                        </span>
                        <span class="d-none d-md-flex flex-column text-start">
                            <strong>{{ $currentUser->name }}</strong>
                            <small>{{ strtoupper($roleUi['role_label']) }}</small>
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 inspector-dropdown">
                        <li><span class="dropdown-item-text small text-muted">{{ $currentUser->email }}</span></li>
                        <li><a class="dropdown-item" href="{{ route('user.'.$roleUi['role'].'.profile') }}"><i class="bi bi-person-circle me-2"></i>Profil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="dropdown-item" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</header>

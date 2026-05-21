<header class="ovh-topbar">
    <div class="d-flex align-items-center gap-2 min-w-0">
        <button class="btn btn-light icon-btn" type="button" data-sidebar-toggle aria-label="Toggle sidebar">
            <i class="bi bi-list"></i>
        </button>
        <div class="topbar-title min-w-0">
            <span class="text-truncate d-block">@yield('page_title', 'Dashboard')</span>
            <small class="text-muted d-none d-sm-block">Overhaul PT. Semen Tonasa</small>
        </div>
    </div>

    <div class="d-flex align-items-center gap-2">
        <div class="topbar-logos d-none d-sm-flex">
            <img src="{{ asset('assets/images/logo/logo-sig.png') }}" alt="SIG" class="company-logo">
            <img src="{{ asset('assets/images/logo/logo-st2.png') }}" alt="Semen Tonasa" class="company-logo">
        </div>
        <div class="dropdown">
            <button class="btn btn-light user-menu" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
                <span class="d-none d-md-inline text-truncate">{{ auth()->user()->name }}</span>
                <i class="bi bi-chevron-down small"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><span class="dropdown-item-text small text-muted">{{ auth()->user()->email }}</span></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="dropdown-item" type="submit">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>

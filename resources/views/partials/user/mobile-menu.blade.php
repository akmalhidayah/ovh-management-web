@php
    $currentUser = auth()->user();
    $profilePhotoUrl = $currentUser?->profilePhotoUrl();
@endphp

<div class="offcanvas offcanvas-start inspector-offcanvas" tabindex="-1" id="userRoleMobileMenu" aria-labelledby="userRoleMobileMenuLabel">
    <div class="offcanvas-header">
        <div class="d-flex align-items-center gap-3">
            <span class="user-brand-mark">
                <img src="{{ asset('assets/images/logo/logo-st2.png') }}" alt="Semen Tonasa">
            </span>
            <div>
                <h5 class="offcanvas-title mb-0" id="userRoleMobileMenuLabel">{{ $roleUi['brand_title'] }}</h5>
                <small class="text-muted">Sistem Overhaul Management</small>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column gap-4">
        <div class="user-company-logos mobile">
            <img src="{{ asset('assets/images/logo/logo-sig.png') }}" alt="SIG">
            <span></span>
            <img src="{{ asset('assets/images/logo/logo-st2.png') }}" alt="Semen Tonasa">
        </div>

        <div class="inspector-search">
            <i class="bi bi-search"></i>
            <input type="search" class="form-control" placeholder="Cari halaman atau dokumen...">
        </div>

        <nav class="d-grid gap-2" aria-label="Navigasi mobile user role">
            @foreach ($roleUi['nav'] as $item)
                <a href="{{ route($item['route']) }}" class="inspector-mobile-link {{ request()->routeIs($item['route']) ? 'active' : '' }}">
                    <i class="bi {{ $item['icon'] }}"></i>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <div class="inspector-mobile-profile">
            <div class="d-flex align-items-center gap-3">
                <span class="inspector-avatar">
                    @if ($profilePhotoUrl)
                        <img src="{{ $profilePhotoUrl }}" alt="{{ $currentUser->name }}">
                    @else
                        {{ strtoupper(substr($currentUser->name ?? 'U', 0, 1)) }}
                    @endif
                </span>
                <div>
                    <strong class="d-block">{{ $currentUser->name }}</strong>
                    <small class="text-muted">{{ $currentUser->email }}</small>
                </div>
            </div>
        </div>
    </div>
</div>

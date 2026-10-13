<div class="page-breadcrumb">
    <span>{{ request()->routeIs('admin.*') && auth()->user()?->hasAdminPanelAccess() ? 'Admin' : 'Inspector' }}</span>
    <i class="bi bi-chevron-right"></i>
    @hasSection('breadcrumb_parent')
        <span>@yield('breadcrumb_parent')</span>
        <i class="bi bi-chevron-right"></i>
    @endif
    <strong>@yield('page_title', 'Dashboard')</strong>
    @hasSection('breadcrumb_actions')
        <div class="page-breadcrumb-actions">
            @yield('breadcrumb_actions')
        </div>
    @endif
</div>

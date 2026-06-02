<div class="page-breadcrumb">
    <span>{{ request()->routeIs('admin.*') && auth()->user()?->hasAdminPanelAccess() ? 'Admin' : 'Inspector' }}</span>
    <i class="bi bi-chevron-right"></i>
    <strong>@yield('page_title', 'Dashboard')</strong>
</div>

<div class="page-breadcrumb">
    <span>{{ auth()->user()->usertype === 'admin' ? 'Admin' : 'Inspector' }}</span>
    <i class="bi bi-chevron-right"></i>
    <strong>@yield('page_title', 'Dashboard')</strong>
</div>

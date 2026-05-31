<?php

namespace App\Http\Middleware;

use App\Support\AdminMenuPermissions;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminMenuPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $routeName = (string) $request->route()?->getName();
        $menuKey = $this->menuKeyForRoute($routeName);

        if ($menuKey && ! AdminMenuPermissions::canSee($user, $menuKey)) {
            abort(403);
        }

        return $next($request);
    }

    private function menuKeyForRoute(string $routeName): ?string
    {
        if ($routeName === 'admin.dashboard') {
            return 'dashboard.dashboard';
        }

        if ($routeName === 'admin.overview') {
            return 'dashboard.overview';
        }

        if ($routeName === 'admin.kalender-overhaul') {
            return 'planning.kalender-overhaul';
        }

        if ($routeName === 'admin.schedule') {
            return 'planning.schedule';
        }

        if ($routeName === 'admin.procurement') {
            return 'procurement.index';
        }

        if ($routeName === 'admin.equipment') {
            return 'asset_records.equipment';
        }

        if ($routeName === 'admin.mom') {
            return 'asset_records.mom';
        }

        if ($routeName === 'admin.dokumen') {
            return 'asset_records.dokumen';
        }

        if (str_starts_with($routeName, 'admin.template-form-qc.')) {
            return 'qc_commissioning.template-qc';
        }

        if (str_starts_with($routeName, 'admin.template-form-commissioning.')) {
            return 'qc_commissioning.template-commissioning';
        }

        if (str_starts_with($routeName, 'admin.qc.submissions.') || $routeName === 'admin.qc') {
            return 'qc_commissioning.qc';
        }

        if (str_starts_with($routeName, 'admin.commissioning.submissions.') || $routeName === 'admin.commissioning') {
            return 'qc_commissioning.commissioning';
        }

        if (str_starts_with($routeName, 'admin.master-data')) {
            return 'master_data.equipment';
        }

        if (str_starts_with($routeName, 'admin.organization-sections')) {
            return 'master_data.unit-kerja';
        }

        if (str_starts_with($routeName, 'admin.user-panel.role-permission')) {
            return 'userpanel.role-permission';
        }

        if (str_starts_with($routeName, 'admin.user-panel')) {
            return 'userpanel.management';
        }

        return null;
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserType
{
    public function handle(Request $request, Closure $next, string $usertype): Response
    {
        $user = $request->user();

        if ($user?->hasMultipleAccessModes() && ! $request->session()->has('active_access_mode')) {
            return redirect()->route('access.choose');
        }

        $allowed = $usertype === 'admin'
            ? (bool) $user?->hasAdminPanelAccess()
            : (bool) $user && $user->usertype === $usertype;

        if (! $allowed) {
            if ($user) {
                return redirect()->route($user->dashboardRouteName());
            }

            abort(403);
        }

        if ($user) {
            $request->session()->put('active_access_mode', $usertype === 'admin' ? 'admin' : 'user');
        }

        return $next($request);
    }
}

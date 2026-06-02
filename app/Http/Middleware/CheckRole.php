<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasUserRole($role)) {
            if ($user) {
                return redirect()->route($user->dashboardRouteName());
            }

            abort(403);
        }

        $request->session()->put('active_access_mode', 'user');
        $request->session()->put('active_user_role', $role);

        return $next($request);
    }
}

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

        if (! $user || $user->usertype !== $usertype) {
            if ($user) {
                return redirect()->route($user->dashboardRouteName());
            }

            abort(403);
        }

        return $next($request);
    }
}

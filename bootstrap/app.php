<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\CheckAdminMenuPermission;
use App\Http\Middleware\CheckUserType;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'usertype' => CheckUserType::class,
            'role' => CheckRole::class,
            'adminmenu' => CheckAdminMenuPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Illuminate\Session\TokenMismatchException $exception, Illuminate\Http\Request $request) {
            $sessionCookie = (string) config('session.cookie');
            $hasSessionCookie = $sessionCookie !== '' && $request->cookies->has($sessionCookie);
            $hasCsrfToken = $request->request->has('_token')
                || $request->headers->has('X-CSRF-TOKEN')
                || $request->headers->has('X-XSRF-TOKEN');
            $user = $request->user();

            Illuminate\Support\Facades\Log::warning('csrf_token_mismatch', [
                'actor_id' => $user?->getAuthIdentifier(),
                'method' => $request->method(),
                'path' => $request->path(),
                'has_session_cookie' => $hasSessionCookie,
                'has_csrf_token' => $hasCsrfToken,
                'content_length' => (int) $request->server('CONTENT_LENGTH', 0),
                'reason' => ! $hasSessionCookie
                    ? 'missing_session_cookie'
                    : (! $hasCsrfToken
                        ? 'missing_csrf_token'
                        : ($user ? 'token_mismatch_or_regenerated_session' : 'unauthenticated_session')),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Sesi halaman sudah kedaluwarsa. Muat ulang halaman, lalu coba lagi.',
                ], 419);
            }

            if ($request->is('login') || ! $user) {
                return redirect()
                    ->route('login')
                    ->withErrors(['email' => 'Sesi sudah kedaluwarsa atau berubah. Silakan login ulang, lalu buka kembali form.']);
            }

            return redirect()
                ->back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors(['session' => 'Sesi form sudah berubah. Muat ulang halaman dan pilih kembali foto sebelum submit. Jangan login akun lain di tab browser yang sama.']);
        });

        $exceptions->render(function (Illuminate\Http\Exceptions\PostTooLargeException $exception, Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Ukuran upload terlalu besar. Kurangi ukuran atau jumlah foto, lalu coba lagi.',
                ], 413);
            }

            return response()->view('errors.413', [], 413);
        });
    })->create();

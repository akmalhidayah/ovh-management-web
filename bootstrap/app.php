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
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Sesi halaman sudah kedaluwarsa. Muat ulang halaman, lalu coba lagi.',
                ], 419);
            }

            if ($request->is('login')) {
                return redirect()
                    ->route('login')
                    ->withErrors(['email' => 'Sesi login sudah kedaluwarsa. Silakan login ulang.']);
            }

            return redirect()
                ->back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors(['session' => 'Sesi halaman sudah kedaluwarsa. Muat ulang halaman, lalu coba lagi.']);
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

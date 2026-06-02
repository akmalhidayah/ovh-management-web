<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class PasswordResetLinkController extends Controller
{
    private const MAX_RESET_LINK_ATTEMPTS = 3;
    private const RESET_LINK_DECAY_SECONDS = 300;

    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $throttleKey = self::throttleKey($request);
        $email = Str::lower((string) $request->input('email'));

        Log::info('password_reset_link_requested', [
            'email' => $email,
            'ip' => $request->ip(),
            'mailer' => config('mail.default'),
        ]);

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_RESET_LINK_ATTEMPTS)) {
            Log::warning('password_reset_link_throttled', [
                'email' => $email,
                'ip' => $request->ip(),
                'available_in' => RateLimiter::availableIn($throttleKey),
            ]);

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => self::tooManyAttemptsMessage(RateLimiter::availableIn($throttleKey))]);
        }

        RateLimiter::hit($throttleKey, self::RESET_LINK_DECAY_SECONDS);

        try {
            $status = Password::sendResetLink($request->only('email'));
        } catch (Throwable $exception) {
            Log::error('password_reset_email_failed_to_send', [
                'email' => $email,
                'ip' => $request->ip(),
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Email reset belum bisa dikirim. Periksa konfigurasi SMTP.']);
        }

        Log::info('password_reset_link_result', [
            'email' => $email,
            'ip' => $request->ip(),
            'status' => $status,
        ]);

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', self::message($status));
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => self::message($status)]);
    }

    public static function message(string $status): string
    {
        return match ($status) {
            Password::RESET_LINK_SENT => 'Link reset password sudah dikirim ke email.',
            Password::RESET_THROTTLED => 'Permintaan reset terlalu sering. Silakan tunggu sebentar.',
            Password::INVALID_USER => 'Email tidak terdaftar.',
            default => 'Reset password belum bisa diproses.',
        };
    }

    private static function throttleKey(Request $request): string
    {
        return 'password-reset:'.Str::lower((string) $request->input('email')).'|'.$request->ip();
    }

    private static function tooManyAttemptsMessage(int $seconds): string
    {
        $minutes = max(1, (int) ceil($seconds / 60));

        return "Terlalu banyak permintaan reset password. Coba lagi dalam {$minutes} menit.";
    }
}

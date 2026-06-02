<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return back()
                ->withErrors(['email' => 'Email atau password tidak sesuai.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::user();
        $request->session()->forget('active_access_mode');

        if ($user->hasMultipleAccessModes()) {
            Log::info('login_multi_access_choice_required', [
                'actor_id' => $user->id,
                'user_role' => $user->role,
                'admin_role' => $user->effectiveAdminRole(),
                'controller' => self::class,
                'status_code' => 302,
            ]);

            return redirect()->route('access.choose');
        }

        $request->session()->put('active_access_mode', $user->hasAdminPanelAccess() ? 'admin' : 'user');

        return redirect()->intended(route(Auth::user()->dashboardRouteName()));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

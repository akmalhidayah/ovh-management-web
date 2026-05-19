<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * @return array<string, string>
     */
    public static function roles(): array
    {
        return [
            'qc' => 'QC',
            'commissioning' => 'Commissioning',
            'pgo' => 'PGO',
            'approval' => 'Approval',
        ];
    }

    public function showRegistrationForm(): View
    {
        return view('auth.register', [
            'roles' => self::roles(),
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', Rule::in(array_keys(self::roles()))],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
            'usertype' => 'user',
            'role' => $validated['role'],
        ]);

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route($user->dashboardRouteName());
    }
}

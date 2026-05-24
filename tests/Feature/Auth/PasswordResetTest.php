<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_request_password_reset_email(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'qc@ovh.test',
        ]);

        $this->post(route('password.email'), [
            'email' => $user->email,
        ])->assertSessionHas('status', 'Link reset password sudah dikirim ke email.');

        Notification::assertSentTo($user, ResetPasswordNotification::class, function (ResetPasswordNotification $notification) use ($user): bool {
            $html = (string) $notification->toMail($user)->render();

            $this->assertStringContainsString('Unit Overhaul', $html);
            $this->assertStringContainsString('PT. Semen Tonasa', $html);
            $this->assertStringContainsString('Reset Password', $html);
            $this->assertStringNotContainsString('logo-st2.png', $html);

            return true;
        });
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'commissioning@ovh.test',
            'password' => 'old-password',
        ]);
        $token = Password::broker()->createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('new-password123', $user->refresh()->password));
    }

    public function test_password_reset_email_requests_are_limited_after_three_attempts(): void
    {
        Notification::fake();
        RateLimiter::clear('password-reset:limited@ovh.test|127.0.0.1');

        $user = User::factory()->create([
            'email' => 'limited@ovh.test',
        ]);

        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->post(route('password.email'), [
                'email' => $user->email,
            ]);
        }

        $this->post(route('password.email'), [
            'email' => $user->email,
        ])->assertSessionHasErrors('email');

        $this->assertStringContainsString(
            'Terlalu banyak permintaan reset password',
            session('errors')->first('email')
        );
    }
}

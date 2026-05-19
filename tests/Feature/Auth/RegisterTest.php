<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_page_can_be_opened_by_url(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('Register User')
            ->assertSee('Role User');
    }

    public function test_operational_user_can_register_with_role(): void
    {
        $response = $this->post('/register', [
            'name' => 'User Commissioning Baru',
            'email' => 'commissioning.baru@ovh.test',
            'phone' => '0812-3456-7890',
            'role' => 'commissioning',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'commissioning.baru@ovh.test')->firstOrFail();

        $response->assertRedirect(route('user.commissioning.dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertSame('user', $user->usertype);
        $this->assertSame('commissioning', $user->role);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_register_rejects_unknown_role(): void
    {
        $this->post('/register', [
            'name' => 'Admin Baru',
            'email' => 'admin.baru@ovh.test',
            'role' => 'admin',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasErrors('role');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', [
            'email' => 'admin.baru@ovh.test',
        ]);
    }
}

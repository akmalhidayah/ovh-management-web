<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_register_page_is_enabled(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('Register User')
            ->assertSee('QC')
            ->assertSee('Commissioning');
    }

    public function test_login_page_shows_register_link_when_public_register_enabled(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Belum punya akun?')
            ->assertSee(route('register'), false);
    }

    public function test_public_register_creates_operational_user(): void
    {
        $this->post('/register', [
            'name' => 'User QC Baru',
            'email' => 'qc.baru@ovh.test',
            'phone' => '081234567890',
            'role' => 'qc',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('user.qc.dashboard'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'qc.baru@ovh.test',
            'usertype' => 'user',
            'role' => 'qc',
        ]);
    }

    public function test_public_register_cannot_create_admin_user(): void
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

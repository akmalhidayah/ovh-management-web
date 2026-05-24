<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_register_page_is_disabled(): void
    {
        $this->get('/register')
            ->assertNotFound();
    }

    public function test_public_register_submission_is_disabled(): void
    {
        $this->post('/register', [
            'name' => 'Admin Baru',
            'email' => 'admin.baru@ovh.test',
            'role' => 'admin',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertNotFound();

        $this->assertGuest();
        $this->assertDatabaseMissing('users', [
            'email' => 'admin.baru@ovh.test',
        ]);
    }
}

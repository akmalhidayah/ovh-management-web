<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_shows_welcome_page_for_guests(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee(route('login'), false)
            ->assertSee(route('register'), false);
    }
}

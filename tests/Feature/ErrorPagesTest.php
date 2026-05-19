<?php

namespace Tests\Feature;

use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    public function test_missing_route_uses_custom_not_found_page(): void
    {
        $this->get('/halaman-yang-tidak-ada')
            ->assertNotFound()
            ->assertSee('Halaman tidak ditemukan');
    }
}

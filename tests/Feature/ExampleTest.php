<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Unauthenticated requests to / are redirected to login.
     */
    public function test_the_application_redirects_unauthenticated_users(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }
}

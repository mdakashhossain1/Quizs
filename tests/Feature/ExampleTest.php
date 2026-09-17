<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * The app has no public landing page — / always redirects into the
     * admin panel, which itself redirects to /admin/login when signed out.
     */
    public function test_root_redirects_into_the_admin_panel(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('admin.dashboard'));
    }
}

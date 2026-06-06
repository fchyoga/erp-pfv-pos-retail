<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(302);
        $response->assertRedirect('/pos');
    }

    public function test_pos_page_requires_authentication(): void
    {
        $response = $this->get('/pos');

        $response->assertStatus(302);
        $response->assertRedirect('/admin/login');
    }

    public function test_pos_page_loads_for_authenticated_users(): void
    {
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)->get('/pos');

        $response->assertStatus(200);
    }

    public function test_report_pages_load_successfully(): void
    {
        $user = \App\Models\User::factory()->create();

        $urls = [
            '/admin/best-seller-report',
            '/admin/payment-summary-report',
            '/admin/profit-margin-report',
            '/admin/slow-moving-report',
            '/admin/sales-report',
            '/admin/roles',
        ];

        foreach ($urls as $url) {
            $response = $this->actingAs($user)->get($url);
            $response->assertStatus(200);
        }
    }
}

<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CorsSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_api_responses_include_cors_headers(): void
    {
        $response = $this->getJson('/api/v1/products');

        $response->assertOk();
        $response->assertHeader('Access-Control-Allow-Origin');
    }

    public function test_preflight_request_returns_cors_headers(): void
    {
        $response = $this->call('OPTIONS', '/api/v1/products', [], [], [], [
            'HTTP_ORIGIN' => 'https://vestra.com',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'GET',
        ]);

        $response->assertStatus(204);
        $response->assertHeader('Access-Control-Allow-Origin');
        $response->assertHeader('Access-Control-Allow-Methods');
    }

    public function test_production_cors_does_not_use_wildcard_origin(): void
    {
        $this->app['config']->set('app.env', 'production');
        $this->app['config']->set('cors.allowed_origins', ['https://vestra.com']);

        $response = $this->getJson('/api/v1/products', [
            'Origin' => 'https://vestra.com',
        ]);

        $response->assertOk();

        $allowedOrigin = $response->headers->get('Access-Control-Allow-Origin');
        $this->assertNotSame('*', $allowedOrigin);
        $this->assertSame('https://vestra.com', $allowedOrigin);
    }
}

<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_api_responses_include_security_headers(): void
    {
        $response = $this->getJson('/api/v1/products');

        $response->assertOk();
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy');
        $response->assertHeader('Content-Security-Policy');
        $this->assertFalse($response->headers->has('X-Powered-By'));
    }

    public function test_hsts_header_is_set_in_production(): void
    {
        $this->app['env'] = 'production';

        $response = $this->getJson('/api/v1/products');

        $response->assertOk();
        $response->assertHeader('Strict-Transport-Security');
    }

    public function test_hsts_header_is_not_set_outside_production(): void
    {
        $this->app['env'] = 'local';

        $response = $this->getJson('/api/v1/products');

        $response->assertOk();
        $response->assertHeaderMissing('Strict-Transport-Security');
    }
}

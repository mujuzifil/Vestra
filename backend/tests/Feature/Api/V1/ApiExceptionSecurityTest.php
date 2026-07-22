<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;

class ApiExceptionSecurityTest extends TestCase
{
    public function test_production_exception_response_does_not_leak_debug_info(): void
    {
        $this->app['config']->set('app.debug', false);

        // Trigger a 404 on an undefined route.
        $response = $this->getJson('/api/v1/trigger-error');

        $response->assertNotFound();
        $this->assertArrayNotHasKey('exception', $response->json());
        $this->assertArrayNotHasKey('file', $response->json());
        $this->assertArrayNotHasKey('line', $response->json());
        $this->assertArrayNotHasKey('trace', $response->json());
    }
}

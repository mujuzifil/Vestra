<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebhookSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_callback_rejects_invalid_signature(): void
    {
        $response = $this->postJson('/api/v1/payments/callback', [
            'status' => 'successful',
            'tx_ref' => 'VST-PAY-TEST',
        ], [
            'verif-hash' => 'invalid-signature',
        ]);

        $response->assertForbidden();
        $this->assertSame('Invalid webhook signature.', $response->json('message'));
        $this->assertArrayNotHasKey('exception', $response->json());
    }

    public function test_payment_callback_rejects_missing_signature(): void
    {
        $response = $this->postJson('/api/v1/payments/callback', [
            'status' => 'successful',
            'tx_ref' => 'VST-PAY-TEST',
        ]);

        $response->assertForbidden();
    }
}

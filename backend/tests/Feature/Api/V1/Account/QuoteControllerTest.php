<?php

namespace Tests\Feature\Api\V1\Account;

use App\Models\QuoteRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class QuoteControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_list_own_quotes(): void
    {
        $user = User::factory()->create();
        QuoteRequest::factory()->count(3)->create(['user_id' => $user->id, 'email' => $user->email]);
        QuoteRequest::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/account/quotes');
        $response->assertOk()->assertJsonCount(3, 'data.data');
    }

    public function test_customer_can_view_own_quote(): void
    {
        $user = User::factory()->create();
        $quote = QuoteRequest::factory()->create(['user_id' => $user->id, 'email' => $user->email]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/account/quotes/{$quote->id}");
        $response->assertOk()->assertJsonPath('data.reference_number', $quote->reference_number);
    }

    public function test_customer_cannot_view_other_customer_quote(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $quote = QuoteRequest::factory()->create(['user_id' => $other->id, 'email' => $other->email]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/account/quotes/{$quote->id}");
        $response->assertNotFound();
    }

    public function test_viewing_quote_logs_activity(): void
    {
        $user = User::factory()->create();
        $quote = QuoteRequest::factory()->create(['user_id' => $user->id, 'email' => $user->email]);

        Sanctum::actingAs($user);

        $this->getJson("/api/v1/account/quotes/{$quote->id}")->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'quote_viewed',
            'subject_type' => QuoteRequest::class,
            'subject_id' => $quote->id,
        ]);
    }
}

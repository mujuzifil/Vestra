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

    public function test_customer_can_view_quote_linked_via_company_profile(): void
    {
        $user = User::factory()->create();
        $profile = \App\Models\CompanyProfile::factory()->create(['user_id' => $user->id]);
        $quote = QuoteRequest::factory()->create([
            'user_id' => null,
            'email' => $user->email,
            'company_profile_id' => $profile->id,
        ]);

        Sanctum::actingAs($user);

        $this->getJson("/api/v1/account/quotes/{$quote->id}")
            ->assertOk()
            ->assertJsonPath('data.reference_number', $quote->reference_number);
    }

    public function test_account_quote_list_reflects_admin_status_update(): void
    {
        $user = User::factory()->create();
        $quote = QuoteRequest::factory()->create([
            'user_id' => $user->id,
            'email' => $user->email,
            'status' => 'pending',
        ]);

        $quote->update(['status' => 'approved']);

        Sanctum::actingAs($user);

        $this->getJson("/api/v1/account/quotes/{$quote->id}")
            ->assertOk()
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonPath('data.status_label', 'Approved');
    }
}

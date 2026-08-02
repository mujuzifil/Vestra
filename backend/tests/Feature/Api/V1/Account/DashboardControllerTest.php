<?php

namespace Tests\Feature\Api\V1\Account;

use App\Models\QuoteRequest;
use App\Models\SavedItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_returns_customer_statistics(): void
    {
        $user = User::factory()->create();
        QuoteRequest::factory()->count(2)->create(['user_id' => $user->id, 'email' => $user->email, 'status' => 'pending']);
        SavedItem::factory()->count(3)->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/account/dashboard');
        $response->assertOk()
            ->assertJsonPath('data.quotes.submitted', 2)
            ->assertJsonPath('data.saved_products', 3);
    }
}

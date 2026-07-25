<?php

namespace Tests\Feature\Api\V1;

use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RecentlyViewedControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function customer(): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('customer');

        return $user;
    }

    public function test_customer_can_record_product_view(): void
    {
        $user = $this->customer();
        $product = Product::factory()->create();

        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson('/api/v1/auth/recently-viewed', [
            'product_id' => $product->id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('recently_viewed_products', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_customer_can_list_recently_viewed(): void
    {
        $user = $this->customer();
        $product = Product::factory()->create();
        \App\Models\RecentlyViewedProduct::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/v1/auth/recently-viewed');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data.items');
    }
}

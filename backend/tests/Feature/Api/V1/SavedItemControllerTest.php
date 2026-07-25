<?php

namespace Tests\Feature\Api\V1;

use App\Models\Product;
use App\Models\SavedItem;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SavedItemControllerTest extends TestCase
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

    public function test_customer_can_save_product_for_later(): void
    {
        $user = $this->customer();
        $product = Product::factory()->create();

        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson('/api/v1/auth/saved-for-later', [
            'product_id' => $product->id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('saved_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_customer_can_move_saved_item_to_cart(): void
    {
        $user = $this->customer();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        SavedItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson("/api/v1/auth/saved-for-later/{$product->id}/move-to-cart");

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
        ]);

        $this->assertDatabaseMissing('saved_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }
}

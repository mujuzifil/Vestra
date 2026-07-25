<?php

namespace Tests\Feature\Api\V1;

use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WishlistControllerTest extends TestCase
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

    public function test_customer_can_add_product_to_wishlist(): void
    {
        $user = $this->customer();
        $product = Product::factory()->create();

        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson('/api/v1/auth/wishlist', [
            'product_id' => $product->id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('wishlists', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_customer_can_remove_product_from_wishlist(): void
    {
        $user = $this->customer();
        $product = Product::factory()->create();
        \App\Models\Wishlist::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->deleteJson("/api/v1/auth/wishlist/{$product->id}");

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_customer_can_move_wishlist_item_to_cart(): void
    {
        $user = $this->customer();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        \App\Models\Wishlist::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson("/api/v1/auth/wishlist/{$product->id}/move-to-cart");

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
        ]);

        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }
}

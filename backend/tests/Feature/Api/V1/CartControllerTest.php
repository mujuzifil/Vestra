<?php

namespace Tests\Feature\Api\V1;

use App\Enums\ProductStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CartControllerTest extends TestCase
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

    public function test_authenticated_customer_can_add_active_product_to_cart(): void
    {
        $user = $this->customer();
        $product = Product::factory()->create(['stock_quantity' => 10, 'price' => 100]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.item_count', 3);

        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $user->cart->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);
    }

    public function test_adding_to_cart_rejects_quantity_exceeding_stock(): void
    {
        $user = $this->customer();
        $product = Product::factory()->create(['stock_quantity' => 5]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['quantity']);
    }

    public function test_adding_inactive_product_to_cart_fails(): void
    {
        $user = $this->customer();
        $product = Product::factory()->inactive()->create(['stock_quantity' => 10]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['product_id']);
    }

    public function test_adding_unknown_product_to_cart_fails(): void
    {
        $user = $this->customer();

        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson('/api/v1/cart/items', [
            'product_id' => 99999,
            'quantity' => 1,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['product_id']);
    }

    public function test_customer_can_update_cart_item_quantity(): void
    {
        $user = $this->customer();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $cart = Cart::factory()->create(['user_id' => $user->id]);
        $item = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->putJson("/api/v1/cart/items/{$item->id}", [
            'quantity' => 5,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.item_count', 5);

        $this->assertDatabaseHas('cart_items', [
            'id' => $item->id,
            'quantity' => 5,
        ]);
    }

    public function test_updating_cart_item_rejects_quantity_exceeding_stock(): void
    {
        $user = $this->customer();
        $product = Product::factory()->create(['stock_quantity' => 5]);
        $cart = Cart::factory()->create(['user_id' => $user->id]);
        $item = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->putJson("/api/v1/cart/items/{$item->id}", [
            'quantity' => 10,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['quantity']);
    }

    public function test_customer_can_remove_cart_item(): void
    {
        $user = $this->customer();
        $product = Product::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id]);
        $item = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
        ]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->deleteJson("/api/v1/cart/items/{$item->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
    }

    public function test_customer_can_clear_cart(): void
    {
        $user = $this->customer();
        $cart = Cart::factory()->create(['user_id' => $user->id]);
        CartItem::factory()->count(3)->create(['cart_id' => $cart->id]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->deleteJson('/api/v1/cart');

        $response->assertOk();
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_merge_guest_cart_ignores_inactive_or_out_of_stock_products(): void
    {
        $user = $this->customer();
        $active = Product::factory()->create(['stock_quantity' => 10]);
        $inactive = Product::factory()->inactive()->create(['stock_quantity' => 10]);
        $outOfStock = Product::factory()->create(['stock_quantity' => 0, 'status' => ProductStatus::OUT_OF_STOCK]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson('/api/v1/cart/merge', [
            'items' => [
                ['product_id' => $active->id, 'quantity' => 2],
                ['product_id' => $inactive->id, 'quantity' => 2],
                ['product_id' => $outOfStock->id, 'quantity' => 2],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.item_count', 2);
    }

    public function test_merge_guest_cart_caps_quantity_at_available_stock(): void
    {
        $user = $this->customer();
        $product = Product::factory()->create(['stock_quantity' => 3]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson('/api/v1/cart/merge', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 10],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.item_count', 3);
    }

    public function test_cart_endpoint_requires_authentication(): void
    {
        $this->getJson('/api/v1/cart')->assertUnauthorized();
    }
}

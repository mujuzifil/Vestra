<?php

namespace Tests\Feature\Api\V1;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderStatusService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderLifecycleTest extends TestCase
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

    private function createCodOrder(int $quantity = 2, float $price = 100): Order
    {
        $user = $this->customer();
        $product = Product::factory()->create(['price' => $price, 'stock_quantity' => 10]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatus::PENDING->value,
            'payment_status' => PaymentStatus::PENDING->value,
            'payment_method' => PaymentMethod::COD->value,
            'stock_decremented' => true,
            'subtotal' => $price * $quantity,
            'shipping_cost' => 0,
            'tax_amount' => round($price * $quantity * 0.18, 2),
            'total_amount' => round($price * $quantity * 1.18, 2),
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'unit_price' => $price,
            'quantity' => $quantity,
            'line_total' => $price * $quantity,
        ]);

        $product->decrement('stock_quantity', $quantity);
        $order->load('items');

        return $order;
    }

    public function test_customer_can_cancel_pending_order_and_restore_stock(): void
    {
        $order = $this->createCodOrder(quantity: 3);
        $product = $order->items->first()->product;
        $this->assertSame(7, $product->stock_quantity);

        Sanctum::actingAs($order->user, ['*']);

        $response = $this->postJson("/api/v1/orders/{$order->id}/cancel");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', OrderStatus::CANCELLED->value);

        $order->refresh();
        $product->refresh();

        $this->assertSame(OrderStatus::CANCELLED->value, $order->status);
        $this->assertFalse($order->stock_decremented);
        $this->assertSame(10, $product->stock_quantity);
    }

    public function test_customer_can_cancel_paid_order_and_restore_stock(): void
    {
        $order = $this->createCodOrder(quantity: 2);
        $order->update(['status' => OrderStatus::PAID->value, 'payment_status' => PaymentStatus::PAID->value]);
        $order->refresh();
        $this->assertSame(OrderStatus::PAID->value, $order->status);

        $product = $order->items->first()->product;
        $this->assertSame(8, $product->stock_quantity);

        Sanctum::actingAs($order->user, ['*']);

        $response = $this->postJson("/api/v1/orders/{$order->id}/cancel");

        $response->assertOk();
        $product->refresh();
        $this->assertSame(10, $product->stock_quantity);
    }

    public function test_customer_cannot_cancel_shipped_order(): void
    {
        $order = $this->createCodOrder();
        $order->update(['status' => OrderStatus::SHIPPED->value]);

        Sanctum::actingAs($order->user, ['*']);

        $response = $this->postJson("/api/v1/orders/{$order->id}/cancel");

        $response->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'This order cannot be cancelled.');
    }

    public function test_customer_cannot_cancel_another_customers_order(): void
    {
        $order = $this->createCodOrder();
        $otherUser = $this->customer();

        Sanctum::actingAs($otherUser, ['*']);

        $response = $this->postJson("/api/v1/orders/{$order->id}/cancel");

        $response->assertNotFound();
    }

    public function test_order_status_transition_prevents_invalid_changes(): void
    {
        $order = $this->createCodOrder();
        $order->update(['status' => OrderStatus::DELIVERED->value]);

        $service = app(OrderStatusService::class);

        $this->assertFalse($service->canTransition($order, OrderStatus::CANCELLED));
        $this->assertFalse($service->transition($order, OrderStatus::CANCELLED));
    }

    public function test_cancelling_already_cancelled_order_is_idempotent(): void
    {
        $order = $this->createCodOrder(quantity: 2);
        $product = $order->items->first()->product;

        $service = app(OrderStatusService::class);
        $service->transition($order, OrderStatus::CANCELLED);

        $product->refresh();
        $this->assertSame(10, $product->stock_quantity);

        // Second transition should be rejected.
        $this->assertFalse($service->transition($order, OrderStatus::CANCELLED));

        $product->refresh();
        $this->assertSame(10, $product->stock_quantity);
    }

    public function test_order_index_returns_only_authenticated_users_orders(): void
    {
        $user = $this->customer();
        Order::factory()->count(2)->create(['user_id' => $user->id]);

        $otherUser = $this->customer();
        Order::factory()->count(3)->create(['user_id' => $otherUser->id]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/v1/orders');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_order_show_returns_404_for_other_users_order(): void
    {
        $order = $this->createCodOrder();
        $otherUser = $this->customer();

        Sanctum::actingAs($otherUser, ['*']);

        $response = $this->getJson("/api/v1/orders/{$order->id}");

        $response->assertNotFound();
    }
}

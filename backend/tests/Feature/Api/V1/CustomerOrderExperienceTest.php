<?php

namespace Tests\Feature\Api\V1;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CustomerOrderExperienceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        Mail::fake();
    }

    private function customer(): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('customer');

        return $user;
    }

    public function test_order_confirmation_email_is_sent_on_creation(): void
    {
        $user = $this->customer();
        $product = Product::factory()->create(['stock_quantity' => 10, 'price' => 100]);
        $cart = Cart::factory()->create(['user_id' => $user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        Sanctum::actingAs($user, ['*']);

        $this->postJson('/api/v1/checkout', [
            'payment_method' => PaymentMethod::COD->value,
            'shipping_address' => [
                'full_name' => 'John Doe',
                'phone' => '0772000000',
                'city' => 'Kampala',
                'address_line' => 'Plot 123',
            ],
        ])->assertCreated();

        Mail::assertSent(\App\Mail\OrderConfirmationMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_customer_can_view_order_history(): void
    {
        $user = $this->customer();
        Order::factory()->count(3)->create([
            'user_id' => $user->id,
            'status' => OrderStatus::DELIVERED,
            'payment_status' => PaymentStatus::PAID,
        ]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/v1/orders');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.status', OrderStatus::DELIVERED->value);
    }

    public function test_order_resource_exposes_expected_fields(): void
    {
        $user = $this->customer();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatus::PENDING,
            'payment_status' => PaymentStatus::PENDING,
            'subtotal' => 100,
            'shipping_cost' => 0,
            'tax_amount' => 18,
            'total_amount' => 118,
        ]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson("/api/v1/orders/{$order->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $order->id)
            ->assertJsonPath('data.invoice_number', $order->invoice_number)
            ->assertJsonPath('data.status', OrderStatus::PENDING->value)
            ->assertJsonPath('data.total_amount', '118.00')
            ->assertJsonPath('data.subtotal', '100.00')
            ->assertJsonPath('data.tax_amount', '18.00');
    }

    public function test_customer_can_complete_end_to_end_purchase_flow(): void
    {
        $user = $this->customer();
        $product = Product::factory()->create(['price' => 500, 'stock_quantity' => 20]);

        Sanctum::actingAs($user, ['*']);

        // Add to cart
        $this->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'quantity' => 4,
        ])->assertCreated();

        // Checkout with COD
        $checkout = $this->postJson('/api/v1/checkout', [
            'payment_method' => PaymentMethod::COD->value,
            'shipping_address' => [
                'full_name' => 'John Doe',
                'phone' => '0772000000',
                'city' => 'Kampala',
                'address_line' => 'Plot 123',
            ],
        ]);

        $checkout->assertCreated();
        $orderId = $checkout->json('data.id');

        $product->refresh();
        $this->assertSame(16, $product->stock_quantity);

        // View order
        $this->getJson("/api/v1/orders/{$orderId}")
            ->assertOk()
            ->assertJsonPath('data.status', OrderStatus::PENDING->value);

        // Cancel order
        $this->postJson("/api/v1/orders/{$orderId}/cancel")
            ->assertOk()
            ->assertJsonPath('data.status', OrderStatus::CANCELLED->value);

        $product->refresh();
        $this->assertSame(20, $product->stock_quantity);
    }

    public function test_cart_is_cleared_after_successful_checkout(): void
    {
        $user = $this->customer();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $cart = Cart::factory()->create(['user_id' => $user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        Sanctum::actingAs($user, ['*']);

        $this->postJson('/api/v1/checkout', [
            'payment_method' => PaymentMethod::COD->value,
            'shipping_address' => [
                'full_name' => 'John Doe',
                'phone' => '0772000000',
                'city' => 'Kampala',
                'address_line' => 'Plot 123',
            ],
        ])->assertCreated();

        $this->assertDatabaseCount('cart_items', 0);
    }
}

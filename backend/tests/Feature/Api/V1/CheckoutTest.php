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
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        Http::preventStrayRequests();
        config()->set('services.flutterwave.secret_key', 'test-secret-key');
    }

    private function customer(): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('customer');

        return $user;
    }

    public function test_customer_can_checkout_with_cod(): void
    {
        $user = $this->customer();
        $product = Product::factory()->create(['price' => 100, 'stock_quantity' => 10]);
        $cart = Cart::factory()->create(['user_id' => $user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson('/api/v1/checkout', [
            'payment_method' => PaymentMethod::COD->value,
            'shipping_address' => $this->shippingAddress(),
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', OrderStatus::PENDING->value)
            ->assertJsonPath('data.payment_method', PaymentMethod::COD->value);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'status' => OrderStatus::PENDING->value,
            'payment_status' => PaymentStatus::PENDING->value,
            'stock_decremented' => true,
        ]);

        $product->refresh();
        $this->assertSame(8, $product->stock_quantity);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_checkout_calculates_totals_server_side(): void
    {
        $user = $this->customer();
        $product = Product::factory()->create(['price' => 100, 'stock_quantity' => 10]);
        $cart = Cart::factory()->create(['user_id' => $user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson('/api/v1/checkout', [
            'payment_method' => PaymentMethod::COD->value,
            'shipping_address' => $this->shippingAddress(),
            // Attempt to manipulate totals from the client.
            'shipping_cost' => 999,
            'tax_amount' => 999,
        ]);

        $response->assertCreated();

        $order = Order::query()->where('user_id', $user->id)->firstOrFail();
        $this->assertSame(200.0, (float) $order->subtotal);
        $this->assertSame(0.0, (float) $order->shipping_cost);
        $this->assertSame(36.0, (float) $order->tax_amount);
        $this->assertSame(236.0, (float) $order->total_amount);
    }

    public function test_checkout_fails_when_cart_is_empty(): void
    {
        $user = $this->customer();
        Cart::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson('/api/v1/checkout', [
            'payment_method' => PaymentMethod::COD->value,
            'shipping_address' => $this->shippingAddress(),
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['cart']);
    }

    public function test_checkout_fails_when_product_is_inactive(): void
    {
        $user = $this->customer();
        $product = Product::factory()->inactive()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson('/api/v1/checkout', [
            'payment_method' => PaymentMethod::COD->value,
            'shipping_address' => $this->shippingAddress(),
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['stock']);
    }

    public function test_checkout_fails_when_stock_is_insufficient(): void
    {
        $user = $this->customer();
        $product = Product::factory()->create(['stock_quantity' => 2]);
        $cart = Cart::factory()->create(['user_id' => $user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson('/api/v1/checkout', [
            'payment_method' => PaymentMethod::COD->value,
            'shipping_address' => $this->shippingAddress(),
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['stock']);
    }

    public function test_checkout_initiates_payment_for_digital_method(): void
    {
        $user = $this->customer();
        $product = Product::factory()->create(['price' => 100, 'stock_quantity' => 10]);
        $cart = Cart::factory()->create(['user_id' => $user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        Http::fake([
            'https://api.flutterwave.com/v3/payments' => Http::response([
                'status' => 'success',
                'data' => ['link' => 'https://flutterwave.com/pay/test'],
            ], 200),
        ]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson('/api/v1/checkout', [
            'payment_method' => PaymentMethod::CARD->value,
            'shipping_address' => $this->shippingAddress(),
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.payment_url', 'https://flutterwave.com/pay/test');

        $order = Order::query()->where('user_id', $user->id)->firstOrFail();
        $this->assertFalse($order->stock_decremented);
        $product->refresh();
        $this->assertSame(10, $product->stock_quantity);
    }

    public function test_checkout_creates_invoice_number(): void
    {
        $user = $this->customer();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $cart = Cart::factory()->create(['user_id' => $user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson('/api/v1/checkout', [
            'payment_method' => PaymentMethod::COD->value,
            'shipping_address' => $this->shippingAddress(),
        ]);

        $response->assertCreated();
        $this->assertNotNull($response->json('data.invoice_number'));
        $this->assertStringStartsWith('VST-', $response->json('data.invoice_number'));
    }

    private function shippingAddress(): array
    {
        return [
            'full_name' => 'John Doe',
            'phone' => '0772000000',
            'city' => 'Kampala',
            'region' => 'Central',
            'district' => 'Kampala',
            'address_line' => 'Plot 123, Main Street',
        ];
    }
}

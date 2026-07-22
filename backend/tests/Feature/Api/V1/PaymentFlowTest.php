<?php

namespace Tests\Feature\Api\V1;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PaymentFlowTest extends TestCase
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

    private function createPendingDigitalOrder(int $quantity = 2, float $price = 100): Order
    {
        $user = $this->customer();
        $product = Product::factory()->create(['price' => $price, 'stock_quantity' => 10]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatus::PENDING,
            'payment_status' => PaymentStatus::PENDING,
            'payment_method' => PaymentMethod::CARD->value,
            'stock_decremented' => false,
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

        $order->load('items');

        return $order;
    }

    public function test_successful_payment_verification_completes_order_and_decrements_stock(): void
    {
        $order = $this->createPendingDigitalOrder(quantity: 3, price: 100);
        $product = $order->items->first()->product;
        $transaction = PaymentTransaction::factory()->create([
            'order_id' => $order->id,
            'transaction_reference' => 'VST-PAY-TEST123',
            'amount' => $order->total_amount,
            'currency' => 'UGX',
            'status' => 'pending',
        ]);

        Http::fake([
            'https://api.flutterwave.com/v3/transactions/verify_by_reference*' => Http::response([
                'status' => 'success',
                'data' => [
                    'status' => 'successful',
                    'amount' => (float) $order->total_amount,
                    'currency' => 'UGX',
                    'id' => 12345,
                    'created_at' => now()->toIso8601String(),
                ],
            ], 200),
        ]);

        Sanctum::actingAs($order->user, ['*']);

        $response = $this->getJson("/api/v1/payments/{$transaction->transaction_reference}/verify");

        $response->assertOk()
            ->assertJsonPath('success', true);

        $order->refresh();
        $product->refresh();

        $this->assertSame(OrderStatus::PAID->value, $order->status);
        $this->assertSame(PaymentStatus::PAID->value, $order->payment_status);
        $this->assertTrue($order->stock_decremented);
        $this->assertSame(7, $product->stock_quantity);
    }

    public function test_payment_verification_is_idempotent_for_stock(): void
    {
        $order = $this->createPendingDigitalOrder(quantity: 2, price: 100);
        $product = $order->items->first()->product;
        $transaction = PaymentTransaction::factory()->create([
            'order_id' => $order->id,
            'transaction_reference' => 'VST-PAY-IDEMPOTENT',
            'amount' => $order->total_amount,
            'currency' => 'UGX',
            'status' => 'success',
        ]);

        Sanctum::actingAs($order->user, ['*']);

        $response = $this->getJson("/api/v1/payments/{$transaction->transaction_reference}/verify");

        $response->assertOk()
            ->assertJsonPath('message', 'Payment already confirmed.');

        $product->refresh();
        $this->assertSame(10, $product->stock_quantity);
    }

    public function test_payment_verification_rejects_mismatched_amount(): void
    {
        $order = $this->createPendingDigitalOrder(quantity: 1, price: 100);
        $transaction = PaymentTransaction::factory()->create([
            'order_id' => $order->id,
            'transaction_reference' => 'VST-PAY-MISMATCH',
            'amount' => $order->total_amount,
            'currency' => 'UGX',
            'status' => 'pending',
        ]);

        Http::fake([
            'https://api.flutterwave.com/v3/transactions/verify_by_reference*' => Http::response([
                'status' => 'success',
                'data' => [
                    'status' => 'successful',
                    'amount' => 1.00,
                    'currency' => 'UGX',
                    'id' => 12345,
                    'created_at' => now()->toIso8601String(),
                ],
            ], 200),
        ]);

        Sanctum::actingAs($order->user, ['*']);

        $response = $this->getJson("/api/v1/payments/{$transaction->transaction_reference}/verify");

        $response->assertBadRequest()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Payment amount does not match order.');

        $order->refresh();
        $this->assertSame(OrderStatus::PENDING->value, $order->status);
        $this->assertSame(PaymentStatus::PENDING->value, $order->payment_status);
        $this->assertFalse($order->stock_decremented);
    }

    public function test_failed_payment_verification_does_not_complete_order(): void
    {
        $order = $this->createPendingDigitalOrder();
        $transaction = PaymentTransaction::factory()->create([
            'order_id' => $order->id,
            'transaction_reference' => 'VST-PAY-FAILED',
            'amount' => $order->total_amount,
            'currency' => 'UGX',
            'status' => 'pending',
        ]);

        Http::fake([
            'https://api.flutterwave.com/v3/transactions/verify_by_reference*' => Http::response([
                'status' => 'error',
                'message' => 'Transaction not found',
            ], 404),
        ]);

        Sanctum::actingAs($order->user, ['*']);

        $response = $this->getJson("/api/v1/payments/{$transaction->transaction_reference}/verify");

        $response->assertBadRequest()
            ->assertJsonPath('success', false);

        $order->refresh();
        $this->assertSame(OrderStatus::PENDING->value, $order->status);
        $this->assertSame(PaymentStatus::PENDING->value, $order->payment_status);
    }

    public function test_webhook_callback_is_idempotent(): void
    {
        config()->set('services.flutterwave.webhook_secret', 'webhook-secret-for-tests');

        $order = $this->createPendingDigitalOrder(quantity: 1, price: 100);
        $product = $order->items->first()->product;
        PaymentTransaction::factory()->create([
            'order_id' => $order->id,
            'transaction_reference' => 'VST-PAY-WEBHOOK',
            'amount' => $order->total_amount,
            'currency' => 'UGX',
            'status' => 'pending',
        ]);

        Http::fake([
            'https://api.flutterwave.com/v3/transactions/verify_by_reference*' => Http::response([
                'status' => 'success',
                'data' => [
                    'status' => 'successful',
                    'amount' => (float) $order->total_amount,
                    'currency' => 'UGX',
                    'id' => 12345,
                    'created_at' => now()->toIso8601String(),
                ],
            ], 200),
        ]);

        $payload = json_encode([
            'status' => 'successful',
            'tx_ref' => 'VST-PAY-WEBHOOK',
        ]);

        $signature = hash_hmac('sha256', $payload, 'webhook-secret-for-tests');

        $this->postJson('/api/v1/payments/callback', json_decode($payload, true), [
            'verif-hash' => $signature,
        ])->assertOk();

        $this->postJson('/api/v1/payments/callback', json_decode($payload, true), [
            'verif-hash' => $signature,
        ])->assertOk()
            ->assertJsonPath('message', 'Payment already confirmed.');

        $product->refresh();
        $this->assertSame(9, $product->stock_quantity);
    }

    public function test_customer_can_initiate_payment_for_their_order(): void
    {
        $order = $this->createPendingDigitalOrder();

        Http::fake([
            'https://api.flutterwave.com/v3/payments' => Http::response([
                'status' => 'success',
                'data' => ['link' => 'https://flutterwave.com/pay/test'],
            ], 200),
        ]);

        Sanctum::actingAs($order->user, ['*']);

        $response = $this->postJson('/api/v1/payments/initiate', [
            'order_id' => $order->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.payment_link', 'https://flutterwave.com/pay/test');
    }

    public function test_customer_cannot_initiate_payment_for_another_users_order(): void
    {
        $order = $this->createPendingDigitalOrder();
        $otherUser = $this->customer();

        Sanctum::actingAs($otherUser, ['*']);

        $response = $this->postJson('/api/v1/payments/initiate', [
            'order_id' => $order->id,
        ]);

        $response->assertForbidden()
            ->assertJsonPath('success', false);
    }
}

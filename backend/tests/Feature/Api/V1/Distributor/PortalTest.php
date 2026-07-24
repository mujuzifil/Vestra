<?php

namespace Tests\Feature\Api\V1\Distributor;

use App\Enums\DistributorAccountStatus;
use App\Enums\DistributorChannel;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\QuotationStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CreditAccount;
use App\Models\Distributor;
use App\Models\DistributorBranch;
use App\Models\DistributorContact;
use App\Models\Order;
use App\Models\PaymentUpload;
use App\Models\Product;
use App\Models\QuotationRequest;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        Role::firstOrCreate(['name' => 'distributor', 'guard_name' => 'web']);
        Http::preventStrayRequests();
        Storage::fake('public');
    }

    private function distributorUser(): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('distributor');

        Distributor::factory()->create([
            'user_id' => $user->id,
            'status' => DistributorAccountStatus::ACTIVE->value,
        ]);

        return $user->load('distributor');
    }

    private function otherDistributorUser(): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('distributor');

        Distributor::factory()->create([
            'user_id' => $user->id,
            'status' => DistributorAccountStatus::ACTIVE->value,
        ]);

        return $user->load('distributor');
    }

    public function test_distributor_can_view_dashboard(): void
    {
        $user = $this->distributorUser();

        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/v1/distributor/dashboard');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.distributor.id', $user->distributor->id);
    }

    public function test_non_distributor_cannot_access_portal(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('customer');

        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/v1/distributor/dashboard');

        $response->assertForbidden()
            ->assertJsonPath('message', 'Distributor access required.');
    }

    public function test_distributor_can_manage_branches(): void
    {
        $user = $this->distributorUser();
        $distributor = $user->distributor;

        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson('/api/v1/distributor/branches', [
            'name' => 'Kampala Warehouse',
            'city' => 'Kampala',
            'is_default' => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Kampala Warehouse')
            ->assertJsonPath('data.is_default', true);

        $branchId = $response->json('data.id');

        $this->putJson("/api/v1/distributor/branches/{$branchId}", [
            'name' => 'Kampala Main Warehouse',
        ])->assertOk()
            ->assertJsonPath('data.name', 'Kampala Main Warehouse');

        $this->getJson('/api/v1/distributor/branches')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->deleteJson("/api/v1/distributor/branches/{$branchId}")
            ->assertOk();

        $this->assertDatabaseMissing('distributor_branches', ['id' => $branchId]);
    }

    public function test_distributor_cannot_view_other_distributor_branch(): void
    {
        $user = $this->distributorUser();
        $otherUser = $this->otherDistributorUser();
        $otherBranch = DistributorBranch::factory()->create([
            'distributor_id' => $otherUser->distributor->id,
        ]);

        Sanctum::actingAs($user, ['*']);

        $this->getJson("/api/v1/distributor/branches/{$otherBranch->id}")
            ->assertForbidden();
    }

    public function test_distributor_can_manage_contacts(): void
    {
        $user = $this->distributorUser();

        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson('/api/v1/distributor/contacts', [
            'name' => 'Jane Doe',
            'role' => 'Procurement',
            'email' => 'jane@example.com',
            'is_primary' => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Jane Doe')
            ->assertJsonPath('data.is_primary', true);

        $contactId = $response->json('data.id');

        $this->putJson("/api/v1/distributor/contacts/{$contactId}", [
            'role' => 'Head of Procurement',
        ])->assertOk()
            ->assertJsonPath('data.role', 'Head of Procurement');

        $this->deleteJson("/api/v1/distributor/contacts/{$contactId}")
            ->assertOk();

        $this->assertDatabaseMissing('distributor_contacts', ['id' => $contactId]);
    }

    public function test_distributor_can_upload_document(): void
    {
        $user = $this->distributorUser();

        Sanctum::actingAs($user, ['*']);

        $file = UploadedFile::fake()->create('certificate.pdf', 100, 'application/pdf');

        $response = $this->postJson('/api/v1/distributor/documents', [
            'title' => 'Trading Certificate',
            'type' => 'certificate',
            'file' => $file,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Trading Certificate');

        Storage::disk('public')->assertExists('distributor-documents/' . $file->hashName());
    }

    public function test_distributor_can_create_and_submit_quotation(): void
    {
        $user = $this->distributorUser();
        $product = Product::factory()->create([
            'distributor_price' => 5000,
            'stock_quantity' => 100,
            'status' => \App\Enums\ProductStatus::ACTIVE->value,
        ]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson('/api/v1/distributor/quotes', [
            'notes' => 'Need pricing for Kampala branch',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 50],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', QuotationStatus::DRAFT->value)
            ->assertJsonPath('data.items.0.quantity', 50);

        $quoteId = $response->json('data.id');

        $this->postJson("/api/v1/distributor/quotes/{$quoteId}/submit")
            ->assertOk()
            ->assertJsonPath('data.status', QuotationStatus::SUBMITTED->value);
    }

    public function test_distributor_can_accept_quoted_quotation(): void
    {
        $user = $this->distributorUser();
        $quote = QuotationRequest::factory()->quoted()->create([
            'distributor_id' => $user->distributor->id,
        ]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson("/api/v1/distributor/quotes/{$quote->id}/accept");

        $response->assertOk()
            ->assertJsonPath('data.status', QuotationStatus::ACCEPTED->value);
    }

    public function test_distributor_cannot_accept_other_distributor_quotation(): void
    {
        $user = $this->distributorUser();
        $otherUser = $this->otherDistributorUser();
        $quote = QuotationRequest::factory()->quoted()->create([
            'distributor_id' => $otherUser->distributor->id,
        ]);

        Sanctum::actingAs($user, ['*']);

        $this->postJson("/api/v1/distributor/quotes/{$quote->id}/accept")
            ->assertForbidden();
    }

    public function test_distributor_can_list_own_orders(): void
    {
        $user = $this->distributorUser();
        $distributor = $user->distributor;

        Order::factory()->create([
            'user_id' => $user->id,
            'distributor_id' => $distributor->id,
            'channel' => DistributorChannel::DISTRIBUTOR->value,
        ]);

        Order::factory()->create([
            'user_id' => User::factory()->create()->id,
        ]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/v1/distributor/orders');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.distributor_id', $distributor->id);
    }

    public function test_distributor_can_checkout_with_credit_authorization(): void
    {
        $user = $this->distributorUser();
        $distributor = $user->distributor;
        $branch = DistributorBranch::factory()->create([
            'distributor_id' => $distributor->id,
        ]);

        CreditAccount::factory()->create([
            'distributor_id' => $distributor->id,
            'limit' => 1000000,
            'balance' => 0,
            'authorized_amount' => 0,
            'status' => 'active',
        ]);

        $product = Product::factory()->create([
            'price' => 10000,
            'distributor_price' => 5000,
            'stock_quantity' => 100,
            'status' => \App\Enums\ProductStatus::ACTIVE->value,
        ]);

        $cart = Cart::factory()->create(['user_id' => $user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson('/api/v1/checkout', [
            'channel' => DistributorChannel::DISTRIBUTOR->value,
            'distributor_branch_id' => $branch->id,
            'payment_method' => PaymentMethod::CREDIT->value,
            'shipping_address' => $this->shippingAddress(),
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.channel', DistributorChannel::DISTRIBUTOR->value)
            ->assertJsonPath('data.payment_method', PaymentMethod::CREDIT->value)
            ->assertJsonPath('data.payment_required', true);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'distributor_id' => $distributor->id,
            'channel' => DistributorChannel::DISTRIBUTOR->value,
            'payment_status' => PaymentStatus::PENDING->value,
        ]);

        $this->assertDatabaseHas('credit_transactions', [
            'type' => 'authorization',
        ]);
    }

    public function test_credit_checkout_fails_when_insufficient_credit(): void
    {
        $user = $this->distributorUser();
        $distributor = $user->distributor;
        $branch = DistributorBranch::factory()->create([
            'distributor_id' => $distributor->id,
        ]);

        CreditAccount::factory()->create([
            'distributor_id' => $distributor->id,
            'limit' => 100,
            'balance' => 0,
            'authorized_amount' => 0,
            'status' => 'active',
        ]);

        $product = Product::factory()->create([
            'price' => 10000,
            'distributor_price' => 5000,
            'stock_quantity' => 100,
            'status' => \App\Enums\ProductStatus::ACTIVE->value,
        ]);

        $cart = Cart::factory()->create(['user_id' => $user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson('/api/v1/checkout', [
            'channel' => DistributorChannel::DISTRIBUTOR->value,
            'distributor_branch_id' => $branch->id,
            'payment_method' => PaymentMethod::CREDIT->value,
            'shipping_address' => $this->shippingAddress(),
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['payment_method']);
    }

    public function test_retail_checkout_is_unchanged(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('customer');

        $product = Product::factory()->create([
            'price' => 100,
            'stock_quantity' => 10,
            'status' => \App\Enums\ProductStatus::ACTIVE->value,
        ]);

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
            ->assertJsonPath('data.status', OrderStatus::PENDING->value)
            ->assertJsonPath('data.payment_method', PaymentMethod::COD->value);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'channel' => DistributorChannel::RETAIL->value,
        ]);
    }

    public function test_distributor_can_upload_payment_proof(): void
    {
        $user = $this->distributorUser();

        Sanctum::actingAs($user, ['*']);

        $file = UploadedFile::fake()->image('receipt.jpg');

        $response = $this->postJson('/api/v1/distributor/payments', [
            'amount' => 500000,
            'reference_number' => 'BANK-12345',
            'file' => $file,
            'notes' => 'Bank transfer for invoice VST-001',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.reference_number', 'BANK-12345');

        Storage::disk('public')->assertExists('payment-uploads/' . $file->hashName());
    }

    public function test_distributor_can_view_statement(): void
    {
        $user = $this->distributorUser();
        $distributor = $user->distributor;

        Order::factory()->create([
            'user_id' => $user->id,
            'distributor_id' => $distributor->id,
            'total_amount' => 100000,
            'payment_status' => PaymentStatus::PENDING->value,
        ]);

        PaymentUpload::factory()->create([
            'distributor_id' => $distributor->id,
            'amount' => 50000,
            'status' => 'verified',
        ]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/v1/distributor/statements');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.opening_balance', '0.00')
            ->assertJsonPath('data.transactions', fn ($transactions) => count($transactions) >= 1);
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

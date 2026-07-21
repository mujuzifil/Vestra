<?php

namespace Tests\Feature;

use App\Models\CustomerAddress;
use App\Models\CustomerFeedback;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthorizationSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    private function createCustomer(string $email, string $password = 'Password123'): User
    {
        $user = User::factory()->create([
            'email' => $email,
            'password' => Hash::make($password),
            'is_admin' => false,
            'status' => 'active',
        ]);
        $user->assignRole('customer');

        return $user;
    }

    private function createAdmin(string $email, string $password = 'Password123'): User
    {
        $user = User::factory()->create([
            'email' => $email,
            'password' => Hash::make($password),
            'is_admin' => true,
            'status' => 'active',
        ]);
        $user->assignRole('Administrator');

        return $user;
    }

    private function customerToken(User $user): string
    {
        return $user->createToken('test-token', ['customer'])->plainTextToken;
    }

    private function adminToken(User $user): string
    {
        return $user->createToken('test-token', ['admin'])->plainTextToken;
    }

    // =========================================================
    // Mass-assignment hardening
    // =========================================================

    public function test_registration_rejects_privilege_escalation_fields(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Attacker',
            'email' => 'attacker@vestra.com',
            'phone' => '0700000000',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'is_admin' => true,
        ])->assertStatus(422);

        $this->assertDatabaseMissing('users', [
            'email' => 'attacker@vestra.com',
            'is_admin' => true,
        ]);
    }

    public function test_customer_cannot_set_user_id_on_address(): void
    {
        $user = $this->createCustomer('customer-addr@vestra.com');

        $response = $this->postJson('/api/v1/auth/addresses', [
            'label' => 'Home',
            'full_name' => 'Test Customer',
            'phone' => '0700000000',
            'city' => 'Kampala',
            'address_line' => 'Plot 1',
            'user_id' => 9999,
        ], [
            'Authorization' => "Bearer {$this->customerToken($user)}",
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('customer_addresses', [
            'user_id' => $user->id,
            'label' => 'Home',
        ]);
        $this->assertDatabaseMissing('customer_addresses', [
            'user_id' => 9999,
        ]);
    }

    public function test_customer_cannot_set_status_on_review(): void
    {
        $user = $this->createCustomer('customer-review@vestra.com');
        $product = Product::first();
        $this->assertNotNull($product);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'paid',
            'payment_status' => 'paid',
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'unit_price' => $product->price,
            'quantity' => 1,
            'line_total' => $product->price,
        ]);

        $this->postJson('/api/v1/reviews', [
            'product_id' => $product->id,
            'rating' => 5,
            'title' => 'Great',
            'comment' => 'Works well',
            'status' => 'approved',
        ], [
            'Authorization' => "Bearer {$this->customerToken($user)}",
        ])->assertCreated();

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'pending',
        ]);
    }

    public function test_profile_update_does_not_allow_status_or_role(): void
    {
        $user = $this->createCustomer('customer-profile@vestra.com');

        $this->putJson('/api/v1/auth/profile', [
            'name' => 'Updated Name',
            'status' => 'inactive',
            'role' => 'Administrator',
        ], [
            'Authorization' => "Bearer {$this->customerToken($user)}",
        ])->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'status' => 'active',
        ]);

        $this->assertFalse($user->fresh()->hasRole('Administrator'));
    }

    // =========================================================
    // Ownership & IDOR prevention
    // =========================================================

    public function test_customer_cannot_view_another_customers_order(): void
    {
        $owner = $this->createCustomer('owner@vestra.com');
        $attacker = $this->createCustomer('attacker-order@vestra.com');

        $order = Order::factory()->create(['user_id' => $owner->id]);

        $this->getJson("/api/v1/orders/{$order->id}", [
            'Authorization' => "Bearer {$this->customerToken($attacker)}",
        ])->assertStatus(404);
    }

    public function test_customer_cannot_view_another_customers_address(): void
    {
        $owner = $this->createCustomer('owner-addr@vestra.com');
        $attacker = $this->createCustomer('attacker-addr@vestra.com');

        $address = CustomerAddress::factory()->create(['user_id' => $owner->id]);

        $this->getJson("/api/v1/auth/addresses/{$address->id}", [
            'Authorization' => "Bearer {$this->customerToken($attacker)}",
        ])->assertStatus(403);
    }

    public function test_customer_cannot_update_another_customers_review(): void
    {
        $owner = $this->createCustomer('owner-review@vestra.com');
        $attacker = $this->createCustomer('attacker-review@vestra.com');
        $product = Product::first();

        $review = Review::factory()->create([
            'user_id' => $owner->id,
            'product_id' => $product->id,
            'status' => 'approved',
        ]);

        $this->putJson("/api/v1/reviews/{$review->id}", [
            'rating' => 1,
            'comment' => 'Hacked',
        ], [
            'Authorization' => "Bearer {$this->customerToken($attacker)}",
        ])->assertStatus(403);
    }

    public function test_customer_cannot_delete_another_customers_review(): void
    {
        $owner = $this->createCustomer('owner-review2@vestra.com');
        $attacker = $this->createCustomer('attacker-review2@vestra.com');
        $product = Product::first();

        $review = Review::factory()->create([
            'user_id' => $owner->id,
            'product_id' => $product->id,
        ]);

        $this->deleteJson("/api/v1/reviews/{$review->id}", [], [
            'Authorization' => "Bearer {$this->customerToken($attacker)}",
        ])->assertStatus(403);
    }

    // =========================================================
    // Report authorization
    // =========================================================

    public function test_customer_cannot_access_reports(): void
    {
        $customer = $this->createCustomer('customer-reports@vestra.com');

        $endpoints = [
            '/api/v1/reports/dashboard',
            '/api/v1/reports/sales-trend',
            '/api/v1/reports/best-sellers',
            '/api/v1/reports/inventory-value',
            '/api/v1/reports/customer-growth',
        ];

        foreach ($endpoints as $endpoint) {
            $this->getJson($endpoint, [
                'Authorization' => "Bearer {$this->customerToken($customer)}",
            ])->assertStatus(403);
        }
    }

    public function test_admin_can_access_reports(): void
    {
        $admin = $this->createAdmin('admin-reports@vestra.com');

        $this->getJson('/api/v1/reports/dashboard', [
            'Authorization' => "Bearer {$this->adminToken($admin)}",
        ])->assertOk();
    }

    // =========================================================
    // Administrative moderation authorization
    // =========================================================

    public function test_customer_cannot_access_admin_review_endpoints(): void
    {
        $customer = $this->createCustomer('customer-admin-review@vestra.com');
        $product = Product::first();
        $review = Review::factory()->create(['product_id' => $product->id]);

        $this->getJson('/api/v1/admin/reviews', [
            'Authorization' => "Bearer {$this->customerToken($customer)}",
        ])->assertStatus(403);

        $this->putJson("/api/v1/admin/reviews/{$review->id}/status", [
            'status' => 'approved',
        ], [
            'Authorization' => "Bearer {$this->customerToken($customer)}",
        ])->assertStatus(403);
    }

    public function test_admin_can_moderate_reviews(): void
    {
        $admin = $this->createAdmin('admin-review@vestra.com');
        $product = Product::first();
        $review = Review::factory()->create(['product_id' => $product->id, 'status' => 'pending']);

        $this->putJson("/api/v1/admin/reviews/{$review->id}/status", [
            'status' => 'approved',
        ], [
            'Authorization' => "Bearer {$this->adminToken($admin)}",
        ])->assertOk();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'status' => 'approved',
        ]);
    }

    public function test_customer_cannot_access_admin_feedback_endpoints(): void
    {
        $customer = $this->createCustomer('customer-admin-feedback@vestra.com');
        $feedback = CustomerFeedback::factory()->create();

        $this->getJson('/api/v1/admin/feedback', [
            'Authorization' => "Bearer {$this->customerToken($customer)}",
        ])->assertStatus(403);

        $this->putJson("/api/v1/admin/feedback/{$feedback->id}/status", [
            'status' => 'resolved',
        ], [
            'Authorization' => "Bearer {$this->customerToken($customer)}",
        ])->assertStatus(403);
    }

    public function test_admin_can_moderate_feedback(): void
    {
        $admin = $this->createAdmin('admin-feedback@vestra.com');
        $feedback = CustomerFeedback::factory()->create(['status' => 'new']);

        $this->putJson("/api/v1/admin/feedback/{$feedback->id}/status", [
            'status' => 'resolved',
        ], [
            'Authorization' => "Bearer {$this->adminToken($admin)}",
        ])->assertOk();

        $this->assertDatabaseHas('customer_feedback', [
            'id' => $feedback->id,
            'status' => 'resolved',
        ]);
    }

    // =========================================================
    // Audit logging
    // =========================================================

    public function test_authorization_denial_is_audited(): void
    {
        $customer = $this->createCustomer('customer-audit@vestra.com');
        $product = Product::first();
        $review = Review::factory()->create(['product_id' => $product->id]);

        $this->deleteJson("/api/v1/reviews/{$review->id}", [], [
            'Authorization' => "Bearer {$this->customerToken($customer)}",
        ])->assertStatus(403);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'authorization.denied',
            'user_id' => $customer->id,
        ]);
    }

    public function test_privilege_escalation_attempt_is_audited(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Attacker',
            'email' => 'attacker-audit@vestra.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'is_admin' => true,
        ])->assertStatus(422);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'privilege_escalation_attempt',
        ]);
    }
}

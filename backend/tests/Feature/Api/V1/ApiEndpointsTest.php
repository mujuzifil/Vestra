<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_categories_endpoint_returns_active_categories(): void
    {
        $response = $this->getJson('/api/v1/categories');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(Category::query()->where('status', 'active')->count(), 'data');
    }

    public function test_products_endpoint_returns_active_products(): void
    {
        $response = $this->getJson('/api/v1/products');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'price',
                        'category',
                        'images',
                    ],
                ],
                'message',
            ]);
    }

    public function test_product_detail_endpoint_returns_product_by_slug(): void
    {
        $product = Product::query()->where('status', 'active')->firstOrFail();

        $response = $this->getJson("/api/v1/products/{$product->slug}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.slug', $product->slug);
    }

    public function test_product_detail_endpoint_returns_404_for_missing_product(): void
    {
        $response = $this->getJson('/api/v1/products/non-existent-slug');

        $response->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Product not found.');
    }

    public function test_settings_endpoint_returns_only_public_settings(): void
    {
        $response = $this->getJson('/api/v1/settings');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'key',
                        'value',
                        'type',
                        'group',
                        'label',
                    ],
                ],
                'message',
            ])
            ->assertJsonCount(Setting::query()->where('is_public', true)->count(), 'data');

        $keys = collect($response->json('data'))->pluck('key')->all();

        // Public branding and operational settings must be present.
        $this->assertContains('app_name', $keys);
        $this->assertContains('company_name', $keys);
        $this->assertContains('company_logo', $keys);
        $this->assertContains('currency', $keys);
        $this->assertContains('timezone', $keys);

        // Secrets and sensitive configuration must never be exposed publicly.
        $this->assertNotContains('smtp_password', $keys);
        $this->assertNotContains('smtp_username', $keys);
        $this->assertNotContains('smtp_host', $keys);
        $this->assertNotContains('smtp_encryption', $keys);
        $this->assertNotContains('sender_email', $keys);
        $this->assertNotContains('flutterwave_secret_key', $keys);
        $this->assertNotContains('flutterwave_public_key', $keys);
        $this->assertNotContains('flutterwave_encryption_key', $keys);
        $this->assertNotContains('flutterwave_webhook_secret', $keys);
        $this->assertNotContains('password_min_length', $keys);
        $this->assertNotContains('password_requires_symbols', $keys);
        $this->assertNotContains('max_login_attempts', $keys);
        $this->assertNotContains('session_timeout_minutes', $keys);
        $this->assertNotContains('debug_mode', $keys);
        $this->assertNotContains('maintenance_mode', $keys);

        // No ciphertext should ever appear in the public payload.
        foreach ($response->json('data') as $setting) {
            $value = (string) ($setting['value'] ?? '');
            $this->assertStringStartsNotWith('eyJpdiI6', $value, "Setting [{$setting['key']}] appears to contain encrypted ciphertext.");
        }
    }

    public function test_contact_endpoint_stores_message_with_validation(): void
    {
        $response = $this->postJson('/api/v1/contact', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '1234567890',
            'subject' => 'Inquiry',
            'message' => 'I have a question about your products.',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'new');

        $this->assertDatabaseHas('contact_messages', [
            'email' => 'jane@example.com',
            'subject' => 'Inquiry',
        ]);
    }

    public function test_contact_endpoint_returns_validation_errors(): void
    {
        $response = $this->postJson('/api/v1/contact', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'subject', 'message']);
    }

    public function test_distributor_endpoint_stores_request_with_validation(): void
    {
        $response = $this->postJson('/api/v1/distributor', [
            'company_name' => 'Test Distributors',
            'contact_person' => 'John Smith',
            'email' => 'john@testdist.com',
            'phone' => '1234567890',
            'address' => '123 Test St',
            'business_description' => 'We distribute cleaning products.',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('distributor_requests', [
            'email' => 'john@testdist.com',
        ]);
    }

    public function test_distributor_endpoint_returns_validation_errors(): void
    {
        $response = $this->postJson('/api/v1/distributor', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['company_name', 'contact_person', 'email']);
    }

    public function test_unified_login_returns_admin_role_and_password_change_required(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@vestra.com',
            'password' => 'Admin@12345',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.role', 'super-administrator')
            ->assertJsonPath('data.redirect_to', '/admin')
            ->assertJsonPath('data.user.must_change_password', true)
            ->assertJsonPath('data.exchange_token', fn (string $token) => strlen($token) === 64)
            ->assertJsonStructure([
                'data' => [
                    'user',
                    'token',
                    'exchange_token',
                    'role',
                    'redirect_to',
                ],
            ]);
    }

    public function test_unified_login_returns_customer_role_and_dashboard_redirect(): void
    {
        $user = User::factory()->create([
            'email' => 'customer@vestra.com',
            'password' => bcrypt('Password123'),
            'is_admin' => false,
            'status' => 'active',
        ]);
        $user->assignRole('customer');

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'customer@vestra.com',
            'password' => 'Password123',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.role', 'customer')
            ->assertJsonPath('data.redirect_to', '/account')
            ->assertJsonPath('data.exchange_token', null)
            ->assertJsonPath('data.user.is_admin', false);
    }

    public function test_public_registration_rejects_privilege_escalation_fields(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Hacker',
            'email' => 'hacker@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'is_admin' => true,
            'role' => 'Super Administrator',
        ]);

        $response->assertUnprocessable();
        $this->assertDatabaseMissing('users', ['email' => 'hacker@example.com']);
    }

    public function test_public_registration_creates_customer_with_customer_role(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Jane Customer',
            'email' => 'jane@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'is_admin' => false,
            'status' => 'active',
        ]);

        $user = User::query()->where('email', 'jane@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole('customer'));
    }

    public function test_disabled_admin_cannot_log_in(): void
    {
        User::factory()->create([
            'email' => 'disabled@vestra.com',
            'password' => bcrypt('Password123'),
            'is_admin' => true,
            'status' => 'inactive',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'disabled@vestra.com',
            'password' => 'Password123',
        ]);

        $response->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Account is disabled. Contact a Super Administrator.');
    }

    public function test_admin_api_access_blocked_until_password_changed(): void
    {
        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@vestra.com',
            'password' => 'Admin@12345',
        ]);

        $token = $login->json('data.token');

        $response = $this->getJson('/api/v1/admin/reviews', [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Password change required before accessing this resource.');
    }

    public function test_admin_can_change_password_and_access_api(): void
    {
        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@vestra.com',
            'password' => 'Admin@12345',
        ]);

        $token = $login->json('data.token');

        $change = $this->postJson('/api/v1/auth/change-password', [
            'current_password' => 'Admin@12345',
            'password' => 'NewStrongP@ssw0rd',
            'password_confirmation' => 'NewStrongP@ssw0rd',
        ], [
            'Authorization' => "Bearer {$token}",
        ]);

        $change->assertOk()
            ->assertJsonPath('success', true);

        $user = User::where('email', 'admin@vestra.com')->firstOrFail();
        $this->assertFalse($user->mustChangePassword());

        $response = $this->getJson('/api/v1/admin/reviews', [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertOk();
    }

    public function test_weak_password_change_is_rejected(): void
    {
        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@vestra.com',
            'password' => 'Admin@12345',
        ]);

        $token = $login->json('data.token');

        $response = $this->postJson('/api/v1/auth/change-password', [
            'current_password' => 'Admin@12345',
            'password' => 'weak',
            'password_confirmation' => 'weak',
        ], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_admin_login_returns_exchange_token_for_filament_bridge(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@vestra.com',
            'password' => 'Admin@12345',
        ]);

        $exchangeToken = $response->json('data.exchange_token');

        $this->assertIsString($exchangeToken);
        $this->assertSame(64, strlen($exchangeToken));

        $exchange = $this->post('/api/v1/auth/exchange', [
            'exchange_token' => $exchangeToken,
        ]);

        $exchange->assertRedirect('/admin/force-password-change');
    }

    public function test_exchange_token_redirects_admin_to_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'admin2@vestra.com',
            'password' => bcrypt('Password123'),
            'is_admin' => true,
            'status' => 'active',
            'force_password_change_at' => null,
        ]);
        $user->assignRole('Super Administrator');

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin2@vestra.com',
            'password' => 'Password123',
        ]);

        $exchangeToken = $login->json('data.exchange_token');

        $exchange = $this->post('/api/v1/auth/exchange', [
            'exchange_token' => $exchangeToken,
        ]);

        $exchange->assertRedirect('/admin');
    }

    public function test_exchange_token_rejects_unknown_token(): void
    {
        $response = $this->post('/api/v1/auth/exchange', [
            'exchange_token' => str_repeat('a', 64),
        ]);

        $response->assertUnauthorized();
    }

    public function test_exchange_token_rejects_expired_token(): void
    {
        $user = User::factory()->create([
            'email' => 'admin3@vestra.com',
            'password' => bcrypt('Password123'),
            'is_admin' => true,
            'status' => 'active',
        ]);
        $user->assignRole('Super Administrator');

        $plainText = str_repeat('b', 64);
        \App\Models\ExchangeToken::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $plainText),
            'expires_at' => now()->subSecond(),
        ]);

        $response = $this->post('/api/v1/auth/exchange', [
            'exchange_token' => $plainText,
        ]);

        $response->assertGone();
    }

    public function test_exchange_token_rejects_reused_token(): void
    {
        $user = User::factory()->create([
            'email' => 'admin4@vestra.com',
            'password' => bcrypt('Password123'),
            'is_admin' => true,
            'status' => 'active',
            'force_password_change_at' => null,
        ]);
        $user->assignRole('Super Administrator');

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin4@vestra.com',
            'password' => 'Password123',
        ]);

        $exchangeToken = $login->json('data.exchange_token');

        $this->post('/api/v1/auth/exchange', [
            'exchange_token' => $exchangeToken,
        ])->assertRedirect('/admin');

        $response = $this->post('/api/v1/auth/exchange', [
            'exchange_token' => $exchangeToken,
        ]);

        $response->assertConflict();
    }

    public function test_exchange_token_rejects_customer(): void
    {
        $user = User::factory()->create([
            'email' => 'customer2@vestra.com',
            'password' => bcrypt('Password123'),
            'is_admin' => false,
            'status' => 'active',
        ]);
        $user->assignRole('customer');

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'customer2@vestra.com',
            'password' => 'Password123',
        ]);

        $this->assertNull($login->json('data.exchange_token'));

        // Customers cannot exchange tokens even if they possess one.
        $plainText = str_repeat('c', 64);
        \App\Models\ExchangeToken::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $plainText),
            'expires_at' => now()->addMinute(),
        ]);

        $response = $this->post('/api/v1/auth/exchange', [
            'exchange_token' => $plainText,
        ]);

        $response->assertForbidden();
    }

    public function test_exchange_token_rejects_missing_token(): void
    {
        $response = $this->post('/api/v1/auth/exchange', []);

        $response->assertUnprocessable();
    }
}

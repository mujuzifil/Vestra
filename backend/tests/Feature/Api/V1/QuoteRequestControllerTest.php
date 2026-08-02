<?php

namespace Tests\Feature\Api\V1;

use App\Mail\QuoteRequestReceivedMail;
use App\Models\Product;
use App\Models\QuoteRequest;
use App\Models\User;
use Database\Seeders\NotificationTemplateSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class QuoteRequestControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(NotificationTemplateSeeder::class);
    }

    public function test_public_user_can_submit_quote_request(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/v1/quote-requests', [
            'full_name' => 'John Doe',
            'company_name' => 'Acme Ltd',
            'email' => 'john@acme.com',
            'phone' => '+256 701 234 567',
            'district' => 'Kampala',
            'city' => 'Nakawa',
            'address' => 'Plot 123, Industrial Area',
            'preferred_delivery_date' => now()->addWeek()->format('Y-m-d'),
            'delivery_location' => 'Kampala Warehouse',
            'requirements' => 'We need bulk supply for a hotel chain.',
            'items' => [
                [
                    'product_name' => 'Heavy Duty Detergent',
                    'package_size' => '20L',
                    'quantity' => 50,
                    'notes' => 'Urgent',
                ],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Thank you. Your quotation request has been received.')
            ->assertJsonPath('data.full_name', 'John Doe')
            ->assertJsonPath('data.company_name', 'Acme Ltd')
            ->assertJsonPath('data.email', 'john@acme.com')
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('quote_requests', [
            'full_name' => 'John Doe',
            'company_name' => 'Acme Ltd',
            'email' => 'john@acme.com',
            'status' => 'pending',
            'source' => 'website',
        ]);

        $this->assertDatabaseHas('quote_request_items', [
            'product_name' => 'Heavy Duty Detergent',
            'package_size' => '20L',
            'quantity' => 50,
        ]);

        Mail::assertSent(QuoteRequestReceivedMail::class, function ($mail) {
            return $mail->quoteRequest->email === 'john@acme.com';
        });
    }

    public function test_quote_request_with_product_id_links_product(): void
    {
        Mail::fake();

        $product = Product::factory()->create(['name' => 'Silk Care', 'sku' => 'SILK-001']);

        $response = $this->postJson('/api/v1/quote-requests', [
            'full_name' => 'Jane Doe',
            'company_name' => 'Fresh Linen Ltd',
            'email' => 'jane@freshlinen.com',
            'phone' => '+256 702 345 678',
            'items' => [
                [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => 100,
                ],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('quote_request_items', [
            'product_id' => $product->id,
            'product_name' => 'Silk Care',
            'quantity' => 100,
        ]);
    }

    public function test_authenticated_user_quote_request_links_to_user(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/quote-requests', [
            'full_name' => $user->name,
            'company_name' => 'Authenticated Co',
            'email' => $user->email,
            'phone' => '+256 704 444 444',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('quote_requests', [
            'email' => $user->email,
            'user_id' => $user->id,
        ]);
    }

    public function test_quote_request_requires_required_fields(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/v1/quote-requests', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['full_name', 'company_name', 'email', 'phone']);
    }

    public function test_quote_request_validates_email_format(): void
    {
        $response = $this->postJson('/api/v1/quote-requests', [
            'full_name' => 'John Doe',
            'company_name' => 'Acme Ltd',
            'email' => 'not-an-email',
            'phone' => '+256 701 234 567',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_quote_request_generates_unique_reference_numbers(): void
    {
        Mail::fake();
        Notification::fake();

        $this->postJson('/api/v1/quote-requests', [
            'full_name' => 'First',
            'company_name' => 'First Co',
            'email' => 'first@example.com',
            'phone' => '+256 701 111 111',
        ]);

        $this->postJson('/api/v1/quote-requests', [
            'full_name' => 'Second',
            'company_name' => 'Second Co',
            'email' => 'second@example.com',
            'phone' => '+256 702 222 222',
        ]);

        $references = QuoteRequest::pluck('reference_number')->all();
        $this->assertCount(2, array_unique($references));
        $this->assertStringStartsWith('QR-', $references[0]);
    }

    public function test_admin_notification_is_dispatched_on_quote_request(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['is_admin' => true, 'status' => 'active']);

        $response = $this->postJson('/api/v1/quote-requests', [
            'full_name' => 'Admin Test',
            'company_name' => 'Notify Co',
            'email' => 'admin-test@example.com',
            'phone' => '+256 703 333 333',
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('notification_deliveries', [
            'recipient' => $admin->email,
            'channel' => 'email',
        ]);
    }
}

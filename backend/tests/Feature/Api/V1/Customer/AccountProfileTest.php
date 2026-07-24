<?php

namespace Tests\Feature\Api\V1\Customer;

use App\Models\AuditLog;
use App\Models\CustomerAddress;
use App\Models\CustomerDeletionRequest;
use App\Models\CustomerPreference;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AccountProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function customer(array $attributes = []): User
    {
        $user = User::factory()->create(array_merge(['status' => 'active'], $attributes));
        $user->assignRole('customer');

        return $user;
    }

    public function test_customer_can_view_profile(): void
    {
        $user = $this->customer();
        Sanctum::actingAs($user, ['*']);

        $this->getJson('/api/v1/auth/profile')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_customer_can_update_profile(): void
    {
        $user = $this->customer();
        Sanctum::actingAs($user, ['*']);

        $response = $this->putJson('/api/v1/auth/profile', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '0772000000',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.first_name', 'John')
            ->assertJsonPath('data.last_name', 'Doe')
            ->assertJsonPath('data.email', 'john.doe@example.com')
            ->assertJsonPath('data.gender', 'male');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'profile_updated',
        ]);
    }

    public function test_profile_email_must_be_unique_excluding_current_user(): void
    {
        $other = $this->customer(['email' => 'taken@example.com']);
        $user = $this->customer();
        Sanctum::actingAs($user, ['*']);

        $this->putJson('/api/v1/auth/profile', [
            'email' => 'taken@example.com',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_customer_can_upload_avatar(): void
    {
        $user = $this->customer();
        Sanctum::actingAs($user, ['*']);

        $file = UploadedFile::fake()->image('avatar.jpg', 100, 100);

        $response = $this->postJson('/api/v1/auth/avatar', [
            'avatar' => $file,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.avatar_url', fn ($url) => str_contains($url, 'avatars/'));

        $path = $user->fresh()->avatar_path;
        $this->assertNotNull($path);
        $this->assertFileExists(public_path($path));

        @unlink(public_path($path));
    }

    public function test_customer_can_delete_avatar(): void
    {
        $directory = public_path('avatars');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = 'test.jpg';
        $path = 'avatars/' . $filename;
        file_put_contents(public_path($path), 'fake-image');

        $user = $this->customer(['avatar_path' => $path]);
        Sanctum::actingAs($user, ['*']);

        $this->deleteJson('/api/v1/auth/avatar')
            ->assertOk()
            ->assertJsonPath('data.avatar_url', null);

        $this->assertNull($user->fresh()->avatar_path);
        $this->assertFileDoesNotExist(public_path($path));
    }

    public function test_customer_can_list_addresses(): void
    {
        $user = $this->customer();
        CustomerAddress::factory()->count(2)->create(['user_id' => $user->id, 'is_default' => false]);
        Sanctum::actingAs($user, ['*']);

        $this->getJson('/api/v1/auth/addresses')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_customer_can_create_address_with_default_flags(): void
    {
        $user = $this->customer();
        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson('/api/v1/auth/addresses', [
            'label' => 'Office',
            'full_name' => 'John Doe',
            'phone' => '0772000000',
            'city' => 'Kampala',
            'address_line' => 'Plot 1',
            'address_line_2' => 'Suite 2',
            'postal_code' => '256',
            'country' => 'Uganda',
            'delivery_notes' => 'Leave at reception',
            'is_default_shipping' => true,
            'is_default_billing' => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.is_default_shipping', true)
            ->assertJsonPath('data.is_default_billing', true)
            ->assertJsonPath('data.postal_code', '256');

        $this->assertDatabaseHas('customer_addresses', [
            'user_id' => $user->id,
            'label' => 'Office',
            'is_default_shipping' => true,
            'is_default_billing' => true,
        ]);
    }

    public function test_only_one_default_shipping_address_per_user(): void
    {
        $user = $this->customer();
        $first = CustomerAddress::factory()->create([
            'user_id' => $user->id,
            'is_default_shipping' => true,
        ]);
        Sanctum::actingAs($user, ['*']);

        $this->postJson('/api/v1/auth/addresses', [
            'label' => 'Home',
            'full_name' => 'Jane Doe',
            'phone' => '0772000001',
            'city' => 'Kampala',
            'address_line' => 'Plot 2',
            'is_default_shipping' => true,
        ])->assertCreated();

        $this->assertFalse($first->fresh()->is_default_shipping);
    }

    public function test_customer_can_update_address(): void
    {
        $user = $this->customer();
        $address = CustomerAddress::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user, ['*']);

        $this->putJson("/api/v1/auth/addresses/{$address->id}", [
            'label' => 'Updated',
            'full_name' => 'John Doe',
            'phone' => '0772000000',
            'city' => 'Kampala',
            'address_line' => 'Plot 1',
        ])->assertOk()
            ->assertJsonPath('data.label', 'Updated');
    }

    public function test_customer_cannot_view_other_user_address(): void
    {
        $user = $this->customer();
        $other = $this->customer();
        $address = CustomerAddress::factory()->create(['user_id' => $other->id]);
        Sanctum::actingAs($user, ['*']);

        $this->getJson("/api/v1/auth/addresses/{$address->id}")
            ->assertForbidden();
    }

    public function test_customer_can_manage_preferences(): void
    {
        $user = $this->customer();
        Sanctum::actingAs($user, ['*']);

        $this->getJson('/api/v1/auth/preferences')
            ->assertOk()
            ->assertJsonPath('data.notification_preferences', (object) []);

        $this->putJson('/api/v1/auth/preferences', [
            'notification_preferences' => ['email' => true],
            'account_preferences' => ['language' => 'en'],
        ])->assertOk()
            ->assertJsonPath('data.notification_preferences.email', true)
            ->assertJsonPath('data.account_preferences.language', 'en');

        $this->assertDatabaseHas('customer_preferences', [
            'user_id' => $user->id,
        ]);
    }

    public function test_customer_can_view_activity(): void
    {
        $user = $this->customer();
        AuditLog::factory()->count(3)->create(['user_id' => $user->id]);
        Sanctum::actingAs($user, ['*']);

        $this->getJson('/api/v1/auth/activity')
            ->assertOk()
            ->assertJsonCount(3, 'data.data');
    }

    public function test_customer_can_request_account_deletion(): void
    {
        $user = $this->customer();
        Sanctum::actingAs($user, ['*']);

        $this->postJson('/api/v1/auth/account-deletion-request', [
            'reason' => 'No longer needed',
        ])->assertCreated()
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('customer_deletion_requests', [
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
    }

    public function test_pending_deletion_request_is_returned_instead_of_duplicate(): void
    {
        $user = $this->customer();
        CustomerDeletionRequest::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
        Sanctum::actingAs($user, ['*']);

        $this->postJson('/api/v1/auth/account-deletion-request')
            ->assertOk()
            ->assertJsonPath('data.status', 'pending');

        $this->assertEquals(1, $user->deletionRequests()->count());
    }
}

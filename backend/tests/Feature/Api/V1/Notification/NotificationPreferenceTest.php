<?php

namespace Tests\Feature\Api\V1\Notification;

use App\Models\CustomerPreference;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationPreferenceTest extends TestCase
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

    public function test_customer_can_view_notification_preferences(): void
    {
        $user = $this->customer();
        Sanctum::actingAs($user, ['*']);

        CustomerPreference::factory()->create([
            'user_id' => $user->id,
            'notification_preferences' => ['email_notifications' => true],
        ]);

        $this->getJson('/api/v1/notifications/preferences')
            ->assertOk()
            ->assertJsonPath('data.notification_preferences.email_notifications', true);
    }

    public function test_customer_can_update_notification_preferences(): void
    {
        $user = $this->customer();
        Sanctum::actingAs($user, ['*']);

        $this->putJson('/api/v1/notifications/preferences', [
            'notification_preferences' => [
                'email_notifications' => false,
                'sms_notifications' => true,
            ],
            'system_alerts' => false,
            'emergency_alerts' => true,
        ])
            ->assertOk()
            ->assertJsonPath('data.notification_preferences.email_notifications', false)
            ->assertJsonPath('data.notification_preferences.sms_notifications', true)
            ->assertJsonPath('data.system_alerts', false)
            ->assertJsonPath('data.emergency_alerts', true);

        $this->assertDatabaseHas('customer_preferences', [
            'user_id' => $user->id,
            'system_alerts' => false,
            'emergency_alerts' => true,
        ]);
    }

    public function test_legacy_preference_endpoint_still_works(): void
    {
        $user = $this->customer();
        Sanctum::actingAs($user, ['*']);

        $this->putJson('/api/v1/auth/preferences', [
            'notification_preferences' => [
                'email_notifications' => false,
            ],
            'system_alerts' => true,
            'emergency_alerts' => false,
        ])
            ->assertOk()
            ->assertJsonPath('data.notification_preferences.email_notifications', false)
            ->assertJsonPath('data.system_alerts', true)
            ->assertJsonPath('data.emergency_alerts', false);
    }

    public function test_guest_cannot_access_preferences(): void
    {
        $this->getJson('/api/v1/notifications/preferences')
            ->assertUnauthorized();
    }
}

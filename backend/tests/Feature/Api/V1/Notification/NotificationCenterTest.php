<?php

namespace Tests\Feature\Api\V1\Notification;

use App\Models\User;
use App\Notifications\SystemNotification;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationCenterTest extends TestCase
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

    public function test_customer_can_list_notifications(): void
    {
        $user = $this->customer();
        Sanctum::actingAs($user, ['*']);

        $user->notify(new SystemNotification(
            templateKey: 'order.created',
            title: 'Order Created',
            inAppBody: 'Your order has been placed.',
            channels: ['database']
        ));

        $this->getJson('/api/v1/notifications')
            ->assertOk()
            ->assertJsonCount(1, 'data.data');
    }

    public function test_customer_can_list_unread_notifications(): void
    {
        $user = $this->customer();
        Sanctum::actingAs($user, ['*']);

        $user->notify(new SystemNotification(
            templateKey: 'order.created',
            title: 'Order Created',
            inAppBody: 'Your order has been placed.',
            channels: ['database']
        ));

        $this->getJson('/api/v1/notifications/unread')
            ->assertOk()
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.read_at', null);
    }

    public function test_customer_can_mark_notification_as_read(): void
    {
        $user = $this->customer();
        Sanctum::actingAs($user, ['*']);

        $user->notify(new SystemNotification(
            templateKey: 'order.created',
            title: 'Order Created',
            inAppBody: 'Your order has been placed.',
            channels: ['database']
        ));

        $notification = $user->notifications()->first();

        $this->putJson("/api/v1/notifications/{$notification->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Notification marked as read.');

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_customer_can_mark_all_notifications_as_read(): void
    {
        $user = $this->customer();
        Sanctum::actingAs($user, ['*']);

        $user->notify(new SystemNotification(
            templateKey: 'order.created',
            title: 'Order Created',
            inAppBody: 'Your order has been placed.',
            channels: ['database']
        ));

        $this->putJson('/api/v1/notifications/read-all')
            ->assertOk()
            ->assertJsonPath('message', 'All notifications marked as read.');

        $this->assertEquals(0, $user->unreadNotifications()->count());
    }

    public function test_customer_can_delete_notification(): void
    {
        $user = $this->customer();
        Sanctum::actingAs($user, ['*']);

        $user->notify(new SystemNotification(
            templateKey: 'order.created',
            title: 'Order Created',
            inAppBody: 'Your order has been placed.',
            channels: ['database']
        ));

        $notification = $user->notifications()->first();

        $this->deleteJson("/api/v1/notifications/{$notification->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Notification deleted.');

        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }

    public function test_customer_cannot_view_other_user_notifications(): void
    {
        $user = $this->customer();
        $other = $this->customer();
        Sanctum::actingAs($user, ['*']);

        $other->notify(new SystemNotification(
            templateKey: 'order.created',
            title: 'Order Created',
            inAppBody: 'Your order has been placed.',
            channels: ['database']
        ));

        $notification = $other->notifications()->first();

        $this->putJson("/api/v1/notifications/{$notification->id}")
            ->assertNotFound();
    }

    public function test_guest_cannot_access_notifications(): void
    {
        $this->getJson('/api/v1/notifications')
            ->assertUnauthorized();
    }
}

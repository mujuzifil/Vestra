<?php

namespace Tests\Feature\Admin;

use App\Enums\NotificationCategory;
use App\Enums\NotificationPriority;
use App\Enums\NotificationType;
use App\Models\User;
use App\Notifications\SystemNotification;
use App\Services\Admin\NotificationService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationsPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function admin(): User
    {
        return User::factory()->create([
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);
    }

    public function test_admin_can_access_notifications_page(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->get('/admin/workspace/notifications');

        $response->assertOk();
        $response->assertSee('Notifications');
        $response->assertSee('Your workspace notifications');
    }

    public function test_non_admin_is_redirected_from_notifications_page(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->get('/admin/workspace/notifications');

        $response->assertRedirect();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/admin/workspace/notifications');

        $response->assertRedirect('/admin/login');
    }

    public function test_page_lists_user_notifications(): void
    {
        $admin = $this->admin();

        $admin->notify(new SystemNotification(
            templateKey: 'test.notification',
            title: 'Test Notification',
            inAppBody: 'This is a test message.',
            channels: ['database'],
            type: NotificationType::SYSTEM,
            priority: NotificationPriority::INFORMATION,
        ));

        $response = $this->actingAs($admin)->get('/admin/workspace/notifications');

        $response->assertOk();
        $response->assertSee('Test Notification');
        $response->assertSee('This is a test message.');
    }

    public function test_page_does_not_show_other_user_notifications(): void
    {
        $admin = $this->admin();
        $other = $this->admin();

        $other->notify(new SystemNotification(
            templateKey: 'test.notification',
            title: 'Other User Notification',
            inAppBody: 'Secret message.',
            channels: ['database'],
        ));

        $response = $this->actingAs($admin)->get('/admin/workspace/notifications');

        $response->assertOk();
        $response->assertDontSee('Other User Notification');
    }

    public function test_search_filters_notifications(): void
    {
        $admin = $this->admin();

        $admin->notify(new SystemNotification(
            templateKey: 'test.notification',
            title: 'Matching Title',
            inAppBody: 'Some body text.',
            channels: ['database'],
        ));

        $admin->notify(new SystemNotification(
            templateKey: 'test.notification',
            title: 'Different Title',
            inAppBody: 'Other body text.',
            channels: ['database'],
        ));

        $response = $this->actingAs($admin)->get('/admin/workspace/notifications?search=Matching');

        $response->assertOk();
        $response->assertSee('Matching Title');
        $response->assertDontSee('Different Title');
    }

    public function test_status_filter_works(): void
    {
        $admin = $this->admin();

        $admin->notify(new SystemNotification(
            templateKey: 'test.notification',
            title: 'Unread Notification',
            channels: ['database'],
        ));

        $admin->notify(new SystemNotification(
            templateKey: 'test.notification',
            title: 'Read Notification',
            channels: ['database'],
        ));
        $admin->notifications()->latest()->first()?->markAsRead();

        $response = $this->actingAs($admin)->get('/admin/workspace/notifications?status=unread');

        $response->assertOk();
        $response->assertSee('Unread Notification');
        $response->assertDontSee('Read Notification');
    }

    public function test_priority_filter_works(): void
    {
        $admin = $this->admin();

        $admin->notify(new SystemNotification(
            templateKey: 'test.notification',
            title: 'Critical Notification',
            channels: ['database'],
            priority: NotificationPriority::CRITICAL,
        ));

        $admin->notify(new SystemNotification(
            templateKey: 'test.notification',
            title: 'Info Notification',
            channels: ['database'],
            priority: NotificationPriority::INFORMATION,
        ));

        $response = $this->actingAs($admin)->get('/admin/workspace/notifications?priority[]=critical');

        $response->assertOk();
        $response->assertSee('Critical Notification');
        $response->assertDontSee('Info Notification');
    }

    public function test_category_filter_works(): void
    {
        $admin = $this->admin();

        $admin->notify(new SystemNotification(
            templateKey: 'test.notification',
            title: 'Sales Notification',
            channels: ['database'],
            type: NotificationType::QUOTE_SUBMITTED,
        ));

        $admin->notify(new SystemNotification(
            templateKey: 'test.notification',
            title: 'System Notification',
            channels: ['database'],
            type: NotificationType::SYSTEM,
        ));

        $response = $this->actingAs($admin)->get('/admin/workspace/notifications?category[]=sales');

        $response->assertOk();
        $response->assertSee('Sales Notification');
        $response->assertDontSee('System Notification');
    }

    public function test_notification_service_marks_as_read(): void
    {
        $admin = $this->admin();

        $admin->notify(new SystemNotification(
            templateKey: 'test.notification',
            title: 'Mark Read Test',
            channels: ['database'],
        ));

        $notification = $admin->notifications()->first();
        $this->assertNull($notification->read_at);

        $this->actingAs($admin);
        app(NotificationService::class)->markAsRead($notification);

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_notification_service_marks_all_read(): void
    {
        $admin = $this->admin();

        $admin->notify(new SystemNotification(
            templateKey: 'test.notification',
            title: 'First',
            channels: ['database'],
        ));

        $admin->notify(new SystemNotification(
            templateKey: 'test.notification',
            title: 'Second',
            channels: ['database'],
        ));

        $this->actingAs($admin);
        app(NotificationService::class)->markAllRead();

        $this->assertEquals(0, $admin->unreadNotifications()->count());
    }

    public function test_notification_service_deletes_selected(): void
    {
        $admin = $this->admin();

        $admin->notify(new SystemNotification(
            templateKey: 'test.notification',
            title: 'Delete Me',
            channels: ['database'],
        ));

        $notification = $admin->notifications()->first();

        $this->actingAs($admin);
        app(NotificationService::class)->deleteSelected([$notification->id]);

        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }

    public function test_notification_service_cannot_delete_other_users_notification(): void
    {
        $admin = $this->admin();
        $other = $this->admin();

        $other->notify(new SystemNotification(
            templateKey: 'test.notification',
            title: 'Other Notification',
            channels: ['database'],
        ));

        $notification = $other->notifications()->first();

        $this->actingAs($admin);
        app(NotificationService::class)->deleteSelected([$notification->id]);

        $this->assertDatabaseHas('notifications', ['id' => $notification->id]);
    }

    public function test_kpi_cards_show_counts(): void
    {
        $admin = $this->admin();

        $admin->notify(new SystemNotification(
            templateKey: 'test.notification',
            title: 'KPI Test',
            channels: ['database'],
            priority: NotificationPriority::WARNING,
            category: NotificationCategory::SYSTEM,
        ));

        $response = $this->actingAs($admin)->get('/admin/workspace/notifications');

        $response->assertOk();
        $response->assertSee('Total Notifications');
        $response->assertSee('Unread');
        $response->assertSee('System Alerts');
    }
}

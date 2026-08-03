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
use Illuminate\Support\Facades\Route;
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

    public function test_notifications_route_is_registered(): void
    {
        $this->assertTrue(Route::has('filament.admin.pages.workspace.notifications'));
    }

    public function test_guest_is_redirected_from_notifications_route(): void
    {
        $response = $this->get('/workspace/notifications');

        $response->assertRedirect();
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

        $this->actingAs($admin);
        $notifications = app(NotificationService::class)->paginateNotifications();

        $this->assertEquals(1, $notifications->total());
        $this->assertEquals('Test Notification', $notifications->first()->data['title']);
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

        $this->actingAs($admin);
        $notifications = app(NotificationService::class)->paginateNotifications();

        $this->assertEquals(0, $notifications->total());
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

        $this->actingAs($admin);
        $notifications = app(NotificationService::class)->paginateNotifications(['search' => 'Matching']);

        $this->assertEquals(1, $notifications->total());
        $this->assertEquals('Matching Title', $notifications->first()->data['title']);
    }

    public function test_status_filter_works(): void
    {
        $admin = $this->admin();

        $admin->notify(new SystemNotification(
            templateKey: 'test.notification',
            title: 'Unread Notification',
            channels: ['database'],
        ));

        $readNotification = new SystemNotification(
            templateKey: 'test.notification',
            title: 'Read Notification',
            channels: ['database'],
        );
        $admin->notify($readNotification);
        $admin->notifications()->where('data->title', 'Read Notification')->first()?->markAsRead();

        $this->actingAs($admin);
        $notifications = app(NotificationService::class)->paginateNotifications(['status' => 'unread']);

        $this->assertEquals(1, $notifications->total());
        $this->assertEquals('Unread Notification', $notifications->first()->data['title']);
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

        $this->actingAs($admin);
        $notifications = app(NotificationService::class)->paginateNotifications(['priority' => ['critical']]);

        $this->assertEquals(1, $notifications->total());
        $this->assertEquals('Critical Notification', $notifications->first()->data['title']);
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

        $this->actingAs($admin);
        $notifications = app(NotificationService::class)->paginateNotifications(['category' => ['sales']]);

        $this->assertEquals(1, $notifications->total());
        $this->assertEquals('Sales Notification', $notifications->first()->data['title']);
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

        $this->actingAs($admin);
        $kpis = app(NotificationService::class)->getKpiCards();

        $this->assertEquals(1, $kpis[0]['value']);
        $this->assertEquals(1, $kpis[1]['value']);
        $this->assertEquals(1, $kpis[3]['value']);
    }
}

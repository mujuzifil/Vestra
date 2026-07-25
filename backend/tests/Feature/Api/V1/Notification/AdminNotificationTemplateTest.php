<?php

namespace Tests\Feature\Api\V1\Notification;

use App\Models\NotificationTemplate;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminNotificationTemplateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(AdminUserSeeder::class);
    }

    private function admin(): User
    {
        return User::where('email', 'admin@vestra.com')->first();
    }

    public function test_admin_can_list_templates(): void
    {
        Sanctum::actingAs($this->admin(), ['*']);

        NotificationTemplate::create([
            'event_key' => 'order.created',
            'name' => 'Order Created',
        ]);

        $this->getJson('/api/v1/admin/notifications/templates')
            ->assertOk()
            ->assertJsonCount(1, 'data.data');
    }

    public function test_admin_can_create_template(): void
    {
        Sanctum::actingAs($this->admin(), ['*']);

        $this->postJson('/api/v1/admin/notifications/templates', [
            'event_key' => 'order.created',
            'name' => 'Order Created',
            'subject' => 'Order {{order_number}}',
            'email_body' => '<p>Order created.</p>',
        ])
            ->assertCreated()
            ->assertJsonPath('data.event_key', 'order.created');

        $this->assertDatabaseHas('notification_templates', ['event_key' => 'order.created']);
    }

    public function test_admin_can_update_template(): void
    {
        Sanctum::actingAs($this->admin(), ['*']);

        $template = NotificationTemplate::create([
            'event_key' => 'order.created',
            'name' => 'Order Created',
        ]);

        $this->putJson("/api/v1/admin/notifications/templates/{$template->id}", [
            'name' => 'Updated Name',
            'subject' => 'Updated Subject',
        ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.subject', 'Updated Subject');
    }

    public function test_admin_can_delete_template(): void
    {
        Sanctum::actingAs($this->admin(), ['*']);

        $template = NotificationTemplate::create([
            'event_key' => 'order.created',
            'name' => 'Order Created',
        ]);

        $this->deleteJson("/api/v1/admin/notifications/templates/{$template->id}")
            ->assertOk();

        $this->assertDatabaseMissing('notification_templates', ['id' => $template->id]);
    }

    public function test_customer_cannot_manage_templates(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('customer');
        Sanctum::actingAs($user, ['*']);

        $this->getJson('/api/v1/admin/notifications/templates')
            ->assertForbidden();
    }
}

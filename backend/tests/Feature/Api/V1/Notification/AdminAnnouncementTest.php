<?php

namespace Tests\Feature\Api\V1\Notification;

use App\Enums\AnnouncementAudience;
use App\Models\Announcement;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminAnnouncementTest extends TestCase
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

    public function test_admin_can_create_announcement(): void
    {
        Sanctum::actingAs($this->admin(), ['*']);

        $this->postJson('/api/v1/admin/announcements', [
            'title' => 'System Maintenance',
            'body' => 'Scheduled maintenance tonight.',
            'target_audience' => AnnouncementAudience::EVERYONE->value,
            'sent_at' => now()->toDateTimeString(),
        ])
            ->assertCreated()
            ->assertJsonPath('data.title', 'System Maintenance');

        $this->assertDatabaseHas('announcements', ['title' => 'System Maintenance']);
    }

    public function test_admin_can_update_announcement(): void
    {
        Sanctum::actingAs($this->admin(), ['*']);

        $announcement = Announcement::create([
            'title' => 'Old Title',
            'body' => 'Body',
            'target_audience' => AnnouncementAudience::EVERYONE->value,
        ]);

        $this->putJson("/api/v1/admin/announcements/{$announcement->id}", [
            'title' => 'New Title',
        ])
            ->assertOk()
            ->assertJsonPath('data.title', 'New Title');
    }

    public function test_admin_can_delete_announcement(): void
    {
        Sanctum::actingAs($this->admin(), ['*']);

        $announcement = Announcement::create([
            'title' => 'To Delete',
            'body' => 'Body',
            'target_audience' => AnnouncementAudience::EVERYONE->value,
        ]);

        $this->deleteJson("/api/v1/admin/announcements/{$announcement->id}")
            ->assertOk();

        $this->assertDatabaseMissing('announcements', ['id' => $announcement->id]);
    }

    public function test_customer_cannot_manage_announcements(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('customer');
        Sanctum::actingAs($user, ['*']);

        $this->getJson('/api/v1/admin/announcements')
            ->assertForbidden();
    }
}

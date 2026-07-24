<?php

namespace Tests\Feature\Api\V1\Notification;

use App\Enums\AnnouncementAudience;
use App\Models\Announcement;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AnnouncementTest extends TestCase
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

    public function test_customer_can_view_active_announcements(): void
    {
        $user = $this->customer();
        Sanctum::actingAs($user, ['*']);

        Announcement::create([
            'title' => 'New Product Launch',
            'body' => 'Check out our new products.',
            'target_audience' => AnnouncementAudience::EVERYONE->value,
            'sent_at' => now(),
        ]);

        $this->getJson('/api/v1/announcements')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_customer_sees_only_relevant_announcements(): void
    {
        $user = $this->customer();
        Sanctum::actingAs($user, ['*']);

        Announcement::create([
            'title' => 'Customer News',
            'body' => 'For customers.',
            'target_audience' => AnnouncementAudience::CUSTOMERS->value,
            'sent_at' => now(),
        ]);

        Announcement::create([
            'title' => 'Distributor News',
            'body' => 'For distributors.',
            'target_audience' => AnnouncementAudience::DISTRIBUTORS->value,
            'sent_at' => now(),
        ]);

        $this->getJson('/api/v1/announcements')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Customer News');
    }

    public function test_unpublished_announcements_are_not_visible(): void
    {
        $user = $this->customer();
        Sanctum::actingAs($user, ['*']);

        Announcement::create([
            'title' => 'Draft',
            'body' => 'Not yet published.',
            'target_audience' => AnnouncementAudience::EVERYONE->value,
            'sent_at' => null,
        ]);

        $this->getJson('/api/v1/announcements')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_guest_cannot_view_announcements(): void
    {
        $this->getJson('/api/v1/announcements')
            ->assertUnauthorized();
    }
}

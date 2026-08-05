<?php

namespace Tests\Feature\Admin;

use App\Enums\ActivityCategory;
use App\Enums\ActivityStatus;
use App\Models\AuditLog;
use App\Models\LoginActivity;
use App\Models\User;
use App\Services\Admin\ActivityService;
use App\Services\ReportExportService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\TestCase;

class ActivityPageTest extends TestCase
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

    private function customer(): User
    {
        return User::factory()->create([
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);
    }

    public function test_activity_route_is_registered(): void
    {
        $this->assertTrue(Route::has('filament.admin.pages.workspace.activity'));
    }

    public function test_guest_is_redirected_from_activity_route(): void
    {
        $response = $this->get('/workspace/activity');

        $response->assertRedirect();
    }

    public function test_non_admin_is_denied_access_to_activity_page(): void
    {
        $customer = $this->customer();

        Livewire::actingAs($customer)
            ->test(\App\Filament\Pages\Workspace\ActivityPage::class)
            ->assertForbidden();
    }

    public function test_admin_can_view_activity_page_and_kpi_cards(): void
    {
        $admin = $this->admin();

        AuditLog::factory()->count(3)->create([
            'action' => 'product_updated',
            'user_id' => $admin->id,
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Pages\Workspace\ActivityPage::class)
            ->assertSuccessful()
            ->assertSee('Activity')
            ->assertSee('Track and review all activities across the system in real time.')
            ->assertSee('Total Activities')
            ->assertSee('User Activities')
            ->assertSee('Security Events')
            ->assertSee('Module Activities')
            ->assertSee('System Events');
    }

    public function test_empty_state_renders_when_no_activity_exists(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(\App\Filament\Pages\Workspace\ActivityPage::class)
            ->assertSuccessful()
            ->assertSee('No activity yet');
    }

    public function test_audit_log_rows_appear_in_feed_with_correct_title_category_and_status(): void
    {
        $admin = $this->admin();

        $log = AuditLog::factory()->create([
            'action' => 'product_updated',
            'user_id' => $admin->id,
        ]);

        $activities = app(ActivityService::class)->getActivities();

        $this->assertEquals(1, $activities->total());

        $item = $activities->first();
        $this->assertEquals("audit-{$log->id}", $item['id']);
        $this->assertEquals(ActivityCategory::PRODUCTS, $item['category']);
        $this->assertEquals(ActivityStatus::INFORMATION, $item['status']);
        $this->assertEquals('Products', $item['module']);
        $this->assertNotEmpty($item['title']);
    }

    public function test_login_activity_rows_appear_in_feed(): void
    {
        $admin = $this->admin();

        $login = LoginActivity::factory()->create([
            'user_id' => $admin->id,
            'successful' => true,
        ]);

        $activities = app(ActivityService::class)->getActivities();

        $this->assertEquals(1, $activities->total());

        $item = $activities->first();
        $this->assertEquals("login-{$login->id}", $item['id']);
        $this->assertEquals(ActivityCategory::AUTHENTICATION, $item['category']);
        $this->assertEquals(ActivityStatus::SUCCESS, $item['status']);
        $this->assertEquals('User login', $item['title']);
    }

    public function test_failed_login_activity_is_categorised_as_security_event(): void
    {
        LoginActivity::factory()->failed()->create([
            'user_id' => null,
            'email' => 'attacker@example.com',
        ]);

        $kpis = app(ActivityService::class)->getKpiCards();

        $this->assertEquals(1, $kpis[0]['value']);
        $this->assertEquals(1, $kpis[2]['value']); // Security Events
        $this->assertEquals(0, $kpis[1]['value']); // User Activities
        $this->assertEquals(1, $kpis[4]['value']); // System Events
    }

    public function test_search_filters_results(): void
    {
        $admin = $this->admin();

        AuditLog::factory()->create([
            'action' => 'matching_action',
            'user_id' => $admin->id,
        ]);

        AuditLog::factory()->create([
            'action' => 'other_action',
            'user_id' => $admin->id,
        ]);

        $activities = app(ActivityService::class)->getActivities(['search' => 'matching']);

        $this->assertEquals(1, $activities->total());
        $this->assertStringContainsString('Matching', $activities->first()['title']);
    }

    public function test_category_filter_works(): void
    {
        $admin = $this->admin();

        AuditLog::factory()->create([
            'action' => 'product_updated',
            'user_id' => $admin->id,
        ]);

        AuditLog::factory()->create([
            'action' => 'user_updated',
            'user_id' => $admin->id,
        ]);

        $activities = app(ActivityService::class)->getActivities(['category' => [ActivityCategory::PRODUCTS->value]]);

        $this->assertEquals(1, $activities->total());
        $this->assertEquals(ActivityCategory::PRODUCTS, $activities->first()['category']);
    }

    public function test_status_filter_works(): void
    {
        $admin = $this->admin();

        AuditLog::factory()->create([
            'action' => 'user_created',
            'user_id' => $admin->id,
        ]);

        AuditLog::factory()->create([
            'action' => 'user_deleted',
            'user_id' => $admin->id,
        ]);

        $activities = app(ActivityService::class)->getActivities(['status' => [ActivityStatus::WARNING->value]]);

        $this->assertEquals(1, $activities->total());
        $this->assertEquals(ActivityStatus::WARNING, $activities->first()['status']);
    }

    public function test_date_range_filter_works(): void
    {
        $admin = $this->admin();

        AuditLog::factory()->create([
            'action' => 'old_action',
            'user_id' => $admin->id,
            'created_at' => now()->subDays(10),
        ]);

        AuditLog::factory()->create([
            'action' => 'recent_action',
            'user_id' => $admin->id,
            'created_at' => now()->subDay(),
        ]);

        $activities = app(ActivityService::class)->getActivities([
            'date_from' => now()->subDays(5)->toDateString(),
            'date_until' => now()->toDateString(),
        ]);

        $this->assertEquals(1, $activities->total());
        $this->assertStringContainsString('Recent', $activities->first()['title']);
    }

    public function test_activity_feed_omits_view_action(): void
    {
        $admin = $this->admin();

        AuditLog::factory()->create([
            'action' => 'product_updated',
            'user_id' => $admin->id,
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Pages\Workspace\ActivityPage::class)
            ->assertSuccessful()
            ->assertDontSeeHtml('wire:click="openDetailPanel')
            ->assertDontSeeHtml('aria-label="View activity details"');
    }

    public function test_export_returns_filtered_rows(): void
    {
        $admin = $this->admin();

        AuditLog::factory()->create([
            'action' => 'product_updated',
            'user_id' => $admin->id,
        ]);

        AuditLog::factory()->create([
            'action' => 'user_updated',
            'user_id' => $admin->id,
        ]);

        $service = app(ActivityService::class);
        $rows = $service->forExport(['category' => [ActivityCategory::PRODUCTS->value]]);

        $this->assertCount(1, $rows);
        $this->assertEquals('Products', $rows[0]['category']);
    }

    public function test_export_action_does_not_error_for_admin(): void
    {
        $admin = $this->admin();

        AuditLog::factory()->create([
            'action' => 'product_updated',
            'user_id' => $admin->id,
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Pages\Workspace\ActivityPage::class)
            ->call('export', 'csv')
            ->assertOk();
    }

    public function test_csv_export_service_streams_data(): void
    {
        $admin = $this->admin();

        AuditLog::factory()->create([
            'action' => 'product_updated',
            'user_id' => $admin->id,
        ]);

        $rows = app(ActivityService::class)->forExport();
        $response = app(ReportExportService::class)->csv('test-activity', $this->exportColumns(), $rows);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('activity', strtolower($response->headers->get('Content-Disposition') ?? ''));
    }

    public function test_pagination_resets_on_filter_change(): void
    {
        $admin = $this->admin();

        AuditLog::factory()->count(25)->create([
            'action' => 'product_updated',
            'user_id' => $admin->id,
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Pages\Workspace\ActivityPage::class)
            ->call('gotoPage', 2)
            ->assertSet('paginators.page', 2)
            ->set('search', 'product')
            ->assertSet('paginators.page', 1);
    }

    /**
     * @return array<int, array{name: string, label: string}>
     */
    private function exportColumns(): array
    {
        return [
            ['name' => 'date', 'label' => 'Date'],
            ['name' => 'activity', 'label' => 'Activity'],
            ['name' => 'category', 'label' => 'Category'],
            ['name' => 'module', 'label' => 'Module'],
            ['name' => 'user', 'label' => 'User'],
            ['name' => 'status', 'label' => 'Status'],
            ['name' => 'ip_address', 'label' => 'IP Address'],
            ['name' => 'user_agent', 'label' => 'User Agent'],
            ['name' => 'related_entity', 'label' => 'Related Entity'],
        ];
    }
}

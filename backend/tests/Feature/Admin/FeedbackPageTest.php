<?php

namespace Tests\Feature\Admin;

use App\Enums\FeedbackCategory;
use App\Enums\FeedbackStatus;
use App\Enums\Priority;
use App\Filament\Pages\CustomerSuccess\FeedbackPage;
use App\Filament\Resources\CustomerFeedbackResource;
use App\Filament\Resources\CustomerFeedbackResource\Pages\ListCustomerFeedback;
use App\Models\CustomerFeedback;
use App\Models\User;
use App\Services\Admin\FeedbackAdminService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\TestCase;

class FeedbackPageTest extends TestCase
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

    private function feedback(array $attrs = []): CustomerFeedback
    {
        return CustomerFeedback::factory()->create(array_merge([
            'status' => FeedbackStatus::NEW->value,
            'priority' => Priority::MEDIUM->value,
            'category' => FeedbackCategory::GENERAL->value,
        ], $attrs));
    }

    public function test_feedback_route_is_registered(): void
    {
        $this->assertTrue(Route::has('filament.admin.pages.customer-success.feedback'));
        $this->assertTrue(Route::has('filament.admin.customer-success.feedback.export'));
        $this->assertStringContainsString('/customer-success/feedback', FeedbackPage::getUrl());
    }

    public function test_legacy_resource_is_hidden_from_navigation(): void
    {
        $this->assertFalse(CustomerFeedbackResource::shouldRegisterNavigation());
        $this->assertSame([], CustomerFeedbackResource::getNavigationItems());
    }

    public function test_legacy_list_page_redirects_to_feedback_workspace(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(CustomerFeedbackResource::getUrl('index'))
            ->assertRedirect(FeedbackPage::getUrl());
    }

    public function test_guest_is_redirected_from_feedback_route(): void
    {
        $response = $this->get('/customer-success/feedback');

        $response->assertRedirect();
    }

    public function test_non_admin_is_denied_access_to_feedback_page(): void
    {
        $customer = $this->customer();

        Livewire::actingAs($customer)
            ->test(FeedbackPage::class)
            ->assertForbidden();
    }

    public function test_admin_can_view_feedback_page_and_kpis(): void
    {
        $admin = $this->admin();

        CustomerFeedback::factory()->count(3)->create();

        Livewire::actingAs($admin)
            ->test(FeedbackPage::class)
            ->assertSuccessful()
            ->assertSee('Feedback')
            ->assertSee('Total')
            ->assertSee('New')
            ->assertSee('In Progress')
            ->assertSee('Resolved')
            ->assertSee('Praise')
            ->assertSee('Complaints');
    }

    public function test_empty_state_renders_when_no_feedback_exists(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(FeedbackPage::class)
            ->assertSuccessful()
            ->assertSee('No customer feedback yet');
    }

    public function test_search_filters_by_subject_and_customer(): void
    {
        $admin = $this->admin();

        $userA = User::factory()->create(['name' => 'Alice Wonderland']);
        $userB = User::factory()->create(['name' => 'Bob Builder']);

        $this->feedback(['user_id' => $userA->id, 'subject' => 'Login broken for me']);
        $this->feedback(['user_id' => $userB->id, 'subject' => 'Love the new feature']);

        Livewire::actingAs($admin)
            ->test(FeedbackPage::class)
            ->set('search', 'Login broken')
            ->assertSee('Login broken for me')
            ->assertDontSee('Love the new feature');
    }

    public function test_status_filter_works(): void
    {
        $admin = $this->admin();

        $this->feedback(['status' => FeedbackStatus::NEW->value, 'subject' => 'New Feedback Item']);
        $this->feedback(['status' => FeedbackStatus::RESOLVED->value, 'subject' => 'Resolved Feedback Item']);

        Livewire::actingAs($admin)
            ->test(FeedbackPage::class)
            ->set('statusFilter', [FeedbackStatus::NEW->value])
            ->assertSee('New Feedback Item')
            ->assertDontSee('Resolved Feedback Item');
    }

    public function test_category_filter_works(): void
    {
        $admin = $this->admin();

        $this->feedback(['category' => FeedbackCategory::PRAISE->value, 'subject' => 'Great work team']);
        $this->feedback(['category' => FeedbackCategory::BUG->value, 'subject' => 'Something crashed']);

        Livewire::actingAs($admin)
            ->test(FeedbackPage::class)
            ->set('categoryFilter', [FeedbackCategory::PRAISE->value])
            ->assertSee('Great work team')
            ->assertDontSee('Something crashed');
    }

    public function test_kpi_cards_use_live_counts(): void
    {
        CustomerFeedback::factory()->count(2)->create(['status' => FeedbackStatus::NEW->value, 'category' => FeedbackCategory::GENERAL->value]);
        CustomerFeedback::factory()->create(['status' => FeedbackStatus::IN_PROGRESS->value, 'category' => FeedbackCategory::BUG->value]);
        CustomerFeedback::factory()->create(['status' => FeedbackStatus::RESOLVED->value, 'category' => FeedbackCategory::PRAISE->value]);
        CustomerFeedback::factory()->create(['status' => FeedbackStatus::NEW->value, 'category' => FeedbackCategory::COMPLAINT->value]);

        $cards = app(FeedbackAdminService::class)->getKpiCards();
        $byLabel = collect($cards)->keyBy('label');

        $this->assertEquals('5', $byLabel['Total']['value']);
        $this->assertEquals('3', $byLabel['New']['value']);
        $this->assertEquals('1', $byLabel['In Progress']['value']);
        $this->assertEquals('1', $byLabel['Resolved']['value']);
        $this->assertEquals('1', $byLabel['Praise']['value']);
        $this->assertEquals('1', $byLabel['Complaints']['value']);
    }

    public function test_detail_drawer_opens_and_marks_read(): void
    {
        $admin = $this->admin();

        $item = $this->feedback(['subject' => 'Unread feedback detail', 'message' => 'Please look into this issue.']);

        $this->assertNull($item->read_at);

        Livewire::actingAs($admin)
            ->test(FeedbackPage::class)
            ->call('openDetailDrawer', $item->id)
            ->assertSet('showDetailDrawer', true)
            ->assertSet('selectedFeedbackId', $item->id)
            ->assertSee('Unread feedback detail')
            ->assertSee('Please look into this issue.');

        $this->assertNotNull($item->fresh()->read_at);
    }

    public function test_mark_in_progress_updates_status(): void
    {
        $admin = $this->admin();
        $item = $this->feedback(['status' => FeedbackStatus::NEW->value]);

        Livewire::actingAs($admin)
            ->test(FeedbackPage::class)
            ->call('markInProgress', $item->id)
            ->assertHasNoErrors();

        $this->assertEquals(FeedbackStatus::IN_PROGRESS->value, $item->fresh()->status);
    }

    public function test_mark_resolved_updates_status(): void
    {
        $admin = $this->admin();
        $item = $this->feedback(['status' => FeedbackStatus::IN_PROGRESS->value]);

        Livewire::actingAs($admin)
            ->test(FeedbackPage::class)
            ->call('markResolved', $item->id)
            ->assertHasNoErrors();

        $this->assertEquals(FeedbackStatus::RESOLVED->value, $item->fresh()->status);
    }

    public function test_mark_unread_clears_read_at(): void
    {
        $admin = $this->admin();
        $item = $this->feedback(['read_at' => now()]);

        Livewire::actingAs($admin)
            ->test(FeedbackPage::class)
            ->call('markUnread', $item->id)
            ->assertHasNoErrors();

        $this->assertNull($item->fresh()->read_at);
    }

    public function test_export_returns_filtered_rows(): void
    {
        $praise = $this->feedback(['category' => FeedbackCategory::PRAISE->value, 'subject' => 'Great service']);
        $bug = $this->feedback(['category' => FeedbackCategory::BUG->value, 'subject' => 'Found a bug']);

        $rows = app(FeedbackAdminService::class)->exportRows([
            'category' => [FeedbackCategory::PRAISE->value],
        ]);

        $this->assertCount(1, $rows);
        $this->assertEquals('Great service', $rows[0]['subject']);
    }

    public function test_export_route_requires_admin(): void
    {
        $customer = $this->customer();

        $this->actingAs($customer)
            ->get(route('filament.admin.customer-success.feedback.export', ['format' => 'csv']))
            ->assertForbidden();
    }

    public function test_export_route_downloads_csv_for_admin(): void
    {
        $admin = $this->admin();
        $this->feedback();

        $response = $this->actingAs($admin)
            ->get(route('filament.admin.customer-success.feedback.export', ['format' => 'csv']));

        $response->assertSuccessful();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_pagination_resets_on_filter_change(): void
    {
        $admin = $this->admin();

        CustomerFeedback::factory()->count(25)->create();

        Livewire::actingAs($admin)
            ->test(FeedbackPage::class)
            ->call('gotoPage', 2)
            ->assertSet('paginators.page', 2)
            ->set('search', 'zzz-no-match')
            ->assertSet('paginators.page', 1);
    }
}

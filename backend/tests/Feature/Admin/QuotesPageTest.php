<?php

namespace Tests\Feature\Admin;

use App\Enums\QuoteRequestStatus;
use App\Filament\Pages\Sales\QuotesPage;
use App\Models\QuoteRequest;
use App\Models\QuoteRequestItem;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\Admin\QuoteAdminService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\TestCase;

class QuotesPageTest extends TestCase
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

    public function test_quotes_route_is_registered(): void
    {
        $this->assertTrue(Route::has('filament.admin.pages.sales.quotes'));
        $this->assertTrue(Route::has('filament.admin.sales.quotes.export'));
    }

    public function test_guest_is_redirected_from_quotes_route(): void
    {
        $response = $this->get('/sales/quotes');

        $response->assertRedirect();
    }

    public function test_non_admin_is_denied_access_to_quotes_page(): void
    {
        $customer = $this->customer();

        Livewire::actingAs($customer)
            ->test(QuotesPage::class)
            ->assertForbidden();
    }

    public function test_admin_can_view_quotes_page_and_kpis(): void
    {
        $admin = $this->admin();

        QuoteRequest::factory()->count(3)->create();

        Livewire::actingAs($admin)
            ->test(QuotesPage::class)
            ->assertSuccessful()
            ->assertSee('Quotes')
            ->assertSee('Manage and track all sales quotes and proposals.')
            ->assertSee('Total Quotes')
            ->assertSee('Pending')
            ->assertSee('Approved')
            ->assertSee('Declined')
            ->assertSee('Total Value (MTD)');
    }

    public function test_empty_state_renders_when_no_quotes_exist(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(QuotesPage::class)
            ->assertSuccessful()
            ->assertSee('No quote requests yet')
            ->assertSee('No quote requests have been submitted yet');
    }

    public function test_search_filters_by_reference_number(): void
    {
        $admin = $this->admin();

        QuoteRequest::factory()->create([
            'reference_number' => 'QR-20260803-0001',
            'company_name' => 'Alpha Co',
        ]);
        QuoteRequest::factory()->create([
            'reference_number' => 'QR-20260803-9999',
            'company_name' => 'Beta Co',
        ]);

        Livewire::actingAs($admin)
            ->test(QuotesPage::class)
            ->set('search', 'QR-20260803-0001')
            ->assertSee('QR-20260803-0001')
            ->assertSee('Alpha Co')
            ->assertDontSee('Beta Co');
    }

    public function test_search_filters_by_company_and_email(): void
    {
        $admin = $this->admin();

        QuoteRequest::factory()->create([
            'company_name' => 'Bright Era',
            'email' => 'bright@example.com',
        ]);
        QuoteRequest::factory()->create([
            'company_name' => 'Other Co',
            'email' => 'other@example.com',
        ]);

        Livewire::actingAs($admin)
            ->test(QuotesPage::class)
            ->set('search', 'bright@example.com')
            ->assertSee('Bright Era')
            ->assertDontSee('Other Co');
    }

    public function test_status_filter_works(): void
    {
        $admin = $this->admin();

        QuoteRequest::factory()->create([
            'status' => QuoteRequestStatus::PENDING,
            'company_name' => 'Pending Co',
        ]);
        QuoteRequest::factory()->create([
            'status' => QuoteRequestStatus::APPROVED,
            'company_name' => 'Approved Co',
        ]);

        Livewire::actingAs($admin)
            ->test(QuotesPage::class)
            ->set('statusFilter', [QuoteRequestStatus::PENDING->value])
            ->assertSee('Pending Co')
            ->assertDontSee('Approved Co');
    }

    public function test_sales_rep_filter_works(): void
    {
        $admin = $this->admin();
        $rep = User::factory()->create(['is_admin' => true, 'name' => 'Sales Rep']);

        QuoteRequest::factory()->create([
            'assigned_to' => $rep->id,
            'company_name' => 'Assigned Co',
        ]);
        QuoteRequest::factory()->create([
            'assigned_to' => null,
            'company_name' => 'Unassigned Co',
        ]);

        Livewire::actingAs($admin)
            ->test(QuotesPage::class)
            ->set('assignedToFilter', $rep->id)
            ->assertSee('Assigned Co')
            ->assertDontSee('Unassigned Co');
    }

    public function test_sorting_by_company_name_works(): void
    {
        QuoteRequest::factory()->create(['company_name' => 'Zebra Ltd']);
        QuoteRequest::factory()->create(['company_name' => 'Alpha Ltd']);

        $quotes = app(QuoteAdminService::class)->paginateQuotes([], 'company_name', 'asc', 10);

        $this->assertEquals('Alpha Ltd', $quotes->first()->company_name);
    }

    public function test_detail_drawer_shows_live_relationships(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create();

        $quote = QuoteRequest::factory()->create([
            'user_id' => $user->id,
            'company_name' => 'Drawer Co',
            'requirements' => 'Need bulk packaging',
        ]);

        QuoteRequestItem::factory()->create([
            'quote_request_id' => $quote->id,
            'product_name' => 'Detergent 5L',
            'quantity' => 10,
        ]);

        SupportTicket::factory()->create([
            'user_id' => $user->id,
            'subject' => 'Linked ticket subject',
            'status' => 'open',
        ]);

        Livewire::actingAs($admin)
            ->test(QuotesPage::class)
            ->call('openDetailDrawer', $quote->id)
            ->assertSet('showDetailDrawer', true)
            ->assertSet('selectedQuoteId', $quote->id)
            ->assertSee('Drawer Co')
            ->assertSee('Detergent 5L')
            ->assertSee('Need bulk packaging')
            ->assertSee('Linked ticket subject');
    }

    public function test_admin_can_update_quote_status(): void
    {
        $admin = $this->admin();
        $quote = QuoteRequest::factory()->create([
            'status' => QuoteRequestStatus::PENDING,
        ]);

        Livewire::actingAs($admin)
            ->test(QuotesPage::class)
            ->call('updateStatus', $quote->id, QuoteRequestStatus::APPROVED->value)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('quote_requests', [
            'id' => $quote->id,
            'status' => QuoteRequestStatus::APPROVED->value,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'quote.status_changed',
            'subject_id' => $quote->id,
        ]);
    }

    public function test_admin_can_edit_quote_via_form(): void
    {
        $admin = $this->admin();
        $quote = QuoteRequest::factory()->create([
            'priority' => 'low',
            'admin_notes' => 'Old notes',
        ]);

        Livewire::actingAs($admin)
            ->test(QuotesPage::class)
            ->call('openEditDrawer', $quote->id)
            ->assertSet('showFormDrawer', true)
            ->set('form.priority', 'high')
            ->set('form.admin_notes', 'Updated notes')
            ->set('form.estimated_value', '1500000')
            ->call('saveQuote')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('quote_requests', [
            'id' => $quote->id,
            'priority' => 'high',
            'admin_notes' => 'Updated notes',
        ]);
    }

    public function test_export_returns_filtered_rows(): void
    {
        QuoteRequest::factory()->create([
            'status' => QuoteRequestStatus::APPROVED,
            'company_name' => 'Approved Co',
        ]);
        QuoteRequest::factory()->create([
            'status' => QuoteRequestStatus::PENDING,
            'company_name' => 'Pending Co',
        ]);

        $rows = app(QuoteAdminService::class)->exportQuotes([
            'status' => [QuoteRequestStatus::APPROVED->value],
        ]);

        $this->assertCount(1, $rows);
        $this->assertEquals('Approved Co', $rows[0]['company_name']);
    }

    public function test_kpi_cards_use_live_counts(): void
    {
        QuoteRequest::factory()->count(2)->create(['status' => QuoteRequestStatus::PENDING]);
        QuoteRequest::factory()->create(['status' => QuoteRequestStatus::APPROVED]);

        $cards = app(QuoteAdminService::class)->getKpiCards();
        $byLabel = collect($cards)->keyBy('label');

        $this->assertEquals('3', $byLabel['Total Quotes']['value']);
        $this->assertEquals('2', $byLabel['Pending']['value']);
        $this->assertEquals('1', $byLabel['Approved']['value']);
    }

    public function test_pagination_resets_on_filter_change(): void
    {
        $admin = $this->admin();

        QuoteRequest::factory()->count(25)->create();

        Livewire::actingAs($admin)
            ->test(QuotesPage::class)
            ->call('gotoPage', 2)
            ->assertSet('paginators.page', 2)
            ->set('search', 'QR')
            ->assertSet('paginators.page', 1);
    }
}

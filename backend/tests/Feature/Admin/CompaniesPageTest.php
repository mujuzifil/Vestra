<?php

namespace Tests\Feature\Admin;

use App\Enums\CompanyStatus;
use App\Filament\Pages\Sales\CompaniesPage;
use App\Models\CompanyProfile;
use App\Models\CustomerDocument;
use App\Models\QuoteRequest;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\Admin\CompanyService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\TestCase;

class CompaniesPageTest extends TestCase
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

    public function test_companies_route_is_registered(): void
    {
        $this->assertTrue(Route::has('filament.admin.pages.sales.companies'));
        $this->assertTrue(Route::has('filament.admin.sales.companies.export'));
        $this->assertStringContainsString('/sales/companies', CompaniesPage::getUrl());
    }

    public function test_customer_resource_is_hidden_from_navigation(): void
    {
        $this->assertFalse(\App\Filament\Resources\CustomerResource::shouldRegisterNavigation());
        $this->assertSame([], \App\Filament\Resources\CustomerResource::getNavigationItems());
    }

    public function test_customers_index_redirects_to_companies_workspace(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get('/customers')
            ->assertRedirect(CompaniesPage::getUrl());
    }

    public function test_guest_is_redirected_from_companies_route(): void
    {
        $response = $this->get('/sales/companies');

        $response->assertRedirect();
    }

    public function test_non_admin_is_denied_access_to_companies_page(): void
    {
        $customer = $this->customer();

        Livewire::actingAs($customer)
            ->test(CompaniesPage::class)
            ->assertForbidden();
    }

    public function test_admin_can_view_companies_page_and_kpis(): void
    {
        $admin = $this->admin();

        CompanyProfile::factory()->count(3)->create();

        Livewire::actingAs($admin)
            ->test(CompaniesPage::class)
            ->assertSuccessful()
            ->assertSee('Companies')
            ->assertSee('Manage and grow your company relationships.')
            ->assertDontSee('Import')
            ->assertSee('Total Companies')
            ->assertSee('Active Companies')
            ->assertSee('New This Month')
            ->assertSee('With Open Quotes')
            ->assertSee('With Active Tickets');
    }

    public function test_empty_state_renders_when_no_companies_exist(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(CompaniesPage::class)
            ->assertSuccessful()
            ->assertSee('No companies yet');
    }

    public function test_search_filters_by_company_name(): void
    {
        $admin = $this->admin();

        CompanyProfile::factory()->create(['company_name' => 'Bright Era Sdn Bhd']);
        CompanyProfile::factory()->create(['company_name' => 'Other Solutions']);

        Livewire::actingAs($admin)
            ->test(CompaniesPage::class)
            ->set('search', 'Bright Era')
            ->assertSee('Bright Era Sdn Bhd')
            ->assertDontSee('Other Solutions');
    }

    public function test_search_filters_by_contact_email(): void
    {
        $admin = $this->admin();

        CompanyProfile::factory()->create(['primary_contact_email' => 'bright@example.com']);
        CompanyProfile::factory()->create(['primary_contact_email' => 'other@example.com']);

        Livewire::actingAs($admin)
            ->test(CompaniesPage::class)
            ->set('search', 'bright@example.com')
            ->assertSee('bright@example.com')
            ->assertDontSee('other@example.com');
    }

    public function test_search_filters_by_tax_id(): void
    {
        $admin = $this->admin();

        CompanyProfile::factory()->create(['tax_identification' => 'TIN-12345678']);
        CompanyProfile::factory()->create(['tax_identification' => 'TIN-87654321']);

        Livewire::actingAs($admin)
            ->test(CompaniesPage::class)
            ->set('search', 'TIN-12345678')
            ->assertSee('TIN-12345678')
            ->assertDontSee('TIN-87654321');
    }

    public function test_status_filter_works(): void
    {
        $admin = $this->admin();

        CompanyProfile::factory()->create([
            'status' => CompanyStatus::ACTIVE->value,
            'company_name' => 'Active Company',
        ]);
        CompanyProfile::factory()->create([
            'status' => CompanyStatus::INACTIVE->value,
            'company_name' => 'Inactive Company',
        ]);

        Livewire::actingAs($admin)
            ->test(CompaniesPage::class)
            ->set('statusFilter', [CompanyStatus::ACTIVE->value])
            ->assertSee('Active Company')
            ->assertDontSee('Inactive Company');
    }

    public function test_account_manager_filter_works(): void
    {
        $admin = $this->admin();
        $manager = User::factory()->create(['is_admin' => true, 'name' => 'Account Manager']);

        CompanyProfile::factory()->create(['account_manager_id' => $manager->id, 'company_name' => 'Managed Company']);
        CompanyProfile::factory()->create(['account_manager_id' => null, 'company_name' => 'Unmanaged Company']);

        Livewire::actingAs($admin)
            ->test(CompaniesPage::class)
            ->set('accountManagerFilter', $manager->id)
            ->assertSee('Managed Company')
            ->assertDontSee('Unmanaged Company');
    }

    public function test_sorting_by_company_name_works(): void
    {
        $admin = $this->admin();

        CompanyProfile::factory()->create(['company_name' => 'Zebra Ltd']);
        CompanyProfile::factory()->create(['company_name' => 'Alpha Ltd']);

        $component = Livewire::actingAs($admin)->test(CompaniesPage::class);

        $companies = app(CompanyService::class)->paginateCompanies([], 'company_name', 'asc', 10);

        $this->assertEquals('Alpha Ltd', $companies->first()->company_name);
    }

    public function test_sorting_by_created_at_works(): void
    {
        $admin = $this->admin();

        $older = CompanyProfile::factory()->create(['created_at' => now()->subDays(5)]);
        $newer = CompanyProfile::factory()->create(['created_at' => now()]);

        $companies = app(CompanyService::class)->paginateCompanies([], 'created_at', 'desc', 10);

        $this->assertEquals($newer->id, $companies->first()->id);
    }

    public function test_detail_drawer_returns_related_quotes_tickets_and_documents(): void
    {
        $admin = $this->admin();
        $profile = CompanyProfile::factory()->create();

        QuoteRequest::factory()->count(2)->create([
            'user_id' => $profile->user_id,
            'status' => 'pending',
        ]);

        SupportTicket::factory()->count(2)->create([
            'user_id' => $profile->user_id,
            'status' => 'open',
        ]);

        CustomerDocument::factory()->count(2)->create([
            'user_id' => $profile->user_id,
            'documentable_type' => CompanyProfile::class,
            'documentable_id' => $profile->id,
        ]);

        Livewire::actingAs($admin)
            ->test(CompaniesPage::class)
            ->call('openDetailDrawer', $profile->id)
            ->assertSet('showDetailDrawer', true)
            ->assertSet('selectedCompanyId', $profile->id)
            ->assertSee('Recent Quotes')
            ->assertSee('Active Support Tickets')
            ->assertSee('Documents');
    }

    public function test_admin_can_create_company_via_form(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(CompaniesPage::class)
            ->call('openCreateDrawer')
            ->assertSet('showFormDrawer', true)
            ->set('form.company_name', 'New Enterprise Ltd')
            ->set('form.primary_contact_name', 'Jane Doe')
            ->set('form.primary_contact_email', 'jane@enterprise.test')
            ->set('form.country', 'Uganda')
            ->set('form.status', CompanyStatus::PROSPECT->value)
            ->call('saveCompany')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('company_profiles', [
            'company_name' => 'New Enterprise Ltd',
            'primary_contact_email' => 'jane@enterprise.test',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'jane@enterprise.test',
        ]);
    }

    public function test_admin_can_update_company_via_form(): void
    {
        $admin = $this->admin();
        $profile = CompanyProfile::factory()->create(['company_name' => 'Old Name']);

        Livewire::actingAs($admin)
            ->test(CompaniesPage::class)
            ->call('openEditDrawer', $profile->id)
            ->assertSet('showFormDrawer', true)
            ->set('form.company_name', 'Updated Name')
            ->call('saveCompany')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('company_profiles', [
            'id' => $profile->id,
            'company_name' => 'Updated Name',
        ]);
    }

    public function test_admin_can_delete_company(): void
    {
        $admin = $this->admin();
        $profile = CompanyProfile::factory()->create();

        Livewire::actingAs($admin)
            ->test(CompaniesPage::class)
            ->call('deleteCompany', $profile->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('company_profiles', ['id' => $profile->id]);
    }

    public function test_export_returns_filtered_rows(): void
    {
        $admin = $this->admin();

        CompanyProfile::factory()->create([
            'status' => CompanyStatus::ACTIVE->value,
            'company_name' => 'Active Company',
        ]);

        CompanyProfile::factory()->create([
            'status' => CompanyStatus::INACTIVE->value,
            'company_name' => 'Inactive Company',
        ]);

        $rows = app(CompanyService::class)->exportCompanies([
            'status' => [CompanyStatus::ACTIVE->value],
        ]);

        $this->assertCount(1, $rows);
        $this->assertEquals('Active Company', $rows[0]['company_name']);
    }

    public function test_export_action_does_not_error_for_admin(): void
    {
        $admin = $this->admin();

        CompanyProfile::factory()->create();

        Livewire::actingAs($admin)
            ->test(CompaniesPage::class)
            ->call('export', 'csv')
            ->assertHasNoErrors();
    }

    public function test_filter_panel_is_closed_by_default(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CompaniesPage::class)
            ->assertSet('showFilterPanel', false)
            ->assertDontSee('Import');
    }

    public function test_filter_panel_opens_only_after_toggle(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CompaniesPage::class)
            ->assertSet('showFilterPanel', false)
            ->call('toggleFilterPanel')
            ->assertSet('showFilterPanel', true)
            ->call('toggleFilterPanel')
            ->assertSet('showFilterPanel', false);
    }

    public function test_pagination_resets_on_filter_change(): void
    {
        $admin = $this->admin();

        CompanyProfile::factory()->count(25)->create();

        Livewire::actingAs($admin)
            ->test(CompaniesPage::class)
            ->call('gotoPage', 2)
            ->assertSet('paginators.page', 2)
            ->set('search', 'company')
            ->assertSet('paginators.page', 1);
    }

    public function test_table_shows_polished_columns_and_with_count(): void
    {
        $admin = $this->admin();
        $profile = CompanyProfile::factory()->create([
            'company_name' => 'Bright Era',
            'primary_contact_name' => 'Jane Contact',
            'primary_contact_email' => 'jane@brightera.test',
            'industry' => 'Technology',
            'country' => 'Uganda',
        ]);

        QuoteRequest::factory()->create([
            'user_id' => $profile->user_id,
            'status' => 'pending',
        ]);

        SupportTicket::factory()->create([
            'user_id' => $profile->user_id,
            'status' => 'open',
        ]);

        Livewire::actingAs($admin)
            ->test(CompaniesPage::class)
            ->assertSee('Bright Era')
            ->assertSee('jane@brightera.test')
            ->assertSee('Technology')
            ->assertSee('Uganda')
            ->assertSee('Contacts')
            ->assertSee('Open Quotes')
            ->assertSee('Active Tickets')
            ->assertSee('Filters')
            ->assertDontSee('Clear all')
            ->call('toggleFilterPanel')
            ->assertSee('Clear all')
            ->assertSee('Apply Filters');

        $companies = app(CompanyService::class)->paginateCompanies([], 'created_at', 'desc', 10);
        $this->assertSame(1, (int) $companies->first()->open_quotes_count);
        $this->assertSame(1, (int) $companies->first()->active_tickets_count);
    }

    public function test_has_distributor_filter_works(): void
    {
        $admin = $this->admin();

        $withDistributor = CompanyProfile::factory()->create(['company_name' => 'Distributor Linked Co']);
        \App\Models\Distributor::factory()->create(['user_id' => $withDistributor->user_id]);

        CompanyProfile::factory()->create(['company_name' => 'No Distributor Co']);

        Livewire::actingAs($admin)
            ->test(CompaniesPage::class)
            ->set('hasDistributor', true)
            ->call('applyFilters')
            ->assertSee('Distributor Linked Co')
            ->assertDontSee('No Distributor Co');
    }

    public function test_bulk_select_toggle_selects_page_ids(): void
    {
        $admin = $this->admin();
        $profiles = CompanyProfile::factory()->count(3)->create();

        $component = Livewire::actingAs($admin)->test(CompaniesPage::class);

        $component->call('toggleSelectAll')
            ->assertCount('selectedCompanyIds', 3);

        $component->call('toggleSelectAll')
            ->assertCount('selectedCompanyIds', 0);

        $this->assertNotEmpty($profiles);
    }

    public function test_active_filter_count_includes_distributor_toggle(): void
    {
        $admin = $this->admin();

        $component = Livewire::actingAs($admin)->test(CompaniesPage::class);

        $this->assertSame(0, $component->instance()->activeFilterCount());

        $component->set('hasDistributor', true);

        $this->assertSame(1, $component->instance()->activeFilterCount());
    }
}

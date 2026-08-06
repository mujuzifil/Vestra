<?php

namespace Tests\Feature\Admin;

use App\Enums\DistributorAccountStatus;
use App\Filament\Pages\Distributors\ActivePartnersPage;
use App\Models\CreditAccount;
use App\Models\Distributor;
use App\Models\DistributorDocument;
use App\Models\DistributorServiceArea;
use App\Models\SalesRepresentative;
use App\Models\User;
use App\Services\Admin\PartnerAdminService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\TestCase;

class ActivePartnersPageTest extends TestCase
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
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
    }

    private function customer(): User
    {
        return User::factory()->create([
            'is_admin' => false,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
    }

    public function test_active_partners_route_is_registered(): void
    {
        $this->assertTrue(Route::has('filament.admin.pages.distributors.active-partners'));
        $this->assertTrue(Route::has('filament.admin.distributors.active-partners.export'));
        $this->assertStringContainsString('/distributors/active-partners', ActivePartnersPage::getUrl());
    }

    public function test_distributor_resource_is_hidden_from_navigation(): void
    {
        $this->assertFalse(\App\Filament\Resources\DistributorResource::shouldRegisterNavigation());
        $this->assertSame([], \App\Filament\Resources\DistributorResource::getNavigationItems());
    }

    public function test_distributors_index_redirects_to_active_partners_workspace(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get('/distributors')
            ->assertRedirect(ActivePartnersPage::getUrl());
    }

    public function test_guest_is_redirected_from_active_partners_route(): void
    {
        $response = $this->get('/distributors/active-partners');

        $response->assertRedirect();
    }

    public function test_suspend_and_activate_partner_actions_update_status_and_public_visibility(): void
    {
        $admin = $this->admin();

        $distributor = Distributor::factory()->create([
            'company_name' => 'Actionable Partner Ltd',
            'status' => DistributorAccountStatus::ACTIVE,
            'email' => 'actionable-partner@example.com',
        ]);

        Livewire::actingAs($admin)
            ->test(ActivePartnersPage::class)
            ->call('openDetailDrawer', $distributor->id)
            ->assertSet('showDetailDrawer', true)
            ->call('suspendPartner', $distributor->id)
            ->assertHasNoErrors();

        $distributor->refresh();
        $this->assertSame(DistributorAccountStatus::SUSPENDED, $distributor->status);

        $payload = $this->getJson('/api/v1/public/distributors')->assertSuccessful()->json('data');
        $this->assertFalse(collect($payload)->contains(fn ($row) => ($row['company_name'] ?? null) === 'Actionable Partner Ltd'));

        Livewire::actingAs($admin)
            ->test(ActivePartnersPage::class)
            ->call('activatePartner', $distributor->id)
            ->assertHasNoErrors();

        $distributor->refresh();
        $this->assertSame(DistributorAccountStatus::ACTIVE, $distributor->status);
        $this->getJson('/api/v1/public/distributors')
            ->assertSuccessful()
            ->assertJsonFragment(['company_name' => 'Actionable Partner Ltd']);
    }

    public function test_non_admin_is_denied_access_to_active_partners_page(): void
    {
        $customer = $this->customer();

        Livewire::actingAs($customer)
            ->test(ActivePartnersPage::class)
            ->assertForbidden();
    }

    public function test_admin_can_view_active_partners_page_and_kpis(): void
    {
        $admin = $this->admin();

        Distributor::factory()->count(3)->create();

        Livewire::actingAs($admin)
            ->test(ActivePartnersPage::class)
            ->assertSuccessful()
            ->assertSee('Active Partners')
            ->assertSee('Total Partners')
            ->assertSee('Active Partners')
            ->assertSee('Suspended Partners')
            ->assertSee('Credit Outstanding');
    }

    public function test_empty_state_renders_when_no_partners_exist(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(ActivePartnersPage::class)
            ->assertSuccessful()
            ->assertSee('No active partners yet');
    }

    public function test_search_filters_by_company_name(): void
    {
        $admin = $this->admin();

        Distributor::factory()->create(['company_name' => 'Bright Era Sdn Bhd']);
        Distributor::factory()->create(['company_name' => 'Other Solutions']);

        Livewire::actingAs($admin)
            ->test(ActivePartnersPage::class)
            ->set('search', 'Bright Era')
            ->assertSee('Bright Era Sdn Bhd')
            ->assertDontSee('Other Solutions');
    }

    public function test_status_filter_works(): void
    {
        $admin = $this->admin();

        Distributor::factory()->create([
            'status' => DistributorAccountStatus::ACTIVE->value,
            'company_name' => 'Active Partner Co',
        ]);
        Distributor::factory()->suspended()->create([
            'company_name' => 'Suspended Partner Co',
        ]);

        Livewire::actingAs($admin)
            ->test(ActivePartnersPage::class)
            ->set('statusFilter', [DistributorAccountStatus::ACTIVE->value])
            ->assertSee('Active Partner Co')
            ->assertDontSee('Suspended Partner Co');
    }

    public function test_country_filter_works(): void
    {
        $admin = $this->admin();

        Distributor::factory()->create(['country' => 'Uganda', 'company_name' => 'Uganda Partner']);
        Distributor::factory()->create(['country' => 'Kenya', 'company_name' => 'Kenya Partner']);

        Livewire::actingAs($admin)
            ->test(ActivePartnersPage::class)
            ->set('countryFilter', ['Uganda'])
            ->assertSee('Uganda Partner')
            ->assertDontSee('Kenya Partner');
    }

    public function test_region_filter_works(): void
    {
        $admin = $this->admin();

        $withRegion = Distributor::factory()->create(['company_name' => 'Central Region Partner']);
        DistributorServiceArea::factory()->create([
            'distributor_id' => $withRegion->id,
            'region' => 'Central Region',
        ]);

        Distributor::factory()->create(['company_name' => 'No Region Partner']);

        Livewire::actingAs($admin)
            ->test(ActivePartnersPage::class)
            ->set('regionFilter', ['Central Region'])
            ->assertSee('Central Region Partner')
            ->assertDontSee('No Region Partner');
    }

    public function test_sales_rep_filter_works(): void
    {
        $admin = $this->admin();
        $rep = SalesRepresentative::factory()->create(['name' => 'Jane Rep']);

        Distributor::factory()->create([
            'sales_representative_id' => $rep->id,
            'company_name' => 'Assigned Partner',
        ]);
        Distributor::factory()->create([
            'sales_representative_id' => null,
            'company_name' => 'Unassigned Partner',
        ]);

        Livewire::actingAs($admin)
            ->test(ActivePartnersPage::class)
            ->set('salesRepFilter', $rep->id)
            ->assertSee('Assigned Partner')
            ->assertDontSee('Unassigned Partner');
    }

    public function test_sorting_by_company_name_works(): void
    {
        Distributor::factory()->create(['company_name' => 'Zebra Distributors']);
        Distributor::factory()->create(['company_name' => 'Alpha Distributors']);

        $partners = app(PartnerAdminService::class)->paginatePartners([], 'company_name', 'asc', 10);

        $this->assertEquals('Alpha Distributors', $partners->first()->company_name);
    }

    public function test_detail_drawer_shows_live_relationships(): void
    {
        $admin = $this->admin();
        $rep = SalesRepresentative::factory()->create(['name' => 'Account Manager Rep']);

        $distributor = Distributor::factory()->create([
            'company_name' => 'Drawer Partner Co',
            'sales_representative_id' => $rep->id,
        ]);

        CreditAccount::factory()->create([
            'distributor_id' => $distributor->id,
            'limit' => 500000,
            'balance' => 150000,
        ]);

        DistributorDocument::factory()->create([
            'distributor_id' => $distributor->id,
            'title' => 'Trading License Document',
        ]);

        Livewire::actingAs($admin)
            ->test(ActivePartnersPage::class)
            ->call('openDetailDrawer', $distributor->id)
            ->assertSet('showDetailDrawer', true)
            ->assertSet('selectedPartnerId', $distributor->id)
            ->assertSee('Drawer Partner Co')
            ->assertSee('Account Manager Rep')
            ->assertSee('Trading License Document');
    }

    public function test_export_returns_filtered_rows(): void
    {
        Distributor::factory()->create([
            'status' => DistributorAccountStatus::ACTIVE->value,
            'company_name' => 'Active Export Co',
        ]);
        Distributor::factory()->suspended()->create([
            'company_name' => 'Suspended Export Co',
        ]);

        $rows = app(PartnerAdminService::class)->exportPartners([
            'status' => [DistributorAccountStatus::ACTIVE->value],
        ]);

        $this->assertCount(1, $rows);
        $this->assertEquals('Active Export Co', $rows[0]['company_name']);
    }

    public function test_export_action_does_not_error_for_admin(): void
    {
        $admin = $this->admin();

        Distributor::factory()->create();

        Livewire::actingAs($admin)
            ->test(ActivePartnersPage::class)
            ->call('export', 'csv')
            ->assertHasNoErrors();
    }

    public function test_kpi_cards_use_live_counts(): void
    {
        Distributor::factory()->count(2)->create(['status' => DistributorAccountStatus::ACTIVE->value]);
        Distributor::factory()->suspended()->create();

        $cards = app(PartnerAdminService::class)->getKpiCards();
        $byLabel = collect($cards)->keyBy('label');

        $this->assertEquals('3', $byLabel['Total Partners']['value']);
        $this->assertEquals('2', $byLabel['Active Partners']['value']);
        $this->assertEquals('1', $byLabel['Suspended Partners']['value']);
    }

    public function test_kpi_cards_do_not_include_fake_top_territory(): void
    {
        Distributor::factory()->count(2)->create();

        $cards = app(PartnerAdminService::class)->getKpiCards();
        $labels = collect($cards)->pluck('label')->all();

        $this->assertNotContains('Top Performing Territory', $labels);
    }

    public function test_pagination_resets_on_filter_change(): void
    {
        $admin = $this->admin();

        Distributor::factory()->count(25)->create();

        Livewire::actingAs($admin)
            ->test(ActivePartnersPage::class)
            ->call('gotoPage', 2)
            ->assertSet('paginators.page', 2)
            ->set('search', 'Distributor')
            ->assertSet('paginators.page', 1);
    }

    public function test_reset_filters_clears_all_filter_state(): void
    {
        $admin = $this->admin();

        $component = Livewire::actingAs($admin)->test(ActivePartnersPage::class)
            ->set('search', 'foo')
            ->set('statusFilter', [DistributorAccountStatus::ACTIVE->value])
            ->set('countryFilter', ['Uganda'])
            ->set('salesRepFilter', 1);

        $this->assertTrue($component->instance()->hasActiveFilters());

        $component->call('resetFilters');

        $this->assertFalse($component->instance()->hasActiveFilters());
    }
}

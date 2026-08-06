<?php

namespace Tests\Feature\Admin;

use App\Filament\Pages\Distributors\TerritoriesPage;
use App\Models\Distributor;
use App\Models\DistributorBranch;
use App\Models\DistributorServiceArea;
use App\Models\User;
use App\Services\Admin\TerritoryAdminService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\TestCase;

class TerritoriesPageTest extends TestCase
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

    public function test_territories_route_is_registered(): void
    {
        $this->assertTrue(Route::has('filament.admin.pages.distributors.territories'));
        $this->assertTrue(Route::has('filament.admin.distributors.territories.export'));
        $this->assertStringContainsString('/distributors/territories', TerritoriesPage::getUrl());
    }

    public function test_distributor_branch_resource_is_hidden_from_navigation(): void
    {
        $this->assertFalse(\App\Filament\Resources\DistributorBranchResource::shouldRegisterNavigation());
        $this->assertSame([], \App\Filament\Resources\DistributorBranchResource::getNavigationItems());
    }

    public function test_branches_index_redirects_to_territories_workspace(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get('/distributor-branches')
            ->assertRedirect(TerritoriesPage::getUrl());
    }

    public function test_guest_is_redirected_from_territories_route(): void
    {
        $response = $this->get('/distributors/territories');

        $response->assertRedirect();
    }

    public function test_non_admin_is_denied_access_to_territories_page(): void
    {
        $customer = $this->customer();

        Livewire::actingAs($customer)
            ->test(TerritoriesPage::class)
            ->assertForbidden();
    }

    public function test_admin_can_view_territories_page_and_kpis(): void
    {
        $admin = $this->admin();

        DistributorBranch::factory()->count(3)->create();

        Livewire::actingAs($admin)
            ->test(TerritoriesPage::class)
            ->assertSuccessful()
            ->assertSee('Territories')
            ->assertSee('Total Branches')
            ->assertSee('Active')
            ->assertSee('Inactive')
            ->assertSee('Distinct Distributors')
            ->assertSee('Distinct Countries');
    }

    public function test_kpis_omit_fake_coverage_and_sales_metrics(): void
    {
        $admin = $this->admin();

        DistributorBranch::factory()->count(2)->create();

        Livewire::actingAs($admin)
            ->test(TerritoriesPage::class)
            ->assertDontSee('Coverage %')
            ->assertDontSee('Total Sales')
            ->assertDontSee('Open Opportunities');
    }

    public function test_empty_state_renders_when_no_branches_exist(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(TerritoriesPage::class)
            ->assertSuccessful()
            ->assertSee('No distributor coverage configured');
    }

    public function test_search_filters_by_branch_name(): void
    {
        $admin = $this->admin();

        DistributorBranch::factory()->create(['name' => 'Kampala Central Branch']);
        DistributorBranch::factory()->create(['name' => 'Nairobi Branch']);

        Livewire::actingAs($admin)
            ->test(TerritoriesPage::class)
            ->set('search', 'Kampala Central')
            ->assertSee('Kampala Central Branch')
            ->assertDontSee('Nairobi Branch');
    }

    public function test_country_filter_works(): void
    {
        $admin = $this->admin();

        DistributorBranch::factory()->create(['name' => 'Uganda Branch', 'country' => 'Uganda']);
        DistributorBranch::factory()->create(['name' => 'Kenya Branch', 'country' => 'Kenya']);

        Livewire::actingAs($admin)
            ->test(TerritoriesPage::class)
            ->set('countryFilter', ['Kenya'])
            ->assertSee('Kenya Branch')
            ->assertDontSee('Uganda Branch');
    }

    public function test_status_filter_works(): void
    {
        $admin = $this->admin();

        DistributorBranch::factory()->create(['name' => 'Active Branch', 'status' => 'active']);
        DistributorBranch::factory()->create(['name' => 'Inactive Branch', 'status' => 'inactive']);

        Livewire::actingAs($admin)
            ->test(TerritoriesPage::class)
            ->set('statusFilter', ['active'])
            ->assertSee('Active Branch')
            ->assertDontSee('Inactive Branch');
    }

    public function test_distributor_filter_works(): void
    {
        $admin = $this->admin();
        $distributor = Distributor::factory()->create(['company_name' => 'Vestra Partners Ltd']);

        DistributorBranch::factory()->create(['distributor_id' => $distributor->id, 'name' => 'Linked Branch']);
        DistributorBranch::factory()->create(['name' => 'Other Branch']);

        Livewire::actingAs($admin)
            ->test(TerritoriesPage::class)
            ->set('distributorFilter', $distributor->id)
            ->assertSee('Linked Branch')
            ->assertDontSee('Other Branch');
    }

    public function test_sorting_by_name_works(): void
    {
        $admin = $this->admin();

        DistributorBranch::factory()->create(['name' => 'Zebra Branch']);
        DistributorBranch::factory()->create(['name' => 'Alpha Branch']);

        Livewire::actingAs($admin)->test(TerritoriesPage::class);

        $branches = app(TerritoryAdminService::class)->paginateBranches([], 'name', 'asc', 10);

        $this->assertEquals('Alpha Branch', $branches->first()->name);
    }

    public function test_detail_drawer_returns_branch_distributor_and_service_areas(): void
    {
        $admin = $this->admin();
        $distributor = Distributor::factory()->create(['company_name' => 'Northline Distribution']);
        $branch = DistributorBranch::factory()->create([
            'distributor_id' => $distributor->id,
            'name' => 'Northline Main Branch',
        ]);

        DistributorServiceArea::factory()->count(2)->create([
            'distributor_id' => $distributor->id,
            'branch_id' => $branch->id,
        ]);

        Livewire::actingAs($admin)
            ->test(TerritoriesPage::class)
            ->call('openDetailDrawer', $branch->id)
            ->assertSet('showDetailDrawer', true)
            ->assertSet('selectedBranchId', $branch->id)
            ->assertSee('Northline Main Branch')
            ->assertSee('Northline Distribution')
            ->assertSee('Parent Distributor')
            ->assertSee('Service Areas');
    }

    public function test_map_view_shows_only_branches_with_both_coordinates(): void
    {
        $admin = $this->admin();

        DistributorBranch::factory()->create([
            'name' => 'Geocoded Branch',
            'latitude' => 0.3476,
            'longitude' => 32.5825,
        ]);

        DistributorBranch::factory()->create([
            'name' => 'Latitude Only Branch',
            'latitude' => 1.2345,
            'longitude' => null,
        ]);

        DistributorBranch::factory()->create([
            'name' => 'No Coordinates Branch',
            'latitude' => null,
            'longitude' => null,
        ]);

        $mappable = app(TerritoryAdminService::class)->getMappableBranches();

        $this->assertCount(1, $mappable);
        $this->assertEquals('Geocoded Branch', $mappable->first()['name']);

        Livewire::actingAs($admin)
            ->test(TerritoriesPage::class)
            ->set('viewMode', 'map')
            ->assertSee('Geocoded Branch')
            ->assertDontSee('Latitude Only Branch')
            ->assertDontSee('No Coordinates Branch');
    }

    public function test_map_view_renders_elegant_empty_state_when_no_branches_are_geocoded(): void
    {
        $admin = $this->admin();

        DistributorBranch::factory()->create([
            'name' => 'Ungeocoded Branch',
            'latitude' => null,
            'longitude' => null,
        ]);

        Livewire::actingAs($admin)
            ->test(TerritoriesPage::class)
            ->set('viewMode', 'map')
            ->assertSuccessful()
            ->assertSee('No geocoded branches yet')
            ->assertSee('we never display estimated or placeholder pins', false)
            ->assertDontSee('Ungeocoded Branch');
    }

    public function test_map_view_empty_state_renders_when_there_are_no_branches_at_all(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(TerritoriesPage::class)
            ->set('viewMode', 'map')
            ->assertSuccessful()
            ->assertSee('No geocoded branches yet');
    }

    public function test_export_returns_filtered_rows(): void
    {
        DistributorBranch::factory()->create([
            'name' => 'Active Branch',
            'status' => 'active',
        ]);

        DistributorBranch::factory()->create([
            'name' => 'Inactive Branch',
            'status' => 'inactive',
        ]);

        $rows = app(TerritoryAdminService::class)->exportBranches([
            'status' => ['active'],
        ]);

        $this->assertCount(1, $rows);
        $this->assertEquals('Active Branch', $rows[0]['name']);
    }

    public function test_export_action_does_not_error_for_admin(): void
    {
        $admin = $this->admin();

        DistributorBranch::factory()->create();

        Livewire::actingAs($admin)
            ->test(TerritoriesPage::class)
            ->call('export', 'csv')
            ->assertHasNoErrors();
    }

    public function test_pagination_resets_on_filter_change(): void
    {
        $admin = $this->admin();

        DistributorBranch::factory()->count(25)->create();

        Livewire::actingAs($admin)
            ->test(TerritoriesPage::class)
            ->call('gotoPage', 2)
            ->assertSet('paginators.page', 2)
            ->set('search', 'branch')
            ->assertSet('paginators.page', 1);
    }

    public function test_active_filter_count_reflects_selected_filters(): void
    {
        $admin = $this->admin();

        $component = Livewire::actingAs($admin)->test(TerritoriesPage::class);

        $this->assertSame(0, $component->instance()->activeFilterCount());

        $component->set('countryFilter', ['Uganda']);

        $this->assertSame(1, $component->instance()->activeFilterCount());
    }

    public function test_view_mode_toggles_between_table_and_map(): void
    {
        $admin = $this->admin();

        $component = Livewire::actingAs($admin)->test(TerritoriesPage::class);

        $component->assertSet('viewMode', 'table');

        $component->call('setViewMode', 'map')->assertSet('viewMode', 'map');
        $component->call('setViewMode', 'invalid')->assertSet('viewMode', 'table');
    }

    public function test_kpi_cards_report_correct_counts(): void
    {
        $distributorOne = Distributor::factory()->create();
        $distributorTwo = Distributor::factory()->create();

        DistributorBranch::factory()->create(['distributor_id' => $distributorOne->id, 'status' => 'active', 'country' => 'Uganda']);
        DistributorBranch::factory()->create(['distributor_id' => $distributorOne->id, 'status' => 'inactive', 'country' => 'Uganda']);
        DistributorBranch::factory()->create(['distributor_id' => $distributorTwo->id, 'status' => 'active', 'country' => 'Kenya']);

        $cards = app(TerritoryAdminService::class)->getKpiCards();
        $indexed = collect($cards)->keyBy('label');

        $this->assertEquals('3', $indexed['Total Branches']['value']);
        $this->assertEquals('2', $indexed['Active']['value']);
        $this->assertEquals('1', $indexed['Inactive']['value']);
        $this->assertEquals('2', $indexed['Distinct Distributors']['value']);
        $this->assertEquals('2', $indexed['Distinct Countries']['value']);
    }
}

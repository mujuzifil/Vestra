<?php

namespace Tests\Feature\Admin;

use App\Enums\DistributorStatus;
use App\Filament\Pages\Distributors\ApplicationsPage;
use App\Filament\Resources\DistributorRequestResource;
use App\Models\Distributor;
use App\Models\DistributorRequest;
use App\Models\User;
use App\Services\Admin\ApplicationAdminService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\TestCase;

class ApplicationsPageTest extends TestCase
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

    public function test_applications_route_is_registered(): void
    {
        $this->assertTrue(Route::has('filament.admin.pages.distributors.applications'));
        $this->assertTrue(Route::has('filament.admin.distributors.applications.export'));
        $this->assertStringContainsString('/distributors/applications', ApplicationsPage::getUrl());
    }

    public function test_legacy_resource_is_hidden_from_navigation(): void
    {
        $this->assertFalse(DistributorRequestResource::shouldRegisterNavigation());
        $this->assertSame([], DistributorRequestResource::getNavigationItems());
    }

    public function test_legacy_list_page_redirects_to_applications_workspace(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get('/distributor-requests')
            ->assertRedirect(ApplicationsPage::getUrl());
    }

    public function test_guest_is_redirected_from_applications_route(): void
    {
        $response = $this->get('/distributors/applications');

        $response->assertRedirect();
    }

    public function test_non_admin_is_denied_access_to_applications_page(): void
    {
        $customer = $this->customer();

        Livewire::actingAs($customer)
            ->test(ApplicationsPage::class)
            ->assertForbidden();
    }

    public function test_admin_can_view_applications_page_and_kpis(): void
    {
        $admin = $this->admin();

        DistributorRequest::factory()->count(3)->create();

        Livewire::actingAs($admin)
            ->test(ApplicationsPage::class)
            ->assertSuccessful()
            ->assertSee('Applications')
            ->assertSee('Total')
            ->assertSee('Pending')
            ->assertSee('Under Review')
            ->assertSee('Information Requested')
            ->assertSee('Approved')
            ->assertSee('Rejected');
    }

    public function test_empty_state_renders_when_no_applications_exist(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(ApplicationsPage::class)
            ->assertSuccessful()
            ->assertSee('No distributor applications yet');
    }

    public function test_search_filters_by_company_and_email(): void
    {
        $admin = $this->admin();

        DistributorRequest::factory()->create([
            'company_name' => 'Alpha Distribution',
            'email' => 'alpha@example.com',
        ]);
        DistributorRequest::factory()->create([
            'company_name' => 'Beta Traders',
            'email' => 'beta@example.com',
        ]);

        Livewire::actingAs($admin)
            ->test(ApplicationsPage::class)
            ->set('search', 'alpha@example.com')
            ->assertSee('Alpha Distribution')
            ->assertDontSee('Beta Traders');
    }

    public function test_status_filter_works(): void
    {
        $admin = $this->admin();

        DistributorRequest::factory()->create([
            'status' => DistributorStatus::PENDING,
            'company_name' => 'Pending Co',
        ]);
        DistributorRequest::factory()->create([
            'status' => DistributorStatus::APPROVED,
            'company_name' => 'Approved Co',
        ]);

        Livewire::actingAs($admin)
            ->test(ApplicationsPage::class)
            ->set('statusFilter', [DistributorStatus::PENDING->value])
            ->assertSee('Pending Co')
            ->assertDontSee('Approved Co');
    }

    public function test_country_filter_works(): void
    {
        $admin = $this->admin();

        DistributorRequest::factory()->create([
            'country' => 'Uganda',
            'company_name' => 'Uganda Co',
        ]);
        DistributorRequest::factory()->create([
            'country' => 'Kenya',
            'company_name' => 'Kenya Co',
        ]);

        Livewire::actingAs($admin)
            ->test(ApplicationsPage::class)
            ->set('countryFilter', ['Uganda'])
            ->assertSee('Uganda Co')
            ->assertDontSee('Kenya Co');
    }

    public function test_kpi_cards_use_live_counts(): void
    {
        DistributorRequest::factory()->count(2)->create(['status' => DistributorStatus::PENDING]);
        DistributorRequest::factory()->create(['status' => DistributorStatus::APPROVED]);
        DistributorRequest::factory()->create(['status' => DistributorStatus::REJECTED]);

        $cards = app(ApplicationAdminService::class)->getKpiCards();
        $byLabel = collect($cards)->keyBy('label');

        $this->assertEquals('4', $byLabel['Total']['value']);
        $this->assertEquals('2', $byLabel['Pending']['value']);
        $this->assertEquals('1', $byLabel['Approved']['value']);
        $this->assertEquals('1', $byLabel['Rejected']['value']);
        $this->assertEquals('0', $byLabel['Under Review']['value']);
        $this->assertEquals('0', $byLabel['Information Requested']['value']);
    }

    public function test_detail_drawer_shows_live_application_data(): void
    {
        $admin = $this->admin();

        $application = DistributorRequest::factory()->create([
            'company_name' => 'Drawer Distribution Co',
            'business_description' => 'We distribute cleaning supplies across the region.',
        ]);

        Livewire::actingAs($admin)
            ->test(ApplicationsPage::class)
            ->call('openDetailDrawer', $application->id)
            ->assertSet('showDetailDrawer', true)
            ->assertSet('selectedApplicationId', $application->id)
            ->assertSee('Drawer Distribution Co')
            ->assertSee('We distribute cleaning supplies across the region.');
    }

    public function test_admin_can_approve_application_and_creates_distributor(): void
    {
        $admin = $this->admin();
        $application = DistributorRequest::factory()->create([
            'status' => DistributorStatus::PENDING,
            'email' => 'newdistributor@example.com',
        ]);

        Livewire::actingAs($admin)
            ->test(ApplicationsPage::class)
            ->call('approve', $application->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('distributor_requests', [
            'id' => $application->id,
            'status' => DistributorStatus::APPROVED->value,
        ]);

        $this->assertDatabaseHas('distributors', [
            'distributor_request_id' => $application->id,
            'company_name' => $application->company_name,
        ]);
    }

    public function test_admin_can_reject_application(): void
    {
        $admin = $this->admin();
        $application = DistributorRequest::factory()->create([
            'status' => DistributorStatus::PENDING,
        ]);

        Livewire::actingAs($admin)
            ->test(ApplicationsPage::class)
            ->call('reject', $application->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('distributor_requests', [
            'id' => $application->id,
            'status' => DistributorStatus::REJECTED->value,
        ]);
    }

    public function test_bulk_approve_creates_distributors_for_selected_applications(): void
    {
        $admin = $this->admin();
        $one = DistributorRequest::factory()->create(['status' => DistributorStatus::PENDING]);
        $two = DistributorRequest::factory()->create(['status' => DistributorStatus::PENDING]);

        Livewire::actingAs($admin)
            ->test(ApplicationsPage::class)
            ->set('selectedIds', [$one->id, $two->id])
            ->call('bulkApprove')
            ->assertHasNoErrors();

        $this->assertEquals(2, Distributor::query()->whereIn('distributor_request_id', [$one->id, $two->id])->count());
    }

    public function test_export_returns_filtered_rows(): void
    {
        DistributorRequest::factory()->create([
            'status' => DistributorStatus::APPROVED,
            'company_name' => 'Approved Co',
        ]);
        DistributorRequest::factory()->create([
            'status' => DistributorStatus::PENDING,
            'company_name' => 'Pending Co',
        ]);

        $rows = app(ApplicationAdminService::class)->exportRows([
            'status' => [DistributorStatus::APPROVED->value],
        ]);

        $this->assertCount(1, $rows);
        $this->assertEquals('Approved Co', $rows[0]['company_name']);
    }

    public function test_export_route_requires_admin(): void
    {
        $customer = $this->customer();

        $this->actingAs($customer)
            ->get(route('filament.admin.distributors.applications.export', ['format' => 'csv']))
            ->assertForbidden();
    }

    public function test_export_route_downloads_csv_for_admin(): void
    {
        $admin = $this->admin();
        DistributorRequest::factory()->create();

        $response = $this->actingAs($admin)
            ->get(route('filament.admin.distributors.applications.export', ['format' => 'csv']));

        $response->assertSuccessful();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_pagination_resets_on_filter_change(): void
    {
        $admin = $this->admin();

        DistributorRequest::factory()->count(25)->create();

        Livewire::actingAs($admin)
            ->test(ApplicationsPage::class)
            ->call('gotoPage', 2)
            ->assertSet('paginators.page', 2)
            ->set('search', 'zzz-no-match')
            ->assertSet('paginators.page', 1);
    }
}

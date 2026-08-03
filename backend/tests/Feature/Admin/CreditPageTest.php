<?php

namespace Tests\Feature\Admin;

use App\Enums\CreditTransactionType;
use App\Filament\Pages\Distributors\CreditPage;
use App\Filament\Resources\CreditAccountResource;
use App\Models\CreditAccount;
use App\Models\CreditTransaction;
use App\Models\Distributor;
use App\Models\User;
use App\Services\Admin\CreditAdminService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\TestCase;

class CreditPageTest extends TestCase
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

    public function test_credit_route_is_registered(): void
    {
        $this->assertTrue(Route::has('filament.admin.pages.distributors.credit'));
        $this->assertTrue(Route::has('filament.admin.distributors.credit.export'));
        $this->assertStringContainsString('/distributors/credit', CreditPage::getUrl());
    }

    public function test_guest_is_redirected_from_credit_route(): void
    {
        $response = $this->get('/distributors/credit');

        $response->assertRedirect();
    }

    public function test_non_admin_is_denied_access_to_credit_page(): void
    {
        $customer = $this->customer();

        Livewire::actingAs($customer)
            ->test(CreditPage::class)
            ->assertForbidden();
    }

    public function test_admin_can_view_credit_page_and_kpis(): void
    {
        $admin = $this->admin();

        CreditAccount::factory()->count(3)->create();

        Livewire::actingAs($admin)
            ->test(CreditPage::class)
            ->assertSuccessful()
            ->assertSee('Credit')
            ->assertSee('Total Accounts')
            ->assertSee('Total Credit Limit')
            ->assertSee('Outstanding Balance')
            ->assertSee('Available Credit')
            ->assertSee('Avg. Utilization');
    }

    public function test_kpis_reflect_real_account_sums(): void
    {
        $admin = $this->admin();

        CreditAccount::factory()->create(['limit' => 1000000, 'balance' => 200000, 'authorized_amount' => 0]);
        CreditAccount::factory()->create(['limit' => 500000, 'balance' => 0, 'authorized_amount' => 0]);

        $cards = app(CreditAdminService::class)->getKpiCards();
        $cardsByLabel = collect($cards)->keyBy('label');

        $this->assertSame('2', $cardsByLabel['Total Accounts']['value']);
        $this->assertStringContainsString('1.5M', $cardsByLabel['Total Credit Limit']['value']);
        $this->assertStringContainsString('200.0K', $cardsByLabel['Outstanding Balance']['value']);

        Livewire::actingAs($admin)->test(CreditPage::class)->assertSuccessful();
    }

    public function test_empty_state_renders_when_no_credit_accounts_exist(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(CreditPage::class)
            ->assertSuccessful()
            ->assertSee('No credit accounts yet');
    }

    public function test_search_filters_by_distributor_name(): void
    {
        $admin = $this->admin();

        $alpha = Distributor::factory()->create(['company_name' => 'Alpha Traders']);
        $beta = Distributor::factory()->create(['company_name' => 'Beta Supplies']);

        CreditAccount::factory()->create(['distributor_id' => $alpha->id]);
        CreditAccount::factory()->create(['distributor_id' => $beta->id]);

        Livewire::actingAs($admin)
            ->test(CreditPage::class)
            ->set('search', 'Alpha')
            ->assertSee('Alpha Traders')
            ->assertDontSee('Beta Supplies');
    }

    public function test_status_filter_works(): void
    {
        $admin = $this->admin();

        $activeDistributor = Distributor::factory()->create(['company_name' => 'Active Distributor Co']);
        $suspendedDistributor = Distributor::factory()->create(['company_name' => 'Suspended Distributor Co']);

        CreditAccount::factory()->create(['distributor_id' => $activeDistributor->id, 'status' => 'active']);
        CreditAccount::factory()->create(['distributor_id' => $suspendedDistributor->id, 'status' => 'suspended']);

        Livewire::actingAs($admin)
            ->test(CreditPage::class)
            ->set('statusFilter', ['active'])
            ->assertSee('Active Distributor Co')
            ->assertDontSee('Suspended Distributor Co');
    }

    public function test_country_filter_works(): void
    {
        $admin = $this->admin();

        $ugandan = Distributor::factory()->create(['company_name' => 'Uganda Co', 'country' => 'Uganda']);
        $kenyan = Distributor::factory()->create(['company_name' => 'Kenya Co', 'country' => 'Kenya']);

        CreditAccount::factory()->create(['distributor_id' => $ugandan->id]);
        CreditAccount::factory()->create(['distributor_id' => $kenyan->id]);

        Livewire::actingAs($admin)
            ->test(CreditPage::class)
            ->set('countryFilter', ['Kenya'])
            ->assertSee('Kenya Co')
            ->assertDontSee('Uganda Co');
    }

    public function test_utilization_percentage_is_displayed(): void
    {
        $admin = $this->admin();

        $distributor = Distributor::factory()->create(['company_name' => 'Utilization Test Co']);
        $account = CreditAccount::factory()->create([
            'distributor_id' => $distributor->id,
            'limit' => 1000,
            'balance' => 250,
            'authorized_amount' => 0,
        ]);

        $this->assertSame(25.0, $account->utilizationPercentage());

        Livewire::actingAs($admin)
            ->test(CreditPage::class)
            ->assertSee('25.0%');
    }

    public function test_admin_can_adjust_credit_limit(): void
    {
        $admin = $this->admin();

        $distributor = Distributor::factory()->create();
        $account = CreditAccount::factory()->create([
            'distributor_id' => $distributor->id,
            'limit' => 1000000,
        ]);

        Livewire::actingAs($admin)
            ->test(CreditPage::class)
            ->call('openAdjustDrawer', $account->id)
            ->assertSet('showAdjustDrawer', true)
            ->assertSet('adjustingAccountId', $account->id)
            ->set('form.new_limit', '2500000')
            ->set('form.reason', 'Distributor requested a higher limit after strong Q2 performance.')
            ->call('saveLimit')
            ->assertHasNoErrors()
            ->assertSet('showAdjustDrawer', false);

        $this->assertDatabaseHas('credit_accounts', [
            'id' => $account->id,
            'limit' => 2500000,
        ]);

        $this->assertDatabaseHas('credit_transactions', [
            'credit_account_id' => $account->id,
            'type' => CreditTransactionType::LIMIT_CHANGE->value,
        ]);
    }

    public function test_adjust_limit_requires_reason_and_numeric_limit(): void
    {
        $admin = $this->admin();
        $account = CreditAccount::factory()->create();

        Livewire::actingAs($admin)
            ->test(CreditPage::class)
            ->call('openAdjustDrawer', $account->id)
            ->set('form.new_limit', '')
            ->set('form.reason', '')
            ->call('saveLimit')
            ->assertHasErrors(['form.new_limit', 'form.reason']);
    }

    public function test_non_admin_cannot_adjust_credit_limit(): void
    {
        $customer = $this->customer();
        $account = CreditAccount::factory()->create();

        Livewire::actingAs($customer)
            ->test(CreditPage::class)
            ->assertForbidden();
    }

    public function test_detail_drawer_shows_transaction_timeline(): void
    {
        $admin = $this->admin();

        $distributor = Distributor::factory()->create(['company_name' => 'Timeline Test Co']);
        $account = CreditAccount::factory()->create(['distributor_id' => $distributor->id]);

        CreditTransaction::create([
            'credit_account_id' => $account->id,
            'type' => CreditTransactionType::ADJUSTMENT->value,
            'amount' => 50000,
            'balance_after' => 50000,
            'description' => 'Manual balance adjustment for testing.',
            'created_by' => $admin->id,
        ]);

        Livewire::actingAs($admin)
            ->test(CreditPage::class)
            ->call('openDetailDrawer', $account->id)
            ->assertSet('showDetailDrawer', true)
            ->assertSet('selectedAccountId', $account->id)
            ->assertSee('Transaction Timeline')
            ->assertSee('Manual balance adjustment for testing.');
    }

    public function test_sorting_by_limit_works(): void
    {
        CreditAccount::factory()->create(['limit' => 100000]);
        CreditAccount::factory()->create(['limit' => 900000]);

        $accounts = app(CreditAdminService::class)->paginateAccounts([], 'limit', 'asc', 10);

        $this->assertEquals(100000, (float) $accounts->first()->limit);
    }

    public function test_export_action_does_not_error_for_admin(): void
    {
        $admin = $this->admin();

        CreditAccount::factory()->create();

        Livewire::actingAs($admin)
            ->test(CreditPage::class)
            ->call('export', 'csv')
            ->assertHasNoErrors();
    }

    public function test_export_returns_filtered_rows(): void
    {
        $active = Distributor::factory()->create(['company_name' => 'Active Export Co']);
        $suspended = Distributor::factory()->create(['company_name' => 'Suspended Export Co']);

        CreditAccount::factory()->create(['distributor_id' => $active->id, 'status' => 'active']);
        CreditAccount::factory()->create(['distributor_id' => $suspended->id, 'status' => 'suspended']);

        $rows = app(CreditAdminService::class)->exportAccounts(['status' => ['active']]);

        $this->assertCount(1, $rows);
        $this->assertEquals('Active Export Co', $rows[0]['distributor']);
    }

    public function test_credit_account_resource_is_hidden_from_navigation(): void
    {
        $this->assertFalse(CreditAccountResource::shouldRegisterNavigation());
        $this->assertSame([], CreditAccountResource::getNavigationItems());
    }

    public function test_credit_accounts_index_redirects_to_credit_workspace(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get('/credit-accounts')
            ->assertRedirect(CreditPage::getUrl());
    }
}

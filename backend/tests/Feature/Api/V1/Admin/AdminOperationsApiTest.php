<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Models\CreditAccount;
use App\Models\Distributor;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminOperationsApiTest extends TestCase
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
        return $this->bootstrapAdmin();
    }

    public function test_admin_can_list_warehouses(): void
    {
        Sanctum::actingAs($this->admin(), ['*']);

        Warehouse::factory()->count(3)->create();

        $this->getJson('/api/v1/admin/warehouses')
            ->assertOk()
            ->assertJsonPath('data.data.0.code', fn (?string $code) => $code !== null);
    }

    public function test_admin_can_list_suppliers(): void
    {
        Sanctum::actingAs($this->admin(), ['*']);

        Supplier::factory()->count(2)->create();

        $this->getJson('/api/v1/admin/suppliers')
            ->assertOk()
            ->assertJsonCount(2, 'data.data');
    }

    public function test_admin_can_list_purchase_orders(): void
    {
        Sanctum::actingAs($this->admin(), ['*']);

        PurchaseOrder::factory()->count(2)->create();

        $this->getJson('/api/v1/admin/purchase-orders')
            ->assertOk()
            ->assertJsonCount(2, 'data.data');
    }

    public function test_admin_can_list_credit_accounts(): void
    {
        Sanctum::actingAs($this->admin(), ['*']);

        $distributor = Distributor::factory()->create();
        CreditAccount::factory()->create([
            'distributor_id' => $distributor->id,
            'limit' => 1000000,
            'balance' => 250000,
            'authorized_amount' => 100000,
        ]);

        $this->getJson('/api/v1/admin/credit-accounts')
            ->assertOk()
            ->assertJsonPath('data.data.0.credit_limit', '1000000.00');
    }

    public function test_admin_can_view_credit_account_summary(): void
    {
        Sanctum::actingAs($this->admin(), ['*']);

        $distributor = Distributor::factory()->create();
        CreditAccount::factory()->create([
            'distributor_id' => $distributor->id,
            'limit' => 1000000,
            'balance' => 250000,
            'authorized_amount' => 100000,
        ]);

        $this->getJson('/api/v1/admin/credit-accounts/summary')
            ->assertOk()
            ->assertJsonPath('data.total_credit_limit', 1000000)
            ->assertJsonPath('data.total_outstanding', 250000)
            ->assertJsonPath('data.total_available', 650000);
    }

    public function test_customer_cannot_access_admin_operations_endpoints(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);
        Sanctum::actingAs($customer, ['*']);

        $this->getJson('/api/v1/admin/warehouses')->assertForbidden();
        $this->getJson('/api/v1/admin/credit-accounts')->assertForbidden();
        $this->getJson('/api/v1/admin/purchase-orders')->assertForbidden();
    }
}

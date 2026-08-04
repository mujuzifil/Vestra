<?php

namespace Tests\Feature\Admin;

use App\Enums\StockMovementType;
use App\Filament\Pages\Products\InventoryPage;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductWarehouseStock;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\TestCase;

class InventoryPageTest extends TestCase
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

    private function makeStock(array $overrides = []): ProductWarehouseStock
    {
        $product = $overrides['product'] ?? Product::factory()->create([
            'name' => $overrides['product_name'] ?? 'Alpha Inventory Product',
            'sku' => $overrides['sku'] ?? 'SKU-ALPHA-001',
            'price' => $overrides['price'] ?? 100,
            'category_id' => $overrides['category_id'] ?? Category::factory()->create()->id,
        ]);

        $warehouse = $overrides['warehouse'] ?? Warehouse::factory()->create([
            'name' => $overrides['warehouse_name'] ?? 'Central Warehouse',
            'code' => $overrides['warehouse_code'] ?? 'WH-CEN',
        ]);

        return ProductWarehouseStock::query()->create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => $overrides['quantity'] ?? 50,
            'reserved_quantity' => $overrides['reserved_quantity'] ?? 5,
            'reorder_level' => $overrides['reorder_level'] ?? 10,
        ]);
    }

    public function test_inventory_route_is_registered(): void
    {
        $this->assertTrue(Route::has('filament.admin.pages.products.inventory'));
        $this->assertTrue(Route::has('filament.admin.products.inventory.export'));
        $this->assertStringContainsString('/products/inventory', InventoryPage::getUrl());
    }

    public function test_guest_is_redirected_from_inventory_route(): void
    {
        $this->get('/products/inventory')->assertRedirect();
    }

    public function test_non_admin_is_denied_access_to_inventory_page(): void
    {
        $customer = $this->customer();

        Livewire::actingAs($customer)
            ->test(InventoryPage::class)
            ->assertForbidden();
    }

    public function test_admin_can_view_inventory_page(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(InventoryPage::class)
            ->assertSuccessful()
            ->assertSee('Inventory');
    }

    public function test_kpi_cards_shown_to_admin(): void
    {
        $admin = $this->admin();
        $this->makeStock(['quantity' => 20, 'reserved_quantity' => 0, 'reorder_level' => 5, 'price' => 100]);

        Livewire::actingAs($admin)
            ->test(InventoryPage::class)
            ->assertSuccessful()
            ->assertSee('Inventory Value')
            ->assertSee('Total Units')
            ->assertSee('Low Stock')
            ->assertSee('Out of Stock')
            ->assertSee('Movements');
    }

    public function test_empty_state_renders_when_no_stock(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(InventoryPage::class)
            ->assertSuccessful()
            ->assertSee('No inventory records yet');
    }

    public function test_stock_lines_appear_in_table(): void
    {
        $admin = $this->admin();
        $this->makeStock(['product_name' => 'Unique Stock Widget XYZ']);

        Livewire::actingAs($admin)
            ->test(InventoryPage::class)
            ->assertSuccessful()
            ->assertSee('Unique Stock Widget XYZ');
    }

    public function test_search_filters_stock_by_product_name(): void
    {
        $admin = $this->admin();
        $this->makeStock(['product_name' => 'Alpha Widget', 'sku' => 'SKU-A1']);
        $this->makeStock(['product_name' => 'Beta Widget', 'sku' => 'SKU-B1', 'warehouse_code' => 'WH-B']);

        Livewire::actingAs($admin)
            ->test(InventoryPage::class)
            ->set('search', 'Alpha')
            ->assertSee('Alpha Widget')
            ->assertDontSee('Beta Widget');
    }

    public function test_warehouse_filter_works(): void
    {
        $admin = $this->admin();
        $warehouseA = Warehouse::factory()->create(['name' => 'Warehouse Alpha', 'code' => 'WA']);
        $warehouseB = Warehouse::factory()->create(['name' => 'Warehouse Beta', 'code' => 'WB']);

        $this->makeStock([
            'product_name' => 'Stock In Alpha',
            'warehouse' => $warehouseA,
            'sku' => 'SKU-WA',
        ]);
        $this->makeStock([
            'product_name' => 'Stock In Beta',
            'warehouse' => $warehouseB,
            'sku' => 'SKU-WB',
            'warehouse_code' => 'WB',
        ]);

        Livewire::actingAs($admin)
            ->test(InventoryPage::class)
            ->set('warehouseFilter', [(string) $warehouseA->id])
            ->assertSee('Stock In Alpha')
            ->assertDontSee('Stock In Beta');
    }

    public function test_stock_status_filter_out_of_stock(): void
    {
        $admin = $this->admin();
        $this->makeStock([
            'product_name' => 'Out Product',
            'quantity' => 0,
            'reserved_quantity' => 0,
            'reorder_level' => 5,
            'sku' => 'SKU-OUT',
        ]);
        $this->makeStock([
            'product_name' => 'In Product',
            'quantity' => 100,
            'reserved_quantity' => 0,
            'reorder_level' => 5,
            'sku' => 'SKU-IN',
            'warehouse_code' => 'WH-IN',
        ]);

        Livewire::actingAs($admin)
            ->test(InventoryPage::class)
            ->set('stockStatusFilter', ['out'])
            ->assertSee('Out Product')
            ->assertDontSee('In Product');
    }

    public function test_admin_can_open_and_close_detail_drawer(): void
    {
        $admin = $this->admin();
        $stock = $this->makeStock();

        Livewire::actingAs($admin)
            ->test(InventoryPage::class)
            ->call('openDetailDrawer', $stock->id)
            ->assertSet('showDetailDrawer', true)
            ->assertSet('selectedStockId', $stock->id)
            ->call('closeDetailDrawer')
            ->assertSet('showDetailDrawer', false)
            ->assertSet('selectedStockId', null);
    }

    public function test_admin_can_adjust_stock_via_inventory_service(): void
    {
        $admin = $this->admin();
        $stock = $this->makeStock([
            'quantity' => 40,
            'reserved_quantity' => 0,
            'reorder_level' => 5,
        ]);

        Livewire::actingAs($admin)
            ->test(InventoryPage::class)
            ->call('openDetailDrawer', $stock->id)
            ->set('adjustQuantity', '10')
            ->set('adjustReason', 'Cycle count correction')
            ->call('adjustStock');

        $this->assertDatabaseHas('product_warehouse_stock', [
            'id' => $stock->id,
            'quantity' => 50,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $stock->product_id,
            'warehouse_id' => $stock->warehouse_id,
            'type' => StockMovementType::ADJUSTMENT->value,
            'reason' => 'Cycle count correction',
            'user_id' => $admin->id,
        ]);
    }

    public function test_drawer_shows_recent_movements(): void
    {
        $admin = $this->admin();
        $stock = $this->makeStock();

        StockMovement::query()->create([
            'product_id' => $stock->product_id,
            'warehouse_id' => $stock->warehouse_id,
            'type' => StockMovementType::IN,
            'quantity' => 12,
            'balance_after' => 50,
            'reason' => 'Purchase receipt movement',
            'user_id' => $admin->id,
        ]);

        Livewire::actingAs($admin)
            ->test(InventoryPage::class)
            ->call('openDetailDrawer', $stock->id)
            ->assertSee('Purchase receipt movement')
            ->assertSee('Stock In');
    }

    public function test_sort_by_toggles_direction(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(InventoryPage::class)
            ->call('sortBy', 'quantity')
            ->assertSet('sortField', 'quantity')
            ->assertSet('sortDirection', 'asc')
            ->call('sortBy', 'quantity')
            ->assertSet('sortDirection', 'desc');
    }

    public function test_reset_filters_clears_all(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(InventoryPage::class)
            ->set('search', 'something')
            ->set('stockStatusFilter', ['low'])
            ->set('warehouseFilter', ['1'])
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('stockStatusFilter', [])
            ->assertSet('warehouseFilter', []);
    }

    public function test_export_url_built_correctly(): void
    {
        $admin = $this->admin();

        $component = Livewire::actingAs($admin)->test(InventoryPage::class);
        $url = $component->instance()->getExportUrl('csv');

        $this->assertStringContainsString('products/inventory/export', $url);
        $this->assertStringContainsString('format=csv', $url);
    }

    public function test_navigation_sort_is_three(): void
    {
        $this->assertSame(3, InventoryPage::getNavigationSort());
    }

    public function test_warehouse_resource_is_hidden_from_navigation(): void
    {
        $this->assertFalse(\App\Filament\Resources\WarehouseResource::shouldRegisterNavigation());
        $this->assertSame([], \App\Filament\Resources\WarehouseResource::getNavigationItems());
    }

    public function test_stock_movement_resource_is_hidden_from_navigation(): void
    {
        $this->assertFalse(\App\Filament\Resources\StockMovementResource::shouldRegisterNavigation());
        $this->assertSame([], \App\Filament\Resources\StockMovementResource::getNavigationItems());
    }

    public function test_no_incoming_column_and_no_transfer_ui_strings(): void
    {
        $admin = $this->admin();
        $this->makeStock();

        Livewire::actingAs($admin)
            ->test(InventoryPage::class)
            ->assertSuccessful()
            ->assertDontSee('Incoming')
            ->assertDontSee('Stock Transfer')
            ->assertDontSee('Transfer Stock');
    }
}

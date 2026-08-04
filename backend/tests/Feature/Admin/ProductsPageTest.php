<?php

namespace Tests\Feature\Admin;

use App\Enums\ProductStatus;
use App\Filament\Pages\Products\ProductsPage;
use App\Filament\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\TestCase;

class ProductsPageTest extends TestCase
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

    private function makeProduct(array $overrides = []): Product
    {
        $suffix = uniqid();

        return Product::factory()->create(array_merge([
            'name' => 'Test Catalog Product '.$suffix,
            'sku' => 'SKU-TEST-'.$suffix,
            'status' => ProductStatus::ACTIVE->value,
            'stock_quantity' => 50,
            'price' => 99.50,
        ], $overrides));
    }

    public function test_products_route_is_registered(): void
    {
        $this->assertTrue(Route::has('filament.admin.pages.products.catalog'));
        $this->assertTrue(Route::has('filament.admin.products.catalog.export'));
        $this->assertStringContainsString('/products/catalog', ProductsPage::getUrl());
    }

    public function test_guest_is_redirected_from_products_route(): void
    {
        $this->get('/products/catalog')->assertRedirect();
    }

    public function test_non_admin_is_denied_access_to_products_page(): void
    {
        $customer = $this->customer();

        Livewire::actingAs($customer)
            ->test(ProductsPage::class)
            ->assertForbidden();
    }

    public function test_admin_can_view_products_page(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(ProductsPage::class)
            ->assertSuccessful()
            ->assertSee('Products');
    }

    public function test_product_resource_navigation_is_hidden(): void
    {
        $this->assertFalse(ProductResource::shouldRegisterNavigation());
    }

    public function test_kpi_cards_shown_to_admin(): void
    {
        $admin = $this->admin();
        Category::factory()->create();

        $this->makeProduct(['status' => ProductStatus::ACTIVE->value]);
        $this->makeProduct(['status' => ProductStatus::INACTIVE->value, 'sku' => 'SKU-INACT']);
        $this->makeProduct(['status' => ProductStatus::OUT_OF_STOCK->value, 'stock_quantity' => 0, 'sku' => 'SKU-OOS']);
        $this->makeProduct(['stock_quantity' => 5, 'sku' => 'SKU-LOW']);

        Livewire::actingAs($admin)
            ->test(ProductsPage::class)
            ->assertSuccessful()
            ->assertSee('Total')
            ->assertSee('Active')
            ->assertSee('Inactive')
            ->assertSee('Out of Stock')
            ->assertSee('Low Stock')
            ->assertSee('Categories');
    }

    public function test_empty_state_renders_when_no_products(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(ProductsPage::class)
            ->assertSuccessful()
            ->assertSee('No products yet');
    }

    public function test_products_appear_in_table(): void
    {
        $admin = $this->admin();
        $this->makeProduct(['name' => 'Unique Product Alpha XYZ']);

        Livewire::actingAs($admin)
            ->test(ProductsPage::class)
            ->assertSuccessful()
            ->assertSee('Unique Product Alpha XYZ');
    }

    public function test_search_filters_products(): void
    {
        $admin = $this->admin();
        $this->makeProduct(['name' => 'Alpha Cleaner', 'sku' => 'SKU-ALPHA']);
        $this->makeProduct(['name' => 'Beta Polish', 'sku' => 'SKU-BETA']);

        Livewire::actingAs($admin)
            ->test(ProductsPage::class)
            ->set('search', 'Alpha')
            ->assertSee('Alpha Cleaner')
            ->assertDontSee('Beta Polish');
    }

    public function test_status_filter_works(): void
    {
        $admin = $this->admin();
        $this->makeProduct(['name' => 'Active Item', 'status' => ProductStatus::ACTIVE->value, 'sku' => 'SKU-A1']);
        $this->makeProduct(['name' => 'Inactive Item', 'status' => ProductStatus::INACTIVE->value, 'sku' => 'SKU-I1']);

        Livewire::actingAs($admin)
            ->test(ProductsPage::class)
            ->set('statusFilter', [ProductStatus::ACTIVE->value])
            ->assertSee('Active Item')
            ->assertDontSee('Inactive Item');
    }

    public function test_stock_filter_low_stock(): void
    {
        $admin = $this->admin();
        $this->makeProduct(['name' => 'Low Stock Item', 'stock_quantity' => 4, 'sku' => 'SKU-LS']);
        $this->makeProduct(['name' => 'Healthy Stock Item', 'stock_quantity' => 80, 'sku' => 'SKU-HS']);

        Livewire::actingAs($admin)
            ->test(ProductsPage::class)
            ->set('stockFilter', 'low')
            ->assertSee('Low Stock Item')
            ->assertDontSee('Healthy Stock Item');
    }

    public function test_admin_can_open_detail_drawer(): void
    {
        $admin = $this->admin();
        $product = $this->makeProduct();

        Livewire::actingAs($admin)
            ->test(ProductsPage::class)
            ->call('openDetailDrawer', $product->id)
            ->assertSet('showDetailDrawer', true)
            ->assertSet('selectedProductId', $product->id);
    }

    public function test_admin_can_close_detail_drawer(): void
    {
        $admin = $this->admin();
        $product = $this->makeProduct();

        Livewire::actingAs($admin)
            ->test(ProductsPage::class)
            ->call('openDetailDrawer', $product->id)
            ->call('closeDetailDrawer')
            ->assertSet('showDetailDrawer', false)
            ->assertSet('selectedProductId', null);
    }

    public function test_sort_by_toggles_direction(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(ProductsPage::class)
            ->call('sortBy', 'name')
            ->assertSet('sortField', 'name')
            ->assertSet('sortDirection', 'asc')
            ->call('sortBy', 'name')
            ->assertSet('sortDirection', 'desc');
    }

    public function test_reset_filters_clears_all(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(ProductsPage::class)
            ->set('search', 'something')
            ->set('statusFilter', [ProductStatus::ACTIVE->value])
            ->set('stockFilter', 'low')
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('statusFilter', [])
            ->assertSet('stockFilter', null);
    }

    public function test_export_url_built_correctly(): void
    {
        $admin = $this->admin();

        $component = Livewire::actingAs($admin)->test(ProductsPage::class);
        $url = $component->instance()->getExportUrl('csv');

        $this->assertStringContainsString('products/catalog/export', $url);
        $this->assertStringContainsString('format=csv', $url);
    }

    public function test_navigation_sort_is_one(): void
    {
        $this->assertSame(1, ProductsPage::getNavigationSort());
    }

    public function test_add_product_shown_when_create_allowed(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(ProductsPage::class)
            ->assertSee('Add Product');
    }

    public function test_kpi_cards_have_no_fake_trends(): void
    {
        $admin = $this->admin();
        $this->makeProduct();

        $component = Livewire::actingAs($admin)->test(ProductsPage::class);
        $cards = $component->instance()->kpiCards;

        foreach ($cards as $card) {
            $this->assertFalse($card['trend_available']);
            $this->assertSame('—', $card['trend']);
        }
    }
}

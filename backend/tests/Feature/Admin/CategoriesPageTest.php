<?php

namespace Tests\Feature\Admin;

use App\Filament\Pages\Products\CategoriesPage;
use App\Filament\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\Admin\CategoryAdminService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\TestCase;

class CategoriesPageTest extends TestCase
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

    private function category(array $attrs = []): Category
    {
        return Category::factory()->create(array_merge([
            'status' => 'active',
        ], $attrs));
    }

    public function test_categories_route_is_registered(): void
    {
        $this->assertTrue(Route::has('filament.admin.pages.products.categories'));
        $this->assertTrue(Route::has('filament.admin.products.categories.export'));
        $this->assertStringContainsString('/products/categories', CategoriesPage::getUrl());
    }

    public function test_legacy_resource_is_hidden_from_navigation(): void
    {
        $this->assertFalse(CategoryResource::shouldRegisterNavigation());
        $this->assertSame([], CategoryResource::getNavigationItems());
    }

    public function test_legacy_list_page_redirects_to_categories_workspace(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(CategoryResource::getUrl('index'))
            ->assertRedirect(CategoriesPage::getUrl());
    }

    public function test_guest_is_redirected_from_categories_route(): void
    {
        $response = $this->get('/products/categories');

        $response->assertRedirect();
    }

    public function test_non_admin_is_denied_access_to_categories_page(): void
    {
        $customer = $this->customer();

        Livewire::actingAs($customer)
            ->test(CategoriesPage::class)
            ->assertForbidden();
    }

    public function test_admin_can_view_categories_page_and_kpis(): void
    {
        $admin = $this->admin();

        Category::factory()->count(3)->create();

        Livewire::actingAs($admin)
            ->test(CategoriesPage::class)
            ->assertSuccessful()
            ->assertSee('Categories')
            ->assertSee('Total')
            ->assertSee('Active')
            ->assertSee('With products')
            ->assertSee('Empty');
    }

    public function test_empty_state_renders_when_no_categories_exist(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(CategoriesPage::class)
            ->assertSuccessful()
            ->assertSee('No categories yet');
    }

    public function test_add_category_button_shown_when_create_allowed(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(CategoriesPage::class)
            ->assertSuccessful()
            ->assertSee('Add Category');
    }

    public function test_search_filters_by_name_and_slug(): void
    {
        $admin = $this->admin();

        $this->category(['name' => 'Detergents', 'slug' => 'detergents']);
        $this->category(['name' => 'Sanitizers', 'slug' => 'sanitizers']);

        Livewire::actingAs($admin)
            ->test(CategoriesPage::class)
            ->set('search', 'Detergents')
            ->assertSee('Detergents')
            ->assertDontSee('Sanitizers');
    }

    public function test_status_filter_works(): void
    {
        $admin = $this->admin();

        $this->category(['name' => 'Active Category', 'status' => 'active']);
        $this->category(['name' => 'Inactive Category', 'status' => 'inactive']);

        Livewire::actingAs($admin)
            ->test(CategoriesPage::class)
            ->set('statusFilter', ['active'])
            ->assertSee('Active Category')
            ->assertDontSee('Inactive Category');
    }

    public function test_kpi_cards_use_live_counts(): void
    {
        $withProducts = $this->category(['status' => 'active']);
        Product::factory()->count(2)->create(['category_id' => $withProducts->id]);

        $this->category(['status' => 'active']);
        $this->category(['status' => 'inactive']);

        $cards = app(CategoryAdminService::class)->getKpiCards();
        $byLabel = collect($cards)->keyBy('label');

        $this->assertEquals('3', $byLabel['Total']['value']);
        $this->assertEquals('2', $byLabel['Active']['value']);
        $this->assertEquals('1', $byLabel['With products']['value']);
        $this->assertEquals('2', $byLabel['Empty']['value']);
    }

    public function test_detail_drawer_shows_assigned_products(): void
    {
        $admin = $this->admin();

        $category = $this->category([
            'name' => 'Floor Care',
            'description' => 'Products for floor maintenance.',
        ]);
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Floor Cleaner Pro',
            'sku' => 'FC-001',
        ]);

        Livewire::actingAs($admin)
            ->test(CategoriesPage::class)
            ->call('openDetailDrawer', $category->id)
            ->assertSet('showDetailDrawer', true)
            ->assertSet('selectedCategoryId', $category->id)
            ->assertSee('Floor Care')
            ->assertSee('Products for floor maintenance.')
            ->assertSee('Floor Cleaner Pro')
            ->assertSee('FC-001');
    }

    public function test_export_returns_filtered_rows(): void
    {
        $this->category(['name' => 'Active One', 'status' => 'active']);
        $this->category(['name' => 'Inactive One', 'status' => 'inactive']);

        $rows = app(CategoryAdminService::class)->exportRows([
            'status' => ['active'],
        ]);

        $this->assertCount(1, $rows);
        $this->assertEquals('Active One', $rows[0]['name']);
    }

    public function test_export_route_requires_admin(): void
    {
        $customer = $this->customer();

        $this->actingAs($customer)
            ->get(route('filament.admin.products.categories.export', ['format' => 'csv']))
            ->assertForbidden();
    }

    public function test_export_route_downloads_csv_for_admin(): void
    {
        $admin = $this->admin();
        $this->category();

        $response = $this->actingAs($admin)
            ->get(route('filament.admin.products.categories.export', ['format' => 'csv']));

        $response->assertSuccessful();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_pagination_resets_on_filter_change(): void
    {
        $admin = $this->admin();

        Category::factory()->count(25)->create();

        Livewire::actingAs($admin)
            ->test(CategoriesPage::class)
            ->call('gotoPage', 2)
            ->assertSet('paginators.page', 2)
            ->set('search', 'zzz-no-match')
            ->assertSet('paginators.page', 1);
    }

    public function test_with_count_products_is_loaded(): void
    {
        $category = $this->category(['name' => 'Counted Category']);
        Product::factory()->count(3)->create(['category_id' => $category->id]);

        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(CategoriesPage::class)
            ->assertSee('Counted Category')
            ->assertSee('3');
    }
}

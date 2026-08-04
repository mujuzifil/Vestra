<?php

namespace Tests\Feature\Admin;

use App\Enums\SettingGroup;
use App\Enums\SettingType;
use App\Filament\Pages\Marketing\MediaPage;
use App\Models\BlogPost;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class MediaPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        Storage::fake('public');
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

    private function makeBlogPostWithImages(array $overrides = []): BlogPost
    {
        $suffix = uniqid();

        Storage::disk('public')->put("blog/featured/featured-{$suffix}.jpg", str_repeat('a', 2048));
        Storage::disk('public')->put("blog/gallery/gallery-{$suffix}-0.jpg", str_repeat('b', 1024));

        return BlogPost::query()->create(array_merge([
            'title' => 'Test Blog Post '.$suffix,
            'content' => 'Body content for test post.',
            'featured_image' => "blog/featured/featured-{$suffix}.jpg",
            'gallery' => ["blog/gallery/gallery-{$suffix}-0.jpg"],
        ], $overrides));
    }

    private function makeProductImage(?Product $product = null): ProductImage
    {
        $product ??= Product::factory()->create(['name' => 'Media Test Product '.uniqid()]);
        $path = 'products/'.uniqid().'.jpg';
        Storage::disk('public')->put($path, str_repeat('c', 4096));

        return ProductImage::query()->create([
            'product_id' => $product->id,
            'image' => $path,
            'sort_order' => 0,
        ]);
    }

    private function makeSettingWithMedia(): Setting
    {
        $setting = Setting::query()->create([
            'key' => 'test_logo_'.uniqid(),
            'type' => SettingType::IMAGE->value,
            'group' => SettingGroup::GENERAL->value,
            'label' => 'Test Logo',
        ]);

        $file = UploadedFile::fake()->image('logo.png', 100, 100);
        $setting->addMedia($file)->toMediaCollection('settings');

        return $setting;
    }

    public function test_media_route_is_registered(): void
    {
        $this->assertTrue(Route::has('filament.admin.pages.marketing.media'));
        $this->assertTrue(Route::has('filament.admin.marketing.media.export'));
        $this->assertStringContainsString('/marketing/media', MediaPage::getUrl());
    }

    public function test_seo_page_is_removed(): void
    {
        $this->assertFalse(class_exists(\App\Filament\Pages\Marketing\SeoPage::class));
        $this->assertFalse(Route::has('filament.admin.pages.marketing.seo'));
    }

    public function test_guest_is_redirected_from_media_route(): void
    {
        $this->get('/marketing/media')->assertRedirect();
    }

    public function test_non_admin_is_denied_access_to_media_page(): void
    {
        $customer = $this->customer();

        Livewire::actingAs($customer)
            ->test(MediaPage::class)
            ->assertForbidden();
    }

    public function test_admin_can_view_media_page(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(MediaPage::class)
            ->assertSuccessful()
            ->assertSee('Media');
    }

    public function test_kpi_cards_shown_to_admin(): void
    {
        $admin = $this->admin();
        $this->makeProductImage();

        Livewire::actingAs($admin)
            ->test(MediaPage::class)
            ->assertSuccessful()
            ->assertSee('Total Files')
            ->assertSee('Images')
            ->assertSee('Documents')
            ->assertSee('Videos')
            ->assertSee('Storage Used');
    }

    public function test_empty_state_renders_when_no_media(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(MediaPage::class)
            ->assertSuccessful()
            ->assertSee('No media files yet');
    }

    public function test_blog_featured_and_gallery_images_appear_in_media(): void
    {
        $admin = $this->admin();
        $post = $this->makeBlogPostWithImages();

        Livewire::actingAs($admin)
            ->test(MediaPage::class)
            ->assertSuccessful()
            ->assertSee($post->title.' (Featured Image)')
            ->assertSee($post->title.' (Gallery)');
    }

    public function test_product_images_appear_in_media(): void
    {
        $admin = $this->admin();
        $product = Product::factory()->create(['name' => 'Unique Media Product XYZ']);
        $this->makeProductImage($product);

        Livewire::actingAs($admin)
            ->test(MediaPage::class)
            ->assertSuccessful()
            ->assertSee('Unique Media Product XYZ');
    }

    public function test_spatie_settings_media_appears_in_media(): void
    {
        $admin = $this->admin();
        $this->makeSettingWithMedia();

        Livewire::actingAs($admin)
            ->test(MediaPage::class)
            ->assertSuccessful()
            ->assertSee('Test Logo');
    }

    public function test_search_filters_media(): void
    {
        $admin = $this->admin();
        $productA = Product::factory()->create(['name' => 'Alpha Searchable Product']);
        $productB = Product::factory()->create(['name' => 'Beta Other Product']);
        $this->makeProductImage($productA);
        $this->makeProductImage($productB);

        Livewire::actingAs($admin)
            ->test(MediaPage::class)
            ->set('search', 'Alpha Searchable')
            ->assertSee('Alpha Searchable Product')
            ->assertDontSee('Beta Other Product');
    }

    public function test_source_filter_isolates_product_source(): void
    {
        $admin = $this->admin();
        $post = $this->makeBlogPostWithImages();
        $product = Product::factory()->create(['name' => 'Filter Source Product']);
        $this->makeProductImage($product);

        Livewire::actingAs($admin)
            ->test(MediaPage::class)
            ->set('sourceFilter', ['product'])
            ->assertSee('Filter Source Product')
            ->assertDontSee($post->title.' (Featured Image)');
    }

    public function test_type_filter_hides_non_matching_types(): void
    {
        $admin = $this->admin();
        $this->makeProductImage();

        Livewire::actingAs($admin)
            ->test(MediaPage::class)
            ->set('typeFilter', ['document'])
            ->assertSee('No files match your filters');
    }

    public function test_admin_can_open_detail_drawer(): void
    {
        $admin = $this->admin();
        $image = $this->makeProductImage();
        $id = 'product-image-'.$image->id;

        Livewire::actingAs($admin)
            ->test(MediaPage::class)
            ->call('openDetailDrawer', $id)
            ->assertSet('showDetailDrawer', true)
            ->assertSet('selectedMediaId', $id);
    }

    public function test_admin_can_close_detail_drawer(): void
    {
        $admin = $this->admin();
        $image = $this->makeProductImage();
        $id = 'product-image-'.$image->id;

        Livewire::actingAs($admin)
            ->test(MediaPage::class)
            ->call('openDetailDrawer', $id)
            ->call('closeDetailDrawer')
            ->assertSet('showDetailDrawer', false)
            ->assertSet('selectedMediaId', null);
    }

    public function test_sort_by_toggles_direction(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(MediaPage::class)
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
            ->test(MediaPage::class)
            ->set('search', 'something')
            ->set('typeFilter', ['image'])
            ->set('sourceFilter', ['product'])
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('typeFilter', [])
            ->assertSet('sourceFilter', []);
    }

    public function test_view_mode_toggle(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(MediaPage::class)
            ->assertSet('viewMode', 'grid')
            ->call('setViewMode', 'list')
            ->assertSet('viewMode', 'list')
            ->call('setViewMode', 'invalid')
            ->assertSet('viewMode', 'grid');
    }

    public function test_export_url_built_correctly(): void
    {
        $admin = $this->admin();

        $component = Livewire::actingAs($admin)->test(MediaPage::class);
        $url = $component->instance()->getExportUrl('csv');

        $this->assertStringContainsString('marketing/media/export', $url);
        $this->assertStringContainsString('format=csv', $url);
    }

    public function test_navigation_sort_is_two(): void
    {
        $this->assertSame(2, MediaPage::getNavigationSort());
    }

    public function test_kpi_cards_have_no_fake_trends(): void
    {
        $admin = $this->admin();
        $this->makeProductImage();

        $component = Livewire::actingAs($admin)->test(MediaPage::class);
        $cards = $component->instance()->kpiCards;

        foreach ($cards as $card) {
            $this->assertFalse($card['trend_available']);
            $this->assertSame('—', $card['trend']);
        }
    }

    public function test_upload_cta_links_present(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(MediaPage::class)
            ->assertSuccessful()
            ->assertSee('Via New Blog Post');
    }
}

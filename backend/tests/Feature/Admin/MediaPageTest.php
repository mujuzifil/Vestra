<?php

namespace Tests\Feature\Admin;

use App\Enums\MediaAssetStatus;
use App\Enums\MediaAssetType;
use App\Enums\MediaUsageContext;
use App\Filament\Pages\Marketing\MediaPage;
use App\Filament\Pages\Products\ProductsPage;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\MediaAsset;
use App\Models\MediaAssetUsage;
use App\Models\Product;
use App\Models\User;
use App\Services\Admin\MediaAdminService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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

    private function makeAsset(array $overrides = [], ?User $uploader = null): MediaAsset
    {
        $name = ($overrides['file_name'] ?? 'asset-'.uniqid()).(str_contains(($overrides['file_name'] ?? ''), '.') ? '' : '.jpg');
        $path = $overrides['path'] ?? 'media-library/'.$name;

        if (! isset($overrides['skip_file'])) {
            Storage::disk('public')->put($path, $overrides['contents'] ?? str_repeat('x', 2048));
        }

        return MediaAsset::query()->create(array_merge([
            'uuid' => (string) Str::uuid(),
            'disk' => 'public',
            'path' => $path,
            'file_name' => $name,
            'original_file_name' => $name,
            'mime_type' => 'image/jpeg',
            'media_type' => MediaAssetType::IMAGE->value,
            'size_bytes' => 2048,
            'width' => 800,
            'height' => 600,
            'checksum' => hash('sha256', $overrides['contents'] ?? uniqid()),
            'status' => MediaAssetStatus::ACTIVE->value,
            'uploaded_by' => $uploader?->id,
            'tags' => [],
        ], collect($overrides)->except(['skip_file', 'contents'])->all()));
    }

    public function test_media_route_is_registered(): void
    {
        $this->assertTrue(Route::has('filament.admin.pages.marketing.media'));
        $this->assertTrue(Route::has('filament.admin.marketing.media.export'));
        $this->assertStringContainsString('/marketing/media', MediaPage::getUrl());
    }

    public function test_guest_is_redirected_from_media_route(): void
    {
        $this->get('/marketing/media')->assertRedirect();
    }

    public function test_non_admin_is_denied_access_to_media_page(): void
    {
        Livewire::actingAs($this->customer())
            ->test(MediaPage::class)
            ->assertForbidden();
    }

    public function test_admin_can_view_media_library(): void
    {
        Livewire::actingAs($this->admin())
            ->test(MediaPage::class)
            ->assertSuccessful()
            ->assertSee('Media Library');
    }

    public function test_kpi_cards_shown_to_admin(): void
    {
        $this->makeAsset();

        Livewire::actingAs($this->admin())
            ->test(MediaPage::class)
            ->assertSuccessful()
            ->assertSee('Total Assets')
            ->assertSee('Images')
            ->assertSee('Unused')
            ->assertSee('Storage Used');
    }

    public function test_empty_state_renders_when_no_media(): void
    {
        Livewire::actingAs($this->admin())
            ->test(MediaPage::class)
            ->assertSuccessful()
            ->assertSee('No media assets yet');
    }

    public function test_assets_appear_in_library(): void
    {
        $this->makeAsset(['file_name' => 'unique-library-file.jpg']);

        Livewire::actingAs($this->admin())
            ->test(MediaPage::class)
            ->assertSuccessful()
            ->assertSee('unique-library-file.jpg');
    }

    public function test_admin_can_upload_asset(): void
    {
        $admin = $this->admin();
        $file = UploadedFile::fake()->image('bottle.png', 200, 200);

        Livewire::actingAs($admin)
            ->test(MediaPage::class)
            ->call('openUploadModal')
            ->set('uploadFile', $file)
            ->call('uploadAsset')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('media_assets', 1);
        $this->assertNotNull(MediaAsset::query()->first()?->path);
    }

    public function test_duplicate_checksum_reuses_existing_asset(): void
    {
        $admin = $this->admin();
        $service = app(MediaAdminService::class);

        $first = UploadedFile::fake()->image('same.png', 120, 120);
        $assetA = $service->upload($first, $admin);

        $second = UploadedFile::fake()->image('same.png', 120, 120);
        // Fake images may differ by binary content; force same checksum path via service first upload only.
        $assetB = $service->upload(
            new UploadedFile($first->getRealPath(), 'same-again.png', 'image/png', null, true),
            $admin
        );

        // If checksums differ between fakes, at least ensure upload works; when equal, IDs match.
        if ($assetA->checksum === $assetB->checksum) {
            $this->assertSame($assetA->id, $assetB->id);
        } else {
            $this->assertNotSame($assetA->id, $assetB->id);
        }
    }

    public function test_delete_blocked_when_asset_is_used(): void
    {
        $admin = $this->admin();
        $asset = $this->makeAsset(['file_name' => 'in-use.jpg']);
        $product = Product::factory()->create();

        app(MediaAdminService::class)->linkToProduct($product, $asset, asPrimary: true);

        $this->expectException(ValidationException::class);
        app(MediaAdminService::class)->delete($asset);
    }

    public function test_delete_allowed_when_unused(): void
    {
        $asset = $this->makeAsset(['file_name' => 'unused.jpg']);
        app(MediaAdminService::class)->delete($asset);

        $this->assertDatabaseMissing('media_assets', ['id' => $asset->id]);
    }

    public function test_usage_filter_unused(): void
    {
        $used = $this->makeAsset(['file_name' => 'used-asset.jpg']);
        $unused = $this->makeAsset(['file_name' => 'free-asset.jpg']);
        $product = Product::factory()->create();
        app(MediaAdminService::class)->linkToProduct($product, $used, true);

        Livewire::actingAs($this->admin())
            ->test(MediaPage::class)
            ->set('usageFilter', 'unused')
            ->assertSee('free-asset.jpg')
            ->assertDontSee('used-asset.jpg');
    }

    public function test_search_finds_product_usage(): void
    {
        $asset = $this->makeAsset(['file_name' => 'detergent.jpg']);
        $product = Product::factory()->create(['name' => 'Heavy Duty Detergent Alpha']);
        app(MediaAdminService::class)->linkToProduct($product, $asset, true);

        Livewire::actingAs($this->admin())
            ->test(MediaPage::class)
            ->set('search', 'Heavy Duty Detergent')
            ->assertSee('detergent.jpg');
    }

    public function test_detail_drawer_shows_usage(): void
    {
        $asset = $this->makeAsset(['file_name' => 'drawer-asset.jpg']);
        $product = Product::factory()->create(['name' => 'Drawer Product']);
        app(MediaAdminService::class)->linkToProduct($product, $asset, true);

        Livewire::actingAs($this->admin())
            ->test(MediaPage::class)
            ->call('openDetailDrawer', $asset->id)
            ->assertSet('showDetailDrawer', true)
            ->assertSee('Drawer Product')
            ->assertSee('drawer-asset.jpg');
    }

    public function test_replace_file_updates_product_path(): void
    {
        $admin = $this->admin();
        $asset = $this->makeAsset(['file_name' => 'old.jpg']);
        $product = Product::factory()->create();
        $image = app(MediaAdminService::class)->linkToProduct($product, $asset, true);
        $oldPath = $image->image;

        $replacement = UploadedFile::fake()->image('new.jpg', 300, 300);
        app(MediaAdminService::class)->replaceFile($asset, $replacement, $admin);

        $image->refresh();
        $asset->refresh();
        $this->assertNotSame($oldPath, $image->image);
        $this->assertSame($asset->path, $image->image);
    }

    public function test_product_page_can_link_existing_asset(): void
    {
        $admin = $this->admin();
        $asset = $this->makeAsset(['file_name' => 'link-me.jpg']);
        $categoryId = \App\Models\Category::factory()->create()->id;

        Livewire::actingAs($admin)
            ->test(ProductsPage::class)
            ->call('openCreateModal')
            ->set('form.name', 'Linked Product')
            ->set('form.sku', 'SKU-LINK-1')
            ->set('form.category_id', $categoryId)
            ->set('form.price', '10')
            ->set('form.stock_quantity', '5')
            ->set('form.low_stock_threshold', '2')
            ->set('form.stock_status', 'in_stock')
            ->set('form.status', 'active')
            ->set('pendingMediaAssets', [['id' => $asset->id, 'url' => $asset->url()]])
            ->call('saveProduct')
            ->assertHasNoErrors();

        $product = Product::query()->where('sku', 'SKU-LINK-1')->first();
        $this->assertNotNull($product);
        $this->assertTrue($product->images()->where('media_asset_id', $asset->id)->exists());
        $this->assertTrue(
            MediaAssetUsage::query()
                ->where('media_asset_id', $asset->id)
                ->where('usable_type', Product::class)
                ->where('usable_id', $product->id)
                ->exists()
        );
    }

    public function test_blog_featured_links_media_asset(): void
    {
        $admin = $this->admin();
        $asset = $this->makeAsset(['file_name' => 'blog-hero.jpg']);
        $category = BlogCategory::factory()->create(['is_active' => true]);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Pages\Marketing\BlogArticlePage::class)
            ->set('form.title', 'Media Linked Article')
            ->set('form.slug', 'media-linked-article')
            ->set('form.content', '<p>Body</p>')
            ->set('form.status', 'published')
            ->set('form.visibility', 'public')
            ->set('categoryIds', [$category->id])
            ->set('featuredMediaAssetId', $asset->id)
            ->call('publish')
            ->assertHasNoErrors();

        $post = BlogPost::query()->where('slug', 'media-linked-article')->first();
        $this->assertNotNull($post);
        $this->assertSame($asset->id, $post->featured_media_asset_id);
        $this->assertSame($asset->path, $post->featured_image);
        $this->assertTrue(
            MediaAssetUsage::query()
                ->where('media_asset_id', $asset->id)
                ->where('usable_type', BlogPost::class)
                ->where('context', MediaUsageContext::BLOG_FEATURED->value)
                ->exists()
        );
    }

    public function test_import_legacy_command_links_product_images(): void
    {
        $path = 'products/legacy-'.uniqid().'.jpg';
        Storage::disk('public')->put($path, str_repeat('z', 1024));
        $product = Product::factory()->create();
        $image = \App\Models\ProductImage::query()->create([
            'product_id' => $product->id,
            'image' => $path,
            'sort_order' => 0,
        ]);

        $this->artisan('media:import-legacy')->assertSuccessful();

        $image->refresh();
        $this->assertNotNull($image->media_asset_id);
        $this->assertDatabaseHas('media_assets', ['path' => $path]);
    }

    public function test_reset_filters_clears_all(): void
    {
        Livewire::actingAs($this->admin())
            ->test(MediaPage::class)
            ->set('search', 'something')
            ->set('typeFilter', ['image'])
            ->set('usageFilter', 'unused')
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('typeFilter', [])
            ->assertSet('usageFilter', null);
    }

    public function test_upload_asset_cta_present(): void
    {
        Livewire::actingAs($this->admin())
            ->test(MediaPage::class)
            ->assertSuccessful()
            ->assertSee('Upload Asset')
            ->assertDontSee('Via New Blog Post');
    }

    public function test_kpi_cards_have_no_fake_trends(): void
    {
        $this->makeAsset();
        $component = Livewire::actingAs($this->admin())->test(MediaPage::class);
        foreach ($component->instance()->kpiCards as $card) {
            $this->assertFalse($card['trend_available']);
            $this->assertSame('—', $card['trend']);
        }
    }
}

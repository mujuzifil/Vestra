<?php

namespace Tests\Feature\Admin;

use App\Enums\BlogPostStatus;
use App\Filament\Pages\Marketing\BlogPage;
use App\Filament\Resources\BlogPostResource;
use App\Models\BlogAuthor;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\TestCase;

class BlogPageTest extends TestCase
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

    private function makePost(array $overrides = []): BlogPost
    {
        $suffix = uniqid();

        return BlogPost::factory()->create(array_merge([
            'title' => 'Test Article '.$suffix,
            'slug' => 'test-article-'.$suffix,
            'status' => BlogPostStatus::DRAFT->value,
        ], $overrides));
    }

    public function test_blog_route_is_registered(): void
    {
        $this->assertTrue(Route::has('filament.admin.pages.marketing.blog'));
        $this->assertTrue(Route::has('filament.admin.marketing.blog.export'));
        $this->assertStringContainsString('/marketing/blog', BlogPage::getUrl());
    }

    public function test_guest_is_redirected_from_blog_route(): void
    {
        $this->get('/marketing/blog')->assertRedirect();
    }

    public function test_non_admin_is_denied_access_to_blog_page(): void
    {
        $customer = $this->customer();

        Livewire::actingAs($customer)
            ->test(BlogPage::class)
            ->assertForbidden();
    }

    public function test_admin_can_view_blog_page(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(BlogPage::class)
            ->assertSuccessful()
            ->assertSee('Blog');
    }

    public function test_blog_post_resource_navigation_is_hidden(): void
    {
        $this->assertFalse(BlogPostResource::shouldRegisterNavigation());
    }

    public function test_kpi_cards_shown_to_admin(): void
    {
        $admin = $this->admin();
        BlogCategory::factory()->create();
        BlogAuthor::factory()->create();

        $this->makePost(['status' => BlogPostStatus::PUBLISHED->value, 'published_at' => now(), 'view_count' => 10]);
        $this->makePost(['status' => BlogPostStatus::DRAFT->value]);
        $this->makePost(['status' => BlogPostStatus::SCHEDULED->value, 'scheduled_at' => now()->addDay()]);
        $this->makePost(['status' => BlogPostStatus::ARCHIVED->value]);

        Livewire::actingAs($admin)
            ->test(BlogPage::class)
            ->assertSuccessful()
            ->assertSee('Published')
            ->assertSee('Draft')
            ->assertSee('Scheduled')
            ->assertSee('Archived')
            ->assertSee('Categories')
            ->assertSee('Authors')
            ->assertSee('Total Views');
    }

    public function test_empty_state_renders_when_no_posts(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(BlogPage::class)
            ->assertSuccessful()
            ->assertSee('No articles yet');
    }

    public function test_posts_appear_in_table(): void
    {
        $admin = $this->admin();
        $this->makePost(['title' => 'Unique Article Alpha XYZ', 'slug' => 'unique-article-alpha-xyz']);

        Livewire::actingAs($admin)
            ->test(BlogPage::class)
            ->assertSuccessful()
            ->assertSee('Unique Article Alpha XYZ');
    }

    public function test_search_filters_posts(): void
    {
        $admin = $this->admin();
        $this->makePost(['title' => 'Alpha Chemical Safety Guide', 'slug' => 'alpha-chemical-safety-guide']);
        $this->makePost(['title' => 'Beta Distribution Insights', 'slug' => 'beta-distribution-insights']);

        Livewire::actingAs($admin)
            ->test(BlogPage::class)
            ->set('search', 'Alpha Chemical')
            ->assertSee('Alpha Chemical Safety Guide')
            ->assertDontSee('Beta Distribution Insights');
    }

    public function test_status_filter_works(): void
    {
        $admin = $this->admin();
        $this->makePost(['title' => 'Published Article One', 'slug' => 'published-article-one', 'status' => BlogPostStatus::PUBLISHED->value, 'published_at' => now()]);
        $this->makePost(['title' => 'Draft Article One', 'slug' => 'draft-article-one', 'status' => BlogPostStatus::DRAFT->value]);

        Livewire::actingAs($admin)
            ->test(BlogPage::class)
            ->set('statusFilter', [BlogPostStatus::PUBLISHED->value])
            ->assertSee('Published Article One')
            ->assertDontSee('Draft Article One');
    }

    public function test_admin_can_open_detail_drawer(): void
    {
        $admin = $this->admin();
        $post = $this->makePost();

        Livewire::actingAs($admin)
            ->test(BlogPage::class)
            ->call('openDetailDrawer', $post->id)
            ->assertSet('showDetailDrawer', true)
            ->assertSet('selectedPostId', $post->id);
    }

    public function test_admin_can_close_detail_drawer(): void
    {
        $admin = $this->admin();
        $post = $this->makePost();

        Livewire::actingAs($admin)
            ->test(BlogPage::class)
            ->call('openDetailDrawer', $post->id)
            ->call('closeDetailDrawer')
            ->assertSet('showDetailDrawer', false)
            ->assertSet('selectedPostId', null);
    }

    public function test_sort_by_toggles_direction(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(BlogPage::class)
            ->call('sortBy', 'title')
            ->assertSet('sortField', 'title')
            ->assertSet('sortDirection', 'asc')
            ->call('sortBy', 'title')
            ->assertSet('sortDirection', 'desc');
    }

    public function test_reset_filters_clears_all(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(BlogPage::class)
            ->set('search', 'something')
            ->set('statusFilter', [BlogPostStatus::PUBLISHED->value])
            ->set('authorFilter', 1)
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('statusFilter', [])
            ->assertSet('authorFilter', null);
    }

    public function test_export_url_built_correctly(): void
    {
        $admin = $this->admin();

        $component = Livewire::actingAs($admin)->test(BlogPage::class);
        $url = $component->instance()->getExportUrl('csv');

        $this->assertStringContainsString('marketing/blog/export', $url);
        $this->assertStringContainsString('format=csv', $url);
    }

    public function test_navigation_sort_is_one(): void
    {
        $this->assertSame(1, BlogPage::getNavigationSort());
    }

    public function test_new_article_shown_when_create_allowed(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(BlogPage::class)
            ->assertSee('New Article');
    }

    public function test_kpi_cards_have_no_fake_trends(): void
    {
        $admin = $this->admin();
        $this->makePost();

        $component = Livewire::actingAs($admin)->test(BlogPage::class);
        $cards = $component->instance()->kpiCards;

        foreach ($cards as $card) {
            $this->assertFalse($card['trend_available']);
            $this->assertSame('—', $card['trend']);
        }
    }
}

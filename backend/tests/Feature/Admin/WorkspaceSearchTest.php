<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\GlobalSearchCommandPalette;
use App\Models\CompanyProfile;
use App\Models\Product;
use App\Models\User;
use App\Services\Admin\WorkspaceSearchService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WorkspaceSearchTest extends TestCase
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

    public function test_search_service_returns_matching_companies_and_products(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $owner = User::factory()->create(['is_admin' => false]);

        CompanyProfile::factory()->create([
            'user_id' => $owner->id,
            'company_name' => 'Acme Detergents Ltd',
        ]);

        Product::factory()->create([
            'name' => 'Acme Floor Cleaner',
            'sku' => 'AFC-100',
        ]);

        $results = app(WorkspaceSearchService::class)->search('Acme');

        $this->assertArrayHasKey('Companies', $results);
        $this->assertArrayHasKey('Products', $results);
        $this->assertTrue(collect($results['Companies'])->contains(fn (array $item) => str_contains($item['title'], 'Acme')));
        $this->assertTrue(collect($results['Products'])->contains(fn (array $item) => str_contains($item['title'], 'Acme')));
    }

    public function test_search_service_returns_empty_for_short_queries(): void
    {
        $this->actingAs($this->admin());

        $this->assertSame([], app(WorkspaceSearchService::class)->search('a'));
        $this->assertSame([], app(WorkspaceSearchService::class)->search(''));
    }

    public function test_search_service_returns_empty_state_for_unknown_term(): void
    {
        $this->actingAs($this->admin());

        $this->assertSame([], app(WorkspaceSearchService::class)->search('zzznomatchxyz'));
    }

    public function test_command_palette_does_not_error_on_search(): void
    {
        $admin = $this->admin();

        CompanyProfile::factory()->create([
            'user_id' => User::factory()->create(['is_admin' => false])->id,
            'company_name' => 'Palette Search Co',
        ]);

        Livewire::actingAs($admin)
            ->test(GlobalSearchCommandPalette::class)
            ->call('open')
            ->set('query', 'Palette')
            ->assertSuccessful()
            ->assertSee('Companies')
            ->assertSee('Palette Search Co');
    }

    public function test_non_admin_receives_no_results(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        CompanyProfile::factory()->create([
            'user_id' => $user->id,
            'company_name' => 'Secret Co',
        ]);

        $this->actingAs($user);

        $this->assertSame([], app(WorkspaceSearchService::class)->search('Secret'));
    }
}

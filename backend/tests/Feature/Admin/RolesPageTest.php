<?php

namespace Tests\Feature\Admin;

use App\Filament\Pages\Administration\RolesPage;
use App\Filament\Resources\RoleResource;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolesPageTest extends TestCase
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

    private function makeCustomRole(array $overrides = []): Role
    {
        $suffix = uniqid();

        return Role::create(array_merge([
            'name' => 'Custom Role '.$suffix,
            'guard_name' => 'web',
            'description' => 'A custom test role.',
        ], $overrides));
    }

    public function test_roles_route_is_registered(): void
    {
        $this->assertTrue(Route::has('filament.admin.pages.administration.roles'));
        $this->assertTrue(Route::has('filament.admin.administration.roles.export'));
        $this->assertStringContainsString('/administration/roles', RolesPage::getUrl());
    }

    public function test_guest_is_redirected_from_roles_route(): void
    {
        $this->get('/administration/roles')->assertRedirect();
    }

    public function test_non_admin_is_denied_access_to_roles_page(): void
    {
        $customer = $this->customer();

        Livewire::actingAs($customer)
            ->test(RolesPage::class)
            ->assertForbidden();
    }

    public function test_admin_can_view_roles_page(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(RolesPage::class)
            ->assertSuccessful()
            ->assertSee('Roles');
    }

    public function test_role_resource_navigation_is_hidden(): void
    {
        $this->assertFalse(RoleResource::shouldRegisterNavigation());
    }

    public function test_kpi_cards_shown_to_admin(): void
    {
        $admin = $this->admin();
        $this->makeCustomRole();

        Livewire::actingAs($admin)
            ->test(RolesPage::class)
            ->assertSuccessful()
            ->assertSee('Total Roles')
            ->assertSee('System Roles')
            ->assertSee('Custom Roles')
            ->assertSee('Users Assigned')
            ->assertSee('Permissions');
    }

    public function test_seeded_system_roles_appear_in_table(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(RolesPage::class)
            ->assertSuccessful()
            ->assertSee('Super Administrator')
            ->assertSee('Administrator')
            ->assertSee('Manager');
    }

    public function test_search_filters_roles(): void
    {
        $admin = $this->admin();
        $this->makeCustomRole(['name' => 'Unique Warehouse Lead']);
        $this->makeCustomRole(['name' => 'Unique Regional Auditor']);

        Livewire::actingAs($admin)
            ->test(RolesPage::class)
            ->set('search', 'Warehouse Lead')
            ->assertSee('Unique Warehouse Lead')
            ->assertDontSee('Unique Regional Auditor');
    }

    public function test_type_filter_shows_only_system_roles(): void
    {
        $admin = $this->admin();
        $this->makeCustomRole(['name' => 'Distinct Custom Role Alpha']);

        Livewire::actingAs($admin)
            ->test(RolesPage::class)
            ->set('typeFilter', ['system'])
            ->assertSee('Super Administrator')
            ->assertDontSee('Distinct Custom Role Alpha');
    }

    public function test_type_filter_shows_only_custom_roles(): void
    {
        $admin = $this->admin();
        $this->makeCustomRole(['name' => 'Distinct Custom Role Beta']);

        Livewire::actingAs($admin)
            ->test(RolesPage::class)
            ->set('typeFilter', ['custom'])
            ->assertSee('Distinct Custom Role Beta')
            ->assertDontSee('Super Administrator');
    }

    public function test_admin_can_open_detail_drawer(): void
    {
        $admin = $this->admin();
        $role = $this->makeCustomRole();

        Livewire::actingAs($admin)
            ->test(RolesPage::class)
            ->call('openDetailDrawer', $role->id)
            ->assertSet('showDetailDrawer', true)
            ->assertSet('selectedRoleId', $role->id);
    }

    public function test_admin_can_close_detail_drawer(): void
    {
        $admin = $this->admin();
        $role = $this->makeCustomRole();

        Livewire::actingAs($admin)
            ->test(RolesPage::class)
            ->call('openDetailDrawer', $role->id)
            ->call('closeDetailDrawer')
            ->assertSet('showDetailDrawer', false)
            ->assertSet('selectedRoleId', null);
    }

    public function test_sort_by_toggles_direction(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(RolesPage::class)
            ->call('sortBy', 'users_count')
            ->assertSet('sortField', 'users_count')
            ->assertSet('sortDirection', 'asc')
            ->call('sortBy', 'users_count')
            ->assertSet('sortDirection', 'desc');
    }

    public function test_reset_filters_clears_all(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(RolesPage::class)
            ->set('search', 'something')
            ->set('typeFilter', ['system'])
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('typeFilter', []);
    }

    public function test_export_url_built_correctly(): void
    {
        $admin = $this->admin();

        $component = Livewire::actingAs($admin)->test(RolesPage::class);
        $url = $component->instance()->getExportUrl('csv');

        $this->assertStringContainsString('administration/roles/export', $url);
        $this->assertStringContainsString('format=csv', $url);
    }

    public function test_navigation_sort_is_two(): void
    {
        $this->assertSame(2, RolesPage::getNavigationSort());
    }

    public function test_new_role_shown_when_create_allowed(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(RolesPage::class)
            ->assertSee('New Role');
    }

    public function test_kpi_cards_have_no_fake_trends(): void
    {
        $admin = $this->admin();
        $this->makeCustomRole();

        $component = Livewire::actingAs($admin)->test(RolesPage::class);
        $cards = $component->instance()->kpiCards;

        foreach ($cards as $card) {
            $this->assertFalse($card['trend_available']);
            $this->assertSame('—', $card['trend']);
        }
    }
}

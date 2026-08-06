<?php

namespace Tests\Feature\Admin;

use App\Filament\Pages\Administration\RoleFormPage;
use App\Filament\Pages\Administration\RolesPage;
use App\Filament\Pages\Products\ProductsPage;
use App\Filament\Resources\RoleResource;
use App\Models\Role;
use App\Models\User;
use App\Services\Admin\PermissionDiscoveryService;
use App\Services\Admin\RoleAdminService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class RolesPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function admin(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'is_admin' => true,
            'status' => 'active',
            'email_verified_at' => now(),
        ], $overrides));
    }

    private function customer(): User
    {
        return User::factory()->create([
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);
    }

    public function test_roles_route_is_registered(): void
    {
        $this->assertTrue(Route::has('filament.admin.pages.administration.roles'));
        $this->assertTrue(Route::has('filament.admin.pages.administration.roles.form'));
        $this->assertTrue(Route::has('filament.admin.administration.roles.export'));
        $this->assertStringContainsString('/administration/roles', RolesPage::getUrl());
        $this->assertStringContainsString('/administration/roles/form', RoleFormPage::getUrl());
    }

    public function test_guest_is_redirected_from_roles_route(): void
    {
        $this->get('/administration/roles')->assertRedirect();
    }

    public function test_non_admin_is_denied_access_to_roles_page(): void
    {
        $this->actingAs($this->customer())
            ->get(RolesPage::getUrl())
            ->assertForbidden();
    }

    public function test_admin_can_view_roles_page(): void
    {
        Livewire::actingAs($this->admin())
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
        Livewire::actingAs($this->admin())
            ->test(RolesPage::class)
            ->assertSuccessful()
            ->assertSee('Total Roles')
            ->assertSee('System Roles')
            ->assertSee('Custom Roles')
            ->assertSee('Permissions');
    }

    public function test_seeded_roles_appear(): void
    {
        Livewire::actingAs($this->admin())
            ->test(RolesPage::class)
            ->assertSuccessful()
            ->assertSee('Administrator');
    }

    public function test_type_filter_system(): void
    {
        $customName = 'Unique Custom Role '.uniqid();
        Role::create([
            'name' => $customName,
            'guard_name' => 'web',
            'slug' => 'unique-custom-'.uniqid(),
            'status' => 'active',
        ]);

        Livewire::actingAs($this->admin())
            ->test(RolesPage::class)
            ->set('typeFilter', 'system')
            ->assertSee('Administrator')
            ->assertDontSee($customName);
    }

    public function test_create_url_points_to_role_form(): void
    {
        $component = Livewire::actingAs($this->admin())->test(RolesPage::class);
        $this->assertStringContainsString('/administration/roles/form', $component->instance()->createUrl);
    }

    public function test_role_form_renders_sections(): void
    {
        Livewire::actingAs($this->admin())
            ->test(RoleFormPage::class)
            ->assertSuccessful()
            ->assertSee('New Role')
            ->assertSee('Role Information')
            ->assertSee('Status')
            ->assertSee('Permissions')
            ->assertDontSee('Role Color')
            ->assertDontSee('Role Summary');
    }

    public function test_permission_discovery_is_dynamic(): void
    {
        $tree = app(PermissionDiscoveryService::class)->getPermissionTree();
        $this->assertNotEmpty($tree);
        $labels = collect($tree)->pluck('label')->all();
        $this->assertContains('Roles', $labels);
        $this->assertSame($labels, collect($labels)->sort()->values()->all());
    }

    public function test_create_role_persists_permissions(): void
    {
        $admin = $this->admin();
        app(PermissionDiscoveryService::class)->syncToDatabase();
        $permission = Permission::query()->where('name', 'like', '%.view')->first();
        $this->assertNotNull($permission);

        $role = app(RoleAdminService::class)->createRole([
            'name' => 'Support Analyst',
            'slug' => 'support-analyst',
            'description' => 'Handles support',
            'status' => 'active',
            'notes' => 'Internal',
        ], [$permission->name], $admin);

        $this->assertDatabaseHas('roles', [
            'name' => 'Support Analyst',
            'slug' => 'support-analyst',
            'status' => 'active',
        ]);
        $this->assertTrue($role->hasPermissionTo($permission->name));
    }

    public function test_admin_can_open_role_detail_drawer(): void
    {
        $admin = $this->admin();
        $role = Role::query()->where('name', 'Administrator')->firstOrFail();

        Livewire::actingAs($admin)
            ->test(RolesPage::class)
            ->call('openDetailDrawer', $role->id)
            ->assertSet('showDetailDrawer', true)
            ->assertSee('Permission Comparison')
            ->assertSee('Assigned Users')
            ->assertSee('Audit History')
            ->assertSee('Edit Role');
    }

    public function test_rbac_blocks_products_without_permission(): void
    {
        app(PermissionDiscoveryService::class)->syncToDatabase();

        $admin = $this->admin();
        $role = app(RoleAdminService::class)->createRole([
            'name' => 'Blog Only',
            'slug' => 'blog-only',
            'status' => 'active',
        ], array_values(Permission::query()->where('name', 'like', 'blog.%')->pluck('name')->all()), $admin);

        $admin->assignRole($role->name);
        $this->actingAs($admin->fresh());

        $this->assertFalse(ProductsPage::canAccess());
        $this->assertFalse(\Illuminate\Support\Facades\Gate::allows('viewAny', \App\Models\Product::class));
    }

    public function test_role_api_endpoints(): void
    {
        $admin = $this->admin();
        $token = $admin->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/admin/roles')
            ->assertOk();

        $this->withToken($token)
            ->getJson('/api/v1/admin/roles-permission-tree')
            ->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_reserved_role_name_rejected(): void
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        app(RoleAdminService::class)->createRole([
            'name' => 'Administrator',
            'slug' => 'administrator-copy',
            'status' => 'active',
        ], [], $this->admin());
    }
}

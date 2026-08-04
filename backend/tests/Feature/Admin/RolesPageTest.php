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
        Livewire::actingAs($this->customer())
            ->test(RolesPage::class)
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
        Role::create(['name' => $customName, 'guard_name' => 'web']);

        Livewire::actingAs($this->admin())
            ->test(RolesPage::class)
            ->set('typeFilter', 'system')
            ->assertSee('Administrator')
            ->assertDontSee($customName);
    }

    public function test_admin_can_open_detail_drawer(): void
    {
        $role = Role::query()->where('name', 'Administrator')->firstOrFail();

        Livewire::actingAs($this->admin())
            ->test(RolesPage::class)
            ->call('openDetailDrawer', $role->id)
            ->assertSet('showDetailDrawer', true)
            ->assertSee('Administrator');
    }

    public function test_analytics_and_integrations_pages_removed(): void
    {
        $this->assertFalse(class_exists(\App\Filament\Pages\Analytics\ExecutiveAnalyticsPage::class));
        $this->assertFalse(class_exists(\App\Filament\Pages\Administration\IntegrationsPage::class));
        $this->assertFalse(class_exists(\App\Filament\Resources\SettingResource::class));
        $this->assertFalse(class_exists(\App\Filament\Resources\AuditLogResource::class));
    }

    public function test_kpi_cards_have_no_fake_trends(): void
    {
        $cards = Livewire::actingAs($this->admin())
            ->test(RolesPage::class)
            ->get('kpiCards');

        foreach ($cards as $card) {
            $this->assertFalse($card['trend_available']);
        }
    }

    public function test_navigation_sort_is_two(): void
    {
        $this->assertSame(2, RolesPage::getNavigationSort());
    }
}

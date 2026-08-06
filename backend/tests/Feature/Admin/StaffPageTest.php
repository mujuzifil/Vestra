<?php

namespace Tests\Feature\Admin;

use App\Filament\Pages\Administration\StaffFormPage;
use App\Filament\Pages\Administration\StaffPage;
use App\Filament\Pages\ForcePasswordChange;
use App\Models\User;
use App\Notifications\StaffWelcomeNotification;
use App\Services\Admin\PermissionDiscoveryService;
use App\Services\Admin\StaffAdminService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StaffPageTest extends TestCase
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

    private function makeStaff(array $overrides = []): User
    {
        $suffix = uniqid();

        return User::factory()->create(array_merge([
            'name' => 'Staff Member '.$suffix,
            'email' => 'staff-'.$suffix.'@vestra.test',
            'is_admin' => true,
            'status' => 'active',
        ], $overrides));
    }

    public function test_staff_route_is_registered(): void
    {
        $this->assertTrue(Route::has('filament.admin.pages.administration.staff'));
        $this->assertTrue(Route::has('filament.admin.pages.administration.staff.form'));
        $this->assertTrue(Route::has('filament.admin.administration.staff.export'));
        $this->assertStringContainsString('/administration/staff', StaffPage::getUrl());
        $this->assertStringContainsString('/administration/staff/form', StaffFormPage::getUrl());
    }

    public function test_guest_is_redirected_from_staff_route(): void
    {
        $this->get('/administration/staff')->assertRedirect();
    }

    public function test_non_admin_is_denied_access_to_staff_page(): void
    {
        $customer = $this->customer();

        Livewire::actingAs($customer)
            ->test(StaffPage::class)
            ->assertForbidden();
    }

    public function test_admin_can_view_staff_page(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(StaffPage::class)
            ->assertSuccessful()
            ->assertSee('Staff');
    }

    public function test_kpi_cards_shown_to_admin(): void
    {
        $admin = $this->admin();
        $this->makeStaff(['status' => 'active']);
        $this->makeStaff(['status' => 'inactive']);

        Livewire::actingAs($admin)
            ->test(StaffPage::class)
            ->assertSuccessful()
            ->assertSee('Total Staff')
            ->assertSee('Active')
            ->assertSee('Inactive')
            ->assertSee('Roles')
            ->assertSee('Pending Password Reset');
    }

    public function test_empty_state_does_not_render_when_admin_exists(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(StaffPage::class)
            ->assertSuccessful()
            ->assertDontSee('No staff yet')
            ->assertDontSee('No staff members yet');
    }

    public function test_staff_appear_in_table(): void
    {
        $admin = $this->admin();
        $this->makeStaff(['name' => 'Unique Staff Alpha XYZ']);

        Livewire::actingAs($admin)
            ->test(StaffPage::class)
            ->assertSuccessful()
            ->assertSee('Unique Staff Alpha XYZ');
    }

    public function test_search_filters_staff(): void
    {
        $admin = $this->admin();
        $this->makeStaff(['name' => 'Alpha Chemical Manager']);
        $this->makeStaff(['name' => 'Beta Distribution Lead']);

        Livewire::actingAs($admin)
            ->test(StaffPage::class)
            ->set('search', 'Alpha Chemical')
            ->assertSee('Alpha Chemical Manager')
            ->assertDontSee('Beta Distribution Lead');
    }

    public function test_search_covers_username_employee_id_and_department(): void
    {
        $admin = $this->admin();
        $this->makeStaff([
            'name' => 'Hidden Name One',
            'username' => 'alpha.ops',
            'employee_id' => 'EMP-9001',
            'department' => 'Warehouse',
        ]);
        $this->makeStaff(['name' => 'Other Person', 'username' => 'beta.sales']);

        Livewire::actingAs($admin)
            ->test(StaffPage::class)
            ->set('search', 'EMP-9001')
            ->assertSee('Hidden Name One')
            ->assertDontSee('Other Person');
    }

    public function test_status_filter_works(): void
    {
        $admin = $this->admin();
        $this->makeStaff(['name' => 'Active Staff One', 'status' => 'active']);
        $this->makeStaff(['name' => 'Inactive Staff One', 'status' => 'inactive']);

        Livewire::actingAs($admin)
            ->test(StaffPage::class)
            ->set('statusFilter', ['active'])
            ->assertSee('Active Staff One')
            ->assertDontSee('Inactive Staff One');
    }

    public function test_role_filter_is_dynamic_from_roles(): void
    {
        $admin = $this->admin();
        $role = Role::query()->where('name', '!=', 'customer')->orderBy('name')->first();
        $this->assertNotNull($role);

        $component = Livewire::actingAs($admin)->test(StaffPage::class);
        $roles = $component->instance()->filterOptions['roles'];
        $names = collect($roles)->pluck('name')->all();

        $this->assertContains($role->name, $names);
        $this->assertSame($names, collect($names)->sort()->values()->all());
        $this->assertSame(count($names), count(array_unique($names)));
    }

    public function test_role_filter_works(): void
    {
        $admin = $this->admin();
        $role = Role::query()->first();

        $withRole = $this->makeStaff(['name' => 'Role Assigned Staff']);
        if ($role) {
            $withRole->roles()->sync([$role->id]);
        }

        $this->makeStaff(['name' => 'No Role Staff']);

        if (! $role) {
            $this->markTestSkipped('No roles seeded.');
        }

        Livewire::actingAs($admin)
            ->test(StaffPage::class)
            ->set('roleFilter', [$role->id])
            ->assertSee('Role Assigned Staff')
            ->assertDontSee('No Role Staff');
    }

    public function test_admin_can_open_detail_drawer(): void
    {
        $admin = $this->admin();
        $staff = $this->makeStaff();

        Livewire::actingAs($admin)
            ->test(StaffPage::class)
            ->call('openDetailDrawer', $staff->id)
            ->assertSet('showDetailDrawer', true)
            ->assertSet('selectedStaffId', $staff->id)
            ->assertSee('Personal Information')
            ->assertSee('Audit Timeline')
            ->assertSee('Edit Staff');
    }

    public function test_admin_can_disable_staff_from_detail(): void
    {
        $admin = $this->admin();
        $staff = $this->makeStaff(['status' => 'active']);

        Livewire::actingAs($admin)
            ->test(StaffPage::class)
            ->call('openDetailDrawer', $staff->id)
            ->call('disableStaff', $staff->id);

        $this->assertSame('inactive', $staff->fresh()->status);
    }

    public function test_admin_can_close_detail_drawer(): void
    {
        $admin = $this->admin();
        $staff = $this->makeStaff();

        Livewire::actingAs($admin)
            ->test(StaffPage::class)
            ->call('openDetailDrawer', $staff->id)
            ->call('closeDetailDrawer')
            ->assertSet('showDetailDrawer', false)
            ->assertSet('selectedStaffId', null);
    }

    public function test_sort_by_toggles_direction(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(StaffPage::class)
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
            ->test(StaffPage::class)
            ->set('search', 'something')
            ->set('statusFilter', ['active'])
            ->set('roleFilter', [1])
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('statusFilter', [])
            ->assertSet('roleFilter', []);
    }

    public function test_export_url_built_correctly(): void
    {
        $admin = $this->admin();

        $component = Livewire::actingAs($admin)->test(StaffPage::class);
        $url = $component->instance()->getExportUrl('csv');

        $this->assertStringContainsString('administration/staff/export', $url);
        $this->assertStringContainsString('format=csv', $url);
    }

    public function test_new_staff_shown_when_create_allowed(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(StaffPage::class)
            ->assertSee('New Staff');
    }

    public function test_create_url_points_to_staff_form(): void
    {
        $admin = $this->admin();
        $component = Livewire::actingAs($admin)->test(StaffPage::class);

        $this->assertStringContainsString('/administration/staff/form', $component->instance()->createUrl);
    }

    public function test_kpi_cards_have_no_fake_trends(): void
    {
        $admin = $this->admin();
        $this->makeStaff();

        $component = Livewire::actingAs($admin)->test(StaffPage::class);
        $cards = $component->instance()->kpiCards;

        foreach ($cards as $card) {
            $this->assertFalse($card['trend_available']);
            $this->assertSame('—', $card['trend']);
        }
    }

    public function test_permission_discovery_builds_dynamic_tree(): void
    {
        $tree = app(PermissionDiscoveryService::class)->getPermissionTree();

        $this->assertNotEmpty($tree);
        $labels = collect($tree)->pluck('label')->all();
        $this->assertContains('Staff', $labels);
        $this->assertSame($labels, collect($labels)->sort()->values()->all());

        foreach ($tree as $group) {
            $this->assertNotEmpty($group['permissions']);
            foreach ($group['permissions'] as $permission) {
                $this->assertStringContainsString('.', $permission['name']);
            }
        }
    }

    public function test_permission_search_filters_tree(): void
    {
        $service = app(PermissionDiscoveryService::class);
        $filtered = $service->getPermissionTree('staff');

        $this->assertNotEmpty($filtered);
        foreach ($filtered as $group) {
            $haystack = mb_strtolower($group['label'].' '.collect($group['permissions'])->pluck('name')->implode(' '));
            $this->assertStringContainsString('staff', $haystack);
        }
    }

    public function test_create_staff_persists_profile_role_and_force_password_change(): void
    {
        Notification::fake();

        $admin = $this->admin();
        $role = Role::query()->where('name', '!=', 'customer')->firstOrFail();
        app(PermissionDiscoveryService::class)->syncToDatabase();

        $result = app(StaffAdminService::class)->createStaff([
            'name' => 'New Hire',
            'email' => 'new.hire@vestra.test',
            'username' => 'new.hire',
            'status' => 'active',
            'role_id' => $role->id,
            'department' => 'Sales',
            'job_title' => 'Account Manager',
            'employee_id' => 'EMP-100',
            'notes' => 'Onboarding',
        ], [], null, $admin);

        $user = $result['user'];

        $this->assertTrue($user->is_admin);
        $this->assertTrue($user->mustChangePassword());
        $this->assertSame('Sales', $user->department);
        $this->assertTrue($user->hasRole($role->name));
        $this->assertNotEmpty($result['temporary_password']);
        Notification::assertSentTo($user, StaffWelcomeNotification::class);
    }

    public function test_staff_form_page_renders_create_sections(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(StaffFormPage::class)
            ->assertSuccessful()
            ->assertSee('Create New Staff')
            ->assertSee('Personal Information')
            ->assertSee('Account Information')
            ->assertSee('Role & Permissions')
            ->assertSee('Additional Information');
    }

    public function test_first_login_password_change_blocks_reuse_of_temporary_password(): void
    {
        $admin = $this->admin([
            'password' => 'TempPass!23456',
            'force_password_change_at' => now(),
        ]);

        Livewire::actingAs($admin)
            ->test(ForcePasswordChange::class)
            ->set('data.current_password', 'TempPass!23456')
            ->set('data.password', 'TempPass!23456')
            ->set('data.password_confirmation', 'TempPass!23456')
            ->call('changePassword')
            ->assertHasErrors(['data.password']);

        $this->assertTrue($admin->fresh()->mustChangePassword());
    }

    public function test_first_login_password_change_clears_flag(): void
    {
        $admin = $this->admin([
            'password' => 'TempPass!23456',
            'force_password_change_at' => now(),
        ]);

        Livewire::actingAs($admin)
            ->test(ForcePasswordChange::class)
            ->set('data.current_password', 'TempPass!23456')
            ->set('data.password', 'BrandNew!Pass99')
            ->set('data.password_confirmation', 'BrandNew!Pass99')
            ->call('changePassword')
            ->assertHasNoErrors()
            ->assertRedirect('/administration/staff');

        $fresh = $admin->fresh();
        $this->assertFalse($fresh->mustChangePassword());
        $this->assertNotNull($fresh->password_changed_at);
        $this->assertTrue(Hash::check('BrandNew!Pass99', $fresh->password));
    }

    public function test_admin_staff_api_roles_and_permission_tree(): void
    {
        $admin = $this->admin();
        $token = $admin->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/admin/staff-role-options')
            ->assertOk()
            ->assertJsonStructure(['data']);

        $this->withToken($token)
            ->getJson('/api/v1/admin/permission-tree')
            ->assertOk()
            ->assertJsonStructure(['data']);
    }
}

<?php

namespace Tests\Feature\Admin;

use App\Filament\Pages\Administration\StaffPage;
use App\Filament\Resources\UserResource;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $this->assertTrue(Route::has('filament.admin.administration.staff.export'));
        $this->assertStringContainsString('/administration/staff', StaffPage::getUrl());
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

    public function test_user_resource_navigation_is_hidden(): void
    {
        $this->assertFalse(UserResource::shouldRegisterNavigation());
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

    public function test_empty_state_renders_when_no_staff(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(StaffPage::class)
            ->assertSuccessful()
            ->assertSee('No staff members yet');
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
            ->assertSet('selectedStaffId', $staff->id);
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
}

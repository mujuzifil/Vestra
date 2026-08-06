<?php

namespace Tests\Feature\Admin;

use App\Filament\Pages\ProfilePage;
use App\Models\AdminSession;
use App\Models\User;
use App\Services\Admin\ProfileAdminService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProfilePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function admin(array $overrides = []): User
    {
        $user = User::factory()->create(array_merge([
            'name' => 'VESTRA Administrator',
            'email' => 'admin-profile-'.uniqid().'@vestra.test',
            'username' => 'vestra.admin.'.uniqid(),
            'phone' => '+254700000000',
            'is_admin' => true,
            'status' => 'active',
            'department' => 'Administration',
            'employee_id' => 'ADM-'.uniqid(),
            'email_verified_at' => now(),
            'password' => 'TempPass!23456',
        ], $overrides));

        $role = Role::query()->where('name', 'Super Administrator')->first();
        if ($role) {
            $user->assignRole($role->name);
        }

        return $user;
    }

    public function test_profile_route_is_registered(): void
    {
        $this->assertTrue(Route::has('filament.admin.pages.profile'));
        $this->assertStringContainsString('/profile', ProfilePage::getUrl());
    }

    public function test_guest_is_redirected_from_profile(): void
    {
        $this->get('/profile')->assertRedirect();
    }

    public function test_admin_can_view_profile_with_real_data(): void
    {
        $admin = $this->admin([
            'created_at' => now()->subMonths(6),
            'last_login_at' => now()->subDay(),
        ]);

        Livewire::actingAs($admin)
            ->test(ProfilePage::class)
            ->assertSuccessful()
            ->assertSee('My Profile')
            ->assertSee($admin->name)
            ->assertSee($admin->email)
            ->assertSee('Member Since')
            ->assertSee('Last Login')
            ->assertSee('Active')
            ->assertSee('Change Password')
            ->assertSee('Edit Profile')
            ->assertDontSee('Two-factor')
            ->assertDontSee('Backup Codes')
            ->assertDontSee('Trusted Devices')
            ->assertDontSee('Download My Data')
            ->assertDontSee('Delete Account');
    }

    public function test_profile_service_exposes_member_since_and_status(): void
    {
        $admin = $this->admin([
            'last_login_at' => now()->subHours(2),
        ]);

        $payload = app(ProfileAdminService::class)->getProfile($admin);

        $this->assertArrayHasKey('member_since', $payload);
        $this->assertSame($admin->created_at?->toDateTimeString(), $payload['member_since']?->toDateTimeString());
        $this->assertSame('active', $payload['status']);
        $this->assertSame('Active', $payload['status_label']);
        $this->assertSame($admin->last_login_at?->toDateTimeString(), $payload['last_login_at']?->toDateTimeString());
    }

    public function test_edit_profile_persists_changes(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(ProfilePage::class)
            ->call('openEditModal')
            ->set('editForm.name', 'Updated Admin Name')
            ->set('editForm.phone', '+254711111111')
            ->call('saveProfile')
            ->assertHasNoErrors();

        $fresh = $admin->fresh();
        $this->assertSame('Updated Admin Name', $fresh->name);
        $this->assertSame('+254711111111', $fresh->phone);
    }

    public function test_change_password_requires_current_and_updates_hash(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(ProfilePage::class)
            ->call('openPasswordModal')
            ->set('passwordForm.current_password', 'TempPass!23456')
            ->set('passwordForm.password', 'BrandNew!Pass99')
            ->set('passwordForm.password_confirmation', 'BrandNew!Pass99')
            ->call('savePassword')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('BrandNew!Pass99', $admin->fresh()->password));
        $this->assertNotNull($admin->fresh()->password_changed_at);
    }

    public function test_sessions_tab_lists_admin_sessions(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);
        session()->start();

        AdminSession::create([
            'user_id' => $admin->id,
            'session_id' => session()->getId(),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'device' => 'Desktop',
            'os' => 'Windows',
            'browser' => 'Chrome',
            'last_activity_at' => now(),
        ]);

        Livewire::actingAs($admin)
            ->test(ProfilePage::class)
            ->call('setTab', 'sessions')
            ->assertSee('Active Sessions')
            ->assertSee('Desktop');
    }

    public function test_profile_service_omits_empty_staff_fields(): void
    {
        $admin = $this->admin([
            'department' => null,
            'job_title' => null,
            'employee_id' => null,
        ]);

        $payload = app(ProfileAdminService::class)->getProfile($admin);

        $this->assertArrayNotHasKey('department', $payload);
        $this->assertArrayNotHasKey('job_title', $payload);
        $this->assertArrayNotHasKey('employee_id', $payload);
        $this->assertSame($admin->email, $payload['email']);
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_seeder_creates_bootstrap_admin_with_default_password(): void
    {
        $this->seed(AdminUserSeeder::class);

        $user = User::where('email', 'admin@vestra.com')->firstOrFail();

        $this->assertSame('VESTRA Administrator', $user->name);
        $this->assertTrue($user->isAdmin());
        $this->assertTrue($user->mustChangePassword());
        $this->assertTrue(Hash::check('Admin@12345', $user->password));
        $this->assertTrue($user->hasRole('Super Administrator'));
    }

    public function test_seeder_preserves_existing_non_default_password(): void
    {
        $existing = User::create([
            'name' => 'VESTRA Administrator',
            'email' => 'admin@vestra.com',
            'password' => Hash::make('CustomP@ssw0rd123'),
            'is_admin' => true,
            'status' => 'active',
            'email_verified_at' => now(),
            'force_password_change_at' => null,
        ]);
        $existing->assignRole('Super Administrator');

        $this->seed(AdminUserSeeder::class);

        $user = User::where('email', 'admin@vestra.com')->firstOrFail();

        $this->assertTrue(Hash::check('CustomP@ssw0rd123', $user->password));
        $this->assertFalse($user->mustChangePassword());
    }

    public function test_seeder_resets_password_when_reset_flag_is_true(): void
    {
        $existing = User::create([
            'name' => 'VESTRA Administrator',
            'email' => 'admin@vestra.com',
            'password' => Hash::make('CustomP@ssw0rd123'),
            'is_admin' => true,
            'status' => 'active',
            'email_verified_at' => now(),
            'force_password_change_at' => null,
        ]);
        $existing->assignRole('Super Administrator');

        // config(), not putenv(). The seeder reads this through config() because
        // `env()` returns null once the config is cached, which every production
        // deployment does — driving the test through putenv() would exercise a
        // path production never takes.
        config(['app.reset_bootstrap_admin' => true]);

        $this->seed(AdminUserSeeder::class);

        config(['app.reset_bootstrap_admin' => false]);

        $user = User::where('email', 'admin@vestra.com')->firstOrFail();

        $this->assertTrue(Hash::check('Admin@12345', $user->password));
        $this->assertTrue($user->mustChangePassword());
    }
}

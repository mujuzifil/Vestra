<?php

namespace Tests\Feature;

use App\Models\User;
use App\Providers\AppServiceProvider;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProductionBootstrapPasswordTest extends TestCase
{
    use RefreshDatabase;

    private function callEnforceMethod(AppServiceProvider $provider): void
    {
        $reflection = new \ReflectionMethod($provider, 'enforceBootstrapPasswordNotDefault');
        $reflection->setAccessible(true);
        $reflection->invoke($provider);
    }

    public function test_production_boot_aborts_when_default_password_is_in_use(): void
    {
        $this->seed(RolePermissionSeeder::class);

        User::create([
            'name' => 'VESTRA Administrator',
            'email' => 'admin@vestra.com',
            'password' => Hash::make('Admin@12345'),
            'is_admin' => true,
            'status' => 'active',
            'email_verified_at' => now(),
        ])->assignRole('Super Administrator');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Default bootstrap administrator password detected in production.');

        $this->app['env'] = 'production';
        $this->callEnforceMethod(new AppServiceProvider($this->app));
    }

    public function test_production_boot_passes_when_password_is_changed(): void
    {
        $this->seed(RolePermissionSeeder::class);

        User::create([
            'name' => 'VESTRA Administrator',
            'email' => 'admin@vestra.com',
            'password' => Hash::make('CustomP@ssw0rd123'),
            'is_admin' => true,
            'status' => 'active',
            'email_verified_at' => now(),
        ])->assignRole('Super Administrator');

        $this->app['env'] = 'production';

        // Should not throw.
        $this->callEnforceMethod(new AppServiceProvider($this->app));

        $this->assertTrue(true);
    }
}

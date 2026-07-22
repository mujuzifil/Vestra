<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * The shipped bootstrap password.
     *
     * Public because AppServiceProvider's production guard checks against it —
     * this exact string is the publicly-known credential that must never reach
     * production.
     */
    public const DEFAULT_PASSWORD = 'Admin@12345';

    public function run(): void
    {
        $reset = filter_var(config('app.reset_bootstrap_admin', false), FILTER_VALIDATE_BOOLEAN);

        $existing = User::where('email', 'admin@vestra.com')->first();

        // Allow the bootstrap password to be overridden via environment for production deployments.
        // The default value is intended for local and test environments only.
        $bootstrapPassword = config('app.bootstrap_admin_password') ?: self::DEFAULT_PASSWORD;

        $password = Hash::make($bootstrapPassword);
        $forcePasswordChangeAt = now();

        if (! $reset && $existing) {
            // Preserve an already-changed password.
            if (! Hash::check($bootstrapPassword, $existing->password)) {
                $password = $existing->password;
                $forcePasswordChangeAt = $existing->force_password_change_at;
            }
        }

        $user = User::updateOrCreate(
            ['email' => 'admin@vestra.com'],
            [
                'name' => 'VESTRA Administrator',
                'password' => $password,
                'status' => 'active',
                'email_verified_at' => now(),
                'force_password_change_at' => $forcePasswordChangeAt,
            ]
        );

        $user->forceFill(['is_admin' => true])->save();

        $user->assignRole('Super Administrator');
    }
}

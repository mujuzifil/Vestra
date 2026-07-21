<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    private const DEFAULT_PASSWORD = 'Admin@12345';

    public function run(): void
    {
        $reset = filter_var(env('RESET_BOOTSTRAP_ADMIN', false), FILTER_VALIDATE_BOOLEAN);

        $existing = User::where('email', 'admin@vestra.com')->first();

        $password = Hash::make(self::DEFAULT_PASSWORD);
        $forcePasswordChangeAt = now();

        if (! $reset && $existing) {
            // Preserve an already-changed password.
            if (! Hash::check(self::DEFAULT_PASSWORD, $existing->password)) {
                $password = $existing->password;
                $forcePasswordChangeAt = $existing->force_password_change_at;
            }
        }

        $user = User::updateOrCreate(
            ['email' => 'admin@vestra.com'],
            [
                'name' => 'VESTRA Administrator',
                'password' => $password,
                'is_admin' => true,
                'status' => 'active',
                'email_verified_at' => now(),
                'force_password_change_at' => $forcePasswordChangeAt,
            ]
        );

        $user->assignRole('Super Administrator');
    }
}

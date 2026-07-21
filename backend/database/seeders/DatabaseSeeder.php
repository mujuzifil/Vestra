<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            AdminUserSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            SettingSeeder::class,
        ]);

        if (filter_var(env('DEMO_DATA', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->call([
                DemoDataSeeder::class,
            ]);
        }
    }
}

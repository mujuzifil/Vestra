<?php

namespace Database\Seeders;

use App\Models\CustomerAddress;
use App\Models\User;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()
            ->count(15)
            ->create(['is_admin' => false])
            ->each(function (User $user) {
                CustomerAddress::factory()
                    ->count(rand(1, 2))
                    ->create(['user_id' => $user->id]);
            });
    }
}

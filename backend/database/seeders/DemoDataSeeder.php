<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CustomerSeeder::class,
            OrderSeeder::class,
            ReviewSeeder::class,
            ContactMessageSeeder::class,
            CustomerFeedbackSeeder::class,
            DistributorRequestSeeder::class,
        ]);
    }
}

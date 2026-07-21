<?php

namespace Database\Seeders;

use App\Models\DistributorRequest;
use Illuminate\Database\Seeder;

class DistributorRequestSeeder extends Seeder
{
    public function run(): void
    {
        DistributorRequest::factory()->count(15)->create();
    }
}

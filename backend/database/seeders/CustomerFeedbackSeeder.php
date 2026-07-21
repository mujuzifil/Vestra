<?php

namespace Database\Seeders;

use App\Models\CustomerFeedback;
use Illuminate\Database\Seeder;

class CustomerFeedbackSeeder extends Seeder
{
    public function run(): void
    {
        CustomerFeedback::factory()->count(20)->create();
    }
}

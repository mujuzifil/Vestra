<?php

namespace Database\Factories;

use App\Models\Distributor;
use App\Models\DistributorBranch;
use App\Models\DistributorServiceArea;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DistributorServiceArea>
 */
class DistributorServiceAreaFactory extends Factory
{
    protected $model = DistributorServiceArea::class;

    public function definition(): array
    {
        return [
            'distributor_id' => Distributor::factory(),
            'branch_id' => DistributorBranch::factory(),
            'region' => fake()->state(),
            'district' => fake()->city(),
            'status' => 'active',
        ];
    }
}

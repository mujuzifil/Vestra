<?php

namespace Database\Factories;

use App\Models\Distributor;
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
            'branch_id' => null,
            'region' => fake()->randomElement(['Central Region', 'Western Region', 'Eastern Region', 'Northern Region']),
            'district' => fake()->city(),
            'status' => 'covered',
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Distributor;
use App\Models\DistributorBranch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DistributorBranch>
 */
class DistributorBranchFactory extends Factory
{
    protected $model = DistributorBranch::class;

    public function definition(): array
    {
        return [
            'distributor_id' => Distributor::factory(),
            'name' => fake()->randomElement(['Head Office', 'Warehouse', 'Retail Branch', 'Distribution Center']),
            'manager_name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'country' => 'Uganda',
            'district' => fake()->city(),
            'city' => fake()->city(),
            'address' => fake()->address(),
            'status' => 'active',
            'is_default' => false,
        ];
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }
}

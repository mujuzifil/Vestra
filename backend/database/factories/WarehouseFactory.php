<?php

namespace Database\Factories;

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Warehouse>
 */
class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company() . ' Warehouse',
            'code' => strtoupper(fake()->unique()->bothify('WH-###')),
            'address' => fake()->address(),
            'manager_name' => fake()->name(),
            'manager_phone' => fake()->phoneNumber(),
            'manager_email' => fake()->companyEmail(),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}

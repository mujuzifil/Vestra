<?php

namespace Database\Factories;

use App\Models\SalesRepresentative;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SalesRepresentative>
 */
class SalesRepresentativeFactory extends Factory
{
    protected $model = SalesRepresentative::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'office' => fake()->randomElement(['Kampala HQ', 'Regional Office', 'Field Office']),
            'is_active' => true,
        ];
    }
}

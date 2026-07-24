<?php

namespace Database\Factories;

use App\Models\Distributor;
use App\Models\DistributorContact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DistributorContact>
 */
class DistributorContactFactory extends Factory
{
    protected $model = DistributorContact::class;

    public function definition(): array
    {
        return [
            'distributor_id' => Distributor::factory(),
            'name' => fake()->name(),
            'role' => fake()->randomElement(['Manager', 'Sales', 'Procurement', 'Accounts']),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'permissions_json' => ['orders', 'quotes', 'invoices'],
            'is_primary' => false,
        ];
    }

    public function primary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary' => true,
        ]);
    }
}

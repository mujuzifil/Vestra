<?php

namespace Database\Factories;

use App\Models\CustomerAddress;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerAddress>
 */
class CustomerAddressFactory extends Factory
{
    protected $model = CustomerAddress::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'label' => fake()->randomElement(['Home', 'Work', 'Other']),
            'full_name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'city' => fake()->city(),
            'region' => fake()->state(),
            'district' => fake()->citySuffix(),
            'address_line' => fake()->streetAddress(),
            'is_default' => true,
        ];
    }
}

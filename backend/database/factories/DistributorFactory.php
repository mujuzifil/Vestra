<?php

namespace Database\Factories;

use App\Enums\DistributorAccountStatus;
use App\Models\Distributor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Distributor>
 */
class DistributorFactory extends Factory
{
    protected $model = Distributor::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => DistributorAccountStatus::ACTIVE->value,
            'company_name' => fake()->company(),
            'trading_name' => fake()->company(),
            'business_type' => fake()->randomElement(['Retailer', 'Wholesaler', 'Distributor']),
            'years_in_business' => fake()->numberBetween(1, 30),
            'primary_contact_name' => fake()->name(),
            'email' => fake()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'country' => 'Uganda',
            'district' => fake()->city(),
            'city' => fake()->city(),
            'expected_monthly_volume' => fake()->randomElement(['100-500', '500-1000', '1000+']),
            'products_of_interest' => fake()->words(3, true),
            'approved_at' => now(),
        ];
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DistributorAccountStatus::SUSPENDED->value,
            'suspended_at' => now(),
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Models\DistributorRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DistributorRequest>
 */
class DistributorRequestFactory extends Factory
{
    protected $model = DistributorRequest::class;

    public function definition(): array
    {
        return [
            'company_name' => fake()->company(),
            'contact_person' => fake()->name(),
            'email' => fake()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'business_description' => fake()->paragraph(),
            'status' => fake()->randomElement(['pending', 'approved', 'rejected']),
            'created_at' => fake()->dateTimeBetween('-60 days', 'now'),
            'updated_at' => fake()->dateTimeBetween('-60 days', 'now'),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Enums\DistributorStatus;
use App\Enums\Priority;
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
            'business_type' => fake()->randomElement(['Wholesaler', 'Retailer', 'Importer', 'Distributor']),
            'years_in_operation' => fake()->numberBetween(1, 25),
            'contact_person' => fake()->name(),
            'email' => fake()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'country' => fake()->randomElement(['Uganda', 'Kenya', 'Tanzania', 'Rwanda']),
            'region' => fake()->randomElement(['Central', 'Eastern', 'Western', 'Northern']),
            'business_description' => fake()->paragraph(),
            'products_interested_in' => fake()->sentence(),
            'target_region' => fake()->randomElement(['Central', 'Eastern', 'Western', 'Northern']),
            'estimated_volume' => fake()->randomElement(['100-500 units/month', '500-1000 units/month', '1000+ units/month']),
            'existing_customer' => fake()->boolean(20),
            'previous_applications' => fake()->numberBetween(0, 2),
            'status' => fake()->randomElement(DistributorStatus::cases())->value,
            'priority' => fake()->randomElement(Priority::cases())->value,
            'created_at' => fake()->dateTimeBetween('-60 days', 'now'),
            'updated_at' => fake()->dateTimeBetween('-60 days', 'now'),
        ];
    }
}

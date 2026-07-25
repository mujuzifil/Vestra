<?php

namespace Database\Factories;

use App\Models\CustomerDeletionRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerDeletionRequest>
 */
class CustomerDeletionRequestFactory extends Factory
{
    protected $model = CustomerDeletionRequest::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'reason' => fake()->sentence(),
            'status' => 'pending',
            'requested_at' => now(),
            'processed_at' => null,
            'processed_by' => null,
        ];
    }
}

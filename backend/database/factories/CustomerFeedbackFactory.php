<?php

namespace Database\Factories;

use App\Models\CustomerFeedback;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerFeedback>
 */
class CustomerFeedbackFactory extends Factory
{
    protected $model = CustomerFeedback::class;

    public function definition(): array
    {
        return [
            'user_id' => fake()->boolean(80) ? User::factory() : null,
            'category' => fake()->randomElement(['general', 'bug', 'feature', 'complaint', 'praise']),
            'subject' => fake()->sentence(),
            'message' => fake()->paragraphs(2, true),
            'status' => fake()->randomElement(['new', 'in_progress', 'resolved']),
            'created_at' => fake()->dateTimeBetween('-60 days', 'now'),
            'updated_at' => fake()->dateTimeBetween('-60 days', 'now'),
        ];
    }
}

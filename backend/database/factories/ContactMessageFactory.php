<?php

namespace Database\Factories;

use App\Models\ContactMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactMessage>
 */
class ContactMessageFactory extends Factory
{
    protected $model = ContactMessage::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'subject' => fake()->sentence(),
            'message' => fake()->paragraphs(2, true),
            'reply' => fake()->optional(0.2)->paragraph(),
            'replied_at' => fake()->optional(0.2)->dateTimeBetween('-30 days', 'now'),
            'status' => fake()->randomElement(['new', 'in_progress', 'resolved']),
            'created_at' => fake()->dateTimeBetween('-60 days', 'now'),
            'updated_at' => fake()->dateTimeBetween('-60 days', 'now'),
        ];
    }
}

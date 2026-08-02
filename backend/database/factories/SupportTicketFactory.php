<?php

namespace Database\Factories;

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupportTicketFactory extends Factory
{
    protected $model = SupportTicket::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'reference_number' => 'ST-' . now()->format('Ymd') . '-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
            'subject' => $this->faker->sentence(),
            'enquiry_type' => 'general',
            'message' => $this->faker->paragraph(),
            'status' => 'open',
            'priority' => 'medium',
        ];
    }
}

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
            'reference_number' => 'ST-'.now()->format('Ymd').'-'.strtoupper(substr(uniqid(), -4)),
            'subject' => $this->faker->sentence(),
            'enquiry_type' => 'general',
            'message' => $this->faker->paragraph(),
            'status' => 'open',
            'priority' => 'medium',
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\QuoteRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuoteRequestFactory extends Factory
{
    protected $model = QuoteRequest::class;

    public function definition(): array
    {
        return [
            'reference_number' => 'QR-' . now()->format('Ymd') . '-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
            'full_name' => $this->faker->name(),
            'company_name' => $this->faker->company(),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'district' => $this->faker->city(),
            'city' => $this->faker->city(),
            'address' => $this->faker->address(),
            'preferred_delivery_date' => now()->addWeek()->format('Y-m-d'),
            'delivery_location' => $this->faker->address(),
            'status' => 'pending',
            'source' => 'website',
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'admin_notes' => null,
            'assigned_to' => null,
        ];
    }
}

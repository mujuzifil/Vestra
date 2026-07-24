<?php

namespace Database\Factories;

use App\Enums\QuotationStatus;
use App\Models\Distributor;
use App\Models\QuotationRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuotationRequest>
 */
class QuotationRequestFactory extends Factory
{
    protected $model = QuotationRequest::class;

    public function definition(): array
    {
        return [
            'distributor_id' => Distributor::factory(),
            'reference_number' => 'QT-' . strtoupper(fake()->bothify('??####')),
            'status' => QuotationStatus::DRAFT->value,
            'notes' => fake()->optional(0.5)->sentence(),
            'admin_notes' => null,
            'subtotal' => 0,
            'tax_amount' => 0,
            'total_amount' => 0,
        ];
    }

    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => QuotationStatus::SUBMITTED->value,
            'submitted_at' => now(),
        ]);
    }

    public function quoted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => QuotationStatus::QUOTED->value,
            'submitted_at' => now()->subDay(),
            'quoted_at' => now(),
            'expires_at' => now()->addDays(14),
        ]);
    }
}

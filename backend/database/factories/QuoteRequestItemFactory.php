<?php

namespace Database\Factories;

use App\Models\QuoteRequest;
use App\Models\QuoteRequestItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuoteRequestItemFactory extends Factory
{
    protected $model = QuoteRequestItem::class;

    public function definition(): array
    {
        return [
            'quote_request_id' => QuoteRequest::factory(),
            'product_id' => null,
            'product_name' => $this->faker->words(3, true),
            'package_size' => $this->faker->randomElement(['500ml', '1L', '5L', '20L']),
            'quantity' => $this->faker->numberBetween(1, 100),
            'notes' => null,
        ];
    }
}

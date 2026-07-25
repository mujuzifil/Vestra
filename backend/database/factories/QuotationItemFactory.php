<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\QuotationItem;
use App\Models\QuotationRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuotationItem>
 */
class QuotationItemFactory extends Factory
{
    protected $model = QuotationItem::class;

    public function definition(): array
    {
        $quantity = fake()->numberBetween(10, 100);
        $unitPrice = fake()->randomFloat(2, 1000, 10000);

        return [
            'quotation_request_id' => QuotationRequest::factory(),
            'product_id' => Product::factory(),
            'product_name' => fake()->words(3, true),
            'product_sku' => fake()->unique()->bothify('SKU-####'),
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'line_total' => round($unitPrice * $quantity, 2),
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }
}

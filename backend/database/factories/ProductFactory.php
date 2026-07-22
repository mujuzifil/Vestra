<?php

namespace Database\Factories;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'name' => fake()->unique()->words(3, true),
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'features' => fake()->words(3),
            'benefits' => fake()->sentences(3),
            'specifications' => ['Volume' => '1 Litre'],
            'sku' => fake()->unique()->bothify('SKU-####'),
            'price' => fake()->randomFloat(2, 10, 500),
            'featured' => fake()->boolean(20),
            'status' => ProductStatus::ACTIVE->value,
            'stock_quantity' => fake()->numberBetween(10, 200),
            'meta_title' => fake()->words(4, true),
            'meta_description' => fake()->sentence(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProductStatus::INACTIVE->value,
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => 0,
            'status' => ProductStatus::OUT_OF_STOCK->value,
        ]);
    }

    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => fake()->numberBetween(1, 5),
        ]);
    }
}

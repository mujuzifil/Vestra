<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\RecentlyViewedProduct;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecentlyViewedProduct>
 */
class RecentlyViewedProductFactory extends Factory
{
    protected $model = RecentlyViewedProduct::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'viewed_at' => now(),
        ];
    }
}

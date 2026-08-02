<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\SavedItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SavedItemFactory extends Factory
{
    protected $model = SavedItem::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
        ];
    }
}

<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Laundry Detergents',
                'slug' => 'laundry-detergents',
                'description' => 'Professional-grade laundry detergents for commercial and home use.',
                'sort_order' => 1,
                'status' => 'active',
            ],
            [
                'name' => 'Fabric Care',
                'slug' => 'fabric-care',
                'description' => 'Specialized solutions for delicate and premium fabrics.',
                'sort_order' => 2,
                'status' => 'active',
            ],
            [
                'name' => 'Stain Removal',
                'slug' => 'stain-removal',
                'description' => 'Powerful stain removers for the toughest marks.',
                'sort_order' => 3,
                'status' => 'active',
            ],
            [
                'name' => 'Garment Finishing',
                'slug' => 'garment-finishing',
                'description' => 'Finishing products for crisp, professional garment care.',
                'sort_order' => 4,
                'status' => 'active',
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['slug' => $category['slug']], $category);
        }
    }
}

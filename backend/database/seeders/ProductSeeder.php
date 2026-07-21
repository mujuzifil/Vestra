<?php

namespace Database\Seeders;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'EcoSuit Cleaner',
                'slug' => 'ecosuit-cleaner',
                'short_description' => 'Gentle, eco-friendly cleaner designed for premium suits and formal wear.',
                'description' => 'EcoSuit Cleaner is formulated with plant-based surfactants that lift dirt and odors from delicate suit fabrics without causing shrinkage or color loss. Ideal for dry-clean-only garments between professional cleanings.',
                'features' => ['Anti-Aging Formula', 'Deep Clean', 'Fabric Safe'],
                'benefits' => [
                    'Extends the life of formal wear',
                    'Maintains creases and structure',
                    'Reduces dry-cleaning frequency',
                ],
                'specifications' => ['Volume' => '5 Litres', 'pH' => 'Neutral', 'Usage' => 'Machine Wash', 'Fragrance' => 'Classic Musk'],
                'sku' => 'ESC-001',
                'price' => 24.99,
                'featured' => true,
                'status' => ProductStatus::ACTIVE,
                'stock_quantity' => 150,
                'meta_title' => 'EcoSuit Cleaner | VESTRA',
                'category_slug' => 'fabric-care',
            ],
            [
                'name' => 'Heavy Duty Detergent',
                'slug' => 'heavy-duty-detergent',
                'short_description' => 'Industrial-strength detergent for commercial laundry operations.',
                'description' => 'Heavy Duty Detergent powers through grease, oil, and heavy soil in commercial laundry environments. Concentrated formula delivers exceptional cleaning at lower doses.',
                'features' => ['10x Enzyme Power', 'Deep Clean & Fiber Care', 'Long Lasting Fresh Fragrance'],
                'benefits' => [
                    'Removes stubborn stains in a single wash',
                    'Protects heavy fabric fibers from damage',
                    'Ideal for commercial laundries and households',
                ],
                'specifications' => ['Volume' => '5 Litres', 'pH' => 'Neutral', 'Usage' => 'Machine & Hand Wash', 'Fragrance' => 'Fresh Linen'],
                'sku' => 'HDD-002',
                'price' => 39.99,
                'featured' => true,
                'status' => ProductStatus::ACTIVE,
                'stock_quantity' => 200,
                'meta_title' => 'Heavy Duty Detergent | VESTRA',
                'category_slug' => 'laundry-detergents',
            ],
            [
                'name' => 'Silk Care',
                'slug' => 'silk-care',
                'short_description' => 'Delicate wash formulated specifically for silk and fine fabrics.',
                'description' => 'Silk Care preserves the natural luster and softness of silk, satin, and other delicate materials. pH-balanced and free from harsh enzymes.',
                'features' => ['Gentle on Delicates', 'Color Protection', 'pH Balanced'],
                'benefits' => [
                    'Preserves silk texture and natural sheen',
                    'Prevents color bleeding and fading',
                    'Safe for lingerie and fine garments',
                ],
                'specifications' => ['Volume' => '2 Litres', 'pH' => '6.5 - 7.0', 'Usage' => 'Hand Wash / Delicate Cycle', 'Fragrance' => 'Lavender'],
                'sku' => 'SC-003',
                'price' => 19.99,
                'featured' => true,
                'status' => ProductStatus::ACTIVE,
                'stock_quantity' => 120,
                'meta_title' => 'Silk Care | VESTRA',
                'category_slug' => 'fabric-care',
            ],
            [
                'name' => 'Stain Pro',
                'slug' => 'stain-pro',
                'short_description' => 'Professional stain remover for food, wine, grease, and ink.',
                'description' => 'Stain Pro targets the toughest stains with a multi-enzyme formula that breaks down proteins, oils, and pigments. Safe for most colorfast fabrics.',
                'features' => ['Targeted Action', 'Color Safe', 'Fast Acting'],
                'benefits' => [
                    'Pre-treats tough stains before washing',
                    'Safe on colored and white fabrics',
                    'Works in minutes',
                ],
                'specifications' => ['Volume' => '500 ml', 'pH' => 'Neutral', 'Usage' => 'Direct Application', 'Fragrance' => 'Unscented'],
                'sku' => 'SP-004',
                'price' => 14.99,
                'featured' => true,
                'status' => ProductStatus::ACTIVE,
                'stock_quantity' => 180,
                'meta_title' => 'Stain Pro | VESTRA',
                'category_slug' => 'stain-removal',
            ],
            [
                'name' => 'Wool & Delicate Fabric Wash',
                'slug' => 'wool-delicate-fabric-wash',
                'short_description' => 'Mild detergent for wool, cashmere, and delicate knitwear.',
                'description' => 'Wool & Delicate Fabric Wash cleans gently while protecting fibers from felting and stretching. Leaves garments soft, fresh, and residue-free.',
                'features' => ['Wool Safe', 'Prevents Shrinkage', 'Softness Retention'],
                'benefits' => [
                    'Maintains wool softness and shape',
                    'Prevents felting and shrinkage',
                    'Gentle enough for baby clothes',
                ],
                'specifications' => ['Volume' => '2 Litres', 'pH' => '6.5 - 7.0', 'Usage' => 'Hand Wash / Delicate Cycle', 'Fragrance' => 'Mild Fresh'],
                'sku' => 'WDW-005',
                'price' => 22.99,
                'featured' => false,
                'status' => ProductStatus::ACTIVE,
                'stock_quantity' => 5,
                'meta_title' => 'Wool & Delicate Fabric Wash | VESTRA',
                'category_slug' => 'fabric-care',
            ],
            [
                'name' => 'Pro Finish Garment Spray',
                'slug' => 'pro-finish-garment-spray',
                'short_description' => 'Wrinkle-release spray for a crisp, pressed look.',
                'description' => 'Pro Finish Garment Spray relaxes wrinkles and refreshes fabrics between washes. Perfect for business travelers and last-minute touch-ups.',
                'features' => ['Crisp Structure', 'Anti-Flake Technology', 'Fast Dry Formula'],
                'benefits' => [
                    'Delivers professional press finish',
                    'Dries quickly without residue',
                    'Keeps garments crisp all day',
                ],
                'specifications' => ['Volume' => '500 ml', 'pH' => 'Neutral', 'Usage' => 'Spray & Iron', 'Fragrance' => 'Fresh'],
                'sku' => 'PFG-006',
                'price' => 16.99,
                'featured' => true,
                'status' => ProductStatus::ACTIVE,
                'stock_quantity' => 140,
                'meta_title' => 'Pro Finish Garment Spray | VESTRA',
                'category_slug' => 'garment-finishing',
            ],
        ];

        foreach ($products as $data) {
            $categorySlug = $data['category_slug'];
            unset($data['category_slug']);

            $category = Category::query()->where('slug', $categorySlug)->first();

            if ($category) {
                $data['category_id'] = $category->id;
                $product = Product::updateOrCreate(['slug' => $data['slug']], $data);

                $imagePath = "products/{$product->slug}.png";

                if (! Storage::disk('public')->exists($imagePath)) {
                    throw new RuntimeException(
                        "Product image missing for [{$product->name}]. Expected file: storage/app/public/{$imagePath}"
                    );
                }

                ProductImage::updateOrCreate(
                    ['product_id' => $product->id, 'sort_order' => 1],
                    [
                        'product_id' => $product->id,
                        'image' => $imagePath,
                        'alt_text' => $product->name,
                        'sort_order' => 1,
                    ]
                );
            }
        }
    }
}

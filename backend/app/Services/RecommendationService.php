<?php

namespace App\Services;

use App\Enums\ProductStatus;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RecommendationService
{
    public function relatedProducts(Product $product, int $limit = 4): Collection
    {
        return Product::query()
            ->with(['category', 'images'])
            ->where('status', ProductStatus::ACTIVE)
            ->where('id', '!=', $product->id)
            ->where(function ($query) use ($product) {
                $query->where('category_id', $product->category_id)
                      ->orWhereJsonContains('tags', $product->tags ?? []);
            })
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    public function frequentlyBoughtTogether(Product $product, int $limit = 4): Collection
    {
        $relatedIds = OrderItem::query()
            ->select('product_id', DB::raw('COUNT(*) as occurrences'))
            ->whereIn('order_id', function ($query) use ($product) {
                $query->select('order_id')
                      ->from('order_items')
                      ->where('product_id', $product->id);
            })
            ->where('product_id', '!=', $product->id)
            ->groupBy('product_id')
            ->orderByDesc('occurrences')
            ->limit($limit)
            ->pluck('product_id');

        if ($relatedIds->isEmpty()) {
            return $this->relatedProducts($product, $limit);
        }

        return Product::query()
            ->with(['category', 'images'])
            ->where('status', ProductStatus::ACTIVE)
            ->whereIn('id', $relatedIds)
            ->orderByRaw('FIELD(id, ' . $relatedIds->implode(',') . ')')
            ->limit($limit)
            ->get();
    }

    public function bestSellers(int $limit = 6): Collection
    {
        $bestSellerIds = OrderItem::query()
            ->select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->pluck('product_id');

        if ($bestSellerIds->isEmpty()) {
            return $this->newArrivals($limit);
        }

        return Product::query()
            ->with(['category', 'images'])
            ->where('status', ProductStatus::ACTIVE)
            ->whereIn('id', $bestSellerIds)
            ->orderByRaw('FIELD(id, ' . $bestSellerIds->implode(',') . ')')
            ->limit($limit)
            ->get();
    }

    public function newArrivals(int $limit = 6): Collection
    {
        return Product::query()
            ->with(['category', 'images'])
            ->where('status', ProductStatus::ACTIVE)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function trending(int $limit = 6): Collection
    {
        $trendingIds = OrderItem::query()
            ->select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->whereHas('order', fn ($q) => $q->where('created_at', '>=', now()->subDays(30)))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->pluck('product_id');

        if ($trendingIds->isEmpty()) {
            return $this->newArrivals($limit);
        }

        return Product::query()
            ->with(['category', 'images'])
            ->where('status', ProductStatus::ACTIVE)
            ->whereIn('id', $trendingIds)
            ->orderByRaw('FIELD(id, ' . $trendingIds->implode(',') . ')')
            ->limit($limit)
            ->get();
    }

    public function recentlyViewed(?int $userId, int $limit = 6): Collection
    {
        if (! $userId) {
            return $this->trending($limit);
        }

        $ids = \App\Models\RecentlyViewedProduct::query()
            ->where('user_id', $userId)
            ->orderByDesc('viewed_at')
            ->limit($limit)
            ->pluck('product_id');

        if ($ids->isEmpty()) {
            return $this->trending($limit);
        }

        return Product::query()
            ->with(['category', 'images'])
            ->where('status', ProductStatus::ACTIVE)
            ->whereIn('id', $ids)
            ->orderByRaw('FIELD(id, ' . $ids->implode(',') . ')')
            ->limit($limit)
            ->get();
    }
}

<?php

namespace App\Services\Catalog;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Invalidates catalog-related caches and notifies the public website
 * so Category/Product changes are visible without manual cache clearing.
 */
class CatalogSyncService
{
    public function syncCategories(?int $categoryId = null): void
    {
        $this->forgetSharedCaches();
        $this->notifyFrontend([
            'type' => 'categories',
            'category_id' => $categoryId,
            'paths' => ['/products', '/'],
            'tags' => ['categories', 'products'],
        ]);
    }

    public function syncProducts(?int $productId = null, ?int $categoryId = null): void
    {
        $this->forgetSharedCaches();
        $this->notifyFrontend([
            'type' => 'products',
            'product_id' => $productId,
            'category_id' => $categoryId,
            'paths' => ['/products', '/'],
            'tags' => ['products', 'categories'],
        ]);
    }

    private function forgetSharedCaches(): void
    {
        Cache::forget('admin.products.low_stock_count');
        Cache::forget('catalog.categories.active');
        Cache::forget('catalog.products.featured');

        for ($limit = 1; $limit <= 24; $limit++) {
            Cache::forget("catalog.products.featured.{$limit}");
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function notifyFrontend(array $payload): void
    {
        $url = config('services.frontend.revalidate_url');
        $secret = config('services.frontend.revalidate_secret');

        if (! filled($url) || ! filled($secret)) {
            return;
        }

        try {
            Http::timeout(3)
                ->withHeaders(['x-revalidate-secret' => $secret])
                ->post($url, $payload);
        } catch (\Throwable $e) {
            Log::warning('Catalog frontend revalidation failed.', [
                'message' => $e->getMessage(),
                'payload' => $payload,
            ]);
        }
    }
}

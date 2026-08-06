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

    public function syncBlog(?int $postId = null, ?string $slug = null): void
    {
        Cache::forget('blog.categories.active');
        Cache::forget('blog.tags.active');
        Cache::forget('blog.posts.featured');

        $paths = ['/blog', '/'];
        if (filled($slug)) {
            $paths[] = '/blog/'.$slug;
        }

        $this->notifyFrontend([
            'type' => 'blog',
            'post_id' => $postId,
            'slug' => $slug,
            'paths' => $paths,
            'tags' => ['blog', 'blog-posts'],
        ]);
    }

    public function syncDistributors(?int $distributorId = null): void
    {
        Cache::forget('catalog.distributors.active');
        Cache::forget('catalog.distributors.stats');
        Cache::forget('catalog.distributors.coverage');

        $this->notifyFrontend([
            'type' => 'distributors',
            'distributor_id' => $distributorId,
            'paths' => ['/where-to-buy', '/'],
            'tags' => ['distributors', 'where-to-buy'],
        ]);
    }

    public function syncMedia(?\App\Models\MediaAsset $asset = null): void
    {
        Cache::forget('media.assets.index');

        if ($asset === null) {
            $this->notifyFrontend([
                'type' => 'media',
                'paths' => ['/', '/products', '/blog'],
                'tags' => ['products', 'categories', 'blog', 'blog-posts', 'media'],
            ]);

            return;
        }

        $asset->loadMissing('usages');

        $productIds = [];
        $blogIds = [];
        $needsHome = false;
        $needsProducts = false;
        $needsBlog = false;

        foreach ($asset->usages as $usage) {
            if ($usage->usable_type === \App\Models\Product::class) {
                $productIds[] = (int) $usage->usable_id;
                $needsProducts = true;
            }
            if ($usage->usable_type === \App\Models\BlogPost::class) {
                $blogIds[] = (int) $usage->usable_id;
                $needsBlog = true;
            }
            $context = $usage->context?->value ?? (string) $usage->context;
            if (in_array($context, ['homepage', 'marketing'], true)) {
                $needsHome = true;
            }
        }

        if ($needsProducts || $productIds !== []) {
            foreach (array_unique($productIds) ?: [null] as $productId) {
                $this->syncProducts($productId);
            }
        }

        if ($needsBlog || $blogIds !== []) {
            $posts = \App\Models\BlogPost::query()
                ->whereIn('id', array_unique($blogIds) ?: [-1])
                ->get(['id', 'slug']);

            if ($posts->isEmpty()) {
                $this->syncBlog();
            } else {
                foreach ($posts as $post) {
                    $this->syncBlog($post->id, $post->slug);
                }
            }
        }

        if ($needsHome || (! $needsProducts && ! $needsBlog)) {
            $this->notifyFrontend([
                'type' => 'media',
                'media_asset_id' => $asset->id,
                'paths' => ['/', '/products', '/blog'],
                'tags' => ['products', 'blog', 'media'],
            ]);
        }
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

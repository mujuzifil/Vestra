<?php

namespace App\Console\Commands;

use App\Enums\MediaAssetStatus;
use App\Enums\MediaAssetType;
use App\Enums\MediaUsageContext;
use App\Models\BlogPost;
use App\Models\MediaAsset;
use App\Models\MediaAssetUsage;
use App\Models\ProductImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportLegacyMediaAssetsCommand extends Command
{
    protected $signature = 'media:import-legacy';

    protected $description = 'Import existing product and blog image paths into the Media Library';

    public function handle(): int
    {
        $imported = 0;
        $linked = 0;

        ProductImage::query()
            ->whereNull('media_asset_id')
            ->whereNotNull('image')
            ->where('image', '!=', '')
            ->orderBy('id')
            ->each(function (ProductImage $image) use (&$imported, &$linked): void {
                $asset = $this->findOrCreateFromPath((string) $image->image, $image->alt_text);
                if ($asset === null) {
                    return;
                }

                if ($asset->wasRecentlyCreated) {
                    $imported++;
                }

                $image->update(['media_asset_id' => $asset->id]);
                $this->attachUsage(
                    $asset,
                    ProductImage::class,
                    $image->id,
                    $image->sort_order === 0
                        ? MediaUsageContext::PRODUCT_PRIMARY
                        : MediaUsageContext::PRODUCT_GALLERY,
                    (int) $image->sort_order
                );
                // Also track against Product for usage listing by product name
                if ($image->product_id) {
                    $this->attachUsage(
                        $asset,
                        \App\Models\Product::class,
                        $image->product_id,
                        $image->sort_order === 0
                            ? MediaUsageContext::PRODUCT_PRIMARY
                            : MediaUsageContext::PRODUCT_GALLERY,
                        (int) $image->sort_order
                    );
                }
                $linked++;
            });

        BlogPost::query()
            ->whereNull('featured_media_asset_id')
            ->whereNotNull('featured_image')
            ->where('featured_image', '!=', '')
            ->orderBy('id')
            ->each(function (BlogPost $post) use (&$imported, &$linked): void {
                $asset = $this->findOrCreateFromPath((string) $post->featured_image);
                if ($asset === null) {
                    return;
                }

                if ($asset->wasRecentlyCreated) {
                    $imported++;
                }

                $post->update(['featured_media_asset_id' => $asset->id]);
                $this->attachUsage(
                    $asset,
                    BlogPost::class,
                    $post->id,
                    MediaUsageContext::BLOG_FEATURED
                );
                $linked++;
            });

        BlogPost::query()
            ->whereNotNull('gallery')
            ->orderBy('id')
            ->each(function (BlogPost $post) use (&$imported, &$linked): void {
                $gallery = is_array($post->gallery) ? $post->gallery : [];
                foreach ($gallery as $index => $path) {
                    if (! is_string($path) || $path === '') {
                        continue;
                    }

                    $asset = $this->findOrCreateFromPath($path);
                    if ($asset === null) {
                        continue;
                    }

                    if ($asset->wasRecentlyCreated) {
                        $imported++;
                    }

                    $this->attachUsage(
                        $asset,
                        BlogPost::class,
                        $post->id,
                        MediaUsageContext::BLOG_GALLERY,
                        (int) $index
                    );
                    $linked++;
                }
            });

        $this->info("Imported {$imported} new media asset(s); linked {$linked} reference(s).");

        return self::SUCCESS;
    }

    private function findOrCreateFromPath(string $path, ?string $alt = null): ?MediaAsset
    {
        $path = ltrim($path, '/');
        $existing = MediaAsset::query()->where('path', $path)->first();
        if ($existing) {
            return $existing;
        }

        $disk = Storage::disk('public');
        $checksum = null;
        $size = 0;
        $width = null;
        $height = null;
        $mime = null;

        if ($disk->exists($path)) {
            try {
                $contents = $disk->get($path);
                $checksum = hash('sha256', $contents);
                $size = strlen($contents);
                $mime = $disk->mimeType($path) ?: null;

                if ($checksum) {
                    $byHash = MediaAsset::query()
                        ->where('checksum', $checksum)
                        ->where('status', MediaAssetStatus::ACTIVE->value)
                        ->first();
                    if ($byHash) {
                        return $byHash;
                    }
                }

                if (is_string($mime) && str_starts_with($mime, 'image/')) {
                    $full = $disk->path($path);
                    $info = @getimagesize($full);
                    if (is_array($info)) {
                        $width = $info[0] ?? null;
                        $height = $info[1] ?? null;
                    }
                }
            } catch (\Throwable) {
                // Continue with metadata we have.
            }
        }

        $fileName = basename($path);
        $extension = strtolower((string) pathinfo($fileName, PATHINFO_EXTENSION));
        $mime ??= $this->guessMime($extension);

        return MediaAsset::query()->create([
            'uuid' => (string) Str::uuid(),
            'disk' => 'public',
            'path' => $path,
            'file_name' => $fileName,
            'original_file_name' => $fileName,
            'mime_type' => $mime,
            'media_type' => $this->resolveType($mime)->value,
            'size_bytes' => $size,
            'width' => $width,
            'height' => $height,
            'checksum' => $checksum,
            'alt_text' => $alt,
            'status' => MediaAssetStatus::ACTIVE->value,
            'tags' => [],
        ]);
    }

    private function attachUsage(
        MediaAsset $asset,
        string $usableType,
        int $usableId,
        MediaUsageContext $context,
        int $sortOrder = 0
    ): void {
        MediaAssetUsage::query()->firstOrCreate(
            [
                'media_asset_id' => $asset->id,
                'usable_type' => $usableType,
                'usable_id' => $usableId,
                'context' => $context->value,
            ],
            ['sort_order' => $sortOrder]
        );
    }

    private function resolveType(?string $mime): MediaAssetType
    {
        $mime = $mime ?? '';

        return match (true) {
            str_starts_with($mime, 'image/') => MediaAssetType::IMAGE,
            str_starts_with($mime, 'video/') => MediaAssetType::VIDEO,
            in_array($mime, [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'text/plain',
                'text/csv',
            ], true) => MediaAssetType::DOCUMENT,
            default => MediaAssetType::OTHER,
        };
    }

    private function guessMime(string $extension): ?string
    {
        return match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'pdf' => 'application/pdf',
            'mp4' => 'video/mp4',
            default => null,
        };
    }
}

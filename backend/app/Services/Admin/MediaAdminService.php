<?php

namespace App\Services\Admin;

use App\Filament\Resources\BlogPostResource;
use App\Filament\Resources\ProductResource;
use App\Filament\Resources\SettingResource;
use App\Models\BlogPost;
use App\Models\ProductImage;
use App\Models\Setting;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

/**
 * Aggregates read-only media metadata from existing upload sources
 * (blog featured images, blog gallery, product images, Spatie media)
 * into a single unified, filterable, sortable, paginated collection.
 *
 * No new schema is introduced — this service only reads existing tables.
 */
class MediaAdminService
{
    public const PER_PAGE = 24;

    /** @var Collection<int, array<string, mixed>>|null */
    private ?Collection $itemsCache = null;

    /** @var array<string, int|null> */
    private array $sizeCache = [];

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters, string $sort, string $direction, int $perPage = self::PER_PAGE, int $page = 1): LengthAwarePaginator
    {
        $collection = $this->sortCollection($this->filteredCollection($filters), $sort, $direction);
        $total = $collection->count();

        $items = $total > 0
            ? $collection->slice(($page - 1) * $perPage, $perPage)->values()
            : collect();

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath()]
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getKpiCards(): array
    {
        $all = $this->allItems();

        $total = $all->count();
        $images = $all->where('type', 'image')->count();
        $documents = $all->where('type', 'document')->count();
        $videos = $all->where('type', 'video')->count();
        $storageBytes = (int) $all->sum(fn (array $item): int => (int) ($item['size_bytes'] ?? 0));

        return [
            $this->buildCard('Total Files', number_format($total), 'heroicon-o-folder', 'primary'),
            $this->buildCard('Images', number_format($images), 'heroicon-o-photo', 'info'),
            $this->buildCard('Documents', number_format($documents), 'heroicon-o-document-text', 'warning'),
            $this->buildCard('Videos', number_format($videos), 'heroicon-o-film', 'success'),
            $this->buildCard('Storage Used', $this->formatBytes($storageBytes), 'heroicon-o-server', 'gray'),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getDetail(string $id): ?array
    {
        return $this->allItems()->firstWhere('id', $id);
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilterOptions(): array
    {
        return [
            'types' => [
                ['value' => 'image', 'label' => 'Images'],
                ['value' => 'document', 'label' => 'Documents'],
                ['value' => 'video', 'label' => 'Videos'],
                ['value' => 'other', 'label' => 'Other'],
            ],
            'sources' => [
                ['value' => 'blog', 'label' => 'Blog'],
                ['value' => 'product', 'label' => 'Product'],
                ['value' => 'settings', 'label' => 'Settings'],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    public function exportRows(array $filters = []): array
    {
        return $this->sortCollection($this->filteredCollection($filters), 'created_at', 'desc')
            ->map(fn (array $item): array => [
                'name' => $item['name'],
                'type' => ucfirst($item['type']),
                'source' => ucfirst($item['source']),
                'owner' => $item['owner_label'],
                'size' => $item['size_bytes'] !== null ? $this->formatBytes((int) $item['size_bytes']) : '—',
                'mime' => $item['mime'] ?? '—',
                'created_at' => $item['created_at']?->format('Y-m-d H:i:s'),
            ])
            ->values()
            ->toArray();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function filteredCollection(array $filters): Collection
    {
        $collection = $this->allItems();

        if (filled($filters['search'] ?? null)) {
            $term = mb_strtolower((string) $filters['search']);
            $collection = $collection->filter(
                fn (array $item): bool => str_contains(mb_strtolower($item['name']), $term)
                    || str_contains(mb_strtolower($item['owner_label']), $term)
            );
        }

        $types = array_filter((array) ($filters['type'] ?? []));
        if ($types !== []) {
            $collection = $collection->filter(fn (array $item): bool => in_array($item['type'], $types, true));
        }

        $sources = array_filter((array) ($filters['source'] ?? []));
        if ($sources !== []) {
            $collection = $collection->filter(fn (array $item): bool => in_array($item['source'], $sources, true));
        }

        if (filled($filters['date_from'] ?? null)) {
            $from = Carbon::parse($filters['date_from'])->startOfDay();
            $collection = $collection->filter(
                fn (array $item): bool => $item['created_at'] instanceof CarbonInterface && $item['created_at']->gte($from)
            );
        }

        if (filled($filters['date_until'] ?? null)) {
            $until = Carbon::parse($filters['date_until'])->endOfDay();
            $collection = $collection->filter(
                fn (array $item): bool => $item['created_at'] instanceof CarbonInterface && $item['created_at']->lte($until)
            );
        }

        return $collection->values();
    }

    private function sortCollection(Collection $collection, string $sort, string $direction): Collection
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        $key = match ($sort) {
            'name' => 'name',
            'size' => 'size_bytes',
            'source' => 'source',
            'type' => 'type',
            default => 'created_at',
        };

        $sorted = $direction === 'asc' ? $collection->sortBy($key) : $collection->sortByDesc($key);

        return $sorted->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function allItems(): Collection
    {
        if ($this->itemsCache !== null) {
            return $this->itemsCache;
        }

        $items = collect()
            ->merge($this->collectBlogFeaturedImages())
            ->merge($this->collectBlogGalleryImages())
            ->merge($this->collectProductImages())
            ->merge($this->collectSpatieMedia())
            ->values();

        return $this->itemsCache = $items;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function collectBlogFeaturedImages(): Collection
    {
        return BlogPost::query()
            ->whereNotNull('featured_image')
            ->where('featured_image', '!=', '')
            ->select(['id', 'title', 'featured_image', 'created_at'])
            ->get()
            ->map(fn (BlogPost $post): array => $this->makeStorageItem(
                id: 'blog-featured-'.$post->id,
                source: 'blog',
                path: (string) $post->featured_image,
                createdAt: $post->created_at,
                ownerLabel: $post->title.' (Featured Image)',
                ownerUrl: BlogPostResource::getUrl('edit', ['record' => $post->id]),
            ));
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function collectBlogGalleryImages(): Collection
    {
        $posts = BlogPost::query()
            ->whereNotNull('gallery')
            ->select(['id', 'title', 'gallery', 'created_at'])
            ->get();

        $items = collect();

        foreach ($posts as $post) {
            $gallery = is_array($post->gallery) ? $post->gallery : [];

            foreach ($gallery as $index => $path) {
                if (! is_string($path) || $path === '') {
                    continue;
                }

                $items->push($this->makeStorageItem(
                    id: "blog-gallery-{$post->id}-{$index}",
                    source: 'blog',
                    path: $path,
                    createdAt: $post->created_at,
                    ownerLabel: $post->title.' (Gallery)',
                    ownerUrl: BlogPostResource::getUrl('edit', ['record' => $post->id]),
                ));
            }
        }

        return $items;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function collectProductImages(): Collection
    {
        return ProductImage::query()
            ->with('product:id,name')
            ->select(['id', 'product_id', 'image', 'created_at'])
            ->get()
            ->map(fn (ProductImage $image): array => $this->makeStorageItem(
                id: 'product-image-'.$image->id,
                source: 'product',
                path: (string) $image->image,
                createdAt: $image->created_at,
                ownerLabel: $image->product?->name ?? 'Unassigned product',
                ownerUrl: $image->product_id ? ProductResource::getUrl('edit', ['record' => $image->product_id]) : null,
            ));
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function collectSpatieMedia(): Collection
    {
        return Media::query()
            ->with('model')
            ->get()
            ->map(function (Media $media): array {
                $mime = $media->mime_type;

                return [
                    'id' => 'spatie-media-'.$media->id,
                    'source' => $media->model_type === Setting::class ? 'settings' : 'spatie',
                    'name' => $media->file_name,
                    'url' => $this->resolveMediaUrl($media),
                    'mime' => $mime,
                    'type' => $this->resolveTypeFromMime($mime),
                    'size_bytes' => (int) $media->size,
                    'created_at' => $media->created_at,
                    'owner_label' => $this->resolveMediaOwnerLabel($media),
                    'owner_url' => $this->resolveMediaOwnerUrl($media),
                    'path' => $media->id.'/'.$media->file_name,
                ];
            });
    }

    /**
     * @return array<string, mixed>
     */
    private function makeStorageItem(string $id, string $source, string $path, ?CarbonInterface $createdAt, string $ownerLabel, ?string $ownerUrl): array
    {
        $extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
        $mime = $this->guessMimeFromExtension($extension);

        return [
            'id' => $id,
            'source' => $source,
            'name' => basename($path),
            'url' => $this->resolveStorageUrl($path),
            'mime' => $mime,
            'type' => $this->resolveTypeFromMime($mime),
            'size_bytes' => $this->resolveStorageSize($path),
            'created_at' => $createdAt,
            'owner_label' => $ownerLabel,
            'owner_url' => $ownerUrl,
            'path' => $path,
        ];
    }

    private function resolveStorageUrl(string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset('storage/'.ltrim($path, '/'));
    }

    private function resolveStorageSize(string $path): ?int
    {
        if (! filled($path)) {
            return null;
        }

        if (array_key_exists($path, $this->sizeCache)) {
            return $this->sizeCache[$path];
        }

        try {
            if (Storage::disk('public')->exists($path)) {
                return $this->sizeCache[$path] = Storage::disk('public')->size($path);
            }
        } catch (Throwable) {
            // File unreadable or disk misconfigured — size stays unknown.
        }

        return $this->sizeCache[$path] = null;
    }

    private function resolveMediaUrl(Media $media): ?string
    {
        try {
            return $media->getUrl();
        } catch (Throwable) {
            return null;
        }
    }

    private function resolveMediaOwnerLabel(Media $media): string
    {
        $model = $media->model;

        if ($model === null) {
            return class_basename($media->model_type).' #'.$media->model_id;
        }

        return $model->label
            ?? $model->name
            ?? $model->title
            ?? $model->key
            ?? class_basename($media->model_type).' #'.$media->model_id;
    }

    private function resolveMediaOwnerUrl(Media $media): ?string
    {
        if ($media->model_type === Setting::class) {
            return SettingResource::getUrl('edit', ['record' => $media->model_id]);
        }

        return null;
    }

    private function guessMimeFromExtension(string $extension): ?string
    {
        return match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'bmp' => 'image/bmp',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'csv' => 'text/csv',
            'txt' => 'text/plain',
            'mp4' => 'video/mp4',
            'mov' => 'video/quicktime',
            'avi' => 'video/x-msvideo',
            'webm' => 'video/webm',
            default => null,
        };
    }

    private function resolveTypeFromMime(?string $mime): string
    {
        $mime = $mime ?? '';

        $documentMimes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/csv',
            'text/plain',
        ];

        return match (true) {
            str_starts_with($mime, 'image/') => 'image',
            str_starts_with($mime, 'video/') => 'video',
            in_array($mime, $documentMimes, true) => 'document',
            default => 'other',
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function buildCard(string $label, string $value, string $icon, string $color): array
    {
        return [
            'label' => $label,
            'value' => $value,
            'icon' => $icon,
            'color' => $color,
            'trend' => '—',
            'trend_label' => 'Live count',
            'trend_positive' => true,
            'trend_available' => false,
        ];
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1_073_741_824) {
            return number_format($bytes / 1_073_741_824, 2).' GB';
        }

        if ($bytes >= 1_048_576) {
            return number_format($bytes / 1_048_576, 2).' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2).' KB';
        }

        return $bytes.' B';
    }
}

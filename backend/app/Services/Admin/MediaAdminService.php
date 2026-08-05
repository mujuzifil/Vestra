<?php

namespace App\Services\Admin;

use App\Enums\MediaAssetStatus;
use App\Enums\MediaAssetType;
use App\Enums\MediaUsageContext;
use App\Models\BlogPost;
use App\Models\MediaAsset;
use App\Models\MediaAssetUsage;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use App\Services\Catalog\CatalogSyncService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MediaAdminService
{
    public const PER_PAGE = 24;

    public function __construct(
        private readonly CatalogSyncService $sync
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters, string $sort, string $direction, int $perPage = self::PER_PAGE): LengthAwarePaginator
    {
        $query = MediaAsset::query()
            ->with(['uploader:id,name'])
            ->withCount('usages');

        $this->applyFilters($query, $filters);
        $this->applySorting($query, $sort, $direction);

        return $query->paginate(max(1, min($perPage, 100)));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getKpiCards(): array
    {
        $base = MediaAsset::query()->where('status', '!=', MediaAssetStatus::ARCHIVED->value);

        $total = (clone $base)->count();
        $images = (clone $base)->where('media_type', MediaAssetType::IMAGE->value)->count();
        $documents = (clone $base)->where('media_type', MediaAssetType::DOCUMENT->value)->count();
        $videos = (clone $base)->where('media_type', MediaAssetType::VIDEO->value)->count();
        $unused = (clone $base)->whereDoesntHave('usages')->count();
        $storageBytes = (int) (clone $base)->sum('size_bytes');

        return [
            $this->card('Total Assets', number_format($total), 'heroicon-o-folder', 'primary'),
            $this->card('Images', number_format($images), 'heroicon-o-photo', 'info'),
            $this->card('Documents', number_format($documents), 'heroicon-o-document-text', 'warning'),
            $this->card('Videos', number_format($videos), 'heroicon-o-film', 'success'),
            $this->card('Unused', number_format($unused), 'heroicon-o-archive-box', 'gray'),
            $this->card('Storage Used', $this->formatBytes($storageBytes), 'heroicon-o-server', 'gray'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilterOptions(): array
    {
        $uploaders = User::query()
            ->whereIn('id', MediaAsset::query()->whereNotNull('uploaded_by')->distinct()->pluck('uploaded_by'))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (User $user) => ['id' => $user->id, 'name' => $user->name])
            ->values()
            ->all();

        $formats = MediaAsset::query()
            ->whereNotNull('mime_type')
            ->distinct()
            ->orderBy('mime_type')
            ->pluck('mime_type')
            ->filter()
            ->values()
            ->all();

        return [
            'types' => array_map(
                fn (MediaAssetType $type) => ['value' => $type->value, 'label' => $type->label()],
                MediaAssetType::cases()
            ),
            'usage' => [
                ['value' => 'products', 'label' => 'Used by Products'],
                ['value' => 'blog', 'label' => 'Used by Blog'],
                ['value' => 'homepage', 'label' => 'Used by Homepage'],
                ['value' => 'unused', 'label' => 'Unused Assets'],
            ],
            'statuses' => array_map(
                fn (MediaAssetStatus $status) => ['value' => $status->value, 'label' => $status->label()],
                MediaAssetStatus::cases()
            ),
            'uploaders' => $uploaders,
            'formats' => $formats,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getDetail(int $id): ?array
    {
        $asset = MediaAsset::query()
            ->with(['uploader:id,name'])
            ->withCount('usages')
            ->find($id);

        if (! $asset) {
            return null;
        }

        return $this->serializeAsset($asset, includeUsage: true);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    public function exportRows(array $filters = []): array
    {
        $query = MediaAsset::query()->with(['uploader:id,name'])->withCount('usages');
        $this->applyFilters($query, $filters);
        $this->applySorting($query, 'created_at', 'desc');

        return $query->limit(5000)->get()->map(fn (MediaAsset $asset) => [
            'file_name' => $asset->file_name,
            'original_file_name' => $asset->original_file_name,
            'type' => $asset->media_type instanceof MediaAssetType ? $asset->media_type->label() : $asset->media_type,
            'mime' => $asset->mime_type ?? '—',
            'size' => $this->formatBytes((int) $asset->size_bytes),
            'dimensions' => $asset->dimensionsLabel() ?? '—',
            'status' => $asset->status instanceof MediaAssetStatus ? $asset->status->label() : $asset->status,
            'used_in' => (int) $asset->usages_count,
            'uploader' => $asset->uploader?->name ?? '—',
            'created_at' => $asset->created_at?->format('Y-m-d H:i:s'),
            'public_url' => $asset->url(),
        ])->all();
    }

    /**
     * Picker listing — active images by default.
     *
     * @param  array<string, mixed>  $filters
     */
    public function pickerPaginate(array $filters, int $perPage = 24): LengthAwarePaginator
    {
        $filters['status'] = $filters['status'] ?? [MediaAssetStatus::ACTIVE->value];
        if (empty($filters['type'])) {
            $filters['type'] = [MediaAssetType::IMAGE->value];
        }

        return $this->paginate($filters, $filters['sort'] ?? 'created_at', $filters['direction'] ?? 'desc', $perPage);
    }

    public function upload(UploadedFile $file, ?User $actor = null, array $meta = []): MediaAsset
    {
        $this->assertValidUpload($file);

        $checksum = hash_file('sha256', $file->getRealPath());
        $existing = MediaAsset::query()
            ->where('checksum', $checksum)
            ->where('status', MediaAssetStatus::ACTIVE->value)
            ->first();

        if ($existing) {
            return $existing;
        }

        $original = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $safeName = Str::slug(pathinfo($original, PATHINFO_FILENAME)) ?: 'asset';
        $fileName = $safeName.'-'.Str::lower(Str::random(8)).'.'.$extension;
        $path = $file->storeAs('media-library/'.now()->format('Y/m'), $fileName, 'public');

        $mime = $file->getMimeType() ?: $file->getClientMimeType();
        [$width, $height] = $this->readImageDimensions($file);

        $asset = MediaAsset::query()->create([
            'disk' => 'public',
            'path' => $path,
            'file_name' => $fileName,
            'original_file_name' => $original,
            'mime_type' => $mime,
            'media_type' => $this->resolveTypeFromMime($mime)->value,
            'size_bytes' => $file->getSize() ?: 0,
            'width' => $width,
            'height' => $height,
            'checksum' => $checksum,
            'alt_text' => $meta['alt_text'] ?? null,
            'caption' => $meta['caption'] ?? null,
            'description' => $meta['description'] ?? null,
            'tags' => $meta['tags'] ?? [],
            'copyright' => $meta['copyright'] ?? null,
            'internal_notes' => $meta['internal_notes'] ?? null,
            'status' => MediaAssetStatus::ACTIVE->value,
            'uploaded_by' => $actor?->id,
        ]);

        $this->sync->syncMedia($asset);

        return $asset;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateMetadata(MediaAsset $asset, array $data): MediaAsset
    {
        $asset->fill([
            'file_name' => $data['file_name'] ?? $asset->file_name,
            'alt_text' => $data['alt_text'] ?? $asset->alt_text,
            'caption' => $data['caption'] ?? $asset->caption,
            'description' => $data['description'] ?? $asset->description,
            'tags' => array_key_exists('tags', $data) ? array_values((array) $data['tags']) : $asset->tags,
            'copyright' => $data['copyright'] ?? $asset->copyright,
            'internal_notes' => $data['internal_notes'] ?? $asset->internal_notes,
        ]);
        $asset->save();

        $this->syncDenormalizedPaths($asset);
        $this->sync->syncMedia($asset);

        return $asset->fresh(['uploader']) ?? $asset;
    }

    public function rename(MediaAsset $asset, string $fileName): MediaAsset
    {
        $fileName = trim($fileName);
        if ($fileName === '') {
            throw ValidationException::withMessages([
                'file_name' => 'Filename is required.',
            ]);
        }

        return $this->updateMetadata($asset, ['file_name' => $fileName]);
    }

    public function replaceFile(MediaAsset $asset, UploadedFile $file, ?User $actor = null): MediaAsset
    {
        $this->assertValidUpload($file);

        $checksum = hash_file('sha256', $file->getRealPath());
        $original = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $safeName = Str::slug(pathinfo($original, PATHINFO_FILENAME)) ?: 'asset';
        $fileName = $safeName.'-'.Str::lower(Str::random(8)).'.'.$extension;

        $oldPath = $asset->path;
        $path = $file->storeAs('media-library/'.now()->format('Y/m'), $fileName, 'public');
        $mime = $file->getMimeType() ?: $file->getClientMimeType();
        [$width, $height] = $this->readImageDimensions($file);

        $asset->update([
            'path' => $path,
            'file_name' => $fileName,
            'original_file_name' => $original,
            'mime_type' => $mime,
            'media_type' => $this->resolveTypeFromMime($mime)->value,
            'size_bytes' => $file->getSize() ?: 0,
            'width' => $width,
            'height' => $height,
            'checksum' => $checksum,
            'uploaded_by' => $actor?->id ?? $asset->uploaded_by,
        ]);

        if ($oldPath && $oldPath !== $path) {
            try {
                Storage::disk($asset->disk ?: 'public')->delete($oldPath);
            } catch (\Throwable) {
            }
        }

        $this->syncDenormalizedPaths($asset->fresh() ?? $asset);
        $this->sync->syncMedia($asset);

        return $asset->fresh(['uploader']) ?? $asset;
    }

    public function archive(MediaAsset $asset): MediaAsset
    {
        $asset->update(['status' => MediaAssetStatus::ARCHIVED->value]);
        $this->sync->syncMedia($asset);

        return $asset;
    }

    public function restore(MediaAsset $asset): MediaAsset
    {
        $asset->update(['status' => MediaAssetStatus::ACTIVE->value]);
        $this->sync->syncMedia($asset);

        return $asset;
    }

    public function delete(MediaAsset $asset): void
    {
        $count = $asset->usages()->count();
        if ($count > 0) {
            throw ValidationException::withMessages([
                'asset' => "This asset is currently being used by {$count} item(s) and cannot be deleted until those references are removed.",
            ]);
        }

        $asset->deleteFile();
        $asset->delete();
        $this->sync->syncMedia(null);
    }

    public function attachUsage(
        MediaAsset $asset,
        Model $owner,
        MediaUsageContext $context,
        int $sortOrder = 0
    ): MediaAssetUsage {
        return MediaAssetUsage::query()->updateOrCreate(
            [
                'media_asset_id' => $asset->id,
                'usable_type' => $owner::class,
                'usable_id' => $owner->getKey(),
                'context' => $context->value,
            ],
            ['sort_order' => $sortOrder]
        );
    }

    /**
     * @param  class-string  $usableType
     */
    public function detachUsage(MediaAsset $asset, string $usableType, int $usableId, ?MediaUsageContext $context = null): void
    {
        $query = MediaAssetUsage::query()
            ->where('media_asset_id', $asset->id)
            ->where('usable_type', $usableType)
            ->where('usable_id', $usableId);

        if ($context) {
            $query->where('context', $context->value);
        }

        $query->delete();
    }

    public function linkToProduct(Product $product, MediaAsset $asset, bool $asPrimary = false): ProductImage
    {
        $sort = (int) $product->images()->max('sort_order');
        $sort++;

        $context = ($asPrimary || $product->images()->count() === 0)
            ? MediaUsageContext::PRODUCT_PRIMARY
            : MediaUsageContext::PRODUCT_GALLERY;

        $image = ProductImage::query()->create([
            'product_id' => $product->id,
            'media_asset_id' => $asset->id,
            'image' => $asset->publicPath(),
            'alt_text' => $asset->alt_text ?: $product->name,
            'sort_order' => $asPrimary ? 0 : $sort,
        ]);

        MediaAssetUsage::query()->updateOrCreate(
            [
                'media_asset_id' => $asset->id,
                'usable_type' => Product::class,
                'usable_id' => $product->id,
                'context' => $context->value,
            ],
            ['sort_order' => $image->sort_order]
        );

        $this->sync->syncProducts($product->id, $product->category_id);

        return $image;
    }

    public function unlinkProductImage(Product $product, ProductImage $image): void
    {
        if ($image->product_id !== $product->id) {
            throw ValidationException::withMessages([
                'image' => 'Image does not belong to this product.',
            ]);
        }

        if ($image->media_asset_id) {
            $remaining = ProductImage::query()
                ->where('product_id', $product->id)
                ->where('media_asset_id', $image->media_asset_id)
                ->where('id', '!=', $image->id)
                ->count();

            if ($remaining === 0) {
                MediaAssetUsage::query()
                    ->where('media_asset_id', $image->media_asset_id)
                    ->where('usable_type', Product::class)
                    ->where('usable_id', $product->id)
                    ->delete();
            }
        }

        $image->delete();
        $this->sync->syncProducts($product->id, $product->category_id);
    }

    public function linkFeaturedToBlog(BlogPost $post, MediaAsset $asset): BlogPost
    {
        if ($post->featured_media_asset_id && $post->featured_media_asset_id !== $asset->id) {
            MediaAssetUsage::query()
                ->where('media_asset_id', $post->featured_media_asset_id)
                ->where('usable_type', BlogPost::class)
                ->where('usable_id', $post->id)
                ->where('context', MediaUsageContext::BLOG_FEATURED->value)
                ->delete();
        }

        $post->update([
            'featured_media_asset_id' => $asset->id,
            'featured_image' => $asset->publicPath(),
        ]);

        MediaAssetUsage::query()->updateOrCreate(
            [
                'media_asset_id' => $asset->id,
                'usable_type' => BlogPost::class,
                'usable_id' => $post->id,
                'context' => MediaUsageContext::BLOG_FEATURED->value,
            ],
            ['sort_order' => 0]
        );

        $this->sync->syncBlog($post->id, $post->slug);

        return $post->fresh() ?? $post;
    }

    public function clearBlogFeatured(BlogPost $post): void
    {
        if ($post->featured_media_asset_id) {
            MediaAssetUsage::query()
                ->where('media_asset_id', $post->featured_media_asset_id)
                ->where('usable_type', BlogPost::class)
                ->where('usable_id', $post->id)
                ->where('context', MediaUsageContext::BLOG_FEATURED->value)
                ->delete();
        }

        $post->update([
            'featured_media_asset_id' => null,
            'featured_image' => null,
        ]);

        $this->sync->syncBlog($post->id, $post->slug);
    }

    public function linkInlineToBlog(BlogPost $post, MediaAsset $asset): void
    {
        MediaAssetUsage::query()->firstOrCreate(
            [
                'media_asset_id' => $asset->id,
                'usable_type' => BlogPost::class,
                'usable_id' => $post->id,
                'context' => MediaUsageContext::BLOG_INLINE->value,
            ],
            ['sort_order' => 0]
        );
    }

    public function syncDenormalizedPaths(MediaAsset $asset): void
    {
        ProductImage::query()
            ->where('media_asset_id', $asset->id)
            ->update(['image' => $asset->publicPath()]);

        BlogPost::query()
            ->where('featured_media_asset_id', $asset->id)
            ->update(['featured_image' => $asset->publicPath()]);
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeAsset(MediaAsset $asset, bool $includeUsage = false): array
    {
        $type = $asset->media_type instanceof MediaAssetType ? $asset->media_type : MediaAssetType::tryFrom((string) $asset->media_type);
        $status = $asset->status instanceof MediaAssetStatus ? $asset->status : MediaAssetStatus::tryFrom((string) $asset->status);

        $data = [
            'id' => $asset->id,
            'uuid' => $asset->uuid,
            'file_name' => $asset->file_name,
            'original_file_name' => $asset->original_file_name,
            'path' => $asset->path,
            'disk' => $asset->disk,
            'url' => $asset->url(),
            'mime_type' => $asset->mime_type,
            'media_type' => $type?->value ?? 'other',
            'media_type_label' => $type?->label() ?? 'Other',
            'size_bytes' => (int) $asset->size_bytes,
            'size_label' => $this->formatBytes((int) $asset->size_bytes),
            'width' => $asset->width,
            'height' => $asset->height,
            'dimensions' => $asset->dimensionsLabel(),
            'checksum' => $asset->checksum,
            'alt_text' => $asset->alt_text,
            'caption' => $asset->caption,
            'description' => $asset->description,
            'tags' => $asset->tags ?? [],
            'copyright' => $asset->copyright,
            'internal_notes' => $asset->internal_notes,
            'status' => $status?->value ?? 'active',
            'status_label' => $status?->label() ?? 'Active',
            'usages_count' => (int) ($asset->usages_count ?? $asset->usages()->count()),
            'uploaded_by' => $asset->uploader ? [
                'id' => $asset->uploader->id,
                'name' => $asset->uploader->name,
            ] : null,
            'created_at' => $asset->created_at,
            'updated_at' => $asset->updated_at,
        ];

        if ($includeUsage) {
            $data['usage_groups'] = $this->buildUsageGroups($asset);
        }

        return $data;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildUsageGroups(MediaAsset $asset): array
    {
        $usages = $asset->usages()->with('usable')->get();
        $groups = [];

        foreach ($usages as $usage) {
            $context = $usage->context instanceof MediaUsageContext
                ? $usage->context
                : MediaUsageContext::tryFrom((string) $usage->context) ?? MediaUsageContext::GENERAL;

            $group = $context->group();
            $label = $this->resolveUsableLabel($usage);
            $groups[$group][] = [
                'context' => $context->value,
                'context_label' => $context->label(),
                'label' => $label,
                'usable_type' => $usage->usable_type,
                'usable_id' => $usage->usable_id,
            ];
        }

        return collect($groups)->map(fn ($items, $name) => [
            'group' => $name,
            'items' => $items,
        ])->values()->all();
    }

    private function resolveUsableLabel(MediaAssetUsage $usage): string
    {
        $usable = $usage->usable;
        if ($usable === null) {
            return class_basename($usage->usable_type).' #'.$usage->usable_id;
        }

        return $usable->name
            ?? $usable->title
            ?? class_basename($usage->usable_type).' #'.$usage->usable_id;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        if (filled($filters['search'] ?? null)) {
            $term = trim((string) $filters['search']);
            $query->where(function (Builder $q) use ($term): void {
                $q->where('file_name', 'like', "%{$term}%")
                    ->orWhere('original_file_name', 'like', "%{$term}%")
                    ->orWhere('alt_text', 'like', "%{$term}%")
                    ->orWhere('caption', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhere('mime_type', 'like', "%{$term}%")
                    ->orWhereHas('uploader', fn (Builder $u) => $u->where('name', 'like', "%{$term}%"))
                    ->orWhereHas('usages', function (Builder $u) use ($term): void {
                        $u->where(function (Builder $inner) use ($term): void {
                            $inner->where('usable_type', Product::class)
                                ->whereIn('usable_id', Product::query()->where('name', 'like', "%{$term}%")->select('id'));
                        })->orWhere(function (Builder $inner) use ($term): void {
                            $inner->where('usable_type', BlogPost::class)
                                ->whereIn('usable_id', BlogPost::query()->where('title', 'like', "%{$term}%")->select('id'));
                        });
                    });
            });
        }

        $types = array_filter((array) ($filters['type'] ?? []));
        if ($types !== []) {
            $query->whereIn('media_type', $types);
        }

        $statuses = array_filter((array) ($filters['status'] ?? []));
        if ($statuses !== []) {
            $query->whereIn('status', $statuses);
        } else {
            // Default: hide archived unless explicitly filtered
            if (! ($filters['include_archived'] ?? false)) {
                $query->where('status', '!=', MediaAssetStatus::ARCHIVED->value);
            }
        }

        $usage = $filters['usage'] ?? null;
        if (is_array($usage)) {
            $usage = $usage[0] ?? null;
        }
        if (filled($usage)) {
            match ($usage) {
                'products' => $query->whereHas('usages', fn (Builder $q) => $q->where('usable_type', Product::class)),
                'blog' => $query->whereHas('usages', fn (Builder $q) => $q->where('usable_type', BlogPost::class)),
                'homepage' => $query->whereHas('usages', fn (Builder $q) => $q->where('context', MediaUsageContext::HOMEPAGE->value)),
                'unused' => $query->whereDoesntHave('usages'),
                default => null,
            };
        }

        if (filled($filters['uploader_id'] ?? null)) {
            $query->where('uploaded_by', (int) $filters['uploader_id']);
        }

        if (filled($filters['format'] ?? null)) {
            $query->where('mime_type', $filters['format']);
        }

        if (filled($filters['date_from'] ?? null)) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (filled($filters['date_until'] ?? null)) {
            $query->whereDate('created_at', '<=', $filters['date_until']);
        }

        if (filled($filters['size_min'] ?? null)) {
            $query->where('size_bytes', '>=', (int) $filters['size_min']);
        }

        if (filled($filters['size_max'] ?? null)) {
            $query->where('size_bytes', '<=', (int) $filters['size_max']);
        }
    }

    private function applySorting(Builder $query, string $sort, string $direction): void
    {
        $direction = strtolower($direction) === 'asc' ? 'asc' : 'desc';

        match ($sort) {
            'file_name', 'name' => $query->orderBy('file_name', $direction),
            'size', 'size_bytes' => $query->orderBy('size_bytes', $direction),
            'type', 'media_type' => $query->orderBy('media_type', $direction),
            'usages', 'usages_count' => $query->orderBy('usages_count', $direction),
            'updated_at' => $query->orderBy('updated_at', $direction),
            default => $query->orderBy('created_at', $direction),
        };
    }

    private function assertValidUpload(UploadedFile $file): void
    {
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf', 'mp4', 'webm', 'mov'];
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: '');

        if (! in_array($ext, $allowed, true)) {
            throw ValidationException::withMessages([
                'file' => 'Unsupported file format. Allowed: '.implode(', ', $allowed).'.',
            ]);
        }

        if ($file->getSize() > 12 * 1024 * 1024) {
            throw ValidationException::withMessages([
                'file' => 'File may not be greater than 12MB.',
            ]);
        }
    }

    /**
     * @return array{0: ?int, 1: ?int}
     */
    private function readImageDimensions(UploadedFile $file): array
    {
        try {
            $info = @getimagesize($file->getRealPath());
            if (is_array($info)) {
                return [(int) ($info[0] ?? 0) ?: null, (int) ($info[1] ?? 0) ?: null];
            }
        } catch (\Throwable) {
        }

        return [null, null];
    }

    private function resolveTypeFromMime(?string $mime): MediaAssetType
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

    /**
     * @return array<string, mixed>
     */
    private function card(string $label, string $value, string $icon, string $color): array
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

    public function formatBytes(int $bytes): string
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

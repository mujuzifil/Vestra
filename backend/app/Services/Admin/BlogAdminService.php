<?php

namespace App\Services\Admin;

use App\Enums\BlogPostStatus;
use App\Enums\BlogPostVisibility;
use App\Models\BlogAuthor;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\MediaAsset;
use App\Models\MediaAssetUsage;
use App\Models\User;
use App\Services\Catalog\CatalogSyncService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BlogAdminService
{
    public function __construct(private readonly CatalogSyncService $catalogSync) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginatePosts(array $filters = [], string $sort = 'created_at', string $direction = 'desc', int $perPage = 15): LengthAwarePaginator
    {
        return $this->queryPosts($filters, $sort, $direction)->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function queryPosts(array $filters = [], string $sort = 'created_at', string $direction = 'desc'): Builder
    {
        $query = BlogPost::query()
            ->with(['author', 'categories', 'tags'])
            ->when($filters['search'] ?? null, function (Builder $q, string $term): void {
                $q->where(function (Builder $inner) use ($term): void {
                    $inner->where('title', 'like', "%{$term}%")
                        ->orWhere('slug', 'like', "%{$term}%")
                        ->orWhere('excerpt', 'like', "%{$term}%")
                        ->orWhere('content', 'like', "%{$term}%")
                        ->orWhereHas('author', fn (Builder $a) => $a->where('name', 'like', "%{$term}%"))
                        ->orWhereHas('categories', fn (Builder $c) => $c->where('name', 'like', "%{$term}%"))
                        ->orWhereHas('tags', fn (Builder $t) => $t->where('name', 'like', "%{$term}%"));
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $q, array $statuses) => $q->whereIn('status', $statuses))
            ->when($filters['author'] ?? null, fn (Builder $q, int $authorId) => $q->where('author_id', $authorId))
            ->when($filters['category'] ?? null, function (Builder $q, array $categoryIds): void {
                $q->whereHas('categories', fn (Builder $inner) => $inner->whereIn('blog_categories.id', $categoryIds));
            })
            ->when($filters['date_from'] ?? null, fn (Builder $q, string $from) => $q->whereDate('created_at', '>=', $from))
            ->when($filters['date_until'] ?? null, fn (Builder $q, string $until) => $q->whereDate('created_at', '<=', $until));

        return $this->applySorting($query, $sort, $direction);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getKpiCards(): array
    {
        return [
            $this->buildCard('Published', BlogPost::query()->where('status', BlogPostStatus::PUBLISHED->value)->count(), 'heroicon-o-check-circle', 'success'),
            $this->buildCard('Draft', BlogPost::query()->where('status', BlogPostStatus::DRAFT->value)->count(), 'heroicon-o-document-text', 'gray'),
            $this->buildCard('Scheduled', BlogPost::query()->where('status', BlogPostStatus::SCHEDULED->value)->count(), 'heroicon-o-clock', 'warning'),
            $this->buildCard('Archived', BlogPost::query()->where('status', BlogPostStatus::ARCHIVED->value)->count(), 'heroicon-o-archive-box', 'danger'),
            $this->buildCard('Categories', BlogCategory::query()->count(), 'heroicon-o-tag', 'info'),
            $this->buildCard('Authors', BlogAuthor::query()->where('is_active', true)->count(), 'heroicon-o-user-group', 'primary'),
            $this->buildCard('Total Views', (int) BlogPost::query()->sum('view_count'), 'heroicon-o-eye', 'info'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getDetail(BlogPost $post): array
    {
        $post->load(['author', 'categories', 'tags', 'featuredMediaAsset']);
        $publicBase = rtrim((string) config('services.frontend.public_url'), '/');
        $publicPath = '/blog/'.$post->slug;

        return [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'excerpt' => $post->excerpt,
            'content' => $post->content,
            'status' => $post->status instanceof BlogPostStatus ? $post->status->value : (string) $post->status,
            'status_label' => $post->statusLabel(),
            'status_color' => $post->statusColor(),
            'visibility' => $post->visibility instanceof BlogPostVisibility ? $post->visibility->value : (string) $post->visibility,
            'visibility_label' => $post->visibility?->label() ?? '—',
            'is_featured' => (bool) $post->is_featured,
            'show_on_homepage' => (bool) $post->show_on_homepage,
            'is_pinned' => (bool) $post->is_pinned,
            'allow_comments' => (bool) $post->allow_comments,
            'featured_image' => $post->featuredMediaAsset?->url() ?? $this->assetUrl($post->featured_image),
            'featured_image_path' => $post->featuredMediaAsset?->publicPath() ?? $post->featured_image,
            'featured_media_asset_id' => $post->featured_media_asset_id,
            'gallery' => collect($post->gallery ?? [])
                ->map(fn ($path) => $this->assetUrl($path))
                ->filter()
                ->values()
                ->toArray(),
            'reading_time_minutes' => $post->estimatedReadingTime(),
            'view_count' => (int) $post->view_count,
            'author' => $post->author ? [
                'id' => $post->author->id,
                'name' => $post->author->name,
                'email' => $post->author->email,
            ] : null,
            'categories' => $post->categories->map(fn (BlogCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
            ])->values()->toArray(),
            'tags' => $post->tags->map(fn (BlogTag $tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
            ])->values()->toArray(),
            'meta_title' => $post->meta_title,
            'meta_description' => $post->meta_description,
            'canonical_url' => $post->canonical_url,
            'og_title' => $post->og_title,
            'og_description' => $post->og_description,
            'public_url' => $publicBase.$publicPath,
            'public_path' => $publicPath,
            'published_at' => $post->published_at,
            'scheduled_at' => $post->scheduled_at,
            'created_at' => $post->created_at,
            'updated_at' => $post->updated_at,
            'edit_url' => \App\Filament\Pages\Marketing\BlogArticlePage::getUrl(['id' => $post->id]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilterOptions(): array
    {
        $this->syncAuthorsFromStaff();

        return [
            'statuses' => array_map(
                fn (BlogPostStatus $status) => ['value' => $status->value, 'label' => $status->label()],
                BlogPostStatus::cases()
            ),
            'authors' => BlogAuthor::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (BlogAuthor $author) => ['id' => $author->id, 'name' => $author->name])
                ->values()
                ->toArray(),
            'categories' => BlogCategory::query()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (BlogCategory $category) => ['id' => $category->id, 'name' => $category->name])
                ->values()
                ->toArray(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getFormOptions(?User $actor = null): array
    {
        $this->syncAuthorsFromStaff();

        $authors = BlogAuthor::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'user_id']);

        $defaultAuthorId = null;
        if ($actor) {
            $defaultAuthorId = $authors->firstWhere('user_id', $actor->id)?->id
                ?? $authors->firstWhere('email', $actor->email)?->id;
        }

        return [
            'statuses' => array_map(
                fn (BlogPostStatus $status) => ['value' => $status->value, 'label' => $status->label()],
                BlogPostStatus::cases()
            ),
            'visibilities' => array_map(
                fn (BlogPostVisibility $visibility) => ['value' => $visibility->value, 'label' => $visibility->label()],
                BlogPostVisibility::cases()
            ),
            'authors' => $authors->map(fn (BlogAuthor $author) => [
                'id' => $author->id,
                'name' => $author->name,
            ])->values()->toArray(),
            'categories' => BlogCategory::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (BlogCategory $category) => ['id' => $category->id, 'name' => $category->name])
                ->values()
                ->toArray(),
            'tags' => BlogTag::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (BlogTag $tag) => ['id' => $tag->id, 'name' => $tag->name])
                ->values()
                ->toArray(),
            'default_author_id' => $defaultAuthorId,
            'has_authors' => $authors->isNotEmpty(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, int|string>  $categoryIds
     * @param  array<int, string>  $tagNames
     * @param  array<int, int>  $inlineMediaAssetIds
     */
    public function createPost(
        array $data,
        array $categoryIds = [],
        array $tagNames = [],
        ?int $featuredMediaAssetId = null,
        array $inlineMediaAssetIds = []
    ): BlogPost {
        return DB::transaction(function () use ($data, $categoryIds, $tagNames, $featuredMediaAssetId, $inlineMediaAssetIds) {
            $payload = $this->preparePayload($data);
            $post = BlogPost::create($payload);
            $this->syncCategories($post, $categoryIds);
            $this->syncTags($post, $tagNames);

            $media = app(MediaAdminService::class);

            if ($featuredMediaAssetId) {
                $asset = MediaAsset::query()->find($featuredMediaAssetId);
                if ($asset) {
                    $media->linkFeaturedToBlog($post, $asset);
                }
            }

            foreach ($inlineMediaAssetIds as $assetId) {
                $asset = MediaAsset::query()->find((int) $assetId);
                if ($asset) {
                    $media->linkInlineToBlog($post, $asset);
                }
            }

            $this->catalogSync->syncBlog($post->id, $post->slug);

            return $post->fresh(['author', 'categories', 'tags', 'featuredMediaAsset']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, int|string>  $categoryIds
     * @param  array<int, string>  $tagNames
     * @param  array<int, int>  $inlineMediaAssetIds
     */
    public function updatePost(
        BlogPost $post,
        array $data,
        array $categoryIds = [],
        array $tagNames = [],
        ?int $featuredMediaAssetId = null,
        bool $removeFeaturedImage = false,
        array $inlineMediaAssetIds = []
    ): BlogPost {
        return DB::transaction(function () use ($post, $data, $categoryIds, $tagNames, $featuredMediaAssetId, $removeFeaturedImage, $inlineMediaAssetIds) {
            $payload = $this->preparePayload($data, $post);
            $post->update($payload);
            $this->syncCategories($post, $categoryIds);
            $this->syncTags($post, $tagNames);

            $media = app(MediaAdminService::class);

            if ($removeFeaturedImage) {
                $media->clearBlogFeatured($post);
            } elseif ($featuredMediaAssetId) {
                $asset = MediaAsset::query()->find($featuredMediaAssetId);
                if ($asset) {
                    $media->linkFeaturedToBlog($post->fresh() ?? $post, $asset);
                }
            }

            foreach ($inlineMediaAssetIds as $assetId) {
                $asset = MediaAsset::query()->find((int) $assetId);
                if ($asset) {
                    $media->linkInlineToBlog($post, $asset);
                }
            }

            $this->catalogSync->syncBlog($post->id, $post->slug);

            return $post->fresh(['author', 'categories', 'tags', 'featuredMediaAsset']);
        });
    }

    public function deletePost(BlogPost $post): void
    {
        DB::transaction(function () use ($post) {
            $slug = $post->slug;
            $id = $post->id;

            MediaAssetUsage::query()
                ->where('usable_type', BlogPost::class)
                ->where('usable_id', $post->id)
                ->delete();

            $post->categories()->detach();
            $post->tags()->detach();
            $post->delete();

            $this->catalogSync->syncBlog($id, $slug);
        });
    }

    /**
     * Ensure BlogAuthor rows exist for active admin/staff users.
     */
    public function syncAuthorsFromStaff(): void
    {
        $admins = User::query()
            ->where('is_admin', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        foreach ($admins as $admin) {
            $existing = BlogAuthor::query()
                ->where(function (Builder $q) use ($admin): void {
                    $q->where('user_id', $admin->id);
                    if (filled($admin->email)) {
                        $q->orWhere('email', $admin->email);
                    }
                })
                ->first();

            if ($existing) {
                $existing->update([
                    'user_id' => $admin->id,
                    'name' => $admin->name ?: $existing->name,
                    'email' => $admin->email ?: $existing->email,
                    'is_active' => true,
                ]);

                continue;
            }

            $baseSlug = Str::slug($admin->name ?: ('author-'.$admin->id)) ?: 'author-'.$admin->id;
            $slug = $baseSlug;
            $i = 1;
            while (BlogAuthor::query()->where('slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.$i;
                $i++;
            }

            BlogAuthor::create([
                'user_id' => $admin->id,
                'name' => $admin->name ?: 'Administrator',
                'slug' => $slug,
                'email' => $admin->email,
                'role' => 'Administrator',
                'is_active' => true,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    public function exportRows(array $filters = []): array
    {
        return $this->queryPosts($filters, 'title', 'asc')
            ->get()
            ->map(fn (BlogPost $post) => [
                'title' => $post->title,
                'slug' => $post->slug,
                'author' => $post->author?->name,
                'status' => $post->statusLabel(),
                'visibility' => $post->visibility?->label(),
                'categories' => $post->categories->pluck('name')->implode(', '),
                'view_count' => (int) $post->view_count,
                'published_at' => $post->published_at?->format('Y-m-d H:i:s'),
                'created_at' => $post->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $post->updated_at?->format('Y-m-d H:i:s'),
            ])
            ->toArray();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function preparePayload(array $data, ?BlogPost $existing = null): array
    {
        $title = trim((string) ($data['title'] ?? ''));
        $slug = trim((string) ($data['slug'] ?? ''));
        $slug = $slug !== '' ? Str::slug($slug) : Str::slug($title);
        if ($slug === '') {
            $slug = 'article';
        }
        $slug = $this->uniqueSlug($slug, $existing?->id);

        $status = (string) ($data['status'] ?? BlogPostStatus::DRAFT->value);
        $scheduledAt = filled($data['scheduled_at'] ?? null) ? $data['scheduled_at'] : null;
        $publishedAt = filled($data['published_at'] ?? null) ? $data['published_at'] : null;

        if ($status === BlogPostStatus::PUBLISHED->value) {
            $publishedAt = $publishedAt ?: now();
            $scheduledAt = null;
        } elseif ($status === BlogPostStatus::SCHEDULED->value) {
            if (! $scheduledAt) {
                throw ValidationException::withMessages([
                    'form.scheduled_at' => 'A publish date and time is required for scheduled articles.',
                ]);
            }
            if (\Carbon\Carbon::parse($scheduledAt)->lte(now())) {
                throw ValidationException::withMessages([
                    'form.scheduled_at' => 'The scheduled publish time must be in the future.',
                ]);
            }
            $publishedAt = null;
        } elseif ($status === BlogPostStatus::DRAFT->value) {
            $publishedAt = null;
        }

        $content = (string) ($data['content'] ?? '');
        $wordCount = str_word_count(strip_tags($content));

        return [
            'title' => $title,
            'slug' => $slug,
            'excerpt' => filled($data['excerpt'] ?? null) ? trim((string) $data['excerpt']) : null,
            'content' => $content,
            'author_id' => filled($data['author_id'] ?? null) ? (int) $data['author_id'] : null,
            'status' => $status,
            'visibility' => (string) ($data['visibility'] ?? BlogPostVisibility::PUBLIC->value),
            'is_featured' => (bool) ($data['is_featured'] ?? false),
            'show_on_homepage' => (bool) ($data['show_on_homepage'] ?? false),
            'is_pinned' => (bool) ($data['is_pinned'] ?? false),
            'allow_comments' => (bool) ($data['allow_comments'] ?? true),
            'reading_time_minutes' => max(1, (int) ceil($wordCount / 200)),
            'published_at' => $publishedAt,
            'scheduled_at' => $scheduledAt,
            'meta_title' => filled($data['meta_title'] ?? null) ? trim((string) $data['meta_title']) : null,
            'meta_description' => filled($data['meta_description'] ?? null) ? trim((string) $data['meta_description']) : null,
            'canonical_url' => filled($data['canonical_url'] ?? null) ? trim((string) $data['canonical_url']) : null,
            'og_title' => filled($data['og_title'] ?? null) ? trim((string) $data['og_title']) : null,
            'og_description' => filled($data['og_description'] ?? null) ? trim((string) $data['og_description']) : null,
        ];
    }

    private function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = $base;
        $i = 1;

        while (
            BlogPost::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn (Builder $q) => $q->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }

    /**
     * @param  array<int, int|string>  $categoryIds
     */
    private function syncCategories(BlogPost $post, array $categoryIds): void
    {
        $ids = collect($categoryIds)
            ->filter(fn ($id) => filled($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $sync = [];
        foreach ($ids as $index => $id) {
            $sync[$id] = ['sort_order' => $index];
        }

        $post->categories()->sync($sync);
    }

    /**
     * @param  array<int, string>  $tagNames
     */
    private function syncTags(BlogPost $post, array $tagNames): void
    {
        $ids = [];

        foreach ($tagNames as $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }

            $slug = Str::slug($name) ?: Str::random(6);
            $tag = BlogTag::query()->firstOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'is_active' => true]
            );
            $ids[] = $tag->id;
        }

        $post->tags()->sync(array_values(array_unique($ids)));
    }

    private function deletePublicFile(string $path): void
    {
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function applySorting(Builder $query, string $sort, string $direction): Builder
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return match ($sort) {
            'title' => $query->orderBy('title', $direction),
            'status' => $query->orderBy('status', $direction),
            'author' => $query->orderBy(
                BlogAuthor::select('name')
                    ->whereColumn('blog_authors.id', 'blog_posts.author_id')
                    ->limit(1),
                $direction
            ),
            'view_count' => $query->orderBy('view_count', $direction),
            'published_at' => $query->orderBy('published_at', $direction),
            'updated_at' => $query->orderBy('updated_at', $direction),
            default => $query->orderByDesc('is_pinned')->orderBy('created_at', $direction),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function buildCard(string $label, float $current, string $icon, string $color): array
    {
        return [
            'label' => $label,
            'value' => number_format($current),
            'icon' => $icon,
            'color' => $color,
            'trend' => '—',
            'trend_label' => 'Live count',
            'trend_positive' => true,
            'trend_available' => false,
        ];
    }

    private function assetUrl(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset('storage/'.$path);
    }
}

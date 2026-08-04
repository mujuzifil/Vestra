<?php

namespace App\Services\Admin;

use App\Enums\BlogPostStatus;
use App\Enums\BlogPostVisibility;
use App\Models\BlogAuthor;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class BlogAdminService
{
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
                        ->orWhere('excerpt', 'like', "%{$term}%");
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
            $this->buildCard('Authors', BlogAuthor::query()->count(), 'heroicon-o-user-group', 'primary'),
            $this->buildCard('Total Views', (int) BlogPost::query()->sum('view_count'), 'heroicon-o-eye', 'info'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getDetail(BlogPost $post): array
    {
        $post->load(['author', 'categories', 'tags']);

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
            'featured_image' => $this->assetUrl($post->featured_image),
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
            'published_at' => $post->published_at,
            'scheduled_at' => $post->scheduled_at,
            'created_at' => $post->created_at,
            'updated_at' => $post->updated_at,
            'edit_url' => \App\Filament\Resources\BlogPostResource::getUrl('edit', ['record' => $post]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilterOptions(): array
    {
        return [
            'statuses' => array_map(
                fn (BlogPostStatus $status) => ['value' => $status->value, 'label' => $status->label()],
                BlogPostStatus::cases()
            ),
            'authors' => BlogAuthor::query()
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
            default => $query->orderBy('created_at', $direction),
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

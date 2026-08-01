<?php

namespace App\Services;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BlogPostService
{
    public function getPublishedPosts(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = BlogPost::with(['author', 'categories', 'tags'])
            ->published()
            ->public();

        $this->applyFilters($query, $filters);
        $this->applySorting($query, $filters['sort'] ?? 'newest');

        return $query->paginate(max(1, min($perPage, 100)));
    }

    public function getFeaturedPost(): ?BlogPost
    {
        return BlogPost::with(['author', 'categories', 'tags'])
            ->published()
            ->public()
            ->featured()
            ->orderByDesc('published_at')
            ->first();
    }

    public function getPostBySlug(string $slug): BlogPost
    {
        $post = BlogPost::with(['author', 'categories', 'tags'])
            ->published()
            ->public()
            ->where('slug', $slug)
            ->first();

        if (! $post) {
            throw new ModelNotFoundException('Blog post not found.');
        }

        return $post;
    }

    /**
     * @return Collection<int, BlogCategory>
     */
    public function getActiveCategories(): Collection
    {
        return BlogCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, BlogTag>
     */
    public function getActiveTags(): Collection
    {
        return BlogTag::where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['category'])) {
            $query->whereHas('categories', function (Builder $q) use ($filters) {
                $q->where('slug', $filters['category']);
            });
        }

        if (! empty($filters['tag'])) {
            $query->whereHas('tags', function (Builder $q) use ($filters) {
                $q->where('slug', $filters['tag']);
            });
        }

        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function (Builder $q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }
    }

    private function applySorting(Builder $query, string $sort): void
    {
        match ($sort) {
            'oldest' => $query->orderBy('published_at', 'asc'),
            'popular' => $query->orderByDesc('view_count')->orderByDesc('published_at'),
            'reading_time' => $query->orderBy('reading_time_minutes', 'asc')->orderByDesc('published_at'),
            default => $query->orderByDesc('published_at'),
        };
    }
}

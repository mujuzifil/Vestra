<?php

namespace App\Models;

use App\Enums\BlogPostStatus;
use App\Enums\BlogPostVisibility;
use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlogPost extends Model
{
    use HasFactory;
    use HasSlug;

    protected string $slugSourceColumn = 'title';

    protected $fillable = [
        'author_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'gallery',
        'content_blocks',
        'status',
        'visibility',
        'is_featured',
        'reading_time_minutes',
        'published_at',
        'scheduled_at',
        'meta_title',
        'meta_description',
        'canonical_url',
        'view_count',
    ];

    protected function casts(): array
    {
        return [
            'status' => BlogPostStatus::class,
            'visibility' => BlogPostVisibility::class,
            'is_featured' => 'boolean',
            'reading_time_minutes' => 'integer',
            'published_at' => 'datetime',
            'scheduled_at' => 'datetime',
            'gallery' => 'array',
            'content_blocks' => 'array',
            'view_count' => 'integer',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(BlogAuthor::class, 'author_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(BlogCategory::class, 'blog_category_post')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(BlogTag::class, 'blog_post_tag');
    }

    public function views(): HasMany
    {
        return $this->hasMany(BlogPostView::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', BlogPostStatus::PUBLISHED->value)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('visibility', BlogPostVisibility::PUBLIC->value);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            BlogPostStatus::PUBLISHED->value,
            BlogPostStatus::SCHEDULED->value,
        ]);
    }

    public function isPublished(): bool
    {
        return $this->status === BlogPostStatus::PUBLISHED
            && $this->published_at !== null
            && $this->published_at <= now();
    }

    public function statusLabel(): string
    {
        return $this->status?->label() ?? ucfirst($this->status);
    }

    public function statusColor(): string
    {
        return $this->status?->color() ?? 'gray';
    }

    public function estimatedReadingTime(): int
    {
        if ($this->reading_time_minutes !== null) {
            return $this->reading_time_minutes;
        }

        $wordCount = str_word_count(strip_tags($this->content ?? ''));

        return (int) ceil($wordCount / 200);
    }
}

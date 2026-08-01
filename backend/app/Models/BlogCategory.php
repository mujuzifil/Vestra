<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BlogCategory extends Model
{
    use HasFactory;
    use HasSlug;

    protected string $slugSourceColumn = 'name';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'meta_title',
        'meta_description',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(BlogPost::class, 'blog_category_post')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    public function publicPosts(): BelongsToMany
    {
        return $this->posts()
            ->where('status', BlogPostStatus::PUBLISHED->value)
            ->where('visibility', BlogPostVisibility::PUBLIC->value)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}

<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BlogTag extends Model
{
    use HasFactory;
    use HasSlug;

    protected string $slugSourceColumn = 'name';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(BlogPost::class, 'blog_post_tag');
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

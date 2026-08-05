<?php

namespace App\Models;

use App\Enums\BlogPostStatus;
use App\Enums\BlogPostVisibility;
use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlogAuthor extends Model
{
    use HasFactory;
    use HasSlug;

    protected string $slugSourceColumn = 'name';

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'email',
        'role',
        'bio',
        'avatar',
        'social_links',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'social_links' => 'array',
            'is_active' => 'boolean',
            'user_id' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(BlogPost::class, 'author_id');
    }

    public function publicPosts(): HasMany
    {
        return $this->posts()
            ->where('status', BlogPostStatus::PUBLISHED->value)
            ->where('visibility', BlogPostVisibility::PUBLIC->value)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}

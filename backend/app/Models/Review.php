<?php

namespace App\Models;

use App\Enums\ReviewStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'rating',
        'title',
        'comment',
        'pros',
        'cons',
        'status',
        'is_hidden',
        'is_featured',
        'is_pinned',
        'helpful_count',
        'reported_count',
        'admin_reply',
        'admin_reply_at',
        'admin_reply_by',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_hidden' => 'boolean',
            'is_featured' => 'boolean',
            'is_pinned' => 'boolean',
            'helpful_count' => 'integer',
            'reported_count' => 'integer',
            'pros' => 'array',
            'cons' => 'array',
            'admin_reply_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ReviewImage::class)->orderBy('sort_order');
    }

    public function helpfulVotes(): HasMany
    {
        return $this->hasMany(ReviewHelpfulVote::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ReviewReport::class);
    }

    public function adminReplier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_reply_by');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', ReviewStatus::PENDING->value);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', ReviewStatus::APPROVED->value);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', ReviewStatus::REJECTED->value);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_hidden', false);
    }

    public function scopeHidden(Builder $query): Builder
    {
        return $query->where('is_hidden', true);
    }

    public function statusLabel(): string
    {
        return ReviewStatus::tryFrom($this->status)?->label() ?? ucfirst($this->status);
    }

    public function statusColor(): string
    {
        return ReviewStatus::tryFrom($this->status)?->color() ?? 'gray';
    }

    public function moderationStatusLabel(): string
    {
        return $this->is_hidden ? 'Hidden' : 'Visible';
    }

    public function moderationStatusColor(): string
    {
        return $this->is_hidden ? 'danger' : 'success';
    }

    public function ratingColor(): string
    {
        return match (true) {
            $this->rating >= 4 => 'success',
            $this->rating === 3 => 'warning',
            default => 'danger',
        };
    }

    public static function pendingModerationCount(): int
    {
        return cache()->remember('admin.reviews.pending_count', 300, function (): int {
            return static::pending()->count();
        });
    }
}

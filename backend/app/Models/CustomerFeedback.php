<?php

namespace App\Models;

use App\Enums\FeedbackCategory;
use App\Enums\FeedbackStatus;
use App\Enums\Priority;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerFeedback extends Model
{
    use HasFactory;

    protected $table = 'customer_feedback';

    protected $fillable = [
        'user_id',
        'category',
        'subject',
        'message',
        'status',
        'priority',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead(Builder $query): Builder
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeByPriority(Builder $query, string $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    public function scopeRecentlyReceived(Builder $query, int $days = 7): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeRecentlyUpdated(Builder $query, int $days = 7): Builder
    {
        return $query->where('updated_at', '>=', now()->subDays($days));
    }

    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }

    public function markAsUnread(): void
    {
        $this->update(['read_at' => null]);
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function statusLabel(): string
    {
        return FeedbackStatus::tryFrom($this->status)?->label() ?? ucfirst($this->status);
    }

    public function statusColor(): string
    {
        return FeedbackStatus::tryFrom($this->status)?->color() ?? 'gray';
    }

    public function categoryLabel(): string
    {
        return FeedbackCategory::tryFrom($this->category)?->label() ?? ucfirst($this->category);
    }

    public function priorityLabel(): string
    {
        return Priority::tryFrom($this->priority)?->label() ?? ucfirst($this->priority);
    }

    public function priorityColor(): string
    {
        return Priority::tryFrom($this->priority)?->color() ?? 'gray';
    }
}

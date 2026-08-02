<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'assignee_id',
        'created_by_id',
        'related_type',
        'related_id',
        'due_date',
        'completed_at',
        'internal_notes',
        'attachment_paths',
    ];

    protected function casts(): array
    {
        return [
            'status' => TaskStatus::class,
            'priority' => TaskPriority::class,
            'due_date' => 'datetime',
            'completed_at' => 'datetime',
            'attachment_paths' => 'array',
        ];
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeStatusIn(Builder $query, array $statuses): Builder
    {
        return $query->whereIn('status', $statuses);
    }

    public function scopePriorityIn(Builder $query, array $priorities): Builder
    {
        return $query->whereIn('priority', $priorities);
    }

    public function scopeAssignedTo(Builder $query, int $userId): Builder
    {
        return $query->where('assignee_id', $userId);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereNotIn('status', [
            TaskStatus::COMPLETED->value,
            TaskStatus::CANCELLED->value,
            TaskStatus::ARCHIVED->value,
        ])->whereNotNull('due_date')->where('due_date', '<', now());
    }

    public function scopeDueToday(Builder $query): Builder
    {
        return $query->whereNotNull('due_date')->whereDate('due_date', today());
    }

    public function scopeDueThisWeek(Builder $query): Builder
    {
        return $query->whereNotNull('due_date')->whereBetween('due_date', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', [
            TaskStatus::COMPLETED->value,
            TaskStatus::CANCELLED->value,
            TaskStatus::ARCHIVED->value,
        ]);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        $like = '%'.str_replace('%', '\\%', $term).'%';

        return $query->where(function (Builder $q) use ($like) {
            $q->where('title', 'like', $like)
                ->orWhere('description', 'like', $like)
                ->orWhere('internal_notes', 'like', $like)
                ->orWhereHas('assignee', fn (Builder $assignee) => $assignee->where('name', 'like', $like))
                ->orWhereHas('creator', fn (Builder $creator) => $creator->where('name', 'like', $like));
        });
    }

    public function isOverdue(): bool
    {
        if ($this->completed_at !== null || in_array($this->status, [TaskStatus::CANCELLED, TaskStatus::ARCHIVED], true)) {
            return false;
        }

        return $this->due_date !== null && $this->due_date->isPast();
    }

    public function markCompleted(?User $user = null): void
    {
        $this->status = TaskStatus::COMPLETED;
        $this->completed_at = now();
        $this->save();
    }

    public function markInProgress(?User $user = null): void
    {
        $this->status = TaskStatus::IN_PROGRESS;
        $this->save();
    }

    public function displayRelatedTo(): ?string
    {
        $related = $this->related;

        if (! $related) {
            return null;
        }

        return $related->name
            ?? $related->title
            ?? $related->company_name
            ?? $related->reference_number
            ?? $related->subject
            ?? ('#'.$related->getKey());
    }

    public function relatedTypeLabel(): ?string
    {
        if (! $this->related_type) {
            return null;
        }

        return match (class_basename($this->related_type)) {
            'User' => 'Customer',
            'CompanyProfile' => 'Company',
            'QuoteRequest' => 'Quote',
            'DistributorRequest' => 'Distributor',
            'Distributor' => 'Distributor',
            'SupportTicket' => 'Support',
            'Product' => 'Product',
            default => str_replace('App\\Models\\', '', $this->related_type),
        };
    }
}

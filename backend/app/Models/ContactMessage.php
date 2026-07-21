<?php

namespace App\Models;

use App\Enums\ContactStatus;
use App\Enums\Priority;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'status',
        'priority',
        'read_at',
        'reply',
        'replied_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ContactStatus::class,
            'read_at' => 'datetime',
            'replied_at' => 'datetime',
        ];
    }

    public function scopeNew(Builder $query): Builder
    {
        return $query->where('status', ContactStatus::NEW->value);
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

    public function isReplied(): bool
    {
        return $this->replied_at !== null;
    }

    public function statusLabel(): string
    {
        return $this->status->label();
    }

    public function statusColor(): string
    {
        return $this->status->color();
    }

    public function priorityLabel(): string
    {
        return Priority::tryFrom($this->priority)?->label() ?? ucfirst($this->priority);
    }

    public function priorityColor(): string
    {
        return Priority::tryFrom($this->priority)?->color() ?? 'gray';
    }

    public static function newCount(): int
    {
        return cache()->remember('admin.contact_messages.new_count', 300, function (): int {
            return static::new()->count();
        });
    }
}

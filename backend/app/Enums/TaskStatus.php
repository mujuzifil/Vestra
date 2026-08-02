<?php

namespace App\Enums;

enum TaskStatus: string
{
    case NEW = 'new';
    case ASSIGNED = 'assigned';
    case IN_PROGRESS = 'in_progress';
    case WAITING = 'waiting';
    case BLOCKED = 'blocked';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::NEW => 'New',
            self::ASSIGNED => 'Assigned',
            self::IN_PROGRESS => 'In Progress',
            self::WAITING => 'Waiting',
            self::BLOCKED => 'Blocked',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
            self::ARCHIVED => 'Archived',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::NEW => 'info',
            self::ASSIGNED => 'primary',
            self::IN_PROGRESS => 'warning',
            self::WAITING => 'gray',
            self::BLOCKED => 'danger',
            self::COMPLETED => 'success',
            self::CANCELLED => 'gray',
            self::ARCHIVED => 'gray',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::NEW => 'heroicon-o-sparkles',
            self::ASSIGNED => 'heroicon-o-user-circle',
            self::IN_PROGRESS => 'heroicon-o-arrow-path',
            self::WAITING => 'heroicon-o-clock',
            self::BLOCKED => 'heroicon-o-no-symbol',
            self::COMPLETED => 'heroicon-o-check-circle',
            self::CANCELLED => 'heroicon-o-x-circle',
            self::ARCHIVED => 'heroicon-o-archive-box',
        };
    }

    public function isOpen(): bool
    {
        return ! in_array($this, [self::COMPLETED, self::CANCELLED, self::ARCHIVED], true);
    }

    /**
     * @return array<int, self>
     */
    public static function open(): array
    {
        return [
            self::NEW,
            self::ASSIGNED,
            self::IN_PROGRESS,
            self::WAITING,
            self::BLOCKED,
        ];
    }
}

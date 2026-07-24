<?php

namespace App\Enums;

enum NotificationStatus: string
{
    case PENDING = 'pending';
    case QUEUED = 'queued';
    case PROCESSING = 'processing';
    case SENT = 'sent';
    case DELIVERED = 'delivered';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::QUEUED => 'Queued',
            self::PROCESSING => 'Processing',
            self::SENT => 'Sent',
            self::DELIVERED => 'Delivered',
            self::FAILED => 'Failed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'gray',
            self::QUEUED => 'info',
            self::PROCESSING => 'warning',
            self::SENT => 'success',
            self::DELIVERED => 'success',
            self::FAILED => 'danger',
            self::CANCELLED => 'secondary',
        };
    }
}

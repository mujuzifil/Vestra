<?php

namespace App\Enums;

enum DistributorStatus: string
{
    case PENDING = 'pending';
    case UNDER_REVIEW = 'under_review';
    case INFORMATION_REQUESTED = 'information_requested';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::UNDER_REVIEW => 'Under Review',
            self::INFORMATION_REQUESTED => 'Information Requested',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::UNDER_REVIEW => 'primary',
            self::INFORMATION_REQUESTED => 'info',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
        };
    }
}

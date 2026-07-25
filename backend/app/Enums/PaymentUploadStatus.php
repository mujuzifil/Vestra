<?php

namespace App\Enums;

enum PaymentUploadStatus: string
{
    case UPLOADED = 'uploaded';
    case PENDING_VERIFICATION = 'pending_verification';
    case VERIFIED = 'verified';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::UPLOADED => 'Uploaded',
            self::PENDING_VERIFICATION => 'Pending Verification',
            self::VERIFIED => 'Verified',
            self::REJECTED => 'Rejected',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::UPLOADED => 'gray',
            self::PENDING_VERIFICATION => 'warning',
            self::VERIFIED => 'success',
            self::REJECTED => 'danger',
        };
    }
}

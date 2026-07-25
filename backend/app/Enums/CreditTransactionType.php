<?php

namespace App\Enums;

enum CreditTransactionType: string
{
    case AUTHORIZATION = 'authorization';
    case CAPTURE = 'capture';
    case RELEASE = 'release';
    case PAYMENT = 'payment';
    case ADJUSTMENT = 'adjustment';
    case LIMIT_CHANGE = 'limit_change';

    public function label(): string
    {
        return match ($this) {
            self::AUTHORIZATION => 'Authorization',
            self::CAPTURE => 'Capture',
            self::RELEASE => 'Release',
            self::PAYMENT => 'Payment',
            self::ADJUSTMENT => 'Adjustment',
            self::LIMIT_CHANGE => 'Limit Change',
        };
    }
}

<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case COD = 'cod';
    case MTN_MOMO = 'mtn_momo';
    case AIRTEL_MONEY = 'airtel_money';
    case CARD = 'card';
    case CREDIT = 'credit';

    public function label(): string
    {
        return match ($this) {
            self::COD => 'Cash on Delivery',
            self::MTN_MOMO => 'MTN Mobile Money',
            self::AIRTEL_MONEY => 'Airtel Money',
            self::CARD => 'Card Payment',
            self::CREDIT => 'Credit Account',
        };
    }
}

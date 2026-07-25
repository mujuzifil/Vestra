<?php

namespace App\Enums;

enum StockMovementType: string
{
    case IN = 'in';
    case OUT = 'out';
    case ADJUSTMENT = 'adjustment';
    case TRANSFER_IN = 'transfer_in';
    case TRANSFER_OUT = 'transfer_out';

    public function label(): string
    {
        return match ($this) {
            self::IN => 'Stock In',
            self::OUT => 'Stock Out',
            self::ADJUSTMENT => 'Adjustment',
            self::TRANSFER_IN => 'Transfer In',
            self::TRANSFER_OUT => 'Transfer Out',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::IN, self::TRANSFER_IN => 'success',
            self::OUT, self::TRANSFER_OUT => 'danger',
            self::ADJUSTMENT => 'warning',
        };
    }
}

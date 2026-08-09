<?php

namespace App\Enums;

enum DistributorStockAvailability: string
{
    case IN_STOCK = 'in_stock';
    case LOW_STOCK = 'low_stock';
    case OUT_OF_STOCK = 'out_of_stock';

    public function label(): string
    {
        return match ($this) {
            self::IN_STOCK => 'In Stock',
            self::LOW_STOCK => 'Low Stock',
            self::OUT_OF_STOCK => 'Out of Stock',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::IN_STOCK => 'success',
            self::LOW_STOCK => 'warning',
            self::OUT_OF_STOCK => 'danger',
        };
    }
}

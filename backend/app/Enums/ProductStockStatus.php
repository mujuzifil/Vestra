<?php

namespace App\Enums;

enum ProductStockStatus: string
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

    public static function options(): array
    {
        return array_map(
            fn (self $case) => ['value' => $case->value, 'label' => $case->label()],
            self::cases()
        );
    }

    public static function fromQuantity(int $quantity, ?int $threshold = null): self
    {
        $threshold ??= 10;

        if ($quantity <= 0) {
            return self::OUT_OF_STOCK;
        }

        if ($quantity <= $threshold) {
            return self::LOW_STOCK;
        }

        return self::IN_STOCK;
    }
}

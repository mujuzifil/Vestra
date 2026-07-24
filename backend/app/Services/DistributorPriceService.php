<?php

namespace App\Services;

use App\Models\Distributor;
use App\Models\Product;

class DistributorPriceService
{
    public function resolve(Product $product, int $quantity, ?Distributor $distributor = null): ?float
    {
        // 1. Negotiated price for this distributor, if effective.
        if ($distributor) {
            $negotiated = $distributor->negotiatedPrices()
                ->where('product_id', $product->id)
                ->where(function ($query) {
                    $query->whereNull('effective_from')->orWhere('effective_from', '<=', now());
                })
                ->where(function ($query) {
                    $query->whereNull('effective_until')->orWhere('effective_until', '>=', now());
                })
                ->orderByDesc('effective_from')
                ->first();

            if ($negotiated) {
                return (float) $negotiated->price;
            }
        }

        // 2. Volume tier price.
        $tier = $product->distributorPriceTiers()
            ->where('min_quantity', '<=', $quantity)
            ->where(function ($query) {
                $query->whereNull('max_quantity')->orWhere('max_quantity', '>=', $quantity);
            })
            ->orderByDesc('min_quantity')
            ->first();

        if ($tier) {
            return (float) $tier->price;
        }

        // 3. Base distributor price.
        if ($product->distributor_price !== null) {
            return (float) $product->distributor_price;
        }

        return null;
    }

    public function resolveOrRetail(Product $product, int $quantity, ?Distributor $distributor = null): float
    {
        return $this->resolve($product, $quantity, $distributor) ?? (float) $product->price;
    }
}

<?php

namespace App\Observers;

use App\Enums\ProductStatus;
use App\Events\Notification\BackInStock;
use App\Events\Notification\PriceDropped;
use App\Models\Product;
use App\Services\AdminNotificationService;

class ProductObserver
{
    public function __construct(private readonly AdminNotificationService $service) {}

    public function updated(Product $product): void
    {
        if ($product->wasChanged('stock_quantity')) {
            $newStock = $product->stock_quantity;
            $oldStock = $product->getOriginal('stock_quantity');

            // Auto-update status based on stock
            if ($newStock <= 0) {
                $product->update(['status' => ProductStatus::OUT_OF_STOCK->value]);
            } elseif ($newStock > 0 && $product->status === ProductStatus::OUT_OF_STOCK) {
                $product->update(['status' => ProductStatus::ACTIVE->value]);
            }

            // Notify customers when a previously out-of-stock product is available again
            if ($oldStock <= 0 && $newStock > 0) {
                BackInStock::dispatch($product->fresh());
            }

            // Send low stock alert
            if ($newStock <= 10 && $newStock > 0) {
                $this->service->lowStock($product->name, $product->sku, $newStock);
            }
        }

        if ($product->wasChanged('price')) {
            $oldPrice = (float) $product->getOriginal('price');
            $newPrice = (float) $product->price;

            if ($newPrice < $oldPrice) {
                PriceDropped::dispatch($product->fresh(), $oldPrice, $newPrice);
            }
        }
    }
}

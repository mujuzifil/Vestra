<?php

namespace App\Services;

use App\Enums\QuotationStatus;
use App\Models\Distributor;
use App\Models\Product;
use App\Models\QuotationItem;
use App\Models\QuotationRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuotationService
{
    public function __construct(
        private readonly DistributorPriceService $priceService,
    ) {}

    public function createDraft(Distributor $distributor, array $data): QuotationRequest
    {
        return DB::transaction(function () use ($distributor, $data) {
            $quotation = QuotationRequest::create([
                'distributor_id' => $distributor->id,
                'reference_number' => $this->generateReference(),
                'status' => QuotationStatus::DRAFT->value,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->syncItems($quotation, $data['items'] ?? []);
            $this->recalculateTotals($quotation);

            return $quotation->load('items');
        });
    }

    public function updateDraft(QuotationRequest $quotation, array $data): QuotationRequest
    {
        return DB::transaction(function () use ($quotation, $data) {
            if (! empty($data['notes'])) {
                $quotation->notes = $data['notes'];
            }

            $quotation->save();

            if (isset($data['items'])) {
                $this->syncItems($quotation, $data['items']);
            }

            $this->recalculateTotals($quotation);

            return $quotation->fresh()->load('items');
        });
    }

    public function submit(QuotationRequest $quotation): QuotationRequest
    {
        if (! $quotation->isEditable()) {
            throw ValidationException::withMessages([
                'status' => ['Only draft or submitted quotations can be submitted.'],
            ]);
        }

        if ($quotation->items()->count() === 0) {
            throw ValidationException::withMessages([
                'items' => ['A quotation must contain at least one item.'],
            ]);
        }

        $this->recalculateTotals($quotation);

        $quotation->update([
            'status' => QuotationStatus::SUBMITTED->value,
            'submitted_at' => now(),
        ]);

        return $quotation->load('items');
    }

    public function accept(QuotationRequest $quotation): QuotationRequest
    {
        if ($quotation->status !== QuotationStatus::QUOTED) {
            throw ValidationException::withMessages([
                'status' => ['Only quoted quotations can be accepted.'],
            ]);
        }

        if ($quotation->expires_at && $quotation->expires_at->isPast()) {
            throw ValidationException::withMessages([
                'status' => ['This quotation has expired.'],
            ]);
        }

        $quotation->update(['status' => QuotationStatus::ACCEPTED->value]);

        return $quotation->load('items');
    }

    public function deleteDraft(QuotationRequest $quotation): void
    {
        if ($quotation->status !== QuotationStatus::DRAFT) {
            throw ValidationException::withMessages([
                'status' => ['Only draft quotations can be deleted.'],
            ]);
        }

        $quotation->items()->delete();
        $quotation->delete();
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function syncItems(QuotationRequest $quotation, array $items): void
    {
        $quotation->items()->delete();

        foreach ($items as $item) {
            $product = Product::find($item['product_id']);

            if (! $product) {
                throw ValidationException::withMessages([
                    "items.{$item['product_id']}" => ['Product not found.'],
                ]);
            }

            $quantity = (int) ($item['quantity'] ?? 1);
            $price = $this->priceService->resolveOrRetail($product, $quantity, $quotation->distributor);

            QuotationItem::create([
                'quotation_request_id' => $quotation->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'unit_price' => $price,
                'quantity' => $quantity,
                'line_total' => round($price * $quantity, 2),
                'notes' => $item['notes'] ?? null,
            ]);
        }
    }

    private function recalculateTotals(QuotationRequest $quotation): void
    {
        $quotation->load('items');

        $subtotal = $quotation->items->sum('line_total');
        $taxRate = $this->taxRate();
        $taxAmount = round($subtotal * $taxRate, 2);
        $totalAmount = round($subtotal + $taxAmount, 2);

        $quotation->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
        ]);
    }

    private function taxRate(): float
    {
        $setting = \App\Models\Setting::where('key', 'tax_rate')->first();

        return is_numeric($setting?->typedValue()) ? (float) $setting->typedValue() : 0.18;
    }

    private function generateReference(): string
    {
        $prefix = 'QT';
        $date = now()->format('Ymd');
        $sequence = QuotationRequest::whereDate('created_at', today())->count() + 1;
        $sequence = str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$date}-{$sequence}";
    }
}

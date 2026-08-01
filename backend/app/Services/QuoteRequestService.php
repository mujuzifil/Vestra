<?php

namespace App\Services;

use App\Enums\QuoteRequestStatus;
use App\Events\Notification\QuoteRequestSubmitted;
use App\Mail\QuoteRequestReceivedMail;
use App\Models\QuoteRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class QuoteRequestService
{
    public function submit(array $data): QuoteRequest
    {
        return DB::transaction(function () use ($data) {
            $quote = QuoteRequest::create([
                'reference_number' => $this->generateReference(),
                'full_name' => $data['full_name'],
                'company_name' => $data['company_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'district' => $data['district'] ?? null,
                'city' => $data['city'] ?? null,
                'address' => $data['address'] ?? null,
                'preferred_delivery_date' => $data['preferred_delivery_date'] ?? null,
                'delivery_location' => $data['delivery_location'] ?? null,
                'status' => QuoteRequestStatus::PENDING->value,
                'source' => $data['source'] ?? 'website',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'requirements' => $data['requirements'] ?? null,
            ]);

            $this->syncItems($quote, $data['items'] ?? []);

            $quote->load('items');

            event(new QuoteRequestSubmitted($quote));

            Mail::to($quote->email)->send(new QuoteRequestReceivedMail($quote));

            return $quote;
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function syncItems(QuoteRequest $quote, array $items): void
    {
        if (empty($items)) {
            return;
        }

        foreach ($items as $item) {
            $quote->items()->create([
                'product_id' => $item['product_id'] ?? null,
                'product_name' => $item['product_name'],
                'package_size' => $item['package_size'] ?? null,
                'quantity' => $item['quantity'],
                'notes' => $item['notes'] ?? null,
            ]);
        }
    }

    private function generateReference(): string
    {
        $prefix = 'QR';
        $date = now()->format('Ymd');
        $sequence = QuoteRequest::whereDate('created_at', today())->count() + 1;
        $sequence = str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$date}-{$sequence}";
    }
}

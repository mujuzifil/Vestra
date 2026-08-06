<?php

namespace App\Services;

use App\Enums\QuoteRequestStatus;
use App\Events\Notification\QuoteRequestSubmitted;
use App\Mail\QuoteRequestReceivedMail;
use App\Models\QuoteRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class QuoteRequestService
{
    public function __construct(
        private readonly CompanyProfileService $companyProfiles,
    ) {}

    public function submit(array $data): QuoteRequest
    {
        return DB::transaction(function () use ($data) {
            $user = auth('sanctum')->user();

            $attachments = $data['attachments'] ?? null;
            unset($data['attachments']);

            $companyProfile = $user !== null
                ? $this->companyProfiles->requireForAuthenticatedUser($user, $data)
                : $this->companyProfiles->resolveForQuote(null, $data);

            $quote = QuoteRequest::create([
                'reference_number' => $this->generateReference(),
                'user_id' => $user?->id ?? $companyProfile?->user_id,
                'company_profile_id' => $companyProfile?->id,
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
                'priority' => $data['priority'] ?? null,
                'estimated_value' => $data['estimated_value'] ?? null,
                'expected_close_date' => $data['expected_close_date'] ?? null,
                'source' => $data['source'] ?? 'website',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'requirements' => $data['requirements'] ?? null,
            ]);

            $this->syncItems($quote, $data['items'] ?? []);

            if (is_array($attachments) && count($attachments) > 0) {
                $stored = [];
                /** @var UploadedFile $file */
                foreach ($attachments as $file) {
                    $stored[] = $file->store("quote_attachments/{$quote->id}", 'public');
                }
                $quote->attachments = $stored;
                $quote->save();
            }

            $quote->load(['items', 'companyProfile']);

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

<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FlutterwaveGateway implements PaymentGatewayInterface
{
    private string $baseUrl = 'https://api.flutterwave.com/v3';
    private string $secretKey;
    private string $publicKey;
    private string $webhookSecret;

    public function __construct()
    {
        $this->secretKey = config('services.flutterwave.secret_key') ?? '';
        $this->publicKey = config('services.flutterwave.public_key') ?? '';
        $this->webhookSecret = config('services.flutterwave.webhook_secret') ?? '';
    }

    public function initiate(float $amount, string $currency, string $reference, array $meta): array
    {
        if (empty($this->secretKey)) {
            return [
                'success' => false,
                'message' => 'Flutterwave not configured. Please set FLUTTERWAVE_SECRET_KEY.',
            ];
        }

        $response = Http::withToken($this->secretKey)
            ->post("{$this->baseUrl}/payments", [
                'tx_ref' => $reference,
                'amount' => $amount,
                'currency' => $currency,
                'redirect_url' => $meta['redirect_url'] ?? route('payment.callback'),
                'meta' => [
                    'order_id' => $meta['order_id'] ?? null,
                ],
                'customer' => [
                    'email' => $meta['customer_email'] ?? '',
                    'phone_number' => $meta['customer_phone'] ?? '',
                    'name' => $meta['customer_name'] ?? '',
                ],
                'customizations' => [
                    'title' => 'VESTRA Order Payment',
                    'logo' => $meta['logo'] ?? '',
                ],
            ]);

        $data = $response->json();

        if ($response->successful() && ($data['status'] ?? '') === 'success') {
            return [
                'success' => true,
                'payment_link' => $data['data']['link'] ?? null,
                'transaction_reference' => $reference,
            ];
        }

        return [
            'success' => false,
            'message' => $data['message'] ?? 'Payment initiation failed.',
        ];
    }

    public function verify(string $reference): array
    {
        if (empty($this->secretKey)) {
            return ['success' => false, 'message' => 'Flutterwave not configured.'];
        }

        $response = Http::withToken($this->secretKey)
            ->get("{$this->baseUrl}/transactions/verify_by_reference", [
                'tx_ref' => $reference,
            ]);

        $data = $response->json();

        if ($response->successful() && ($data['status'] ?? '') === 'success') {
            $transaction = $data['data'] ?? [];
            return [
                'success' => true,
                'status' => $transaction['status'] ?? 'unknown',
                'amount' => $transaction['amount'] ?? 0,
                'currency' => $transaction['currency'] ?? 'UGX',
                'provider_reference' => $transaction['id'] ?? null,
                'paid_at' => $transaction['created_at'] ?? null,
            ];
        }

        return ['success' => false, 'message' => $data['message'] ?? 'Verification failed.'];
    }

    public function handleCallback(array $payload): array
    {
        $status = $payload['status'] ?? '';
        $txRef = $payload['tx_ref'] ?? '';

        if ($status === 'successful' || $status === 'completed') {
            return $this->verify($txRef);
        }

        return ['success' => false, 'status' => $status, 'message' => 'Payment not successful.'];
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        // Prefer the dedicated webhook secret; fall back to the API secret key
        // only when the webhook secret is not configured.
        $secret = ! empty($this->webhookSecret) ? $this->webhookSecret : $this->secretKey;

        if (empty($secret) || empty($signature)) {
            return false;
        }

        $computed = hash_hmac('sha256', $payload, $secret);

        return hash_equals($computed, $signature);
    }
}

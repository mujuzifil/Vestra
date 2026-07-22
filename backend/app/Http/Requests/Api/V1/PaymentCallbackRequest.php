<?php

namespace App\Http\Requests\Api\V1;

use App\Services\FlutterwaveGateway;
use Illuminate\Foundation\Http\FormRequest;

class PaymentCallbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->verifySignature();
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string'],
            'tx_ref' => ['required', 'string'],
            'transaction_id' => ['nullable', 'string'],
        ];
    }

    private function verifySignature(): bool
    {
        // Prefer the dedicated webhook secret; fall back to the API secret key
        // only when the webhook secret is not configured.
        $secret = config('services.flutterwave.webhook_secret')
            ?: config('services.flutterwave.secret_key')
            ?: '';

        if (empty($secret)) {
            return false;
        }

        $signature = $this->header('verif-hash');
        if (empty($signature)) {
            return false;
        }

        $payload = $this->getContent();
        $computed = hash_hmac('sha256', $payload, $secret);

        return hash_equals($computed, $signature);
    }

    protected function failedAuthorization()
    {
        \Illuminate\Support\Facades\Log::warning('Webhook signature verification failed.', [
            'tx_ref' => $this->input('tx_ref'),
            'status' => $this->input('status'),
            'ip' => $this->ip(),
            'user_agent' => $this->userAgent(),
        ]);

        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Invalid webhook signature.',
            ], 403)
        );
    }
}

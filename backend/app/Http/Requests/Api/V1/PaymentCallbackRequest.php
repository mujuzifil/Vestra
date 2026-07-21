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
        $secret = config('services.flutterwave.secret_key') ?? '';
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
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Invalid webhook signature.',
            ], 403)
        );
    }
}

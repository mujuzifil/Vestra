<?php

namespace App\Contracts;

use App\Models\PaymentTransaction;

interface PaymentGatewayInterface
{
    public function initiate(float $amount, string $currency, string $reference, array $meta): array;

    public function verify(string $reference): array;

    public function handleCallback(array $payload): array;
}

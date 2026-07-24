<?php

namespace App\Services\Sms;

use App\Contracts\SmsProviderInterface;
use Illuminate\Support\Facades\Log;

class LogSmsProvider implements SmsProviderInterface
{
    public function send(string $to, string $message): array
    {
        Log::channel('daily')->info('[SMS] Message logged for delivery', [
            'provider' => $this->name(),
            'to' => $to,
            'message' => $message,
        ]);

        return [
            'success' => true,
            'reference' => 'log-'.uniqid(),
        ];
    }

    public function name(): string
    {
        return 'log';
    }
}

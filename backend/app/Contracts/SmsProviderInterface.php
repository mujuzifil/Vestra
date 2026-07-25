<?php

namespace App\Contracts;

interface SmsProviderInterface
{
    /**
     * Send an SMS message.
     *
     * @param  string  $to  The recipient phone number in E.164 or local format.
     * @param  string  $message  The message body.
     * @return array{success: bool, reference?: string, error?: string}
     */
    public function send(string $to, string $message): array;

    /**
     * Provider name for logging/display.
     */
    public function name(): string;
}

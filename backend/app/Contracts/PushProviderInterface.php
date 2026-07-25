<?php

namespace App\Contracts;

interface PushProviderInterface
{
    /**
     * Send a push notification.
     *
     * @param  string  $token  The device push token.
     * @param  string  $title  Notification title.
     * @param  string  $body  Notification body.
     * @param  array<string, mixed>  $data  Optional payload data.
     * @return array{success: bool, reference?: string, error?: string}
     */
    public function send(string $token, string $title, string $body, array $data = []): array;

    /**
     * Provider name for logging/display.
     */
    public function name(): string;
}

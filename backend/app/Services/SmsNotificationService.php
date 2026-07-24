<?php

namespace App\Services;

use App\Contracts\SmsProviderInterface;
use Illuminate\Support\Facades\Log;

class SmsNotificationService
{
    public function __construct(
        protected NotificationTemplateService $templateService,
        protected SmsProviderInterface $provider
    ) {}

    /**
     * Send an SMS notification to a recipient.
     */
    public function send(string $to, string $templateKey, array $variables = [], array $metadata = []): bool
    {
        try {
            $template = $this->templateService->resolve($templateKey, $metadata['subject'] ?? 'VESTRA Notification');
            $rendered = $this->templateService->renderTemplate($template, $variables);

            if ($rendered['sms_body'] === '') {
                return false;
            }

            $result = $this->provider->send($to, $rendered['sms_body']);

            return $result['success'] ?? false;
        } catch (\Throwable $e) {
            Log::error('Failed to send SMS notification', [
                'to' => $to,
                'template' => $templateKey,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}

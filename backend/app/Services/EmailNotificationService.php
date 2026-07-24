<?php

namespace App\Services;

use App\Mail\TemplateMailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailNotificationService
{
    public function __construct(
        protected NotificationTemplateService $templateService
    ) {}

    /**
     * Send a templated email to a recipient.
     */
    public function send(string $to, string $templateKey, array $variables = [], array $metadata = []): bool
    {
        try {
            $template = $this->templateService->resolve($templateKey, $metadata['subject'] ?? 'VESTRA Notification');
            $rendered = $this->templateService->renderTemplate($template, $variables);

            Mail::to($to)->queue(new TemplateMailable(
                subjectLine: $rendered['subject'],
                htmlBody: $rendered['email_body'],
                plainBody: strip_tags($rendered['email_body']),
                metadata: array_merge($metadata, ['template_key' => $template->key])
            ));

            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to queue email notification', [
                'to' => $to,
                'template' => $templateKey,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}

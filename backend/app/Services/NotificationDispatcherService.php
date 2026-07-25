<?php

namespace App\Services;

use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class NotificationDispatcherService
{
    public function __construct(
        protected NotificationTemplateService $templateService,
        protected NotificationPreferenceService $preferenceService,
        protected NotificationDeliveryService $deliveryService,
        protected EmailNotificationService $emailService,
        protected SmsNotificationService $smsService,
    ) {}

    /**
     * Dispatch a notification to a user across selected channels.
     *
     * @param  User  $user
     * @param  string  $templateKey
     * @param  array  $variables
     * @param  array  $channels  List of NotificationChannel values
     * @param  string  $topic
     * @param  array  $metadata
     * @return Collection<int, \App\Models\NotificationDelivery>
     */
    public function dispatch(
        User $user,
        string $templateKey,
        array $variables = [],
        array $channels = [],
        string $topic = 'order_updates',
        array $metadata = []
    ): Collection {
        $channels = $this->normalizeChannels($channels);
        $template = $this->templateService->resolve($templateKey, $metadata['subject'] ?? 'VESTRA Notification');
        $rendered = $this->templateService->renderTemplate($template, $variables);
        $deliveries = new Collection;

        foreach ($channels as $channel) {
            if (! $this->preferenceService->wantsChannel($user, $channel, $topic)) {
                continue;
            }

            $context = array_merge($metadata, [
                'subject' => $rendered['subject'],
                'content' => $channel === NotificationChannel::IN_APP ? $rendered['in_app_body'] : $rendered['email_body'],
            ]);

            $delivery = $this->deliveryService->create($user, $template, $channel, $variables, $context);

            try {
                match ($channel) {
                    NotificationChannel::EMAIL => $this->sendEmail($user, $templateKey, $variables, $metadata, $delivery),
                    NotificationChannel::SMS => $this->sendSms($user, $templateKey, $variables, $metadata, $delivery),
                    NotificationChannel::IN_APP => $this->sendInApp($user, $rendered, $metadata, $delivery),
                    NotificationChannel::PUSH => $this->sendPush($user, $templateKey, $variables, $metadata, $delivery),
                };
            } catch (\Throwable $e) {
                $this->deliveryService->markFailed($delivery, $e->getMessage());
                Log::error('Notification dispatch failed', [
                    'user_id' => $user->id,
                    'channel' => $channel->value,
                    'template' => $templateKey,
                    'error' => $e->getMessage(),
                ]);
            }

            $deliveries->push($delivery->fresh());
        }

        return $deliveries;
    }

    /**
     * Dispatch to email and update delivery record.
     */
    protected function sendEmail(User $user, string $templateKey, array $variables, array $metadata, $delivery): void
    {
        $success = $this->emailService->send($user->email, $templateKey, $variables, $metadata);

        $this->deliveryService->markStatus(
            $delivery,
            $success ? NotificationStatus::QUEUED : NotificationStatus::FAILED,
            $success ? 'Queued for delivery' : 'Email service returned failure'
        );
    }

    /**
     * Dispatch to SMS and update delivery record.
     */
    protected function sendSms(User $user, string $templateKey, array $variables, array $metadata, $delivery): void
    {
        $phone = $user->phone ?? $metadata['phone'] ?? null;

        if (empty($phone)) {
            $this->deliveryService->markFailed($delivery, 'No phone number available');

            return;
        }

        $success = $this->smsService->send($phone, $templateKey, $variables, $metadata);

        $this->deliveryService->markStatus(
            $delivery,
            $success ? NotificationStatus::SENT : NotificationStatus::FAILED,
            $success ? 'Sent to SMS provider' : 'SMS provider returned failure'
        );
    }

    /**
     * Dispatch an in-app/database notification.
     */
    protected function sendInApp(User $user, array $rendered, array $metadata, $delivery): void
    {
        $notification = new SystemNotification(
            templateKey: $metadata['template_key'] ?? 'system.notification',
            title: $rendered['subject'],
            subject: $rendered['subject'],
            emailBody: $rendered['email_body'],
            inAppBody: $rendered['in_app_body'] ?: $rendered['email_body'],
            variables: $variables,
            channels: [NotificationChannel::IN_APP->value],
            actionUrl: $metadata['action_url'] ?? null,
            priority: $metadata['priority'] ?? 'normal'
        );

        $user->notify($notification);

        $this->deliveryService->markStatus($delivery, NotificationStatus::SENT, 'Delivered in-app');
    }

    /**
     * Placeholder for push notifications.
     */
    protected function sendPush(User $user, string $templateKey, array $variables, array $metadata, $delivery): void
    {
        $this->deliveryService->markStatus($delivery, NotificationStatus::FAILED, 'Push provider not configured');
    }

    /**
     * Normalize channel input to NotificationChannel enums.
     *
     * @return NotificationChannel[]
     */
    protected function normalizeChannels(array $channels): array
    {
        if (empty($channels)) {
            return [NotificationChannel::IN_APP];
        }

        return collect($channels)
            ->map(fn ($channel) => $channel instanceof NotificationChannel ? $channel : NotificationChannel::tryFrom((string) $channel))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}

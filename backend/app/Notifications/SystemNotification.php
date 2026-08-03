<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use App\Enums\NotificationChannel;
use App\Enums\NotificationPriority;
use App\Enums\NotificationType;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SystemNotification extends Notification
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $variables
     * @param  array<string>  $channels
     */
    public function __construct(
        private readonly string $templateKey,
        private readonly string $title,
        private readonly ?string $subject = null,
        private readonly ?string $emailBody = null,
        private readonly ?string $smsBody = null,
        private readonly ?string $inAppBody = null,
        private readonly array $variables = [],
        private readonly array $channels = [NotificationChannel::IN_APP->value],
        private readonly ?string $actionUrl = null,
        private readonly NotificationPriority|string $priority = NotificationPriority::INFORMATION,
        private readonly NotificationCategory|string|null $category = null,
        private readonly NotificationType|string|null $type = null,
        private readonly ?string $relatedType = null,
        private readonly ?int $relatedId = null,
        private readonly ?int $triggeredByUserId = null,
    ) {}

    public function via(object $notifiable): array
    {
        $via = [];

        foreach ($this->channels as $channel) {
            $via[] = match ($channel) {
                NotificationChannel::EMAIL->value => 'mail',
                NotificationChannel::SMS->value => 'sms',
                NotificationChannel::IN_APP->value => 'database',
                NotificationChannel::PUSH->value => 'push',
                default => $channel,
            };
        }

        return array_values(array_unique($via));
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->replaceVariables($this->subject ?? $this->title);
        $body = $this->replaceVariables($this->emailBody ?? $this->inAppBody ?? '');

        $message = (new MailMessage)
            ->subject($subject)
            ->line($this->replaceVariables($this->title));

        if (! empty($body)) {
            $message->line($body);
        }

        if ($this->actionUrl) {
            $message->action('View', $this->actionUrl);
        }

        return $message;
    }

    public function toDatabase(object $notifiable): array
    {
        $type = $this->resolveType();
        $category = $this->resolveCategory($type);
        $priority = $this->resolvePriority();

        return [
            'template_key' => $this->templateKey,
            'title' => $this->replaceVariables($this->title),
            'message' => $this->replaceVariables($this->inAppBody ?? $this->emailBody ?? $this->smsBody ?? ''),
            'priority' => $priority->value,
            'category' => $category->value,
            'type' => $type->value,
            'action_url' => $this->actionUrl,
            'variables' => $this->variables,
            'related_type' => $this->relatedType,
            'related_id' => $this->relatedId,
            'triggered_by_user_id' => $this->triggeredByUserId,
        ];
    }

    public function toSms(object $notifiable): string
    {
        return $this->replaceVariables($this->smsBody ?? $this->inAppBody ?? $this->title);
    }

    public function toPush(object $notifiable): array
    {
        $type = $this->resolveType();
        $category = $this->resolveCategory($type);
        $priority = $this->resolvePriority();

        return [
            'title' => $this->replaceVariables($this->title),
            'body' => $this->replaceVariables($this->inAppBody ?? $this->smsBody ?? $this->title),
            'action_url' => $this->actionUrl,
            'priority' => $priority->value,
            'category' => $category->value,
            'type' => $type->value,
        ];
    }

    private function resolveType(): NotificationType
    {
        if ($this->type === null) {
            return NotificationType::SYSTEM;
        }

        return $this->type instanceof NotificationType
            ? $this->type
            : NotificationType::tryFrom($this->type) ?? NotificationType::SYSTEM;
    }

    private function resolveCategory(?NotificationType $type): NotificationCategory
    {
        if ($this->category === null) {
            return $type?->category() ?? NotificationCategory::SYSTEM;
        }

        return $this->category instanceof NotificationCategory
            ? $this->category
            : NotificationCategory::tryFrom($this->category) ?? ($type?->category() ?? NotificationCategory::SYSTEM);
    }

    private function resolvePriority(): NotificationPriority
    {
        return $this->priority instanceof NotificationPriority
            ? $this->priority
            : NotificationPriority::tryFrom($this->priority) ?? NotificationPriority::INFORMATION;
    }

    private function replaceVariables(?string $content): string
    {
        if (empty($content)) {
            return '';
        }

        $replacements = [];
        foreach ($this->variables as $key => $value) {
            $replacements["{{{$key}}}"] = is_scalar($value) ? (string) $value : json_encode($value);
        }

        return strtr($content, $replacements);
    }
}

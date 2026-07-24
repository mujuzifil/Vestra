<?php

namespace App\Enums;

enum NotificationChannel: string
{
    case EMAIL = 'email';
    case SMS = 'sms';
    case IN_APP = 'in_app';
    case PUSH = 'push';
    case WHATSAPP = 'whatsapp';
    case WEBHOOK = 'webhook';

    public function label(): string
    {
        return match ($this) {
            self::EMAIL => 'Email',
            self::SMS => 'SMS',
            self::IN_APP => 'In-App',
            self::PUSH => 'Push Notification',
            self::WHATSAPP => 'WhatsApp',
            self::WEBHOOK => 'Webhook',
        };
    }
}

<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StaffWelcomeNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $temporaryPassword,
        private readonly bool $isReset = false
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $loginUrl = url('/admin/login');

        $mail = (new MailMessage)
            ->subject($this->isReset ? 'VESTRA Admin Password Reset' : 'Welcome to VESTRA Admin Portal')
            ->greeting('Hello '.$notifiable->name.',')
            ->line($this->isReset
                ? 'Your administrator password has been reset.'
                : 'An administrator account has been created for you on the VESTRA Admin Portal.')
            ->line('Email: '.$notifiable->email)
            ->line('Temporary password: '.$this->temporaryPassword)
            ->line('You will be required to change this password on first login.')
            ->action('Sign in', $loginUrl)
            ->line('Do not share this temporary password with anyone.');

        return $mail;
    }
}

<?php

namespace App\Notifications;

use App\Models\Distributor;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DistributorApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Distributor $distributor) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your VESTRA Distributor Application Has Been Approved')
            ->line("Congratulations! Your distributor application for {$this->distributor->company_name} has been approved.")
            ->action('Access Distributor Portal', url('/distributor/dashboard'))
            ->line('You can now log in and start placing wholesale orders.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'distributor_id' => $this->distributor->id,
            'title' => 'Distributor application approved',
            'message' => "Your application for {$this->distributor->company_name} has been approved.",
            'action_url' => '/distributor/dashboard',
        ];
    }
}

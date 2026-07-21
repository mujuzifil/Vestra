<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $subjectLine,
        public string $content,
        public array $data = []
    ) {}

    public function build(): self
    {
        return $this->subject('[VESTRA Admin] ' . $this->subjectLine)
            ->view('emails.admin.notification');
    }
}

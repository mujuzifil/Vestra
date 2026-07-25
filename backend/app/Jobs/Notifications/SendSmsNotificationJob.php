<?php

namespace App\Jobs\Notifications;

use App\Services\SmsNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSmsNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $to,
        public string $templateKey,
        public array $variables = [],
        public array $metadata = []
    ) {}

    public function handle(SmsNotificationService $service): void
    {
        $service->send($this->to, $this->templateKey, $this->variables, $this->metadata);
    }
}

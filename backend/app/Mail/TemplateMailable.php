<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TemplateMailable extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $subjectLine,
        public string $htmlBody,
        public string $plainBody = '',
        public array $metadata = []
    ) {}

    public function build(): self
    {
        $mailable = $this->subject($this->subjectLine)
            ->view('emails.template')
            ->with([
                'htmlBody' => $this->htmlBody,
                'metadata' => $this->metadata,
            ]);

        if ($this->plainBody !== '') {
            $mailable->text('emails.template_plain');
        }

        return $mailable;
    }
}

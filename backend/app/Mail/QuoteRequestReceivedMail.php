<?php

namespace App\Mail;

use App\Models\QuoteRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QuoteRequestReceivedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public QuoteRequest $quoteRequest) {}

    public function build(): self
    {
        return $this->subject('We received your quotation request — ' . $this->quoteRequest->reference_number)
            ->view('emails.quote-request.received')
            ->with([
                'quote' => $this->quoteRequest,
                'items' => $this->quoteRequest->items,
            ]);
    }
}

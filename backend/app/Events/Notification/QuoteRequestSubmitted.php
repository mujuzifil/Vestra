<?php

namespace App\Events\Notification;

use App\Models\QuoteRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuoteRequestSubmitted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public QuoteRequest $quoteRequest) {}
}

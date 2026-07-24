<?php

namespace App\Events\Notification;

use App\Models\QuotationRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuotationApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(public QuotationRequest $quotationRequest) {}
}

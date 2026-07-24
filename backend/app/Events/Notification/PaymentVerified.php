<?php

namespace App\Events\Notification;

use App\Models\PaymentUpload;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentVerified
{
    use Dispatchable, SerializesModels;

    public function __construct(public PaymentUpload $paymentUpload) {}
}

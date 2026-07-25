<?php

namespace App\Events\Notification;

use App\Models\Product;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BackInStock
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Product $product) {}
}

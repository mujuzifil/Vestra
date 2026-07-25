<?php

namespace App\Events\Notification;

use App\Models\Review;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReviewReplied
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Review $review) {}
}

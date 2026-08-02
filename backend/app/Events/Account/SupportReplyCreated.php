<?php

namespace App\Events\Account;

use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupportReplyCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly SupportTicket $ticket,
        public readonly SupportTicketReply $reply
    ) {}
}

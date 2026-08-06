<?php

namespace App\Policies;

use App\Models\SupportTicket;
use App\Models\User;

class SupportTicketPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, SupportTicket $ticket): bool
    {
        return $user->isAdmin() || $ticket->user_id === $user->id;
    }

    public function update(User $user, SupportTicket $ticket): bool
    {
        return $user->isAdmin();
    }

    public function reply(User $user, SupportTicket $ticket): bool
    {
        if ($user->isAdmin()) {
            return ! in_array($ticket->status, ['closed']);
        }

        return $ticket->user_id === $user->id && ! in_array($ticket->status, ['closed', 'resolved']);
    }

    public function export(User $user): bool
    {
        return $user->isAdmin();
    }
}

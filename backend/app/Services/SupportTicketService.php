<?php

namespace App\Services;

use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class SupportTicketService
{
    public function listForUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return SupportTicket::where('user_id', $user->id)
            ->with(['assignedStaff', 'replies'])
            ->latest()
            ->paginate($perPage);
    }

    public function findForUser(User $user, int $id): ?SupportTicket
    {
        return SupportTicket::where('user_id', $user->id)
            ->with(['assignedStaff', 'replies.user', 'replies.staff'])
            ->find($id);
    }

    public function create(User $user, array $data): SupportTicket
    {
        return DB::transaction(function () use ($user, $data) {
            $attachmentPaths = $this->storeAttachments($data['attachments'] ?? [], 'support_tickets');

            $ticket = SupportTicket::create([
                'user_id' => $user->id,
                'reference_number' => $this->generateReference(),
                'subject' => $data['subject'],
                'enquiry_type' => $data['enquiry_type'] ?? 'general',
                'message' => $data['message'],
                'priority' => $data['priority'] ?? 'medium',
                'status' => 'open',
                'attachments' => $attachmentPaths,
            ]);

            SupportTicketReply::create([
                'support_ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'message' => $data['message'],
                'attachments' => $attachmentPaths,
            ]);

            return $ticket;
        });
    }

    public function reply(SupportTicket $ticket, User $user, array $data): SupportTicketReply
    {
        $attachmentPaths = $this->storeAttachments($data['attachments'] ?? [], "support_tickets/{$ticket->id}/replies");

        $reply = SupportTicketReply::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => $data['message'],
            'attachments' => $attachmentPaths,
        ]);

        if (! in_array($ticket->status, ['open', 'in_progress'])) {
            $ticket->update(['status' => 'in_progress']);
        }

        return $reply;
    }

    public function adminReply(SupportTicket $ticket, User $staff, array $data): SupportTicketReply
    {
        $attachmentPaths = $this->storeAttachments($data['attachments'] ?? [], "support_tickets/{$ticket->id}/replies");

        $reply = SupportTicketReply::create([
            'support_ticket_id' => $ticket->id,
            'staff_id' => $staff->id,
            'user_id' => null,
            'message' => $data['message'],
            'attachments' => $attachmentPaths,
            'is_internal' => (bool) ($data['is_internal'] ?? false),
        ]);

        if (! ($data['is_internal'] ?? false) && ! in_array($ticket->status, ['resolved', 'closed'])) {
            $ticket->update(['status' => 'in_progress']);
        }

        return $reply;
    }

    /**
     * @param  array<int, UploadedFile>  $attachments
     * @return array<int, string>
     */
    private function storeAttachments(array $attachments, string $directory): array
    {
        $paths = [];
        foreach ($attachments as $file) {
            if ($file instanceof UploadedFile) {
                $paths[] = $file->store($directory, 'public');
            }
        }

        return $paths;
    }

    private function generateReference(): string
    {
        $date = now()->format('Ymd');
        $sequence = SupportTicket::whereDate('created_at', today())->count() + 1;

        return "ST-{$date}-".str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}

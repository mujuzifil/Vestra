<?php

namespace App\Listeners\Account;

use App\Events\Account\CompanyProfileUpdated;
use App\Events\Account\CustomerDocumentDownloaded;
use App\Events\Account\NotificationRead;
use App\Events\Account\QuoteViewed;
use App\Events\Account\SupportReplyCreated;
use App\Events\Account\SupportTicketCreated;
use App\Events\Notification\DistributorApplicationApproved;
use App\Events\Notification\DistributorApplicationRejected;
use App\Events\Notification\DistributorApplicationSubmitted;
use App\Events\Notification\QuoteRequestStatusChanged;
use App\Events\Notification\QuoteRequestSubmitted;
use App\Models\AuditLog;
use App\Models\User;

class LogCustomerActivity
{
    public function handle(object $event): void
    {
        $user = $event->user ?? null;

        if (! $user && isset($event->quoteRequest)) {
            $quote = $event->quoteRequest;
            $user = $quote->user
                ?? ($quote->user_id ? User::query()->find($quote->user_id) : null)
                ?? (filled($quote->email) ? User::query()->where('email', $quote->email)->first() : null);
        }

        if (! $user) {
            return;
        }

        $action = $this->actionFor($event);
        if (! $action) {
            return;
        }

        $subject = $this->subjectFor($event);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id,
            'details' => $this->detailsFor($event),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    private function actionFor(object $event): ?string
    {
        return match (get_class($event)) {
            QuoteRequestSubmitted::class => 'quote_submitted',
            QuoteRequestStatusChanged::class => 'quote_status_changed',
            QuoteViewed::class => 'quote_viewed',
            DistributorApplicationSubmitted::class => 'distributor_application_submitted',
            DistributorApplicationApproved::class => 'distributor_application_approved',
            DistributorApplicationRejected::class => 'distributor_application_rejected',
            SupportTicketCreated::class => 'support_ticket_created',
            SupportReplyCreated::class => 'support_reply_created',
            CompanyProfileUpdated::class => 'company_profile_updated',
            CustomerDocumentDownloaded::class => 'document_downloaded',
            NotificationRead::class => $event->all ? 'notifications_read_all' : 'notification_read',
            default => null,
        };
    }

    private function subjectFor(object $event): ?object
    {
        return $event->quoteRequest
            ?? $event->quote
            ?? $event->ticket
            ?? $event->profile
            ?? $event->document
            ?? null;
    }

    private function detailsFor(object $event): array
    {
        if (isset($event->quoteRequest)) {
            $details = ['reference_number' => $event->quoteRequest->reference_number];

            if ($event instanceof QuoteRequestStatusChanged) {
                $details['previous_status'] = $event->previousStatus;
                $details['new_status'] = $event->newStatus;
            }

            return $details;
        }
        if (isset($event->quote)) {
            return ['reference_number' => $event->quote->reference_number];
        }
        if (isset($event->ticket)) {
            return ['reference_number' => $event->ticket->reference_number, 'subject' => $event->ticket->subject];
        }
        if (isset($event->profile)) {
            return ['company_name' => $event->profile->company_name];
        }
        if (isset($event->document)) {
            return ['title' => $event->document->title, 'type' => $event->document->type];
        }
        if (isset($event->notificationId)) {
            return ['notification_id' => $event->notificationId];
        }

        return [];
    }
}

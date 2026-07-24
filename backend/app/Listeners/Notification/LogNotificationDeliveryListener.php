<?php

namespace App\Listeners\Notification;

use App\Events\Notification\*;
use Illuminate\Support\Facades\Log;

class LogNotificationDeliveryListener
{
    /**
     * Handle the event by writing an audit log entry.
     */
    public function handle(object $event): void
    {
        Log::info('Notification event dispatched', [
            'event' => get_class($event),
            'model' => $this->modelIdentifier($event),
        ]);
    }

    protected function modelIdentifier(object $event): ?array
    {
        $model = match (true) {
            $event instanceof CustomerRegistered => $event->user,
            $event instanceof DistributorApplicationSubmitted => $event->distributorRequest,
            $event instanceof DistributorApplicationApproved => $event->distributor,
            $event instanceof DistributorApplicationRejected => $event->distributorRequest,
            $event instanceof PasswordChanged => $event->user,
            $event instanceof PasswordResetRequested => $event->user,
            $event instanceof ProfileUpdated => $event->user,
            $event instanceof OrderCreated,
            $event instanceof OrderPaid,
            $event instanceof OrderCancelled,
            $event instanceof OrderPacked,
            $event instanceof OrderShipped,
            $event instanceof OrderDelivered,
            $event instanceof InvoiceGenerated => $event->order,
            $event instanceof QuotationSubmitted,
            $event instanceof QuotationApproved,
            $event instanceof QuotationRejected => $event->quotationRequest,
            $event instanceof PaymentUploaded,
            $event instanceof PaymentVerified => $event->paymentUpload,
            $event instanceof CreditLimitUpdated => $event->creditAccount,
            $event instanceof StatementGenerated => $event->distributor,
            $event instanceof AdminAnnouncementPublished => $event->announcement,
            default => null,
        };

        if ($model === null) {
            return null;
        }

        return [
            'class' => get_class($model),
            'id' => $model->getKey(),
        ];
    }
}

<?php

namespace App\Listeners\Notification;

use App\Enums\NotificationChannel;
use App\Events\Notification\AdminAnnouncementPublished;
use App\Events\Notification\BackInStock;
use App\Events\Notification\CreditLimitUpdated;
use App\Events\Notification\CustomerRegistered;
use App\Events\Notification\DistributorApplicationApproved;
use App\Events\Notification\DistributorApplicationRejected;
use App\Events\Notification\DistributorApplicationSubmitted;
use App\Events\Notification\InvoiceGenerated;
use App\Events\Notification\OrderCancelled;
use App\Events\Notification\OrderCreated;
use App\Events\Notification\OrderDelivered;
use App\Events\Notification\OrderPacked;
use App\Events\Notification\OrderPaid;
use App\Events\Notification\OrderShipped;
use App\Events\Notification\PasswordChanged;
use App\Events\Notification\PasswordResetRequested;
use App\Events\Notification\PaymentUploaded;
use App\Events\Notification\PaymentVerified;
use App\Events\Notification\PriceDropped;
use App\Events\Notification\ProfileUpdated;
use App\Events\Notification\QuotationApproved;
use App\Events\Notification\QuotationRejected;
use App\Events\Notification\QuotationSubmitted;
use App\Events\Notification\ReviewApproved;
use App\Events\Notification\ReviewReplied;
use App\Events\Notification\StatementGenerated;
use App\Events\Notification\SystemMaintenanceScheduled;
use App\Models\DistributorRequest;
use App\Models\PaymentUpload;
use App\Models\SavedItem;
use App\Models\User;
use App\Models\Wishlist;
use App\Services\NotificationDispatcherService;
use Illuminate\Support\Facades\Log;

class DispatchNotificationListener
{
    public function __construct(
        protected NotificationDispatcherService $dispatcher
    ) {}

    /**
     * Handle notification events.
     */
    public function handle(object $event): void
    {
        $config = $this->resolveConfig($event);

        if ($config === null) {
            return;
        }

        foreach ($config['users'] as $user) {
            if (! $user instanceof User) {
                continue;
            }

            try {
                $this->dispatcher->dispatch(
                    user: $user,
                    templateKey: $config['template'],
                    variables: $config['variables'],
                    channels: $config['channels'],
                    topic: $config['topic'],
                    metadata: $config['metadata'] ?? []
                );
            } catch (\Throwable $e) {
                Log::error('Notification dispatch listener failed', [
                    'event' => get_class($event),
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Resolve dispatch configuration for a given event.
     */
    protected function resolveConfig(object $event): ?array
    {
        return match (true) {
            $event instanceof CustomerRegistered => [
                'users' => [$event->user],
                'template' => 'customer.registered',
                'channels' => [NotificationChannel::IN_APP, NotificationChannel::EMAIL],
                'topic' => 'order_updates',
                'variables' => ['customer_name' => $event->user->name, 'email' => $event->user->email],
            ],
            $event instanceof DistributorApplicationSubmitted => [
                'users' => $this->usersFromDistributorRequest($event->distributorRequest),
                'template' => 'distributor.application_submitted',
                'channels' => [NotificationChannel::IN_APP, NotificationChannel::EMAIL],
                'topic' => 'order_updates',
                'variables' => [
                    'customer_name' => $event->distributorRequest->contact_person,
                    'company_name' => $event->distributorRequest->company_name,
                ],
            ],
            $event instanceof DistributorApplicationApproved => [
                'users' => [$event->distributor->user],
                'template' => 'distributor.application_approved',
                'channels' => [NotificationChannel::IN_APP, NotificationChannel::EMAIL, NotificationChannel::SMS],
                'topic' => 'order_updates',
                'variables' => [
                    'customer_name' => $event->distributor->user?->name,
                    'company_name' => $event->distributor->company_name,
                ],
            ],
            $event instanceof DistributorApplicationRejected => [
                'users' => $this->usersFromDistributorRequest($event->distributorRequest),
                'template' => 'distributor.application_rejected',
                'channels' => [NotificationChannel::IN_APP, NotificationChannel::EMAIL],
                'topic' => 'order_updates',
                'variables' => [
                    'customer_name' => $event->distributorRequest->contact_person,
                    'company_name' => $event->distributorRequest->company_name,
                    'reason' => $event->reason,
                ],
            ],
            $event instanceof PasswordChanged => [
                'users' => [$event->user],
                'template' => 'security.password_changed',
                'channels' => [NotificationChannel::IN_APP, NotificationChannel::EMAIL],
                'topic' => 'system_alert',
                'variables' => ['customer_name' => $event->user->name, 'email' => $event->user->email],
            ],
            $event instanceof PasswordResetRequested => [
                'users' => [$event->user],
                'template' => 'security.password_reset_requested',
                'channels' => [NotificationChannel::EMAIL],
                'topic' => 'system_alert',
                'variables' => [
                    'customer_name' => $event->user->name,
                    'email' => $event->user->email,
                    'token' => $event->token,
                ],
            ],
            $event instanceof ProfileUpdated => [
                'users' => [$event->user],
                'template' => 'customer.profile_updated',
                'channels' => [NotificationChannel::IN_APP],
                'topic' => 'order_updates',
                'variables' => ['customer_name' => $event->user->name],
            ],
            $event instanceof OrderCreated => [
                'users' => [$event->order->user],
                'template' => 'order.created',
                'channels' => [NotificationChannel::IN_APP, NotificationChannel::EMAIL],
                'topic' => 'order_updates',
                'variables' => [
                    'customer_name' => $event->order->user?->name,
                    'order_number' => $event->order->order_number ?? $event->order->invoice_number,
                    'invoice_number' => $event->order->invoice_number,
                    'amount' => number_format($event->order->total_amount ?? 0, 2),
                ],
            ],
            $event instanceof OrderPaid => [
                'users' => [$event->order->user],
                'template' => 'order.paid',
                'channels' => [NotificationChannel::IN_APP, NotificationChannel::EMAIL, NotificationChannel::SMS],
                'topic' => 'order_updates',
                'variables' => [
                    'customer_name' => $event->order->user?->name,
                    'order_number' => $event->order->order_number ?? $event->order->invoice_number,
                    'invoice_number' => $event->order->invoice_number,
                    'amount' => number_format($event->order->total_amount ?? 0, 2),
                ],
            ],
            $event instanceof OrderCancelled => [
                'users' => [$event->order->user],
                'template' => 'order.cancelled',
                'channels' => [NotificationChannel::IN_APP, NotificationChannel::EMAIL],
                'topic' => 'order_updates',
                'variables' => [
                    'customer_name' => $event->order->user?->name,
                    'order_number' => $event->order->order_number ?? $event->order->invoice_number,
                    'reason' => $event->reason,
                ],
            ],
            $event instanceof OrderPacked => [
                'users' => [$event->order->user],
                'template' => 'order.packed',
                'channels' => [NotificationChannel::IN_APP, NotificationChannel::EMAIL],
                'topic' => 'order_updates',
                'variables' => [
                    'customer_name' => $event->order->user?->name,
                    'order_number' => $event->order->order_number ?? $event->order->invoice_number,
                ],
            ],
            $event instanceof OrderShipped => [
                'users' => [$event->order->user],
                'template' => 'order.shipped',
                'channels' => [NotificationChannel::IN_APP, NotificationChannel::EMAIL, NotificationChannel::SMS],
                'topic' => 'order_updates',
                'variables' => [
                    'customer_name' => $event->order->user?->name,
                    'order_number' => $event->order->order_number ?? $event->order->invoice_number,
                    'tracking_number' => $event->order->tracking_number,
                ],
            ],
            $event instanceof OrderDelivered => [
                'users' => [$event->order->user],
                'template' => 'order.delivered',
                'channels' => [NotificationChannel::IN_APP, NotificationChannel::EMAIL, NotificationChannel::SMS],
                'topic' => 'order_updates',
                'variables' => [
                    'customer_name' => $event->order->user?->name,
                    'order_number' => $event->order->order_number ?? $event->order->invoice_number,
                ],
            ],
            $event instanceof InvoiceGenerated => [
                'users' => [$event->order->user],
                'template' => 'invoice.generated',
                'channels' => [NotificationChannel::IN_APP, NotificationChannel::EMAIL],
                'topic' => 'order_updates',
                'variables' => [
                    'customer_name' => $event->order->user?->name,
                    'invoice_number' => $event->order->invoice_number,
                    'amount' => number_format($event->order->total_amount ?? 0, 2),
                ],
            ],
            $event instanceof QuotationSubmitted => [
                'users' => [$event->quotationRequest->distributor->user],
                'template' => 'quotation.submitted',
                'channels' => [NotificationChannel::IN_APP, NotificationChannel::EMAIL],
                'topic' => 'order_updates',
                'variables' => [
                    'customer_name' => $event->quotationRequest->distributor->user?->name,
                    'quotation_number' => $event->quotationRequest->reference_number,
                ],
            ],
            $event instanceof QuotationApproved => [
                'users' => [$event->quotationRequest->distributor->user],
                'template' => 'quotation.approved',
                'channels' => [NotificationChannel::IN_APP, NotificationChannel::EMAIL],
                'topic' => 'order_updates',
                'variables' => [
                    'customer_name' => $event->quotationRequest->distributor->user?->name,
                    'quotation_number' => $event->quotationRequest->reference_number,
                ],
            ],
            $event instanceof QuotationRejected => [
                'users' => [$event->quotationRequest->distributor->user],
                'template' => 'quotation.rejected',
                'channels' => [NotificationChannel::IN_APP, NotificationChannel::EMAIL],
                'topic' => 'order_updates',
                'variables' => [
                    'customer_name' => $event->quotationRequest->distributor->user?->name,
                    'quotation_number' => $event->quotationRequest->reference_number,
                    'reason' => $event->reason,
                ],
            ],
            $event instanceof PaymentUploaded => [
                'users' => $this->usersFromPaymentUpload($event->paymentUpload),
                'template' => 'payment.uploaded',
                'channels' => [NotificationChannel::IN_APP, NotificationChannel::EMAIL],
                'topic' => 'order_updates',
                'variables' => [
                    'customer_name' => $event->paymentUpload->distributor->user?->name,
                    'reference' => $event->paymentUpload->reference_number,
                    'amount' => number_format($event->paymentUpload->amount, 2),
                ],
            ],
            $event instanceof PaymentVerified => [
                'users' => $this->usersFromPaymentUpload($event->paymentUpload),
                'template' => 'payment.verified',
                'channels' => [NotificationChannel::IN_APP, NotificationChannel::EMAIL],
                'topic' => 'order_updates',
                'variables' => [
                    'customer_name' => $event->paymentUpload->distributor->user?->name,
                    'reference' => $event->paymentUpload->reference_number,
                    'amount' => number_format($event->paymentUpload->amount, 2),
                ],
            ],
            $event instanceof CreditLimitUpdated => [
                'users' => [$event->creditAccount->distributor->user],
                'template' => 'credit.limit_updated',
                'channels' => [NotificationChannel::IN_APP, NotificationChannel::EMAIL],
                'topic' => 'order_updates',
                'variables' => [
                    'customer_name' => $event->creditAccount->distributor->user?->name,
                    'company_name' => $event->creditAccount->distributor->company_name,
                    'credit_limit' => number_format($event->creditAccount->limit, 2),
                    'reason' => $event->reason,
                ],
            ],
            $event instanceof StatementGenerated => [
                'users' => [$event->distributor->user],
                'template' => 'statement.generated',
                'channels' => [NotificationChannel::IN_APP, NotificationChannel::EMAIL],
                'topic' => 'order_updates',
                'variables' => [
                    'customer_name' => $event->distributor->user?->name,
                    'company_name' => $event->distributor->company_name,
                    'statement_period' => $event->statementPeriod,
                ],
            ],
            $event instanceof AdminAnnouncementPublished => [
                'users' => [],
                'template' => 'admin.announcement',
                'channels' => [],
                'topic' => 'system_alert',
                'variables' => [],
            ],
            $event instanceof SystemMaintenanceScheduled => [
                'users' => User::whereNotNull('email')->cursor(),
                'template' => 'system.maintenance_scheduled',
                'channels' => [NotificationChannel::IN_APP, NotificationChannel::EMAIL],
                'topic' => 'emergency_alert',
                'variables' => [
                    'window' => $event->window,
                    'description' => $event->description,
                ],
            ],
            $event instanceof ReviewApproved => [
                'users' => [$event->review->user],
                'template' => 'review.approved',
                'channels' => [NotificationChannel::IN_APP, NotificationChannel::EMAIL],
                'topic' => 'order_updates',
                'variables' => [
                    'customer_name' => $event->review->user?->name,
                    'product_name' => $event->review->product?->name,
                    'review_title' => $event->review->title,
                ],
            ],
            $event instanceof ReviewReplied => [
                'users' => [$event->review->user],
                'template' => 'review.replied',
                'channels' => [NotificationChannel::IN_APP, NotificationChannel::EMAIL],
                'topic' => 'order_updates',
                'variables' => [
                    'customer_name' => $event->review->user?->name,
                    'product_name' => $event->review->product?->name,
                    'review_title' => $event->review->title,
                ],
            ],
            $event instanceof PriceDropped => [
                'users' => $this->usersInterestedInProduct($event->product),
                'template' => 'product.price_dropped',
                'channels' => [NotificationChannel::IN_APP, NotificationChannel::EMAIL],
                'topic' => 'promotions',
                'variables' => [
                    'product_name' => $event->product->name,
                    'old_price' => number_format($event->oldPrice, 2),
                    'new_price' => number_format($event->newPrice, 2),
                ],
            ],
            $event instanceof BackInStock => [
                'users' => $this->usersInterestedInProduct($event->product),
                'template' => 'product.back_in_stock',
                'channels' => [NotificationChannel::IN_APP, NotificationChannel::EMAIL],
                'topic' => 'order_updates',
                'variables' => [
                    'product_name' => $event->product->name,
                    'product_url' => route('products.show', $event->product->slug),
                ],
            ],
            default => null,
        };
    }

    /**
     * Resolve user(s) who have saved or wishlisted a product.
     *
     * @return array<int, User>
     */
    protected function usersInterestedInProduct(\App\Models\Product $product): array
    {
        $wishlistUserIds = Wishlist::where('product_id', $product->id)->pluck('user_id');
        $savedUserIds = SavedItem::where('product_id', $product->id)->pluck('user_id');

        $userIds = $wishlistUserIds->merge($savedUserIds)->unique()->values();

        return User::whereIn('id', $userIds)->get()->all();
    }

    /**
     * Resolve user(s) from a distributor request by email.
     *
     * @return array<int, User>
     */
    protected function usersFromDistributorRequest(DistributorRequest $request): array
    {
        if ($request->email) {
            $user = User::where('email', $request->email)->first();

            if ($user) {
                return [$user];
            }
        }

        return [];
    }

    /**
     * Resolve user(s) from a payment upload via distributor.
     *
     * @return array<int, User>
     */
    protected function usersFromPaymentUpload(PaymentUpload $paymentUpload): array
    {
        $user = $paymentUpload->distributor?->user;

        return $user ? [$user] : [];
    }
}

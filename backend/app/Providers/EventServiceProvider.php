<?php

namespace App\Providers;

use App\Events\Account\CompanyProfileUpdated;
use App\Events\Account\CustomerDocumentDownloaded;
use App\Events\Account\NotificationRead;
use App\Events\Account\QuoteViewed;
use App\Events\Account\SupportReplyCreated;
use App\Events\Account\SupportTicketCreated;
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
use App\Events\Notification\QuoteRequestStatusChanged;
use App\Events\Notification\QuoteRequestSubmitted;
use App\Events\Notification\ReviewApproved;
use App\Events\Notification\ReviewReplied;
use App\Events\Notification\StatementGenerated;
use App\Events\Notification\SystemMaintenanceScheduled;
use App\Listeners\Account\LogCustomerActivity;
use App\Listeners\LogAdminFailedLogin;
use App\Listeners\LogAdminLogin;
use App\Listeners\LogAdminLogout;
use App\Listeners\Notification\DispatchNotificationListener;
use App\Listeners\Notification\LogNotificationDeliveryListener;
use App\Listeners\UpdateAdminSessionActivity;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Routing\Events\RouteMatched;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Login::class => [
            LogAdminLogin::class,
        ],
        Failed::class => [
            LogAdminFailedLogin::class,
        ],
        Logout::class => [
            LogAdminLogout::class,
        ],
        RouteMatched::class => [
            UpdateAdminSessionActivity::class,
        ],

        // Notification engine events
        CustomerRegistered::class => [
            DispatchNotificationListener::class,
            LogNotificationDeliveryListener::class,
        ],
        DistributorApplicationSubmitted::class => [
            DispatchNotificationListener::class,
            LogNotificationDeliveryListener::class,
            LogCustomerActivity::class,
        ],
        DistributorApplicationApproved::class => [
            DispatchNotificationListener::class,
            LogNotificationDeliveryListener::class,
            LogCustomerActivity::class,
        ],
        DistributorApplicationRejected::class => [
            DispatchNotificationListener::class,
            LogNotificationDeliveryListener::class,
            LogCustomerActivity::class,
        ],
        PasswordChanged::class => [
            DispatchNotificationListener::class,
            LogNotificationDeliveryListener::class,
        ],
        PasswordResetRequested::class => [
            DispatchNotificationListener::class,
            LogNotificationDeliveryListener::class,
        ],
        ProfileUpdated::class => [
            DispatchNotificationListener::class,
            LogNotificationDeliveryListener::class,
        ],
        OrderCreated::class => [
            DispatchNotificationListener::class,
            LogNotificationDeliveryListener::class,
        ],
        OrderPaid::class => [
            DispatchNotificationListener::class,
            LogNotificationDeliveryListener::class,
        ],
        OrderCancelled::class => [
            DispatchNotificationListener::class,
            LogNotificationDeliveryListener::class,
        ],
        OrderPacked::class => [
            DispatchNotificationListener::class,
            LogNotificationDeliveryListener::class,
        ],
        OrderShipped::class => [
            DispatchNotificationListener::class,
            LogNotificationDeliveryListener::class,
        ],
        OrderDelivered::class => [
            DispatchNotificationListener::class,
            LogNotificationDeliveryListener::class,
        ],
        InvoiceGenerated::class => [
            DispatchNotificationListener::class,
            LogNotificationDeliveryListener::class,
        ],
        QuotationSubmitted::class => [
            DispatchNotificationListener::class,
            LogNotificationDeliveryListener::class,
        ],
        QuoteRequestSubmitted::class => [
            DispatchNotificationListener::class,
            LogNotificationDeliveryListener::class,
            LogCustomerActivity::class,
        ],
        QuoteRequestStatusChanged::class => [
            DispatchNotificationListener::class,
            LogNotificationDeliveryListener::class,
            LogCustomerActivity::class,
        ],
        QuoteViewed::class => [
            LogCustomerActivity::class,
        ],
        SupportTicketCreated::class => [
            LogCustomerActivity::class,
        ],
        SupportReplyCreated::class => [
            LogCustomerActivity::class,
        ],
        CompanyProfileUpdated::class => [
            LogCustomerActivity::class,
        ],
        CustomerDocumentDownloaded::class => [
            LogCustomerActivity::class,
        ],
        NotificationRead::class => [
            LogCustomerActivity::class,
        ],
        QuotationApproved::class => [
            DispatchNotificationListener::class,
            LogNotificationDeliveryListener::class,
        ],
        QuotationRejected::class => [
            DispatchNotificationListener::class,
            LogNotificationDeliveryListener::class,
        ],
        PaymentUploaded::class => [
            DispatchNotificationListener::class,
            LogNotificationDeliveryListener::class,
        ],
        PaymentVerified::class => [
            DispatchNotificationListener::class,
            LogNotificationDeliveryListener::class,
        ],
        CreditLimitUpdated::class => [
            DispatchNotificationListener::class,
            LogNotificationDeliveryListener::class,
        ],
        StatementGenerated::class => [
            DispatchNotificationListener::class,
            LogNotificationDeliveryListener::class,
        ],
        AdminAnnouncementPublished::class => [
            LogNotificationDeliveryListener::class,
        ],
        SystemMaintenanceScheduled::class => [
            DispatchNotificationListener::class,
            LogNotificationDeliveryListener::class,
        ],
        ReviewApproved::class => [
            DispatchNotificationListener::class,
            LogNotificationDeliveryListener::class,
        ],
        ReviewReplied::class => [
            DispatchNotificationListener::class,
            LogNotificationDeliveryListener::class,
        ],
        PriceDropped::class => [
            DispatchNotificationListener::class,
            LogNotificationDeliveryListener::class,
        ],
        BackInStock::class => [
            DispatchNotificationListener::class,
            LogNotificationDeliveryListener::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }
}

<?php

namespace App\Providers;

use App\Events\Notification\*;
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
        ],
        DistributorApplicationApproved::class => [
            DispatchNotificationListener::class,
            LogNotificationDeliveryListener::class,
        ],
        DistributorApplicationRejected::class => [
            DispatchNotificationListener::class,
            LogNotificationDeliveryListener::class,
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
    ];

    public function boot(): void
    {
        parent::boot();
    }
}

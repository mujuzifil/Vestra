<?php

namespace App\Providers;

use App\Models\AdminSession;
use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\CompanyProfile;
use App\Models\ContactMessage;
use App\Models\CreditAccount;
use App\Models\CustomerAddress;
use App\Models\CustomerDocument;
use App\Models\CustomerFeedback;
use App\Models\Distributor;
use App\Models\SupportTicket;
use App\Models\Task;
use App\Models\DistributorBranch;
use App\Models\DistributorContact;
use App\Models\DistributorDocument;
use App\Models\DistributorRequest;
use App\Models\LoginActivity;
use App\Models\NotificationTemplate;
use App\Models\PaymentUpload;
use App\Models\QuotationRequest;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\ProductWarehouseStock;
use App\Models\QuoteRequest;
use App\Models\Review;
use App\Models\Setting;
use App\Models\User;
use App\Policies\AdminSessionPolicy;
use App\Policies\AnnouncementPolicy;
use App\Policies\CustomerAddressPolicy;
use App\Policies\AuditLogPolicy;
use App\Policies\CartItemPolicy;
use App\Policies\CartPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\CompanyProfilePolicy;
use App\Policies\ContactMessagePolicy;
use App\Policies\CreditAccountPolicy;
use App\Policies\CustomerDocumentPolicy;
use App\Policies\CustomerFeedbackPolicy;
use App\Policies\SupportTicketPolicy;
use App\Policies\DistributorBranchPolicy;
use App\Policies\DistributorContactPolicy;
use App\Policies\DistributorDocumentPolicy;
use App\Policies\DistributorPolicy;
use App\Policies\DistributorRequestPolicy;
use App\Policies\LoginActivityPolicy;
use App\Policies\NotificationPolicy;
use App\Policies\NotificationTemplatePolicy;
use App\Policies\PaymentUploadPolicy;
use App\Policies\QuotationRequestPolicy;
use App\Policies\OrderPolicy;
use App\Policies\QuoteRequestPolicy;
use App\Policies\PaymentTransactionPolicy;
use App\Policies\ProductPolicy;
use App\Policies\ProductWarehouseStockPolicy;
use App\Policies\ReviewPolicy;
use App\Policies\SettingPolicy;
use App\Policies\TaskPolicy;
use App\Policies\UserPolicy;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        AdminSession::class => AdminSessionPolicy::class,
        Announcement::class => AnnouncementPolicy::class,
        AuditLog::class => AuditLogPolicy::class,
        Cart::class => CartPolicy::class,
        CartItem::class => CartItemPolicy::class,
        Category::class => CategoryPolicy::class,
        ContactMessage::class => ContactMessagePolicy::class,
        CustomerAddress::class => CustomerAddressPolicy::class,
        CustomerDocument::class => CustomerDocumentPolicy::class,
        CustomerFeedback::class => CustomerFeedbackPolicy::class,
        CompanyProfile::class => CompanyProfilePolicy::class,
        CreditAccount::class => CreditAccountPolicy::class,
        Distributor::class => DistributorPolicy::class,
        SupportTicket::class => SupportTicketPolicy::class,
        DistributorBranch::class => DistributorBranchPolicy::class,
        DistributorContact::class => DistributorContactPolicy::class,
        DistributorDocument::class => DistributorDocumentPolicy::class,
        DistributorRequest::class => DistributorRequestPolicy::class,
        LoginActivity::class => LoginActivityPolicy::class,
        NotificationTemplate::class => NotificationTemplatePolicy::class,
        PaymentUpload::class => PaymentUploadPolicy::class,
        QuotationRequest::class => QuotationRequestPolicy::class,
        QuoteRequest::class => QuoteRequestPolicy::class,
        Order::class => OrderPolicy::class,
        PaymentTransaction::class => PaymentTransactionPolicy::class,
        Product::class => ProductPolicy::class,
        ProductWarehouseStock::class => ProductWarehouseStockPolicy::class,
        Review::class => ReviewPolicy::class,
        Setting::class => SettingPolicy::class,
        Task::class => TaskPolicy::class,
        User::class => UserPolicy::class,
        DatabaseNotification::class => NotificationPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('view reports', fn (User $user): bool => $user->isAdmin());
        Gate::define('admin', fn (User $user): bool => $user->isAdmin());
    }
}

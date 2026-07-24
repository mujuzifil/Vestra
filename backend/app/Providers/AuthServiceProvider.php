<?php

namespace App\Providers;

use App\Models\AdminSession;
use App\Models\AuditLog;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\CustomerAddress;
use App\Models\CustomerFeedback;
use App\Models\Distributor;
use App\Models\DistributorBranch;
use App\Models\DistributorContact;
use App\Models\DistributorDocument;
use App\Models\DistributorRequest;
use App\Models\LoginActivity;
use App\Models\PaymentUpload;
use App\Models\QuotationRequest;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\Review;
use App\Models\Setting;
use App\Models\User;
use App\Policies\AdminSessionPolicy;
use App\Policies\CustomerAddressPolicy;
use App\Policies\AuditLogPolicy;
use App\Policies\CartItemPolicy;
use App\Policies\CartPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\ContactMessagePolicy;
use App\Policies\CustomerFeedbackPolicy;
use App\Policies\DistributorBranchPolicy;
use App\Policies\DistributorContactPolicy;
use App\Policies\DistributorDocumentPolicy;
use App\Policies\DistributorPolicy;
use App\Policies\DistributorRequestPolicy;
use App\Policies\LoginActivityPolicy;
use App\Policies\PaymentUploadPolicy;
use App\Policies\QuotationRequestPolicy;
use App\Policies\OrderPolicy;
use App\Policies\PaymentTransactionPolicy;
use App\Policies\ProductPolicy;
use App\Policies\ReviewPolicy;
use App\Policies\SettingPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        AdminSession::class => AdminSessionPolicy::class,
        AuditLog::class => AuditLogPolicy::class,
        Cart::class => CartPolicy::class,
        CartItem::class => CartItemPolicy::class,
        Category::class => CategoryPolicy::class,
        ContactMessage::class => ContactMessagePolicy::class,
        CustomerAddress::class => CustomerAddressPolicy::class,
        CustomerFeedback::class => CustomerFeedbackPolicy::class,
        Distributor::class => DistributorPolicy::class,
        DistributorBranch::class => DistributorBranchPolicy::class,
        DistributorContact::class => DistributorContactPolicy::class,
        DistributorDocument::class => DistributorDocumentPolicy::class,
        DistributorRequest::class => DistributorRequestPolicy::class,
        LoginActivity::class => LoginActivityPolicy::class,
        PaymentUpload::class => PaymentUploadPolicy::class,
        QuotationRequest::class => QuotationRequestPolicy::class,
        Order::class => OrderPolicy::class,
        PaymentTransaction::class => PaymentTransactionPolicy::class,
        Product::class => ProductPolicy::class,
        Review::class => ReviewPolicy::class,
        Setting::class => SettingPolicy::class,
        User::class => UserPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('view reports', fn (User $user): bool => $user->isAdmin());
    }
}

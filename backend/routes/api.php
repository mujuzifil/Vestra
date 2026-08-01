<?php

use App\Http\Controllers\Api\V1\Auth\AccountDeletionController;
use App\Http\Controllers\Api\V1\Auth\ActivityController;
use App\Http\Controllers\Api\V1\Auth\AddressController;
use App\Http\Controllers\Api\V1\Auth\AvatarController;
use App\Http\Controllers\Api\V1\Auth\ChangePasswordController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\PreferenceController;
use App\Http\Controllers\Api\V1\Auth\ProfileController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\Auth\UnifiedLoginController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\DistributorController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\Distributor\DashboardController as DistributorDashboardController;
use App\Http\Controllers\Api\V1\Distributor\ProfileController as DistributorProfileController;
use App\Http\Controllers\Api\V1\Distributor\BranchController as DistributorBranchController;
use App\Http\Controllers\Api\V1\Distributor\ContactController as DistributorContactController;
use App\Http\Controllers\Api\V1\Distributor\DocumentController as DistributorDocumentController;
use App\Http\Controllers\Api\V1\Distributor\ProductController as DistributorProductController;
use App\Http\Controllers\Api\V1\Distributor\OrderController as DistributorOrderController;
use App\Http\Controllers\Api\V1\Distributor\QuotationController as DistributorQuotationController;
use App\Http\Controllers\Api\V1\Distributor\InvoiceController as DistributorInvoiceController;
use App\Http\Controllers\Api\V1\Distributor\StatementController as DistributorStatementController;
use App\Http\Controllers\Api\V1\Distributor\PaymentUploadController as DistributorPaymentUploadController;
use App\Http\Controllers\Api\V1\Distributor\AnalyticsController as DistributorAnalyticsController;
use App\Http\Controllers\Api\V1\Distributor\NotificationController as DistributorNotificationController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\QuoteRequestController;
use App\Http\Controllers\Api\V1\RecommendationController;
use App\Http\Controllers\Api\V1\SettingController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\InvoiceController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\PublicDistributorController;
use App\Http\Controllers\Api\V1\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Api\V1\Admin\AutomatedWorkflowController as AdminAutomatedWorkflowController;
use App\Http\Controllers\Api\V1\Admin\CreditAccountController as AdminCreditAccountController;
use App\Http\Controllers\Api\V1\Admin\NotificationDashboardController;
use App\Http\Controllers\Api\V1\Admin\NotificationTemplateController;
use App\Http\Controllers\Api\V1\Admin\PurchaseOrderController as AdminPurchaseOrderController;
use App\Http\Controllers\Api\V1\Admin\SupplierController as AdminSupplierController;
use App\Http\Controllers\Api\V1\Admin\WarehouseController as AdminWarehouseController;
use App\Http\Controllers\Api\V1\AnnouncementController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\NotificationPreferenceController;
use App\Http\Controllers\Api\V1\RecentlyViewedController;
use App\Http\Controllers\Api\V1\ReviewController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\SavedItemController;
use App\Http\Controllers\Api\V1\WishlistController;
use App\Http\Controllers\Api\V1\FeedbackController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Health checks (public, no auth)
    Route::get('/health', [HealthController::class, 'index']);
    Route::get('/health/ready', [HealthController::class, 'readiness']);
    Route::get('/health/live', [HealthController::class, 'liveness']);

    // Public routes
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{slug}', [ProductController::class, 'show']);
    Route::get('/products/{slug}/reviews', [ReviewController::class, 'index']);

    Route::get('/search/autocomplete', [SearchController::class, 'autocomplete']);
    Route::get('/search/popular', [SearchController::class, 'popular']);
    Route::get('/recommendations', [RecommendationController::class, 'index']);
    Route::get('/products/{slug}/recommendations', [RecommendationController::class, 'forProduct']);
    Route::get('/settings', [SettingController::class, 'index']);

    Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:contact');
    Route::post('/distributor', [DistributorController::class, 'store'])->middleware('throttle:distributor');
    Route::post('/feedback', [FeedbackController::class, 'store'])->middleware('throttle:feedback');
    Route::post('/quote-requests', [QuoteRequestController::class, 'store'])->middleware('throttle:contact');

    // Public distributor directory
    Route::get('/public/distributors', [PublicDistributorController::class, 'index']);
    Route::get('/public/distributors/stats', [PublicDistributorController::class, 'stats']);
    Route::get('/public/distributors/coverage', [PublicDistributorController::class, 'coverageRegions']);

    // Customer auth (public)
    Route::post('/auth/register', [RegisterController::class, 'register'])->middleware('throttle:register');
    Route::post('/auth/login', [UnifiedLoginController::class, 'login'])->middleware('throttle:login');

    // Customer auth (protected)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [LogoutController::class, 'logout']);
        Route::get('/auth/profile', [ProfileController::class, 'show']);
        Route::put('/auth/profile', [ProfileController::class, 'update']);
        Route::post('/auth/avatar', [AvatarController::class, 'store']);
        Route::delete('/auth/avatar', [AvatarController::class, 'destroy']);
        Route::post('/auth/change-password', [ChangePasswordController::class, 'store'])->middleware('throttle:change-password');

        // Addresses
        Route::get('/auth/addresses', [AddressController::class, 'index']);
        Route::post('/auth/addresses', [AddressController::class, 'store']);
        Route::get('/auth/addresses/{address}', [AddressController::class, 'show']);
        Route::put('/auth/addresses/{address}', [AddressController::class, 'update']);
        Route::delete('/auth/addresses/{address}', [AddressController::class, 'destroy']);

        // Preferences & activity
        Route::get('/auth/preferences', [PreferenceController::class, 'show']);
        Route::put('/auth/preferences', [PreferenceController::class, 'update']);
        Route::get('/auth/activity', [ActivityController::class, 'index']);

        // Account deletion request (does not delete the account)
        Route::post('/auth/account-deletion-request', [AccountDeletionController::class, 'store']);

        // Cart
        Route::get('/cart', [CartController::class, 'index']);
        Route::post('/cart/items', [CartController::class, 'store']);
        Route::put('/cart/items/{item}', [CartController::class, 'update']);
        Route::delete('/cart/items/{item}', [CartController::class, 'destroy']);
        Route::delete('/cart', [CartController::class, 'clear']);
        Route::post('/cart/merge', [CartController::class, 'merge']);

        // Orders
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);
        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);
        Route::get('/orders/{order}/invoice', [InvoiceController::class, 'download']);
        Route::post('/checkout', [CheckoutController::class, 'store']);

        // Payments
        Route::post('/payments/initiate', [PaymentController::class, 'initiate'])->middleware('throttle:payment');
        Route::get('/payments/{reference}/verify', [PaymentController::class, 'verify']);
        Route::get('/payments/{reference}', [PaymentController::class, 'getTransaction']);

        // Reports (admin only)
        Route::middleware(['can:view reports', 'log.api'])->group(function () {
            Route::get('/reports/dashboard', [ReportController::class, 'dashboard']);
            Route::get('/reports/executive', [ReportController::class, 'executive']);
            Route::get('/reports/revenue', [ReportController::class, 'revenue']);
            Route::get('/reports/sales', [ReportController::class, 'sales']);
            Route::get('/reports/sales-trend', [ReportController::class, 'salesTrend']);
            Route::get('/reports/best-sellers', [ReportController::class, 'bestSellers']);
            Route::get('/reports/inventory', [ReportController::class, 'inventory']);
            Route::get('/reports/inventory-value', [ReportController::class, 'inventoryValue']);
            Route::get('/reports/customers', [ReportController::class, 'customers']);
            Route::get('/reports/customer-growth', [ReportController::class, 'customerGrowth']);
            Route::get('/reports/customer-intelligence', [ReportController::class, 'customerIntelligence']);
            Route::get('/reports/distributors', [ReportController::class, 'distributors']);
            Route::get('/reports/distributor-intelligence', [ReportController::class, 'distributorIntelligence']);
            Route::get('/reports/inventory-intelligence', [ReportController::class, 'inventoryIntelligence']);
            Route::get('/reports/engagement', [ReportController::class, 'engagement']);
            Route::get('/reports/search-analytics', [ReportController::class, 'searchAnalytics']);
            Route::get('/reports/forecast', [ReportController::class, 'forecast']);
            Route::get('/reports/api-analytics', [ReportController::class, 'apiAnalytics']);
            Route::get('/reports/operational', [ReportController::class, 'operational']);
        });

        // Reviews
        Route::get('/auth/reviews', [ReviewController::class, 'myReviews']);
        Route::post('/reviews', [ReviewController::class, 'store']);
        Route::put('/reviews/{review}', [ReviewController::class, 'update']);
        Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']);
        Route::post('/reviews/{review}/helpful', [ReviewController::class, 'helpful']);
        Route::post('/reviews/{review}/report', [ReviewController::class, 'report']);

        // Wishlist
        Route::get('/auth/wishlist', [WishlistController::class, 'index']);
        Route::post('/auth/wishlist', [WishlistController::class, 'store']);
        Route::post('/auth/wishlist/merge', [WishlistController::class, 'merge']);
        Route::delete('/auth/wishlist/{product}', [WishlistController::class, 'destroy']);
        Route::post('/auth/wishlist/{product}/move-to-cart', [WishlistController::class, 'moveToCart']);

        // Saved for later
        Route::get('/auth/saved-for-later', [SavedItemController::class, 'index']);
        Route::post('/auth/saved-for-later', [SavedItemController::class, 'store']);
        Route::post('/auth/saved-for-later/merge', [SavedItemController::class, 'merge']);
        Route::delete('/auth/saved-for-later/{product}', [SavedItemController::class, 'destroy']);
        Route::post('/auth/saved-for-later/{product}/move-to-cart', [SavedItemController::class, 'moveToCart']);

        // Recently viewed
        Route::get('/auth/recently-viewed', [RecentlyViewedController::class, 'index']);
        Route::post('/auth/recently-viewed', [RecentlyViewedController::class, 'store']);
        Route::delete('/auth/recently-viewed/{product}', [RecentlyViewedController::class, 'destroy']);
        Route::delete('/auth/recently-viewed', [RecentlyViewedController::class, 'clear']);

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::get('/notifications/unread', [NotificationController::class, 'unread']);
        Route::put('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::put('/notifications/{id}', [NotificationController::class, 'markAsRead']);
        Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
        Route::get('/notifications/preferences', [NotificationPreferenceController::class, 'show']);
        Route::put('/notifications/preferences', [NotificationPreferenceController::class, 'update']);

        // Announcements
        Route::get('/announcements', [AnnouncementController::class, 'index']);

        // Distributor application status (any authenticated user)
        Route::get('/distributor/application-status', [DistributorController::class, 'applicationStatus']);

        // Distributor portal
        Route::middleware('distributor')->prefix('distributor')->group(function () {
            Route::get('/dashboard', [DistributorDashboardController::class, 'index']);
            Route::get('/profile', [DistributorProfileController::class, 'show']);
            Route::put('/profile', [DistributorProfileController::class, 'update']);
            Route::post('/profile/logo', [DistributorProfileController::class, 'uploadLogo']);
            Route::delete('/profile/logo', [DistributorProfileController::class, 'removeLogo']);

            Route::apiResource('/branches', DistributorBranchController::class);
            Route::apiResource('/contacts', DistributorContactController::class);
            Route::apiResource('/documents', DistributorDocumentController::class)->only(['index', 'store', 'destroy']);

            Route::get('/products', [DistributorProductController::class, 'index']);
            Route::get('/products/{slug}', [DistributorProductController::class, 'show']);

            Route::apiResource('/quotes', DistributorQuotationController::class);
            Route::post('/quotes/{quote}/submit', [DistributorQuotationController::class, 'submit']);
            Route::post('/quotes/{quote}/accept', [DistributorQuotationController::class, 'accept']);

            Route::get('/orders', [DistributorOrderController::class, 'index']);
            Route::get('/orders/{order}', [DistributorOrderController::class, 'show']);

            Route::get('/invoices', [DistributorInvoiceController::class, 'index']);
            Route::get('/invoices/{invoice}', [DistributorInvoiceController::class, 'show']);

            Route::get('/statements', [DistributorStatementController::class, 'index']);

            Route::get('/payments', [DistributorPaymentUploadController::class, 'index']);
            Route::post('/payments', [DistributorPaymentUploadController::class, 'store']);

            Route::get('/analytics', [DistributorAnalyticsController::class, 'index']);
            Route::get('/notifications', [DistributorNotificationController::class, 'index']);
        });

        // Admin-only routes
        Route::middleware([\App\Http\Middleware\RequireAdminPasswordChange::class])->group(function () {
            Route::get('/admin/reviews', [ReviewController::class, 'adminIndex']);
            Route::put('/admin/reviews/{review}/status', [ReviewController::class, 'updateStatus']);
            Route::post('/admin/reviews/{review}/reply', [ReviewController::class, 'reply']);
            Route::get('/admin/feedback', [FeedbackController::class, 'adminIndex']);
            Route::put('/admin/feedback/{feedback}/status', [FeedbackController::class, 'updateStatus']);

            // Search analytics
            Route::get('/admin/search-analytics', [SearchController::class, 'analytics']);

            // Notification management
            Route::get('/admin/notification-dashboard', [NotificationDashboardController::class, 'index']);
            Route::apiResource('/admin/notifications/templates', NotificationTemplateController::class);
            Route::apiResource('/admin/announcements', AdminAnnouncementController::class);

            // Inventory & Procurement
            Route::apiResource('/admin/warehouses', AdminWarehouseController::class)->only(['index', 'show']);
            Route::apiResource('/admin/suppliers', AdminSupplierController::class)->only(['index', 'show']);
            Route::apiResource('/admin/purchase-orders', AdminPurchaseOrderController::class)->only(['index', 'show']);
            Route::get('/admin/purchase-orders/status-counts', [AdminPurchaseOrderController::class, 'statusCounts']);

            // Finance
            Route::apiResource('/admin/credit-accounts', AdminCreditAccountController::class)->only(['index', 'show']);
            Route::get('/admin/credit-accounts/summary', [AdminCreditAccountController::class, 'summary']);

            // Workflow automation
            Route::apiResource('/admin/automated-workflows', AdminAutomatedWorkflowController::class)->only(['index', 'show']);
            Route::get('/admin/automated-workflows/events', [AdminAutomatedWorkflowController::class, 'events']);
        });
    });

    // Payment callback (public webhook)
    Route::post('/payments/callback', [PaymentController::class, 'callback'])->middleware('throttle:webhook');
});

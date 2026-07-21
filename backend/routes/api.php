<?php

use App\Http\Controllers\Api\V1\Auth\AddressController;
use App\Http\Controllers\Api\V1\Auth\ChangePasswordController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\ProfileController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\Auth\UnifiedLoginController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\DistributorController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\SettingController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\InvoiceController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\ReviewController;
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
    Route::get('/settings', [SettingController::class, 'index']);

    Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:contact');
    Route::post('/distributor', [DistributorController::class, 'store']);
    Route::post('/feedback', [FeedbackController::class, 'store']);

    // Customer auth (public)
    Route::post('/auth/register', [RegisterController::class, 'register'])->middleware('throttle:login');
    Route::post('/auth/login', [UnifiedLoginController::class, 'login'])->middleware('throttle:login');

    // Customer auth (protected)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [LogoutController::class, 'logout']);
        Route::get('/auth/profile', [ProfileController::class, 'show']);
        Route::put('/auth/profile', [ProfileController::class, 'update']);
        Route::post('/auth/change-password', [ChangePasswordController::class, 'store']);


        // Addresses
        Route::get('/auth/addresses', [AddressController::class, 'index']);
        Route::post('/auth/addresses', [AddressController::class, 'store']);
        Route::get('/auth/addresses/{address}', [AddressController::class, 'show']);
        Route::put('/auth/addresses/{address}', [AddressController::class, 'update']);
        Route::delete('/auth/addresses/{address}', [AddressController::class, 'destroy']);

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
        Route::get('/orders/{order}/invoice', [InvoiceController::class, 'download']);
        Route::post('/checkout', [CheckoutController::class, 'store']);

        // Payments
        Route::post('/payments/initiate', [PaymentController::class, 'initiate'])->middleware('throttle:payment');
        Route::get('/payments/{reference}/verify', [PaymentController::class, 'verify']);
        Route::get('/payments/{reference}', [PaymentController::class, 'getTransaction']);

        // Reports
        Route::get('/reports/dashboard', [ReportController::class, 'dashboard']);
        Route::get('/reports/sales-trend', [ReportController::class, 'salesTrend']);
        Route::get('/reports/best-sellers', [ReportController::class, 'bestSellers']);
        Route::get('/reports/inventory-value', [ReportController::class, 'inventoryValue']);
        Route::get('/reports/customer-growth', [ReportController::class, 'customerGrowth']);

        // Reviews
        Route::post('/reviews', [ReviewController::class, 'store']);
        Route::put('/reviews/{review}', [ReviewController::class, 'update']);
        Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']);

        // Admin-only routes
        Route::middleware([\App\Http\Middleware\RequireAdminPasswordChange::class])->group(function () {
            Route::get('/admin/reviews', [ReviewController::class, 'adminIndex']);
            Route::put('/admin/reviews/{review}/status', [ReviewController::class, 'updateStatus']);
            Route::get('/admin/feedback', [FeedbackController::class, 'adminIndex']);
            Route::put('/admin/feedback/{feedback}/status', [FeedbackController::class, 'updateStatus']);
        });
    });

    // Payment callback (public webhook)
    Route::post('/payments/callback', [PaymentController::class, 'callback']);
});

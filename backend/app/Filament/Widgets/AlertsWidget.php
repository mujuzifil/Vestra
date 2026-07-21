<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\ContactMessage;
use App\Models\DistributorRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Filament\Widgets\Widget;

class AlertsWidget extends Widget
{
    protected static string $view = 'filament.widgets.alerts';

    protected int | string | array $columnSpan = ['lg' => 1];

    public function getAlerts(): array
    {
        $alerts = [];

        $awaitingPayment = Order::query()
            ->where('payment_status', PaymentStatus::PENDING->value)
            ->count();

        if ($awaitingPayment > 0) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'heroicon-o-credit-card',
                'message' => "{$awaitingPayment} " . ($awaitingPayment === 1 ? 'order is' : 'orders are') . ' awaiting payment',
                'url' => route('filament.admin.resources.orders.index', ['tableFilters' => ['payment_status' => ['value' => 'pending']]]),
            ];
        }

        $lowStock = Product::lowStock()->count();
        if ($lowStock > 0) {
            $alerts[] = [
                'type' => 'danger',
                'icon' => 'heroicon-o-exclamation-triangle',
                'message' => "{$lowStock} " . ($lowStock === 1 ? 'product is' : 'products are') . ' low in stock',
                'url' => route('filament.admin.resources.products.index'),
            ];
        }

        $pendingReviews = Review::pending()->count();
        if ($pendingReviews > 0) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'heroicon-o-star',
                'message' => "{$pendingReviews} " . ($pendingReviews === 1 ? 'review is' : 'reviews are') . ' awaiting moderation',
                'url' => route('filament.admin.resources.reviews.index', ['tableFilters' => ['status' => ['value' => 'pending']]]),
            ];
        }

        $newMessages = ContactMessage::new()->count();
        if ($newMessages > 0) {
            $alerts[] = [
                'type' => 'info',
                'icon' => 'heroicon-o-envelope',
                'message' => "{$newMessages} new contact " . ($newMessages === 1 ? 'message' : 'messages'),
                'url' => route('filament.admin.resources.contact-messages.index', ['tableFilters' => ['status' => ['value' => 'new']]]),
            ];
        }

        $distributorRequests = DistributorRequest::query()
            ->where('status', 'pending')
            ->count();

        if ($distributorRequests > 0) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'heroicon-o-truck',
                'message' => "{$distributorRequests} distributor " . ($distributorRequests === 1 ? 'request' : 'requests') . ' pending review',
                'url' => route('filament.admin.resources.distributor-requests.index', ['tableFilters' => ['status' => ['value' => 'pending']]]),
            ];
        }

        return $alerts;
    }
}

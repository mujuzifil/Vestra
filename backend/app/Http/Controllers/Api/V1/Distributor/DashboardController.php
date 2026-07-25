<?php

namespace App\Http\Controllers\Api\V1\Distributor;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Distributor\DashboardResource;
use App\Services\AuditService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use RespondsWithJson;

    public function index(Request $request): JsonResponse
    {
        $distributor = $request->user()->distributor;

        $this->authorize('view', $distributor);

        $distributor->load('creditAccount');

        $recentOrders = $distributor->orders()
            ->with('items')
            ->latest()
            ->limit(5)
            ->get();

        $recentQuotations = $distributor->quotations()
            ->with('items')
            ->latest()
            ->limit(5)
            ->get();

        $creditAccount = $distributor->creditAccount;

        $pendingOrders = $distributor->orders()
            ->whereNotIn('status', ['delivered', 'cancelled', 'refunded'])
            ->count();

        $pendingQuotes = $distributor->quotations()
            ->whereIn('status', ['draft', 'submitted', 'reviewed', 'quoted'])
            ->count();

        $outstandingBalance = (float) $distributor->orders()
            ->where('payment_status', 'pending')
            ->sum('total_amount');

        $statistics = [
            'total_orders' => $distributor->orders()->count(),
            'pending_orders' => $pendingOrders,
            'total_quotes' => $distributor->quotations()->count(),
            'pending_quotes' => $pendingQuotes,
            'unread_notifications' => 0,
            'credit_limit' => $creditAccount ? (string) $creditAccount->limit : '0.00',
            'available_credit' => $creditAccount ? (string) $creditAccount->availableCredit() : '0.00',
            'outstanding_balance' => (string) $outstandingBalance,
        ];

        $recentNotifications = $this->buildNotifications($distributor, $recentOrders, $recentQuotations);

        AuditService::log(
            $request->user(),
            'distributor_dashboard_viewed',
            $distributor,
            null,
            $request->ip(),
            $request->userAgent()
        );

        return $this->successResponse(
            new DashboardResource([
                'distributor' => $distributor,
                'recent_orders' => $recentOrders,
                'recent_quotations' => $recentQuotations,
                'recent_notifications' => $recentNotifications,
                'statistics' => $statistics,
                'stats' => $statistics,
                'recent_quotes' => $recentQuotations,
            ])
        );
    }

    private function buildNotifications($distributor, $recentOrders, $recentQuotations): array
    {
        $notifications = [];

        foreach ($recentOrders as $order) {
            $notifications[] = [
                'id' => 'order-' . $order->id,
                'type' => 'order',
                'title' => "Order {$order->invoice_number} updated",
                'message' => "Your order is now {$order->status}.",
                'is_read' => false,
                'action_url' => "/distributor/orders/{$order->id}",
                'created_at' => $order->updated_at,
            ];
        }

        foreach ($recentQuotations as $quote) {
            $notifications[] = [
                'id' => 'quote-' . $quote->id,
                'type' => 'quote',
                'title' => "Quote {$quote->reference_number} updated",
                'message' => "Your quote is now {$quote->status->value}.",
                'is_read' => false,
                'action_url' => "/distributor/quotes/{$quote->id}",
                'created_at' => $quote->updated_at,
            ];
        }

        usort($notifications, fn ($a, $b) => $b['created_at'] <=> $a['created_at']);

        return array_slice($notifications, 0, 5);
    }
}

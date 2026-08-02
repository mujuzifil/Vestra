<?php

namespace App\Services;

use App\Models\CustomerDocument;
use App\Models\DistributorRequest;
use App\Models\QuoteRequest;
use App\Models\SavedItem;
use App\Models\SupportTicket;
use App\Models\User;

class AccountDashboardService
{
    public function forUser(User $user): array
    {
        $quoteCounts = QuoteRequest::where('user_id', $user->id)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->all();

        $supportCount = SupportTicket::where('user_id', $user->id)->count();
        $documentCount = CustomerDocument::where('user_id', $user->id)->count();
        $savedCount = SavedItem::where('user_id', $user->id)->count();
        $unreadNotifications = $user->unreadNotifications()->count();

        $distributorStatus = DistributorRequest::where('email', $user->email)
            ->latest()
            ->value('status');

        $recentQuotes = QuoteRequest::where('user_id', $user->id)
            ->with('items')
            ->latest()
            ->limit(3)
            ->get();

        $recentDocuments = CustomerDocument::where('user_id', $user->id)
            ->latest()
            ->limit(3)
            ->get();

        return [
            'quotes' => [
                'submitted' => array_sum($quoteCounts),
                'pending' => $quoteCounts['pending'] ?? 0,
                'approved' => ($quoteCounts['approved'] ?? 0) + ($quoteCounts['quoted'] ?? 0),
            ],
            'support_enquiries' => $supportCount,
            'documents' => $documentCount,
            'saved_products' => $savedCount,
            'unread_notifications' => $unreadNotifications,
            'distributor_status' => $distributorStatus,
            'recent_quotes' => $recentQuotes,
            'recent_documents' => $recentDocuments,
        ];
    }
}

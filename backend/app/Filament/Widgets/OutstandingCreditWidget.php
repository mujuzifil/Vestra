<?php

namespace App\Filament\Widgets;

use App\Models\CreditAccount;
use App\Models\DistributorRequest;
use App\Models\PaymentUpload;
use Filament\Widgets\StatsOverviewWidget;
use Illuminate\Support\Facades\Cache;

class OutstandingCreditWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $outstandingCredit = Cache::remember('admin.finance.outstanding_credit', 300, function (): float {
            return CreditAccount::query()->sum('balance') ?? 0;
        });

        $pendingCreditApprovals = Cache::remember('admin.finance.pending_credit_approvals', 300, function (): int {
            return CreditAccount::query()
                ->where('status', 'pending')
                ->orWhere('status', 'awaiting_approval')
                ->count();
        });

        $pendingPaymentUploads = Cache::remember('admin.finance.pending_payment_uploads', 300, function (): int {
            return PaymentUpload::query()->where('status', 'pending')->count();
        });

        $pendingDistributorApplications = Cache::remember('admin.distributors.pending_applications', 300, function (): int {
            return DistributorRequest::query()->where('status', 'pending')->orWhere('status', 'submitted')->count();
        });

        return [
            StatsOverviewWidget\Stat::make('Outstanding Credit', 'UGX ' . number_format($outstandingCredit))
                ->description('Total distributor credit balance')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color($outstandingCredit > 0 ? 'warning' : 'gray'),

            StatsOverviewWidget\Stat::make('Credit Approvals', number_format($pendingCreditApprovals))
                ->description('Awaiting credit approval')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($pendingCreditApprovals > 0 ? 'warning' : 'gray'),

            StatsOverviewWidget\Stat::make('Payment Uploads', number_format($pendingPaymentUploads))
                ->description('Pending verification')
                ->descriptionIcon('heroicon-m-document-check')
                ->color($pendingPaymentUploads > 0 ? 'warning' : 'gray'),

            StatsOverviewWidget\Stat::make('Distributor Applications', number_format($pendingDistributorApplications))
                ->description('Pending review')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color($pendingDistributorApplications > 0 ? 'info' : 'gray'),
        ];
    }
}

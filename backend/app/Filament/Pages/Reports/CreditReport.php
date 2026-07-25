<?php

namespace App\Filament\Pages\Reports;

use App\Models\CreditAccount;
use App\Models\CreditTransaction;
use Illuminate\Support\Facades\Cache;

class CreditReport extends ReportPage
{
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'Credit';

    protected static ?int $navigationSort = 60;

    protected static string $view = 'filament.pages.reports.credit-report';

    public function getTitle(): string
    {
        return 'Credit Report';
    }

    public function getTotalCreditLimit(): float
    {
        return Cache::remember('admin.reports.credit.total_limit', 300, function (): float {
            return CreditAccount::query()->sum('limit') ?? 0;
        });
    }

    public function getTotalOutstanding(): float
    {
        return Cache::remember('admin.reports.credit.total_outstanding', 300, function (): float {
            return CreditAccount::query()->sum('balance') ?? 0;
        });
    }

    public function getTotalAvailable(): float
    {
        return max(0, CreditAccount::query()->sum('limit') - CreditAccount::query()->sum('balance') - CreditAccount::query()->sum('authorized_amount'));
    }

    public function getCreditAccounts(): array
    {
        return CreditAccount::query()
            ->with('distributor')
            ->orderByDesc('balance')
            ->limit(20)
            ->get()
            ->map(fn (CreditAccount $account) => [
                'distributor' => $account->distributor?->company_name,
                'credit_limit' => $account->limit,
                'outstanding_balance' => $account->balance,
                'available_credit' => $account->availableCredit(),
                'status' => $account->status,
            ])
            ->toArray();
    }

    public function getRecentTransactions(): array
    {
        return CreditTransaction::query()
            ->with(['creditAccount.distributor'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (CreditTransaction $tx) => [
                'distributor' => $tx->creditAccount?->distributor?->company_name,
                'type' => $tx->type,
                'amount' => $tx->amount,
                'created_at' => $tx->created_at->format('M d, Y'),
            ])
            ->toArray();
    }

    protected function getReportSlug(): string
    {
        return 'credit';
    }

    protected function getExportColumns(): array
    {
        return [
            ['name' => 'distributor', 'label' => 'Distributor'],
            ['name' => 'credit_limit', 'label' => 'Credit Limit'],
            ['name' => 'outstanding_balance', 'label' => 'Outstanding'],
            ['name' => 'available_credit', 'label' => 'Available'],
            ['name' => 'status', 'label' => 'Status'],
        ];
    }

    protected function getExportRows(): array
    {
        return $this->getCreditAccounts();
    }
}

<?php

namespace App\Services\Admin;

use App\Models\CreditAccount;
use App\Models\CreditTransaction;
use App\Models\Distributor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CreditAdminService
{
    /**
     * Credit account statuses supported by the credit_accounts.status column.
     *
     * @var array<int, string>
     */
    public const STATUSES = ['active', 'pending', 'suspended'];

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateAccounts(array $filters = [], string $sort = 'updated_at', string $direction = 'desc', int $perPage = 15): LengthAwarePaginator
    {
        return $this->queryAccounts($filters, $sort, $direction)->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function queryAccounts(array $filters = [], string $sort = 'updated_at', string $direction = 'desc'): Builder
    {
        $query = CreditAccount::query()
            ->with(['distributor'])
            ->withCount('transactions')
            ->when($filters['search'] ?? null, function (Builder $q, string $term): void {
                $q->whereHas('distributor', function (Builder $sub) use ($term): void {
                    $sub->where('company_name', 'like', "%{$term}%")
                        ->orWhere('trading_name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $q, array $statuses) => $q->whereIn('status', $statuses))
            ->when($filters['country'] ?? null, function (Builder $q, array $countries): void {
                $q->whereHas('distributor', fn (Builder $sub) => $sub->whereIn('country', $countries));
            });

        return $this->applySorting($query, $sort, $direction);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getKpiCards(): array
    {
        $accounts = CreditAccount::query()->get(['id', 'limit', 'balance', 'authorized_amount']);

        $totalAccounts = $accounts->count();
        $totalLimit = (float) $accounts->sum(fn (CreditAccount $account) => (float) $account->limit);
        $totalBalance = (float) $accounts->sum(fn (CreditAccount $account) => (float) $account->balance);
        $totalAvailable = (float) $accounts->sum(fn (CreditAccount $account) => $account->availableCredit());
        $avgUtilization = $totalAccounts > 0
            ? $accounts->avg(fn (CreditAccount $account) => $account->utilizationPercentage())
            : 0.0;

        $currentMonthStart = now()->copy()->startOfMonth();
        $newThisMonth = CreditAccount::query()->where('created_at', '>=', $currentMonthStart)->count();

        return [
            $this->buildCountCard('Total Accounts', $totalAccounts, $newThisMonth, 'heroicon-o-identification', 'primary'),
            $this->buildValueCard('Total Credit Limit', $totalLimit, 'heroicon-o-banknotes', 'info'),
            $this->buildValueCard('Outstanding Balance', $totalBalance, 'heroicon-o-receipt-percent', 'warning'),
            $this->buildValueCard('Available Credit', $totalAvailable, 'heroicon-o-shield-check', 'success'),
            $this->buildPercentageCard('Avg. Utilization', (float) $avgUtilization, 'heroicon-o-chart-bar', 'primary'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getDetail(CreditAccount $account): array
    {
        $account->load('distributor');

        return [
            'id' => $account->id,
            'limit' => (float) $account->limit,
            'balance' => (float) $account->balance,
            'authorized_amount' => (float) $account->authorized_amount,
            'available_credit' => $account->availableCredit(),
            'utilization_percentage' => $account->utilizationPercentage(),
            'status' => $account->status,
            'admin_notes' => $account->admin_notes,
            'created_at' => $account->created_at,
            'updated_at' => $account->updated_at,
            'distributor' => $account->distributor ? [
                'id' => $account->distributor->id,
                'company_name' => $account->distributor->company_name,
                'trading_name' => $account->distributor->trading_name,
                'email' => $account->distributor->email,
                'phone' => $account->distributor->phone,
                'country' => $account->distributor->country,
                'district' => $account->distributor->district,
                'city' => $account->distributor->city,
                'status' => $account->distributor->status,
            ] : null,
            'transactions' => CreditTransaction::query()
                ->where('credit_account_id', $account->id)
                ->with('creator')
                ->latest()
                ->limit(25)
                ->get()
                ->map(fn (CreditTransaction $transaction) => [
                    'id' => $transaction->id,
                    'type' => $transaction->type,
                    'type_label' => $transaction->type?->label() ?? ucfirst((string) $transaction->type),
                    'amount' => (float) $transaction->amount,
                    'balance_after' => (float) $transaction->balance_after,
                    'description' => $transaction->description,
                    'created_by' => $transaction->creator?->name,
                    'created_at' => $transaction->created_at,
                ])->toArray(),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     *
     * @return array<int, array<string, mixed>>
     */
    public function exportAccounts(array $filters = []): array
    {
        return $this->queryAccounts($filters, 'updated_at', 'desc')
            ->get()
            ->map(fn (CreditAccount $account) => [
                'distributor' => $account->distributor?->company_name,
                'country' => $account->distributor?->country,
                'limit' => (float) $account->limit,
                'balance' => (float) $account->balance,
                'authorized_amount' => (float) $account->authorized_amount,
                'available_credit' => $account->availableCredit(),
                'utilization_percentage' => round($account->utilizationPercentage(), 1),
                'status' => ucfirst((string) $account->status),
                'updated_at' => $account->updated_at?->format('Y-m-d H:i:s'),
            ])
            ->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilterOptions(): array
    {
        return [
            'statuses' => self::STATUSES,
            'countries' => Distributor::query()
                ->whereHas('creditAccount')
                ->whereNotNull('country')
                ->where('country', '!=', '')
                ->distinct()
                ->orderBy('country')
                ->pluck('country')
                ->toArray(),
        ];
    }

    private function applySorting(Builder $query, string $sort, string $direction): Builder
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return match ($sort) {
            'distributor' => $query->orderBy(
                Distributor::select('company_name')
                    ->whereColumn('distributors.id', 'credit_accounts.distributor_id')
                    ->limit(1),
                $direction
            ),
            'limit' => $query->orderBy('limit', $direction),
            'balance' => $query->orderBy('balance', $direction),
            'status' => $query->orderBy('status', $direction),
            'created_at' => $query->orderBy('created_at', $direction),
            'updated_at' => $query->orderBy('updated_at', $direction),
            default => $query->orderBy('updated_at', 'desc'),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function buildCountCard(string $label, int $current, int $newThisMonth, string $icon, string $color): array
    {
        $hasComparison = $newThisMonth > 0;

        return [
            'label' => $label,
            'value' => number_format($current),
            'icon' => $icon,
            'color' => $color,
            'trend' => $hasComparison ? '+'.$newThisMonth : '—',
            'trend_label' => $hasComparison ? $newThisMonth.' new this month' : 'No new accounts this month',
            'trend_positive' => true,
            'trend_available' => $hasComparison,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildValueCard(string $label, float $amount, string $icon, string $color): array
    {
        return [
            'label' => $label,
            'value' => $this->formatCurrency($amount),
            'icon' => $icon,
            'color' => $color,
            'trend' => '—',
            'trend_label' => 'Point-in-time balance',
            'trend_positive' => true,
            'trend_available' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPercentageCard(string $label, float $percentage, string $icon, string $color): array
    {
        return [
            'label' => $label,
            'value' => number_format($percentage, 1).'%',
            'icon' => $icon,
            'color' => $color,
            'trend' => '—',
            'trend_label' => 'Across all credit accounts',
            'trend_positive' => true,
            'trend_available' => false,
        ];
    }

    private function formatCurrency(float $amount): string
    {
        if ($amount >= 1_000_000_000) {
            return 'UGX '.number_format($amount / 1_000_000_000, 2).'B';
        }

        if ($amount >= 1_000_000) {
            return 'UGX '.number_format($amount / 1_000_000, 1).'M';
        }

        if ($amount >= 1_000) {
            return 'UGX '.number_format($amount / 1_000, 1).'K';
        }

        return 'UGX '.number_format($amount, 0);
    }
}

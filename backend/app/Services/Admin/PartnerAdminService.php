<?php

namespace App\Services\Admin;

use App\Enums\DistributorAccountStatus;
use App\Enums\PaymentStatus;
use App\Models\AuditLog;
use App\Models\CreditAccount;
use App\Models\Distributor;
use App\Models\DistributorServiceArea;
use App\Models\Order;
use App\Models\SalesRepresentative;
use App\Models\User;
use App\Services\AuditService;
use App\Services\Catalog\CatalogSyncService;
use App\Services\DistributorCoverageSync;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PartnerAdminService
{
    public function __construct(
        private readonly CatalogSyncService $catalogSync,
        private readonly DistributorCoverageSync $coverageSync,
    ) {}
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginatePartners(array $filters = [], string $sort = 'created_at', string $direction = 'desc', int $perPage = 15): LengthAwarePaginator
    {
        return $this->queryPartners($filters, $sort, $direction)->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function queryPartners(array $filters = [], string $sort = 'created_at', string $direction = 'desc'): Builder
    {
        $query = Distributor::query()
            ->with(['user', 'salesRepresentative', 'creditAccount', 'branches'])
            ->when($filters['search'] ?? null, fn (Builder $q, string $term) => $q->search($term))
            ->when($filters['status'] ?? null, fn (Builder $q, array $statuses) => $q->statusIn($statuses))
            ->when($filters['country'] ?? null, fn (Builder $q, array $countries) => $q->whereIn('country', $countries))
            ->when($filters['region'] ?? null, fn (Builder $q, array $regions) => $q->inRegions($regions))
            ->when($filters['sales_rep'] ?? null, fn (Builder $q, int $id) => $q->where('sales_representative_id', $id));

        return $this->applySorting($query, $sort, $direction);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getKpiCards(): array
    {
        $now = now();
        $currentMonthStart = $now->copy()->startOfMonth();
        $previousMonthStart = $now->copy()->subMonth()->startOfMonth();
        $previousMonthEnd = $previousMonthStart->copy()->endOfMonth();

        $totalCurrent = Distributor::query()->count();
        $totalPrevious = Distributor::query()
            ->where('created_at', '<', $currentMonthStart)
            ->count();

        $activeCurrent = Distributor::query()->where('status', DistributorAccountStatus::ACTIVE->value)->count();
        $activePrevious = Distributor::query()
            ->where('status', DistributorAccountStatus::ACTIVE->value)
            ->where('created_at', '<', $currentMonthStart)
            ->count();

        $suspendedCurrent = Distributor::query()->where('status', DistributorAccountStatus::SUSPENDED->value)->count();
        $suspendedPrevious = Distributor::query()
            ->where('status', DistributorAccountStatus::SUSPENDED->value)
            ->where('created_at', '<', $currentMonthStart)
            ->count();

        $cards = [
            $this->buildCard('Total Partners', $totalCurrent, $totalPrevious, 'vs last month', 'heroicon-o-building-storefront', 'primary'),
            $this->buildCard('Active Partners', $activeCurrent, $activePrevious, 'vs last month', 'heroicon-o-check-badge', 'success'),
            $this->buildCard('Suspended Partners', $suspendedCurrent, $suspendedPrevious, 'vs last month', 'heroicon-o-exclamation-triangle', 'danger'),
        ];

        if ($this->ordersTableHasDistributorRevenue()) {
            $revenueCurrent = (float) Order::query()
                ->whereNotNull('distributor_id')
                ->where('payment_status', PaymentStatus::PAID->value)
                ->whereBetween('created_at', [$currentMonthStart, $now])
                ->sum('total_amount');

            $revenuePrevious = (float) Order::query()
                ->whereNotNull('distributor_id')
                ->where('payment_status', PaymentStatus::PAID->value)
                ->whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])
                ->sum('total_amount');

            $hasRevenueHistory = Order::query()
                ->whereNotNull('distributor_id')
                ->where('payment_status', PaymentStatus::PAID->value)
                ->where('created_at', '<', $currentMonthStart)
                ->exists();

            $cards[] = $this->buildValueCard('Revenue (This Month)', $revenueCurrent, $revenuePrevious, $hasRevenueHistory, 'heroicon-o-banknotes', 'info');
        }

        $creditOutstanding = (float) CreditAccount::query()->sum('balance');

        $cards[] = $this->buildValueCard('Credit Outstanding', $creditOutstanding, 0, false, 'heroicon-o-credit-card', 'warning');

        return $cards;
    }

    /**
     * @return array<string, mixed>
     */
    public function getDetail(Distributor $distributor): array
    {
        $distributor->load([
            'user',
            'salesRepresentative',
            'creditAccount',
            'branches',
            'documents',
            'contacts',
            'serviceAreas',
            'request',
        ]);

        $creditAccount = $distributor->creditAccount;

        return [
            'id' => $distributor->id,
            'company_name' => $distributor->company_name,
            'trading_name' => $distributor->trading_name,
            'status' => $distributor->status,
            'tier' => $distributor->tier,
            'tier_label' => $distributor->tier?->label(),
            'registration_number' => $distributor->registration_number,
            'tax_identification' => $distributor->tax_identification,
            'vat_number' => $distributor->vat_number,
            'business_type' => $distributor->business_type,
            'industry' => $distributor->industry,
            'years_in_business' => $distributor->years_in_business,
            'company_size' => $distributor->company_size,
            'website' => $distributor->website,
            'email' => $distributor->email,
            'phone' => $distributor->phone,
            'whatsapp' => $distributor->whatsapp,
            'country' => $distributor->country,
            'district' => $distributor->district,
            'city' => $distributor->city,
            'address' => $distributor->address,
            'google_maps_url' => $distributor->google_maps_url,
            'stock_availability' => $distributor->stock_availability,
            'stock_availability_label' => $distributor->stock_availability?->label(),
            'operating_hours' => $distributor->operating_hours_json,
            'expected_monthly_volume' => $distributor->expected_monthly_volume,
            'products_of_interest' => $distributor->products_of_interest,
            'approved_at' => $distributor->approved_at,
            'suspended_at' => $distributor->suspended_at,
            'created_at' => $distributor->created_at,
            'updated_at' => $distributor->updated_at,
            'primary_contact' => [
                'name' => $distributor->primary_contact_name,
                'email' => $distributor->email,
                'phone' => $distributor->phone,
            ],
            'sales_rep' => $distributor->salesRepresentative ? [
                'id' => $distributor->salesRepresentative->id,
                'name' => $distributor->salesRepresentative->name,
                'email' => $distributor->salesRepresentative->email,
                'phone' => $distributor->salesRepresentative->phone,
            ] : null,
            'company' => [
                'name' => $distributor->company_name,
                'trading_name' => $distributor->trading_name,
                'registration_number' => $distributor->registration_number,
                'business_type' => $distributor->business_type,
                'industry' => $distributor->industry,
            ],
            'branches' => $distributor->branches->map(fn ($branch) => [
                'id' => $branch->id,
                'name' => $branch->name,
                'manager_name' => $branch->manager_name,
                'phone' => $branch->phone,
                'email' => $branch->email,
                'country' => $branch->country,
                'district' => $branch->district,
                'city' => $branch->city,
                'address' => $branch->address,
                'status' => $branch->status,
                'is_default' => $branch->is_default,
            ])->toArray(),
            'service_areas' => $distributor->serviceAreas->map(fn (DistributorServiceArea $area) => [
                'id' => $area->id,
                'region' => $area->region,
                'district' => $area->district,
                'status' => $area->status,
            ])->toArray(),
            'credit' => $creditAccount ? [
                'id' => $creditAccount->id,
                'limit' => (float) $creditAccount->limit,
                'balance' => (float) $creditAccount->balance,
                'authorized_amount' => (float) $creditAccount->authorized_amount,
                'available_credit' => $creditAccount->availableCredit(),
                'utilization_percentage' => $creditAccount->utilizationPercentage(),
                'status' => $creditAccount->status,
                'admin_notes' => $creditAccount->admin_notes,
            ] : null,
            'documents' => $distributor->documents->map(fn ($doc) => [
                'id' => $doc->id,
                'title' => $doc->title,
                'type' => $doc->type,
                'file_name' => basename((string) $doc->file_path),
                'url' => $doc->fileUrl(),
                'version' => $doc->version,
                'created_at' => $doc->created_at,
            ])->toArray(),
            'recent_orders' => Order::query()
                ->where('distributor_id', $distributor->id)
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (Order $order) => [
                    'id' => $order->id,
                    'invoice_number' => $order->invoice_number,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'total_amount' => (float) $order->total_amount,
                    'created_at' => $order->created_at,
                ])->toArray(),
            'recent_activity' => AuditLog::query()
                ->where('subject_type', Distributor::class)
                ->where('subject_id', $distributor->id)
                ->with('user')
                ->latest()
                ->limit(10)
                ->get()
                ->map(fn (AuditLog $log) => [
                    'id' => $log->id,
                    'action' => $log->action,
                    'user' => $log->user?->name ?? 'System',
                    'created_at' => $log->created_at,
                ])->toArray(),
            'application' => $distributor->request ? [
                'id' => $distributor->request->id,
                'status' => $distributor->request->status?->value,
                'submitted_at' => $distributor->request->created_at,
            ] : null,
            'actions' => [
                'suspend' => $distributor->isActive(),
                'activate' => $distributor->isSuspended(),
                'credit' => $creditAccount !== null,
                'coverage' => true,
                'edit' => true,
                'delete' => true,
            ],
            'edit_url' => \App\Filament\Pages\Distributors\PartnerEditPage::getUrl(['partner' => $distributor->id]),
            'credit_url' => $creditAccount
                ? \App\Filament\Pages\Distributors\CreditPage::getUrl(['search' => $distributor->company_name])
                : null,
            'territories_url' => \App\Filament\Pages\Distributors\TerritoriesPage::getUrl([
                'distributor' => $distributor->id,
            ]),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     *
     * @return array<int, array<string, mixed>>
     */
    public function exportPartners(array $filters = []): array
    {
        return $this->queryPartners($filters, 'company_name', 'asc')
            ->get()
            ->map(fn (Distributor $distributor) => [
                'company_name' => $distributor->company_name,
                'trading_name' => $distributor->trading_name,
                'status' => $distributor->status?->label(),
                'business_type' => $distributor->business_type,
                'country' => $distributor->country,
                'district' => $distributor->district,
                'city' => $distributor->city,
                'email' => $distributor->email,
                'phone' => $distributor->phone,
                'sales_rep' => $distributor->salesRepresentative?->name,
                'credit_limit' => $distributor->creditAccount?->limit,
                'credit_balance' => $distributor->creditAccount?->balance,
                'registration_number' => $distributor->registration_number,
                'created_at' => $distributor->created_at?->format('Y-m-d H:i:s'),
            ])
            ->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilterOptions(): array
    {
        return [
            'countries' => Distributor::query()
                ->whereNotNull('country')
                ->where('country', '!=', '')
                ->distinct()
                ->orderBy('country')
                ->pluck('country')
                ->toArray(),
            'regions' => DistributorServiceArea::query()
                ->whereNotNull('region')
                ->where('region', '!=', '')
                ->distinct()
                ->orderBy('region')
                ->pluck('region')
                ->toArray(),
            'sales_reps' => SalesRepresentative::query()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (SalesRepresentative $rep) => ['id' => $rep->id, 'name' => $rep->name])
                ->toArray(),
        ];
    }

    public function suspend(Distributor $distributor, ?User $admin = null, ?string $reason = null): Distributor
    {
        return DB::transaction(function () use ($distributor, $admin, $reason) {
            $distributor->refresh();

            if ($distributor->isSuspended()) {
                return $distributor;
            }

            $distributor->suspend();

            AuditService::log(
                $admin,
                'distributor_suspended',
                $distributor,
                ['reason' => $reason],
                request()?->ip(),
                request()?->userAgent()
            );

            $distributorId = $distributor->id;

            DB::afterCommit(fn () => $this->catalogSync->syncDistributors($distributorId));

            return $distributor->fresh(['user', 'creditAccount', 'branches', 'serviceAreas']);
        });
    }

    public function activate(Distributor $distributor, ?User $admin = null): Distributor
    {
        return DB::transaction(function () use ($distributor, $admin) {
            $distributor->refresh();

            if ($distributor->isActive()) {
                return $distributor;
            }

            $distributor->activate();
            $this->coverageSync->sync($distributor);

            AuditService::log(
                $admin,
                'distributor_activated',
                $distributor,
                null,
                request()?->ip(),
                request()?->userAgent()
            );

            $distributorId = $distributor->id;

            DB::afterCommit(fn () => $this->catalogSync->syncDistributors($distributorId));

            return $distributor->fresh(['user', 'creditAccount', 'branches', 'serviceAreas']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateProfile(Distributor $distributor, array $data, ?User $admin = null): Distributor
    {
        return DB::transaction(function () use ($distributor, $data, $admin) {
            $allowed = array_intersect_key($data, array_flip([
                'company_name',
                'trading_name',
                'registration_number',
                'tax_identification',
                'vat_number',
                'business_type',
                'industry',
                'years_in_business',
                'company_size',
                'website',
                'primary_contact_name',
                'email',
                'phone',
                'whatsapp',
                'country',
                'district',
                'city',
                'address',
                'postal_address',
                'google_maps_url',
                'tier',
                'stock_availability',
                'operating_hours_json',
                'expected_monthly_volume',
                'products_of_interest',
                'sales_representative_id',
            ]));

            if ($allowed === []) {
                throw ValidationException::withMessages([
                    'profile' => 'No valid profile fields were provided.',
                ]);
            }

            $distributor->update($allowed);

            $locationTouched = collect(['country', 'district', 'city', 'address'])
                ->intersect(array_keys($allowed))
                ->isNotEmpty();

            if ($locationTouched || $distributor->isActive()) {
                $this->coverageSync->sync($distributor->fresh());
            }

            AuditService::log(
                $admin,
                'distributor_profile_updated',
                $distributor,
                ['fields' => array_keys($allowed)],
                request()?->ip(),
                request()?->userAgent()
            );

            $distributorId = $distributor->id;

            DB::afterCommit(fn () => $this->catalogSync->syncDistributors($distributorId));

            return $distributor->fresh(['user', 'creditAccount', 'branches', 'serviceAreas']);
        });
    }

    public function purge(Distributor $distributor, ?User $admin = null): void
    {
        DB::transaction(function () use ($distributor, $admin): void {
            $distributor->refresh();
            $userId = $distributor->user_id;
            $distributorId = $distributor->id;
            $companyName = $distributor->company_name;

            AuditService::log(
                $admin,
                'distributor_purged',
                $distributor,
                ['company_name' => $companyName, 'user_id' => $userId],
                request()?->ip(),
                request()?->userAgent()
            );

            // Detach optional channel links without violating non-null FKs.
            if (Schema::hasColumn('orders', 'distributor_id')) {
                Order::query()->where('distributor_id', $distributorId)->update(['distributor_id' => null]);
            }

            // quotation_requests.distributor_id is NOT NULL — delete rows (items cascade).
            if (Schema::hasTable('quotation_requests')) {
                $quotationIds = DB::table('quotation_requests')
                    ->where('distributor_id', $distributorId)
                    ->pluck('id');

                if ($quotationIds->isNotEmpty() && Schema::hasTable('quotation_items')) {
                    DB::table('quotation_items')->whereIn('quotation_request_id', $quotationIds)->delete();
                }

                DB::table('quotation_requests')->where('distributor_id', $distributorId)->delete();
            }

            if (Schema::hasTable('payment_uploads')) {
                DB::table('payment_uploads')->where('distributor_id', $distributorId)->delete();
            }

            if (Schema::hasTable('distributor_product_prices')) {
                DB::table('distributor_product_prices')->where('distributor_id', $distributorId)->delete();
            }

            $creditAccount = $distributor->creditAccount;
            if ($creditAccount !== null) {
                $creditAccount->transactions()->delete();
                $creditAccount->delete();
            }

            $distributor->serviceAreas()->delete();
            $distributor->documents()->delete();
            $distributor->contacts()->delete();
            $distributor->branches()->delete();

            $distributor->delete();

            if ($userId) {
                $this->purgePortalUser((int) $userId);
            }

            DB::afterCommit(fn () => $this->catalogSync->syncDistributors($distributorId));
        });
    }

    private function purgePortalUser(int $userId): void
    {
        $user = User::query()->find($userId);
        if ($user === null) {
            return;
        }

        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }

        // orders.user_id is RESTRICT — keep a deactivated shell when history exists.
        $hasRestrictedOrders = Schema::hasTable('orders')
            && Order::query()->where('user_id', $userId)->exists();

        if ($hasRestrictedOrders) {
            $user->forceFill([
                'name' => 'Deleted Partner',
                'email' => 'deleted-partner-'.$userId.'@vestra.invalid',
                'phone' => null,
                'status' => 'inactive',
                'password' => bcrypt(str()->random(40)),
                'remember_token' => null,
                'email_verified_at' => null,
            ])->save();

            return;
        }

        $user->delete();
    }

    /**
     * @param  array<int, array<string, mixed>>  $areas
     */
    public function updateCoverage(Distributor $distributor, array $areas, ?User $admin = null): Distributor
    {
        return DB::transaction(function () use ($distributor, $areas, $admin) {
            $defaultBranch = $distributor->branches()->where('is_default', true)->first()
                ?? $distributor->branches()->first();

            $normalized = collect($areas)
                ->map(fn (array $area) => [
                    'region' => trim((string) ($area['region'] ?? '')),
                    'district' => trim((string) ($area['district'] ?? '')),
                    'status' => $area['status'] ?? 'covered',
                ])
                ->filter(fn (array $area) => $area['region'] !== '' && $area['district'] !== '')
                ->unique(fn (array $area) => mb_strtolower($area['region'].'|'.$area['district']))
                ->values();

            $distributor->serviceAreas()->delete();

            foreach ($normalized as $area) {
                DistributorServiceArea::create([
                    'distributor_id' => $distributor->id,
                    'branch_id' => $defaultBranch?->id,
                    'region' => $area['region'],
                    'district' => $area['district'],
                    'status' => $area['status'],
                ]);
            }

            AuditService::log(
                $admin,
                'distributor_coverage_updated',
                $distributor,
                ['area_count' => $normalized->count()],
                request()?->ip(),
                request()?->userAgent()
            );

            $distributorId = $distributor->id;

            DB::afterCommit(fn () => $this->catalogSync->syncDistributors($distributorId));

            return $distributor->fresh(['user', 'creditAccount', 'branches', 'serviceAreas']);
        });
    }

    private function applySorting(Builder $query, string $sort, string $direction): Builder
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return match ($sort) {
            'company_name' => $query->orderBy('company_name', $direction),
            'status' => $query->orderBy('status', $direction),
            'country' => $query->orderBy('country', $direction),
            'created_at' => $query->orderBy('created_at', $direction),
            'updated_at' => $query->orderBy('updated_at', $direction),
            'sales_rep' => $query->orderBy(
                SalesRepresentative::select('name')
                    ->whereColumn('sales_representatives.id', 'distributors.sales_representative_id')
                    ->limit(1),
                $direction
            ),
            default => $query->orderBy('created_at', 'desc'),
        };
    }

    private function ordersTableHasDistributorRevenue(): bool
    {
        return Schema::hasColumn('orders', 'distributor_id')
            && Schema::hasColumn('orders', 'payment_status');
    }

    /**
     * @return array<string, mixed>
     */
    private function buildCard(string $label, float $current, float $previous, string $comparisonLabel, string $icon, string $color): array
    {
        $trendAvailable = $previous > 0 || $current > 0;
        $hasComparison = $previous > 0;

        $trend = $hasComparison ? $this->calculateTrend($current, $previous) : [
            'value' => '—',
            'label' => 'No comparison available',
            'positive' => true,
        ];

        return [
            'label' => $label,
            'value' => number_format($current),
            'icon' => $icon,
            'color' => $color,
            'trend' => $trend['value'],
            'trend_label' => $hasComparison ? ($trend['label'].' '.$comparisonLabel) : 'No comparison available',
            'trend_positive' => $trend['positive'],
            'trend_available' => $hasComparison && $trendAvailable,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildValueCard(string $label, float $current, float $previous, bool $hasHistory, string $icon, string $color): array
    {
        $hasComparison = $hasHistory && $previous > 0;

        $trend = $hasComparison ? $this->calculateTrend($current, $previous) : [
            'value' => '—',
            'label' => 'No comparison available',
            'positive' => true,
        ];

        return [
            'label' => $label,
            'value' => $this->formatCurrency($current),
            'icon' => $icon,
            'color' => $color,
            'trend' => $trend['value'],
            'trend_label' => $hasComparison ? ($trend['label'].' vs last month') : 'No comparison available',
            'trend_positive' => $trend['positive'],
            'trend_available' => $hasComparison,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function calculateTrend(float $current, float $previous): array
    {
        if ($previous <= 0 && $current <= 0) {
            return [
                'value' => '0%',
                'label' => 'No change',
                'positive' => true,
            ];
        }

        if ($previous <= 0) {
            return [
                'value' => '—',
                'label' => 'No comparison available',
                'positive' => true,
            ];
        }

        $change = (($current - $previous) / $previous) * 100;
        $positive = $change >= 0;

        return [
            'value' => sprintf('%s%.1f%%', $positive ? '+' : '', $change),
            'label' => $positive ? 'Up' : 'Down',
            'positive' => $positive,
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

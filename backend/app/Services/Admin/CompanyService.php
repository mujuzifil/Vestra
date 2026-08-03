<?php

namespace App\Services\Admin;

use App\Enums\CompanyStatus;
use App\Models\AuditLog;
use App\Models\CompanyProfile;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompanyService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateCompanies(array $filters = [], string $sort = 'created_at', string $direction = 'desc', int $perPage = 15): LengthAwarePaginator
    {
        return $this->queryCompanies($filters, $sort, $direction)->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function queryCompanies(array $filters = [], string $sort = 'created_at', string $direction = 'desc'): Builder
    {
        $query = CompanyProfile::query()
            ->with(['user', 'accountManager'])
            ->when($filters['search'] ?? null, fn (Builder $q, string $term) => $q->search($term))
            ->when($filters['status'] ?? null, fn (Builder $q, array $statuses) => $q->statusIn($statuses))
            ->when($filters['industry'] ?? null, fn (Builder $q, array $industries) => $q->whereIn('industry', $industries))
            ->when($filters['country'] ?? null, fn (Builder $q, array $countries) => $q->whereIn('country', $countries))
            ->when($filters['region'] ?? null, fn (Builder $q, array $regions) => $q->whereIn('region', $regions))
            ->when($filters['district'] ?? null, fn (Builder $q, array $districts) => $q->whereIn('district', $districts))
            ->when($filters['account_manager'] ?? null, fn (Builder $q, int $id) => $q->where('account_manager_id', $id))
            ->when($filters['date_from'] ?? null, fn (Builder $q, string $from) => $q->whereDate('created_at', '>=', $from))
            ->when($filters['date_until'] ?? null, fn (Builder $q, string $until) => $q->whereDate('created_at', '<=', $until))
            ->when($filters['has_open_quotes'] ?? false, fn (Builder $q) => $q->withOpenQuotes())
            ->when($filters['has_active_tickets'] ?? false, fn (Builder $q) => $q->withActiveTickets());

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

        $totalCurrent = CompanyProfile::query()->count();
        $totalPrevious = CompanyProfile::query()
            ->whereDate('created_at', '<', $currentMonthStart)
            ->count();

        $activeCurrent = CompanyProfile::query()->where('status', CompanyStatus::ACTIVE->value)->count();
        $activePrevious = CompanyProfile::query()
            ->where('status', CompanyStatus::ACTIVE->value)
            ->whereDate('created_at', '<', $currentMonthStart)
            ->count();

        $newThisMonth = CompanyProfile::query()->createdThisMonth()->count();
        $newPreviousMonth = CompanyProfile::query()
            ->whereMonth('created_at', $previousMonthStart->month)
            ->whereYear('created_at', $previousMonthStart->year)
            ->count();

        $withOpenQuotes = CompanyProfile::query()->withOpenQuotes()->count();
        $withActiveTickets = CompanyProfile::query()->withActiveTickets()->count();

        return [
            $this->buildCard('Total Companies', $totalCurrent, $totalPrevious, 'vs last month', 'heroicon-o-building-office', 'primary'),
            $this->buildCard('Active Companies', $activeCurrent, $activePrevious, 'vs last month', 'heroicon-o-check-circle', 'success'),
            $this->buildCard('New This Month', $newThisMonth, $newPreviousMonth, 'vs last month', 'heroicon-o-sparkles', 'warning'),
            $this->buildCard('With Open Quotes', $withOpenQuotes, 0, 'vs last month', 'heroicon-o-document-text', 'info', false),
            $this->buildCard('With Active Tickets', $withActiveTickets, 0, 'vs last month', 'heroicon-o-ticket', 'danger', false),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getCompanyDetail(CompanyProfile $profile): array
    {
        $profile->load(['user', 'accountManager', 'user.distributor', 'user.addresses']);

        return [
            'id' => $profile->id,
            'company_name' => $profile->company_name,
            'industry' => $profile->industry,
            'business_type' => $profile->business_type,
            'tax_identification' => $profile->tax_identification,
            'registration_number' => $profile->registration_number,
            'website' => $profile->website,
            'district' => $profile->district,
            'city' => $profile->city,
            'country' => $profile->country,
            'address' => $profile->address,
            'region' => $profile->region,
            'notes' => $profile->notes,
            'status' => $profile->status,
            'created_at' => $profile->created_at,
            'updated_at' => $profile->updated_at,
            'primary_contact' => [
                'name' => $profile->primary_contact_name,
                'email' => $profile->primary_contact_email,
                'phone' => $profile->primary_contact_phone,
                'user_id' => $profile->user_id,
            ],
            'account_manager' => $profile->accountManager ? [
                'id' => $profile->accountManager->id,
                'name' => $profile->accountManager->name,
                'email' => $profile->accountManager->email,
                'initials' => $profile->accountManager->initials(),
            ] : null,
            'addresses' => $profile->user?->addresses?->map(fn ($address) => [
                'id' => $address->id,
                'label' => $address->label ?? 'Address',
                'full_address' => $address->full_address ?? implode(', ', array_filter([
                    $address->address_line_1,
                    $address->address_line_2,
                    $address->city,
                    $address->district,
                    $address->country,
                ])),
            ])->toArray() ?? [],
            'recent_quotes' => $profile->quoteRequests()->latest()->limit(5)->get()->map(fn ($quote) => [
                'id' => $quote->id,
                'reference_number' => $quote->reference_number,
                'status' => $quote->status?->label() ?? $quote->status,
                'color' => $quote->status?->color() ?? 'gray',
                'estimated_value' => $quote->estimated_value,
                'created_at' => $quote->created_at,
            ])->toArray(),
            'active_tickets' => $profile->supportTickets()
                ->whereNotIn('status', ['resolved', 'closed'])
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (SupportTicket $ticket) => [
                    'id' => $ticket->id,
                    'reference_number' => $ticket->reference_number,
                    'subject' => $ticket->subject,
                    'status' => $ticket->status,
                    'priority' => $ticket->priority,
                    'created_at' => $ticket->created_at,
                ])->toArray(),
            'distributor' => $profile->user?->distributor ? [
                'id' => $profile->user->distributor->id,
                'name' => $profile->user->distributor->name ?? $profile->user->distributor->company_name,
                'status' => $profile->user->distributor->status ?? null,
            ] : null,
            'documents' => $profile->customerDocuments()->latest()->limit(10)->get()->map(fn ($doc) => [
                'id' => $doc->id,
                'title' => $doc->title,
                'type' => $doc->type,
                'file_name' => $doc->file_name,
                'url' => $doc->fileUrl(),
                'created_at' => $doc->created_at,
            ])->toArray(),
            'recent_activity' => AuditLog::query()
                ->where(function (Builder $q) use ($profile): void {
                    $q->where('user_id', $profile->user_id)
                        ->orWhere(function (Builder $sub) use ($profile): void {
                            $sub->where('subject_type', CompanyProfile::class)
                                ->where('subject_id', $profile->id);
                        });
                })
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
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createCompany(array $data): CompanyProfile
    {
        return DB::transaction(function () use ($data) {
            $email = $data['primary_contact_email'] ?? null;
            $user = null;

            if ($email) {
                $user = User::query()->where('email', $email)->first();
            }

            if ($user === null) {
                $user = User::create([
                    'name' => $data['primary_contact_name'] ?? explode('@', (string) $email)[0],
                    'email' => $email,
                    'phone' => $data['primary_contact_phone'] ?? null,
                    'password' => bcrypt(str()->random(16)),
                    'status' => 'active',
                ]);
            }

            $profile = new CompanyProfile([
                'user_id' => $user->id,
                'company_name' => $data['company_name'] ?? null,
                'industry' => $data['industry'] ?? null,
                'business_type' => $data['business_type'] ?? null,
                'tax_identification' => $data['tax_identification'] ?? null,
                'registration_number' => $data['registration_number'] ?? null,
                'website' => $data['website'] ?? null,
                'district' => $data['district'] ?? null,
                'city' => $data['city'] ?? null,
                'country' => $data['country'] ?? 'Uganda',
                'address' => $data['address'] ?? null,
                'primary_contact_name' => $data['primary_contact_name'] ?? $user->name,
                'primary_contact_email' => $email ?? $user->email,
                'primary_contact_phone' => $data['primary_contact_phone'] ?? $user->phone,
                'status' => $data['status'] ?? CompanyStatus::PROSPECT->value,
                'account_manager_id' => $data['account_manager_id'] ?? null,
                'region' => $data['region'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $profile->save();

            AuditService::log(
                auth()->user(),
                'company.created',
                $profile,
                ['company_name' => $profile->company_name, 'email' => $profile->primary_contact_email]
            );

            return $profile;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateCompany(CompanyProfile $profile, array $data): CompanyProfile
    {
        return DB::transaction(function () use ($profile, $data) {
            $profile->fill([
                'company_name' => $data['company_name'] ?? $profile->company_name,
                'industry' => $data['industry'] ?? $profile->industry,
                'business_type' => $data['business_type'] ?? $profile->business_type,
                'tax_identification' => $data['tax_identification'] ?? $profile->tax_identification,
                'registration_number' => $data['registration_number'] ?? $profile->registration_number,
                'website' => $data['website'] ?? $profile->website,
                'district' => $data['district'] ?? $profile->district,
                'city' => $data['city'] ?? $profile->city,
                'country' => $data['country'] ?? $profile->country,
                'address' => $data['address'] ?? $profile->address,
                'primary_contact_name' => $data['primary_contact_name'] ?? $profile->primary_contact_name,
                'primary_contact_email' => $data['primary_contact_email'] ?? $profile->primary_contact_email,
                'primary_contact_phone' => $data['primary_contact_phone'] ?? $profile->primary_contact_phone,
                'status' => $data['status'] ?? $profile->status->value,
                'account_manager_id' => array_key_exists('account_manager_id', $data) ? $data['account_manager_id'] : $profile->account_manager_id,
                'region' => $data['region'] ?? $profile->region,
                'notes' => $data['notes'] ?? $profile->notes,
            ]);

            $profile->save();

            AuditService::log(
                auth()->user(),
                'company.updated',
                $profile,
                ['company_name' => $profile->company_name, 'changes' => $profile->getChanges()]
            );

            return $profile;
        });
    }

    public function deleteCompany(CompanyProfile $profile): void
    {
        DB::transaction(function () use ($profile) {
            AuditService::log(
                auth()->user(),
                'company.deleted',
                $profile,
                ['company_name' => $profile->company_name]
            );

            $profile->delete();
        });
    }

    /**
     * @param  array<string, mixed>  $filters
     *
     * @return array<int, array<string, mixed>>
     */
    public function exportCompanies(array $filters = []): array
    {
        return $this->queryCompanies($filters, 'company_name', 'asc')
            ->get()
            ->map(fn (CompanyProfile $profile) => [
                'company_name' => $profile->company_name,
                'industry' => $profile->industry,
                'business_type' => $profile->business_type,
                'country' => $profile->country,
                'district' => $profile->district,
                'city' => $profile->city,
                'primary_contact_name' => $profile->primary_contact_name,
                'primary_contact_email' => $profile->primary_contact_email,
                'primary_contact_phone' => $profile->primary_contact_phone,
                'tax_identification' => $profile->tax_identification,
                'registration_number' => $profile->registration_number,
                'status' => $profile->status?->label() ?? $profile->status,
                'account_manager' => $profile->accountManager?->name,
                'region' => $profile->region,
                'created_at' => $profile->created_at?->format('Y-m-d H:i:s'),
            ])
            ->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function importCompanies(UploadedFile $file): array
    {
        $path = $file->getRealPath();
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return [
                'total' => 0,
                'created' => 0,
                'updated' => 0,
                'skipped' => 1,
                'errors' => ['Unable to read uploaded file.'],
            ];
        }

        // Skip BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $headers = fgetcsv($handle);
        if ($headers === false) {
            fclose($handle);

            return [
                'total' => 0,
                'created' => 0,
                'updated' => 0,
                'skipped' => 1,
                'errors' => ['Uploaded file is empty or not a valid CSV.'],
            ];
        }

        $headers = array_map(fn ($h) => strtolower(trim((string) $h)), $headers);

        $total = 0;
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) !== count($headers)) {
                continue;
            }

            $record = array_combine($headers, $row);
            $total++;

            $email = trim($record['primary_contact_email'] ?? '');

            if (empty($email)) {
                $skipped++;
                $errors[] = "Row {$total}: missing primary contact email.";
                continue;
            }

            $user = User::query()->where('email', $email)->first();

            if ($user === null) {
                $skipped++;
                $errors[] = "Row {$total}: no user found for {$email}.";
                continue;
            }

            try {
                $profile = CompanyProfile::query()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'company_name' => $this->value($record, 'company_name') ?? $user->name,
                        'industry' => $this->value($record, 'industry'),
                        'business_type' => $this->value($record, 'business_type'),
                        'tax_identification' => $this->value($record, 'tax_identification'),
                        'registration_number' => $this->value($record, 'registration_number'),
                        'website' => $this->value($record, 'website'),
                        'district' => $this->value($record, 'district'),
                        'city' => $this->value($record, 'city'),
                        'country' => $this->value($record, 'country') ?? 'Uganda',
                        'address' => $this->value($record, 'address'),
                        'primary_contact_name' => $this->value($record, 'primary_contact_name') ?? $user->name,
                        'primary_contact_phone' => $this->value($record, 'primary_contact_phone') ?? $user->phone,
                        'primary_contact_email' => $email,
                        'status' => $this->value($record, 'status') ?? CompanyStatus::PROSPECT->value,
                        'region' => $this->value($record, 'region'),
                        'notes' => $this->value($record, 'notes'),
                    ]
                );

                if ($profile->wasRecentlyCreated) {
                    $created++;
                } else {
                    $updated++;
                }
            } catch (\Throwable $e) {
                $skipped++;
                $errors[] = "Row {$total}: {$e->getMessage()}";
                Log::error('Company import failed', ['row' => $record, 'error' => $e->getMessage()]);
            }
        }

        fclose($handle);

        return compact('total', 'created', 'updated', 'skipped', 'errors');
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilterOptions(): array
    {
        $profiles = CompanyProfile::query();

        return [
            'industries' => (clone $profiles)->whereNotNull('industry')->distinct()->orderBy('industry')->pluck('industry')->toArray(),
            'countries' => (clone $profiles)->whereNotNull('country')->distinct()->orderBy('country')->pluck('country')->toArray(),
            'regions' => (clone $profiles)->whereNotNull('region')->distinct()->orderBy('region')->pluck('region')->toArray(),
            'districts' => (clone $profiles)->whereNotNull('district')->distinct()->orderBy('district')->pluck('district')->toArray(),
            'account_managers' => User::query()
                ->where('is_admin', true)
                ->orWhereHas('roles')
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (User $user) => ['id' => $user->id, 'name' => $user->name])
                ->toArray(),
        ];
    }

    private function applySorting(Builder $query, string $sort, string $direction): Builder
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return match ($sort) {
            'company_name' => $query->orderBy('company_name', $direction),
            'status' => $query->orderBy('status', $direction),
            'created_at' => $query->orderBy('created_at', $direction),
            'industry' => $query->orderBy('industry', $direction),
            'country' => $query->orderBy('country', $direction),
            'account_manager' => $query->orderBy(
                User::select('name')
                    ->whereColumn('users.id', 'company_profiles.account_manager_id')
                    ->limit(1),
                $direction
            ),
            default => $query->orderBy('created_at', 'desc'),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function buildCard(string $label, float $current, float $previous, string $comparisonLabel, string $icon, string $color, bool $trendAvailable = true): array
    {
        $trend = $trendAvailable ? $this->calculateTrend($current, $previous) : [
            'value' => '—',
            'label' => 'No comparison',
            'positive' => true,
        ];

        return [
            'label' => $label,
            'value' => number_format($current),
            'icon' => $icon,
            'color' => $color,
            'trend' => $trend['value'],
            'trend_label' => $trend['label'].' '.$comparisonLabel,
            'trend_positive' => $trend['positive'],
            'trend_available' => $trendAvailable && $previous > 0,
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
                'value' => '+100%',
                'label' => 'Up',
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

    /**
     * @param  array<string, string|null>  $record
     */
    private function value(array $record, string $key): ?string
    {
        $value = $record[$key] ?? null;

        return $value !== null && $value !== '' ? trim($value) : null;
    }
}

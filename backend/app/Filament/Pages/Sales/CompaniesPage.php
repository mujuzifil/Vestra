<?php

namespace App\Filament\Pages\Sales;

use App\Enums\CompanyStatus;
use App\Models\CompanyProfile;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\Admin\CompanyService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class CompaniesPage extends Page
{
    use WithPagination;

    protected static string $layout = 'filament.layouts.crm';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?string $navigationLabel = 'Companies';

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.sales.companies';

    protected static ?string $slug = 'sales/companies';

    #[Url(as: 'search')]
    public string $search = '';

    #[Url(as: 'status')]
    public array $statusFilter = [];

    #[Url(as: 'industry')]
    public array $industryFilter = [];

    #[Url(as: 'country')]
    public array $countryFilter = [];

    #[Url(as: 'region')]
    public array $regionFilter = [];

    #[Url(as: 'district')]
    public array $districtFilter = [];

    #[Url(as: 'account_manager')]
    public ?int $accountManagerFilter = null;

    #[Url(as: 'date_from')]
    public ?string $dateFrom = null;

    #[Url(as: 'date_until')]
    public ?string $dateUntil = null;

    public bool $hasOpenQuotes = false;

    public bool $hasActiveTickets = false;

    public bool $hasDistributor = false;

    public bool $showFilterPanel = false;

    /** @var array<int, int> */
    public array $selectedCompanyIds = [];

    #[Url(as: 'sort')]
    public string $sortField = 'created_at';

    #[Url(as: 'direction')]
    public string $sortDirection = 'desc';

    public int $perPage = 15;

    public bool $showDetailDrawer = false;

    public ?int $selectedCompanyId = null;

    public bool $showFormDrawer = false;

    public ?int $editingCompanyId = null;

    /**
     * @var array<string, mixed>
     */
    public array $form = [
        'company_name' => '',
        'industry' => '',
        'business_type' => '',
        'tax_identification' => '',
        'registration_number' => '',
        'website' => '',
        'district' => '',
        'city' => '',
        'country' => 'Uganda',
        'address' => '',
        'primary_contact_name' => '',
        'primary_contact_email' => '',
        'primary_contact_phone' => '',
        'status' => '',
        'account_manager_id' => null,
        'region' => '',
        'notes' => '',
    ];

    public string $ticketSubject = '';

    public string $ticketMessage = '';

    public function getTitle(): string
    {
        return 'Companies';
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }

    public function mount(): void
    {
        Gate::authorize('viewAny', CompanyProfile::class);
    }

    public function getCompanyServiceProperty(): CompanyService
    {
        return app(CompanyService::class);
    }

    public function getCompaniesProperty(): mixed
    {
        return $this->getCompanyServiceProperty()
            ->paginateCompanies($this->buildFilters(), $this->sortField, $this->sortDirection, $this->perPage);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getKpiCardsProperty(): array
    {
        return $this->getCompanyServiceProperty()->getKpiCards();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getSelectedCompanyProperty(): ?array
    {
        if (empty($this->selectedCompanyId)) {
            return null;
        }

        $profile = CompanyProfile::query()->find($this->selectedCompanyId);

        if ($profile === null) {
            return null;
        }

        Gate::authorize('view', $profile);

        return $this->getCompanyServiceProperty()->getCompanyDetail($profile);
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilterOptionsProperty(): array
    {
        return $this->getCompanyServiceProperty()->getFilterOptions();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAssigneesProperty(): array
    {
        return User::query()
            ->where('is_admin', true)
            ->orWhereHas('roles')
            ->orderBy('name')
            ->get()
            ->map(fn (User $user) => ['id' => $user->id, 'name' => $user->name, 'initials' => $user->initials()])
            ->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildFilters(): array
    {
        return [
            'search' => $this->search,
            'status' => $this->statusFilter,
            'industry' => $this->industryFilter,
            'country' => $this->countryFilter,
            'region' => $this->regionFilter,
            'district' => $this->districtFilter,
            'account_manager' => $this->accountManagerFilter,
            'date_from' => $this->dateFrom,
            'date_until' => $this->dateUntil,
            'has_open_quotes' => $this->hasOpenQuotes,
            'has_active_tickets' => $this->hasActiveTickets,
            'has_distributor' => $this->hasDistributor,
        ];
    }

    public function openDetailDrawer(int $id): void
    {
        $profile = CompanyProfile::query()->findOrFail($id);
        Gate::authorize('view', $profile);

        $this->selectedCompanyId = $id;
        $this->showDetailDrawer = true;
    }

    public function closeDetailDrawer(): void
    {
        $this->showDetailDrawer = false;
        $this->selectedCompanyId = null;
        $this->reset('ticketSubject', 'ticketMessage');
    }

    public function openCreateDrawer(): void
    {
        Gate::authorize('create', CompanyProfile::class);

        $this->resetForm();
        $this->editingCompanyId = null;
        $this->showFormDrawer = true;
    }

    public function openEditDrawer(int $id): void
    {
        $profile = CompanyProfile::query()->findOrFail($id);
        Gate::authorize('update', $profile);

        $this->editingCompanyId = $profile->id;
        $this->form = [
            'company_name' => $profile->company_name ?? '',
            'industry' => $profile->industry ?? '',
            'business_type' => $profile->business_type ?? '',
            'tax_identification' => $profile->tax_identification ?? '',
            'registration_number' => $profile->registration_number ?? '',
            'website' => $profile->website ?? '',
            'district' => $profile->district ?? '',
            'city' => $profile->city ?? '',
            'country' => $profile->country ?? 'Uganda',
            'address' => $profile->address ?? '',
            'primary_contact_name' => $profile->primary_contact_name ?? '',
            'primary_contact_email' => $profile->primary_contact_email ?? '',
            'primary_contact_phone' => $profile->primary_contact_phone ?? '',
            'status' => $profile->status?->value ?? CompanyStatus::PROSPECT->value,
            'account_manager_id' => $profile->account_manager_id,
            'region' => $profile->region ?? '',
            'notes' => $profile->notes ?? '',
        ];
        $this->showFormDrawer = true;
    }

    public function closeFormDrawer(): void
    {
        $this->showFormDrawer = false;
        $this->editingCompanyId = null;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->form = [
            'company_name' => '',
            'industry' => '',
            'business_type' => '',
            'tax_identification' => '',
            'registration_number' => '',
            'website' => '',
            'district' => '',
            'city' => '',
            'country' => 'Uganda',
            'address' => '',
            'primary_contact_name' => '',
            'primary_contact_email' => '',
            'primary_contact_phone' => '',
            'status' => CompanyStatus::PROSPECT->value,
            'account_manager_id' => null,
            'region' => '',
            'notes' => '',
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'form.company_name' => ['required', 'string', 'max:255'],
            'form.industry' => ['nullable', 'string', 'max:255'],
            'form.business_type' => ['nullable', 'string', 'max:255'],
            'form.tax_identification' => ['nullable', 'string', 'max:255'],
            'form.registration_number' => ['nullable', 'string', 'max:255'],
            'form.website' => ['nullable', 'url', 'max:255'],
            'form.district' => ['nullable', 'string', 'max:255'],
            'form.city' => ['nullable', 'string', 'max:255'],
            'form.country' => ['required', 'string', 'max:255'],
            'form.address' => ['nullable', 'string'],
            'form.primary_contact_name' => ['required', 'string', 'max:255'],
            'form.primary_contact_email' => ['required', 'email', 'max:255'],
            'form.primary_contact_phone' => ['nullable', 'string', 'max:255'],
            'form.status' => ['required', 'string', 'in:'.implode(',', array_map(fn ($s) => $s->value, CompanyStatus::cases()))],
            'form.account_manager_id' => ['nullable', 'integer', 'exists:users,id'],
            'form.region' => ['nullable', 'string', 'max:255'],
            'form.notes' => ['nullable', 'string'],
        ];
    }

    public function saveCompany(): void
    {
        $this->validate();

        $service = $this->getCompanyServiceProperty();
        $data = $this->form;

        if ($this->editingCompanyId) {
            $profile = CompanyProfile::query()->findOrFail($this->editingCompanyId);
            Gate::authorize('update', $profile);
            $service->updateCompany($profile, $data);
        } else {
            Gate::authorize('create', CompanyProfile::class);
            $service->createCompany($data);
        }

        $this->closeFormDrawer();
        $this->resetPage();

        Notification::make()
            ->title($this->editingCompanyId ? 'Company updated' : 'Company created')
            ->success()
            ->send();
    }

    public function deleteCompany(int $id): void
    {
        $profile = CompanyProfile::query()->findOrFail($id);
        Gate::authorize('delete', $profile);

        $this->getCompanyServiceProperty()->deleteCompany($profile);

        if ($this->selectedCompanyId === $id) {
            $this->closeDetailDrawer();
        }

        $this->resetPage();
    }

    public function createSupportTicket(): void
    {
        if (empty($this->selectedCompanyId)) {
            return;
        }

        $profile = CompanyProfile::query()->findOrFail($this->selectedCompanyId);
        Gate::authorize('view', $profile);

        $this->validate([
            'ticketSubject' => ['required', 'string', 'max:255'],
            'ticketMessage' => ['required', 'string'],
        ]);

        SupportTicket::create([
            'user_id' => $profile->user_id,
            'reference_number' => 'ST-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
            'subject' => $this->ticketSubject,
            'enquiry_type' => 'general',
            'message' => $this->ticketMessage,
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $this->reset('ticketSubject', 'ticketMessage');

        Notification::make()
            ->title('Support ticket created')
            ->success()
            ->send();
    }

    public function export(string $format)
    {
        Gate::authorize('export', CompanyProfile::class);

        $allowed = ['csv', 'excel', 'pdf'];
        $format = strtolower($format);

        if (! in_array($format, $allowed, true)) {
            abort(400, 'Unsupported export format.');
        }

        return redirect()->to($this->getExportUrl($format));
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->statusFilter = [];
        $this->industryFilter = [];
        $this->countryFilter = [];
        $this->regionFilter = [];
        $this->districtFilter = [];
        $this->accountManagerFilter = null;
        $this->dateFrom = null;
        $this->dateUntil = null;
        $this->hasOpenQuotes = false;
        $this->hasActiveTickets = false;
        $this->hasDistributor = false;
        $this->sortField = 'created_at';
        $this->sortDirection = 'desc';
        $this->selectedCompanyIds = [];
        $this->resetPage();
    }

    public function applyFilters(): void
    {
        $this->selectedCompanyIds = [];
        $this->resetPage();
    }

    public function clearStatusFilter(): void
    {
        $this->statusFilter = [];
        $this->resetPage();
    }

    public function setDatePreset(?string $preset): void
    {
        $preset = $preset ?: null;

        if ($preset === null || $preset === '') {
            $this->dateFrom = null;
            $this->dateUntil = null;
            $this->resetPage();

            return;
        }

        $until = now()->toDateString();

        $this->dateUntil = $until;
        $this->dateFrom = match ($preset) {
            'this_week' => now()->startOfWeek()->toDateString(),
            'this_month' => now()->startOfMonth()->toDateString(),
            'last_30' => now()->subDays(29)->toDateString(),
            'last_90' => now()->subDays(89)->toDateString(),
            default => null,
        };

        if ($this->dateFrom === null) {
            $this->dateUntil = null;
        }

        $this->resetPage();
    }

    public function getDatePresetProperty(): string
    {
        if (! filled($this->dateFrom) || ! filled($this->dateUntil)) {
            return '';
        }

        $until = now()->toDateString();
        if ($this->dateUntil !== $until) {
            return '';
        }

        return match ($this->dateFrom) {
            now()->startOfWeek()->toDateString() => 'this_week',
            now()->startOfMonth()->toDateString() => 'this_month',
            now()->subDays(29)->toDateString() => 'last_30',
            now()->subDays(89)->toDateString() => 'last_90',
            default => '',
        };
    }

    public function toggleFilterPanel(): void
    {
        $this->showFilterPanel = ! $this->showFilterPanel;
    }

    public function toggleSelectAll(): void
    {
        $pageIds = $this->getCompaniesProperty()->pluck('id')->map(fn ($id) => (int) $id)->all();

        if (count(array_intersect($this->selectedCompanyIds, $pageIds)) === count($pageIds) && count($pageIds) > 0) {
            $this->selectedCompanyIds = array_values(array_diff($this->selectedCompanyIds, $pageIds));
        } else {
            $this->selectedCompanyIds = array_values(array_unique(array_merge($this->selectedCompanyIds, $pageIds)));
        }
    }

    public function activeFilterCount(): int
    {
        $count = 0;

        if (filled($this->statusFilter)) {
            $count++;
        }
        if (filled($this->industryFilter)) {
            $count++;
        }
        if (filled($this->countryFilter)) {
            $count++;
        }
        if (filled($this->regionFilter)) {
            $count++;
        }
        if (filled($this->districtFilter)) {
            $count++;
        }
        if (filled($this->accountManagerFilter)) {
            $count++;
        }
        if (filled($this->dateFrom) || filled($this->dateUntil)) {
            $count++;
        }
        if ($this->hasOpenQuotes) {
            $count++;
        }
        if ($this->hasActiveTickets) {
            $count++;
        }
        if ($this->hasDistributor) {
            $count++;
        }

        return $count;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedIndustryFilter(): void
    {
        $this->resetPage();
    }

    public function updatedCountryFilter(): void
    {
        $this->resetPage();
    }

    public function updatedRegionFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDistrictFilter(): void
    {
        $this->resetPage();
    }

    public function updatedAccountManagerFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateUntil(): void
    {
        $this->resetPage();
    }

    public function updatedHasOpenQuotes(): void
    {
        $this->resetPage();
    }

    public function updatedHasActiveTickets(): void
    {
        $this->resetPage();
    }

    public function updatedHasDistributor(): void
    {
        $this->resetPage();
    }

    public function hasActiveFilters(): bool
    {
        return filled($this->search)
            || filled($this->statusFilter)
            || filled($this->industryFilter)
            || filled($this->countryFilter)
            || filled($this->regionFilter)
            || filled($this->districtFilter)
            || filled($this->accountManagerFilter)
            || filled($this->dateFrom)
            || filled($this->dateUntil)
            || $this->hasOpenQuotes
            || $this->hasActiveTickets
            || $this->hasDistributor;
    }

    public function getExportUrl(string $format): string
    {
        return route('filament.admin.sales.companies.export', [
            'format' => $format,
            'search' => $this->search ?: null,
            'status' => $this->statusFilter ?: null,
            'industry' => $this->industryFilter ?: null,
            'country' => $this->countryFilter ?: null,
            'region' => $this->regionFilter ?: null,
            'district' => $this->districtFilter ?: null,
            'account_manager' => $this->accountManagerFilter,
            'date_from' => $this->dateFrom,
            'date_until' => $this->dateUntil,
            'has_open_quotes' => $this->hasOpenQuotes ?: null,
            'has_active_tickets' => $this->hasActiveTickets ?: null,
            'has_distributor' => $this->hasDistributor ?: null,
        ]);
    }
}

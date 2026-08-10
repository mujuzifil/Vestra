<?php

namespace App\Filament\Pages\Distributors;

use App\Enums\DistributorAccountStatus;
use App\Enums\DistributorStockAvailability;
use App\Enums\DistributorTier;
use App\Models\Distributor;
use App\Services\Admin\PartnerAdminService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Url;

class PartnerEditPage extends Page
{
    protected static string $layout = 'filament.layouts.crm';

    protected static ?string $navigationGroup = 'Distributors';

    protected static ?string $navigationLabel = 'Edit Partner';

    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';

    protected static ?int $navigationSort = 3;

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.distributors.partner-edit';

    protected static ?string $slug = 'distributors/partners/edit';

    #[Url]
    public ?int $partner = null;

    /**
     * @var array<string, mixed>
     */
    public array $form = [];

    /**
     * @var array<int, array{day: string, hours: string}>
     */
    public array $hourRows = [];

    public function getTitle(): string
    {
        return 'Edit Partner';
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }

    public function mount(): void
    {
        abort_unless(filled($this->partner), 404);

        $distributor = Distributor::query()->findOrFail($this->partner);
        Gate::authorize('update', $distributor);

        $this->hydrateFromDistributor($distributor);
    }

    public function getPartnerServiceProperty(): PartnerAdminService
    {
        return app(PartnerAdminService::class);
    }

    public function getDistributorProperty(): Distributor
    {
        return Distributor::query()->findOrFail($this->partner);
    }

    public function getCancelUrlProperty(): string
    {
        return ActivePartnersPage::getUrl();
    }

    /**
     * @return array<int, array{value: string, label: string, description: string}>
     */
    public function getTierOptionsProperty(): array
    {
        return [
            [
                'value' => DistributorTier::SILVER->value,
                'label' => DistributorTier::SILVER->label(),
                'description' => 'Authorized partner with standard network visibility.',
            ],
            [
                'value' => DistributorTier::GOLD->value,
                'label' => DistributorTier::GOLD->label(),
                'description' => 'Preferred partner with elevated brand presence.',
            ],
            [
                'value' => DistributorTier::MASTER->value,
                'label' => DistributorTier::MASTER->label(),
                'description' => 'Flagship partner with highest public ranking.',
            ],
        ];
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function getStockOptionsProperty(): array
    {
        return collect(DistributorStockAvailability::cases())
            ->map(fn (DistributorStockAvailability $stock) => [
                'value' => $stock->value,
                'label' => $stock->label(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function getStatusOptionsProperty(): array
    {
        return collect(DistributorAccountStatus::cases())
            ->map(fn (DistributorAccountStatus $status) => [
                'value' => $status->value,
                'label' => $status->label(),
            ])
            ->values()
            ->all();
    }

    public function addHourRow(): void
    {
        $this->hourRows[] = ['day' => '', 'hours' => ''];
    }

    public function removeHourRow(int $index): void
    {
        unset($this->hourRows[$index]);
        $this->hourRows = array_values($this->hourRows);
    }

    public function save(): void
    {
        $distributor = Distributor::query()->findOrFail($this->partner);
        Gate::authorize('update', $distributor);

        $validated = $this->validate([
            'form.company_name' => ['required', 'string', 'min:2', 'max:255'],
            'form.trading_name' => ['nullable', 'string', 'max:255'],
            'form.primary_contact_name' => ['nullable', 'string', 'max:255'],
            'form.email' => ['nullable', 'email', 'max:255'],
            'form.phone' => ['nullable', 'string', 'max:255'],
            'form.whatsapp' => ['nullable', 'string', 'max:255'],
            'form.district' => ['nullable', 'string', 'max:255'],
            'form.city' => ['nullable', 'string', 'max:255'],
            'form.country' => ['nullable', 'string', 'max:255'],
            'form.address' => ['nullable', 'string', 'max:2000'],
            'form.google_maps_url' => ['nullable', 'url', 'max:2048'],
            'form.tier' => ['required', Rule::in(array_column(DistributorTier::cases(), 'value'))],
            'form.stock_availability' => ['required', Rule::in(array_column(DistributorStockAvailability::cases(), 'value'))],
            'form.status' => ['required', Rule::in(array_column(DistributorAccountStatus::cases(), 'value'))],
            'hourRows' => ['array'],
            'hourRows.*.day' => ['nullable', 'string', 'max:100'],
            'hourRows.*.hours' => ['nullable', 'string', 'max:100'],
        ]);

        $hours = collect($this->hourRows)
            ->map(fn (array $row) => [
                'day' => trim((string) ($row['day'] ?? '')),
                'hours' => trim((string) ($row['hours'] ?? '')),
            ])
            ->filter(fn (array $row) => $row['day'] !== '' && $row['hours'] !== '')
            ->mapWithKeys(fn (array $row) => [$row['day'] => $row['hours']])
            ->all();

        try {
            $this->getPartnerServiceProperty()->updateProfile($distributor, [
                'company_name' => $validated['form']['company_name'],
                'trading_name' => $validated['form']['trading_name'] ?: null,
                'primary_contact_name' => $validated['form']['primary_contact_name'] ?: null,
                'email' => $validated['form']['email'] ?: null,
                'phone' => $validated['form']['phone'] ?: null,
                'whatsapp' => $validated['form']['whatsapp'] ?: null,
                'district' => $validated['form']['district'] ?: null,
                'city' => $validated['form']['city'] ?: null,
                'country' => $validated['form']['country'] ?: null,
                'address' => $validated['form']['address'] ?: null,
                'google_maps_url' => $validated['form']['google_maps_url'] ?: null,
                'tier' => $validated['form']['tier'],
                'stock_availability' => $validated['form']['stock_availability'],
                'operating_hours_json' => $hours === [] ? null : $hours,
            ], auth()->user());

            $desiredStatus = DistributorAccountStatus::from($validated['form']['status']);
            $distributor->refresh();

            if ($desiredStatus === DistributorAccountStatus::SUSPENDED && $distributor->isActive()) {
                $this->getPartnerServiceProperty()->suspend($distributor, auth()->user(), 'Updated from partner edit workspace');
            } elseif ($desiredStatus === DistributorAccountStatus::ACTIVE && $distributor->isSuspended()) {
                $this->getPartnerServiceProperty()->activate($distributor, auth()->user());
            }
        } catch (ValidationException $exception) {
            throw $exception;
        }

        Notification::make()
            ->title('Partner updated')
            ->body('Locator profile, rank, and visibility settings were saved.')
            ->success()
            ->send();

        $this->redirect(ActivePartnersPage::getUrl(['search' => $validated['form']['company_name']]), navigate: true);
    }

    public function deletePartner(): void
    {
        $distributor = Distributor::query()->findOrFail($this->partner);
        Gate::authorize('delete', $distributor);

        $this->getPartnerServiceProperty()->purge($distributor, auth()->user());

        Notification::make()
            ->title('Partner deleted')
            ->body('The partner was permanently removed from admin, portal, and the public website.')
            ->success()
            ->send();

        $this->redirect(ActivePartnersPage::getUrl(), navigate: true);
    }

    protected function hydrateFromDistributor(Distributor $distributor): void
    {
        $hours = is_array($distributor->operating_hours_json) ? $distributor->operating_hours_json : [];

        $this->form = [
            'company_name' => $distributor->company_name,
            'trading_name' => $distributor->trading_name,
            'primary_contact_name' => $distributor->primary_contact_name,
            'email' => $distributor->email,
            'phone' => $distributor->phone,
            'whatsapp' => $distributor->whatsapp,
            'district' => $distributor->district,
            'city' => $distributor->city,
            'country' => $distributor->country ?: 'Uganda',
            'address' => $distributor->address,
            'google_maps_url' => $distributor->google_maps_url,
            'tier' => $distributor->tier?->value ?? DistributorTier::SILVER->value,
            'stock_availability' => $distributor->stock_availability?->value ?? DistributorStockAvailability::IN_STOCK->value,
            'status' => $distributor->status?->value ?? DistributorAccountStatus::ACTIVE->value,
        ];

        $this->hourRows = collect($hours)
            ->map(fn ($hoursValue, $day) => [
                'day' => (string) $day,
                'hours' => is_scalar($hoursValue) ? (string) $hoursValue : '',
            ])
            ->values()
            ->all();

        if ($this->hourRows === []) {
            $this->hourRows = [
                ['day' => 'Mon-Fri', 'hours' => '08:00-17:00'],
                ['day' => 'Sat', 'hours' => '09:00-13:00'],
            ];
        }
    }
}

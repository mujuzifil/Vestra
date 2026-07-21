<?php

namespace App\Filament\Concerns;

use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

/**
 * Shared reporting filter logic for Filament pages.
 *
 * Provides a standard date-range filter form, plus helper fields that individual
 * report pages can enable by overriding `getFilterFormSchema()`.
 */
trait HasReportFilters
{
    #[Url(as: 'filters', history: true)]
    public ?array $filterFormData = [];

    /**
     * Configure the page's filter form. Call this from the page's form() method.
     */
    public function configureFilterForm(Form $form): Form
    {
        return $form
            ->schema($this->getFilterFormSchema())
            ->statePath('filterFormData')
            ->columns($this->getFilterFormColumns());
    }

    protected function getFilterFormSchema(): array
    {
        return [
            DatePicker::make('start_date')
                ->label('From')
                ->default(now()->subDays(29)->startOfDay())
                ->native(false),

            DatePicker::make('end_date')
                ->label('To')
                ->default(now()->endOfDay())
                ->native(false),
        ];
    }

    protected function getFilterFormColumns(): int | array
    {
        return [
            'default' => 1,
            'sm' => 2,
            'lg' => 4,
        ];
    }

    protected function getFiltersFormActions(): array
    {
        return [
            \Filament\Forms\Components\Actions\Action::make('reset')
                ->label('Reset')
                ->color('gray')
                ->action(fn () => $this->resetFilterForm()),
        ];
    }

    protected function resetFilterForm(): void
    {
        $this->filterFormData = [];
        $this->mountFiltersForm();
    }

    public function mountFiltersForm(): void
    {
        $defaults = $this->getDefaultFilterFormData();
        $current = $this->filterFormData ?? [];

        $this->filterFormData = array_merge($defaults, array_filter($current, fn ($value) => $value !== null && $value !== ''));
        $this->form?->fill($this->filterFormData);
    }

    protected function getDefaultFilterFormData(): array
    {
        return [
            'start_date' => now()->subDays(29)->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ];
    }

    public function getStartDate(): Carbon
    {
        $value = $this->filterFormData['start_date'] ?? null;

        return $value ? Carbon::parse($value)->startOfDay() : now()->subDays(29)->startOfDay();
    }

    public function getEndDate(): Carbon
    {
        $value = $this->filterFormData['end_date'] ?? null;

        return $value ? Carbon::parse($value)->endOfDay() : now()->endOfDay();
    }

    public function getFilterValue(string $key, mixed $default = null): mixed
    {
        $value = data_get($this->filterFormData, $key, $default);

        return ($value === null || $value === '') ? $default : $value;
    }

    /**
     * Apply a date-range scope to any query using the active filters.
     */
    protected function applyDateRange(Builder $query, string $column = 'created_at'): Builder
    {
        return $query->whereBetween($column, [$this->getStartDate(), $this->getEndDate()]);
    }

    /**
     * Hash the active filters for deterministic cache keys.
     */
    protected function getFilterCacheHash(): string
    {
        return md5(json_encode($this->filterFormData));
    }

    /**
     * Helper to add a status select to the filter form.
     */
    protected function statusSelect(array $options, string $label = 'Status'): Select
    {
        return Select::make('status')
            ->label($label)
            ->options($options)
            ->placeholder('All statuses')
            ->native(false);
    }

    /**
     * Helper to add a category select to the filter form.
     */
    protected function categorySelect(): Select
    {
        return Select::make('category_id')
            ->label('Category')
            ->relationship('category', 'name')
            ->placeholder('All categories')
            ->native(false)
            ->preload();
    }

    /**
     * Build an export filename using the report slug and active date range.
     */
    protected function getExportFilename(string $report): string
    {
        return sprintf(
            '%s_report_%s_to_%s',
            $report,
            $this->getStartDate()->format('Y-m-d'),
            $this->getEndDate()->format('Y-m-d')
        );
    }
}

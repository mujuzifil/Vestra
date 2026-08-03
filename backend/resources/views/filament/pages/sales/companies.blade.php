@php
$companies = $this->companies;
$kpiCards = $this->kpiCards;
$filterOptions = $this->filterOptions;
$assignees = $this->assignees;
$selectedCompany = $this->selectedCompany;
$activeFilterCount = $this->activeFilterCount();
@endphp

<x-filament-panels::page>
    <div class="vestra-workspace vestra-companies">
        <x-companies.page-header
            title="Companies"
            description="Manage and grow your company relationships."
            :csv-url="$this->getExportUrl('csv')"
            :excel-url="$this->getExportUrl('excel')"
            :pdf-url="$this->getExportUrl('pdf')"
        />

        <section class="vestra-workspace__section" aria-label="Company metrics">
            <x-companies.kpi-cards :cards="$kpiCards" />
        </section>

        <section class="vestra-workspace__section vestra-companies__content" aria-label="Company list">
            <div class="vestra-companies__layout @if ($showFilterPanel) vestra-companies__layout--with-panel @endif">
                <div class="vestra-companies__main">
                    <div class="vestra-card vestra-companies__table-card">
                        <x-companies.filter-bar
                            :status-options="\App\Enums\CompanyStatus::cases()"
                            :industry-options="$filterOptions['industries'] ?? []"
                            :country-options="$filterOptions['countries'] ?? []"
                            :account-manager-options="$filterOptions['account_managers'] ?? []"
                            :active-filter-count="$activeFilterCount"
                            :show-filter-panel="$showFilterPanel"
                            :status-filter="$statusFilter"
                            :industry-filter="$industryFilter"
                            :country-filter="$countryFilter"
                            :account-manager-filter="$accountManagerFilter"
                        />

                        @if (count($selectedCompanyIds) > 0)
                            <div class="vestra-companies__bulk-bar" role="region" aria-label="Bulk selection">
                                <span class="vestra-companies__bulk-count">{{ count($selectedCompanyIds) }} selected</span>
                            </div>
                        @endif

                        @if ($companies->total() > 0)
                            <x-companies.company-table
                                :companies="$companies"
                                :sort-field="$sortField"
                                :sort-direction="$sortDirection"
                                :selected-ids="$selectedCompanyIds"
                            />

                            <x-companies.pagination :paginator="$companies" />
                        @else
                            <x-companies.empty-state
                                :has-filters="$this->hasActiveFilters()"
                            />
                        @endif
                    </div>
                </div>

                @if ($showFilterPanel)
                    <x-companies.filter-panel
                        :show="true"
                        :status-options="\App\Enums\CompanyStatus::cases()"
                        :industry-options="$filterOptions['industries'] ?? []"
                        :country-options="$filterOptions['countries'] ?? []"
                        :region-options="$filterOptions['regions'] ?? []"
                        :district-options="$filterOptions['districts'] ?? []"
                        :account-manager-options="$filterOptions['account_managers'] ?? []"
                        :status-filter="$statusFilter"
                        :industry-filter="$industryFilter"
                        :country-filter="$countryFilter"
                        :region-filter="$regionFilter"
                        :district-filter="$districtFilter"
                        :date-preset="$this->datePreset"
                        :active-filter-count="$activeFilterCount"
                    />
                @endif
            </div>
        </section>

        <x-companies.detail-drawer
            :show="$showDetailDrawer"
            :company="$selectedCompany"
        />

        <x-companies.company-form
            :show="$showFormDrawer"
            :editing-company-id="$editingCompanyId"
            :assignees="$assignees"
            :status-options="\App\Enums\CompanyStatus::cases()"
        />

        <x-companies.import-drawer
            :show="$showImportDrawer"
        />
    </div>
</x-filament-panels::page>

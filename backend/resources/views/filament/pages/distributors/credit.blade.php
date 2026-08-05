@php
$accounts = $this->accounts;
$kpiCards = $this->kpiCards;
$filterOptions = $this->filterOptions;
$selectedAccount = $this->selectedAccount;
@endphp
    <div class="vestra-workspace vestra-credit">
        <x-credit.page-header
            title="Credit"
            description="Monitor distributor credit limits, outstanding balances and utilization."
            :csv-url="$this->getExportUrl('csv')"
            :excel-url="$this->getExportUrl('excel')"
            :pdf-url="$this->getExportUrl('pdf')"
        />

        <section class="vestra-workspace__section" aria-label="Credit metrics">
            <x-credit.kpi-cards :cards="$kpiCards" />
        </section>

        <section class="vestra-workspace__section vestra-credit__content" aria-label="Credit account list">
            <div class="vestra-card vestra-credit__table-card">
                <x-credit.filter-bar
                    :status-options="\App\Services\Admin\CreditAdminService::STATUSES"
                    :country-options="$filterOptions['countries'] ?? []"
                />

                @if ($accounts->total() > 0)
                    <x-credit.credit-table
                        :accounts="$accounts"
                        :sort-field="$sortField"
                        :sort-direction="$sortDirection"
                    />

                    <x-credit.pagination :paginator="$accounts" />
                @else
                    <x-credit.empty-state
                        :has-filters="$this->hasActiveFilters()"
                    />
                @endif
            </div>
        </section>

        <x-credit.detail-drawer
            :show="$showDetailDrawer"
            :account="$selectedAccount"
        />

        <x-credit.adjust-limit-form
            :show="$showAdjustDrawer"
        />
    </div>

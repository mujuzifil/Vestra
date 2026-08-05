@php
$partners = $this->partners;
$kpiCards = $this->kpiCards;
$filterOptions = $this->filterOptions;
$selectedPartner = $this->selectedPartner;
$statusOptions = $this->statusOptions;
@endphp
    <div class="vestra-workspace vestra-partners">
        <x-partners.page-header
            title="Active Partners"
            description="Manage and grow your network of active distributors."
            :csv-url="$this->getExportUrl('csv')"
            :excel-url="$this->getExportUrl('excel')"
            :pdf-url="$this->getExportUrl('pdf')"
        />

        <section class="vestra-workspace__section" aria-label="Partner metrics">
            <x-partners.kpi-cards :cards="$kpiCards" />
        </section>

        <section class="vestra-workspace__section vestra-partners__content" aria-label="Partner list">
            <div class="vestra-card vestra-partners__table-card">
                <x-partners.filter-bar
                    :status-options="$statusOptions"
                    :country-options="$filterOptions['countries'] ?? []"
                    :region-options="$filterOptions['regions'] ?? []"
                    :sales-rep-options="$filterOptions['sales_reps'] ?? []"
                />

                @if ($partners->total() > 0)
                    <x-partners.partner-table
                        :partners="$partners"
                        :sort-field="$sortField"
                        :sort-direction="$sortDirection"
                    />

                    <x-partners.pagination :paginator="$partners" />
                @else
                    <x-partners.empty-state
                        :has-filters="$this->hasActiveFilters()"
                    />
                @endif
            </div>
        </section>

        <x-partners.detail-drawer
            :show="$showDetailDrawer"
            :partner="$selectedPartner"
        />
    </div>

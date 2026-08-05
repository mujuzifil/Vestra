@php
$enquiries       = $this->enquiries;
$kpiCards        = $this->kpiCards;
$filterOptions   = $this->filterOptions;
$selectedEnquiry = $this->selectedEnquiry;
@endphp
    <div class="vestra-workspace vestra-enquiries">
        <x-enquiries.page-header
            title="Enquiries"
            description="Manage incoming customer enquiries and respond promptly."
            :csv-url="$this->getExportUrl('csv')"
            :excel-url="$this->getExportUrl('excel')"
            :pdf-url="$this->getExportUrl('pdf')"
        />

        <section class="vestra-workspace__section" aria-label="Enquiry metrics">
            <x-enquiries.kpi-cards :cards="$kpiCards" />
        </section>

        <section class="vestra-workspace__section vestra-enquiries__content" aria-label="Enquiry list">
            <div class="vestra-card vestra-enquiries__table-card">
                <x-enquiries.filter-bar
                    :status-options="$filterOptions['statuses'] ?? []"
                    :enquiry-type-options="$filterOptions['enquiry_types'] ?? []"
                    :priority-options="$filterOptions['priorities'] ?? []"
                    :source-options="$filterOptions['sources'] ?? []"
                    :assignee-options="$filterOptions['assignees'] ?? []"
                />

                @if ($enquiries->total() > 0)
                    <x-enquiries.enquiry-table
                        :enquiries="$enquiries"
                        :sort-field="$sortField"
                        :sort-direction="$sortDirection"
                    />

                    <x-enquiries.pagination :paginator="$enquiries" />
                @else
                    <x-enquiries.empty-state
                        :has-filters="$this->hasActiveFilters()"
                    />
                @endif
            </div>
        </section>

        <x-enquiries.detail-drawer
            :show="$showDetailDrawer"
            :enquiry="$selectedEnquiry"
        />
    </div>

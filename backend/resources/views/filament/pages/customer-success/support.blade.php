@php
$tickets = $this->tickets;
$kpiCards = $this->kpiCards;
$filterOptions = $this->filterOptions;
$selectedTicket = $this->selectedTicket;
@endphp
    <div class="vestra-workspace vestra-support">
        <x-support.page-header
            title="Support"
            description="Manage customer support tickets and enquiries."
            :csv-url="$this->getExportUrl('csv')"
            :excel-url="$this->getExportUrl('excel')"
            :pdf-url="$this->getExportUrl('pdf')"
        />

        <section class="vestra-workspace__section" aria-label="Support metrics">
            <x-support.kpi-cards :cards="$kpiCards" />
        </section>

        <section class="vestra-workspace__section vestra-support__content" aria-label="Ticket list">
            <div class="vestra-card vestra-support__table-card">
                <x-support.filter-bar
                    :status-options="$filterOptions['statuses'] ?? []"
                    :priority-options="$filterOptions['priorities'] ?? []"
                    :enquiry-type-options="$filterOptions['enquiry_types'] ?? []"
                    :assignee-options="$filterOptions['assignees'] ?? []"
                />

                @if ($tickets->total() > 0)
                    <x-support.ticket-table
                        :tickets="$tickets"
                        :sort-field="$sortField"
                        :sort-direction="$sortDirection"
                        :selected-ids="$selectedIds"
                    />

                    <x-support.pagination :paginator="$tickets" />
                @else
                    <x-support.empty-state :has-filters="$this->hasActiveFilters()" />
                @endif
            </div>
        </section>

        <x-support.detail-drawer
            :show="$showDetailDrawer"
            :ticket="$selectedTicket"
        />
    </div>

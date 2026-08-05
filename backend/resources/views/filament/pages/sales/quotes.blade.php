@php
$quotes = $this->quotes;
$kpiCards = $this->kpiCards;
$filterOptions = $this->filterOptions;
$assignees = $this->assignees;
$selectedQuote = $this->selectedQuote;
@endphp

<div class="vestra-workspace vestra-quotes">
    <x-quotes.page-header
        title="Quotes"
        description="Manage and track all sales quotes and proposals."
        :csv-url="$this->getExportUrl('csv')"
        :excel-url="$this->getExportUrl('excel')"
        :pdf-url="$this->getExportUrl('pdf')"
    />

    <section class="vestra-workspace__section" aria-label="Quote metrics">
        <x-quotes.kpi-cards :cards="$kpiCards" />
    </section>

    <section class="vestra-workspace__section vestra-quotes__content" aria-label="Quote list">
        <div class="vestra-card vestra-quotes__table-card">
            <x-quotes.filter-bar
                :status-options="\App\Enums\QuoteRequestStatus::cases()"
                :priority-options="$filterOptions['priorities'] ?? []"
                :district-options="$filterOptions['districts'] ?? []"
                :city-options="$filterOptions['cities'] ?? []"
                :sales-rep-options="$filterOptions['sales_reps'] ?? []"
            />

            @if (count($selectedQuoteIds) > 0)
                <div class="vestra-quotes__bulk-bar" role="region" aria-label="Bulk actions">
                    <span class="vestra-quotes__bulk-count">{{ count($selectedQuoteIds) }} selected</span>
                    <div class="vestra-quotes__bulk-actions">
                        <button type="button" wire:click="bulkUpdateStatus('contacted')" class="vestra-button vestra-button--secondary">Mark Contacted</button>
                        <button type="button" wire:click="bulkUpdateStatus('quoted')" class="vestra-button vestra-button--secondary">Mark Quoted</button>
                        <button type="button" wire:click="bulkUpdateStatus('closed')" class="vestra-button vestra-button--secondary">Mark Closed</button>
                    </div>
                </div>
            @endif

            @if ($quotes->total() > 0)
                <x-quotes.quote-table
                    :quotes="$quotes"
                    :sort-field="$sortField"
                    :sort-direction="$sortDirection"
                    :selected-ids="$selectedQuoteIds"
                />

                <x-quotes.pagination :paginator="$quotes" />
            @else
                <x-quotes.empty-state
                    :has-filters="$this->hasActiveFilters()"
                />
            @endif
        </div>
    </section>

    <x-quotes.detail-drawer
        :show="$showDetailDrawer"
        :quote="$selectedQuote"
    />

    <x-quotes.quote-form
        :show="$showFormDrawer"
        :editing-quote-id="$editingQuoteId"
        :assignees="$assignees"
        :status-options="\App\Enums\QuoteRequestStatus::cases()"
        :priority-options="\App\Enums\QuoteRequestPriority::cases()"
    />
</div>

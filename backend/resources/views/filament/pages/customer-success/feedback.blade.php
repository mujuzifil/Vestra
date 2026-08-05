@php
$feedback = $this->feedback;
$kpiCards = $this->kpiCards;
$selectedFeedback = $this->selectedFeedback;
@endphp
    <div class="vestra-workspace vestra-feedback">
        <x-feedback.page-header
            title="Feedback"
            description="Review and manage customer feedback submissions."
            :csv-url="$this->getExportUrl('csv')"
            :excel-url="$this->getExportUrl('excel')"
            :pdf-url="$this->getExportUrl('pdf')"
        />

        <section class="vestra-workspace__section" aria-label="Feedback metrics">
            <x-feedback.kpi-cards :cards="$kpiCards" />
        </section>

        <section class="vestra-workspace__section vestra-feedback__content" aria-label="Feedback list">
            <div class="vestra-card vestra-feedback__table-card">
                <x-feedback.filter-bar
                    :status-options="\App\Enums\FeedbackStatus::cases()"
                    :category-options="\App\Enums\FeedbackCategory::cases()"
                    :priority-options="\App\Enums\Priority::cases()"
                />

                @if ($feedback->total() > 0)
                    <x-feedback.feedback-table
                        :feedback="$feedback"
                        :sort-field="$sortField"
                        :sort-direction="$sortDirection"
                    />

                    <x-feedback.pagination :paginator="$feedback" />
                @else
                    <x-feedback.empty-state :has-filters="$this->hasActiveFilters()" />
                @endif
            </div>
        </section>

        <x-feedback.detail-drawer
            :show="$showDetailDrawer"
            :feedback="$selectedFeedback"
        />
    </div>

@php
$posts = $this->posts;
$kpiCards = $this->kpiCards;
$filterOptions = $this->filterOptions;
$selectedPost = $this->selectedPost;
@endphp
    <div class="vestra-workspace vestra-blog">
        <x-blog.page-header
            title="Blog"
            description="Manage articles, authors, and publishing schedules."
            :csv-url="$this->getExportUrl('csv')"
            :excel-url="$this->getExportUrl('excel')"
            :pdf-url="$this->getExportUrl('pdf')"
            :can-create="$this->canCreate"
            :create-url="$this->createUrl"
        />

        <section class="vestra-workspace__section" aria-label="Blog metrics">
            <x-blog.kpi-cards :cards="$kpiCards" />
        </section>

        <section class="vestra-workspace__section vestra-blog__content" aria-label="Blog post list">
            <div class="vestra-card vestra-blog__table-card">
                <x-blog.filter-bar
                    :status-options="$filterOptions['statuses'] ?? []"
                    :author-options="$filterOptions['authors'] ?? []"
                    :category-options="$filterOptions['categories'] ?? []"
                />

                @if ($posts->total() > 0)
                    <x-blog.post-table
                        :posts="$posts"
                        :sort-field="$sortField"
                        :sort-direction="$sortDirection"
                    />

                    <x-blog.pagination :paginator="$posts" />
                @else
                    <x-blog.empty-state
                        :has-filters="$this->hasActiveFilters()"
                        :can-create="$this->canCreate"
                        :create-url="$this->createUrl"
                    />
                @endif
            </div>
        </section>

        <x-blog.detail-drawer
            :show="$showDetailDrawer"
            :post="$selectedPost"
        />
    </div>

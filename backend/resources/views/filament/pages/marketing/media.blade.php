@php
$mediaItems = $this->mediaItems;
$kpiCards = $this->kpiCards;
$filterOptions = $this->filterOptions;
$selectedMedia = $this->selectedMedia;
@endphp
    <div class="vestra-workspace vestra-media">
        <x-media.page-header
            title="Media"
            description="Browse every image and file already uploaded across Blog, Products, and Settings."
            :view-mode="$this->viewMode"
            :csv-url="$this->getExportUrl('csv')"
            :excel-url="$this->getExportUrl('excel')"
            :pdf-url="$this->getExportUrl('pdf')"
            :can-upload-product="$this->canUploadProduct"
            :blog-upload-url="$this->blogUploadUrl"
            :product-upload-url="$this->productUploadUrl"
        />

        <section class="vestra-workspace__section" aria-label="Media metrics">
            <x-media.kpi-cards :cards="$kpiCards" />
        </section>

        <section class="vestra-workspace__section vestra-media__content" aria-label="Media library">
            <div class="vestra-card vestra-media__card">
                <x-media.filter-bar
                    :type-options="$filterOptions['types'] ?? []"
                    :source-options="$filterOptions['sources'] ?? []"
                    :date-from="$dateFrom"
                    :date-until="$dateUntil"
                />

                @if ($mediaItems->total() > 0)
                    @if ($this->viewMode === 'grid')
                        <x-media.grid-view :items="$mediaItems" :selected-ids="$selectedIds" />
                    @else
                        <x-media.list-view
                            :items="$mediaItems"
                            :sort-field="$sortField"
                            :sort-direction="$sortDirection"
                            :selected-ids="$selectedIds"
                        />
                    @endif

                    <x-media.pagination :paginator="$mediaItems" />
                @else
                    <x-media.empty-state
                        :has-filters="$this->hasActiveFilters()"
                        :blog-upload-url="$this->blogUploadUrl"
                        :product-upload-url="$this->productUploadUrl"
                        :can-upload-product="$this->canUploadProduct"
                    />
                @endif
            </div>
        </section>

        <x-media.detail-drawer
            :show="$showDetailDrawer"
            :item="$selectedMedia"
        />
    </div>

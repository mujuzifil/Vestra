@php
$mediaItems = $this->mediaItems;
$kpiCards = $this->kpiCards;
$filterOptions = $this->filterOptions;
$selectedMedia = $this->selectedMedia;
@endphp
<div class="vestra-workspace vestra-media">
    <x-media.page-header
        title="Media Library"
        description="Centralized repository of images, documents and reusable assets used throughout the Vestra platform."
        :view-mode="$this->viewMode"
        :csv-url="$this->getExportUrl('csv')"
        :excel-url="$this->getExportUrl('excel')"
        :pdf-url="$this->getExportUrl('pdf')"
        :can-upload="$this->canUpload"
    />

    <section class="vestra-workspace__section" aria-label="Media metrics">
        <x-media.kpi-cards :cards="$kpiCards" />
    </section>

    <section class="vestra-workspace__section vestra-media__content" aria-label="Media library">
        <div class="vestra-card vestra-media__card">
            <x-media.filter-bar
                :type-options="$filterOptions['types'] ?? []"
                :usage-options="$filterOptions['usage'] ?? []"
                :format-options="$filterOptions['formats'] ?? []"
                :uploader-options="$filterOptions['uploaders'] ?? []"
                :date-from="$dateFrom"
                :date-until="$dateUntil"
            />

            @if ($mediaItems->total() > 0)
                @if ($this->viewMode === 'grid')
                    <x-media.grid-view :items="$mediaItems" />
                @else
                    <x-media.list-view
                        :items="$mediaItems"
                        :sort-field="$sortField"
                        :sort-direction="$sortDirection"
                    />
                @endif

                <x-media.pagination :paginator="$mediaItems" />
            @else
                <x-media.empty-state
                    :has-filters="$this->hasActiveFilters()"
                    :can-upload="$this->canUpload"
                />
            @endif
        </div>
    </section>

    <x-media.detail-drawer
        :show="$showDetailDrawer"
        :item="$selectedMedia"
        :can-manage="$this->canManageSelected"
    />

    <x-media.upload-modal :show="$showUploadModal" />
</div>

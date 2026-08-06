<?php

namespace App\Filament\Pages\Marketing;

use App\Models\MediaAsset;
use App\Services\Admin\MediaAdminService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Url;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class MediaPage extends Page
{
    use WithFileUploads;
    use WithPagination;

    protected static string $layout = 'filament.layouts.crm';

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?string $navigationLabel = 'Media';

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.marketing.media';

    protected static ?string $slug = 'marketing/media';

    #[Url(as: 'search')]
    public string $search = '';

    #[Url(as: 'type')]
    public array $typeFilter = [];

    #[Url(as: 'usage')]
    public ?string $usageFilter = null;

    #[Url(as: 'format')]
    public ?string $formatFilter = null;

    #[Url(as: 'uploader')]
    public ?int $uploaderFilter = null;

    #[Url(as: 'date_from')]
    public ?string $dateFrom = null;

    #[Url(as: 'date_until')]
    public ?string $dateUntil = null;

    #[Url(as: 'view')]
    public string $viewMode = 'grid';

    #[Url(as: 'sort')]
    public string $sortField = 'created_at';

    #[Url(as: 'direction')]
    public string $sortDirection = 'desc';

    public int $perPage = 24;

    public bool $showDetailDrawer = false;

    public ?int $selectedMediaId = null;

    public bool $showUploadModal = false;

    public $uploadFile = null;

    public string $metaFileName = '';

    public string $metaAltText = '';

    public string $metaCaption = '';

    public string $metaDescription = '';

    public string $metaCopyright = '';

    public string $metaNotes = '';

    public string $metaTags = '';

    public $replaceFile = null;

    public function getTitle(): string
    {
        return 'Media Library';
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }

    public function mount(): void
    {
        abort_unless(Gate::allows('viewAny', MediaAsset::class), 403);

        if (! in_array($this->viewMode, ['grid', 'list'], true)) {
            $this->viewMode = 'grid';
        }
    }

    public static function canAccess(): bool
    {
        return Gate::allows('viewAny', MediaAsset::class);
    }

    public function getMediaServiceProperty(): MediaAdminService
    {
        return app(MediaAdminService::class);
    }

    public function getMediaItemsProperty(): mixed
    {
        $paginator = $this->getMediaServiceProperty()
            ->paginate($this->buildFilters(), $this->sortField, $this->sortDirection, $this->perPage);

        $service = $this->getMediaServiceProperty();
        $paginator->getCollection()->transform(
            fn (MediaAsset $asset) => $service->serializeAsset($asset)
        );

        return $paginator;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getKpiCardsProperty(): array
    {
        return $this->getMediaServiceProperty()->getKpiCards();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getSelectedMediaProperty(): ?array
    {
        if (! $this->selectedMediaId) {
            return null;
        }

        return $this->getMediaServiceProperty()->getDetail($this->selectedMediaId);
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilterOptionsProperty(): array
    {
        return $this->getMediaServiceProperty()->getFilterOptions();
    }

    public function getCanUploadProperty(): bool
    {
        return Gate::allows('create', MediaAsset::class);
    }

    public function getCanManageSelectedProperty(): bool
    {
        if (! $this->selectedMediaId) {
            return false;
        }

        $asset = MediaAsset::query()->find($this->selectedMediaId);

        return $asset !== null && Gate::allows('update', $asset);
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildFilters(): array
    {
        return [
            'search' => $this->search,
            'type' => $this->typeFilter,
            'usage' => $this->usageFilter,
            'format' => $this->formatFilter,
            'uploader_id' => $this->uploaderFilter,
            'date_from' => $this->dateFrom,
            'date_until' => $this->dateUntil,
        ];
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = in_array($mode, ['grid', 'list'], true) ? $mode : 'grid';
    }

    public function openDetailDrawer(int $id): void
    {
        $this->selectedMediaId = $id;
        $this->showDetailDrawer = true;
        $this->hydrateMetaFromSelected();
    }

    public function closeDetailDrawer(): void
    {
        $this->showDetailDrawer = false;
        $this->selectedMediaId = null;
        $this->replaceFile = null;
    }

    public function openUploadModal(): void
    {
        Gate::authorize('create', MediaAsset::class);
        $this->resetUploadState();
        $this->showUploadModal = true;
    }

    public function closeUploadModal(): void
    {
        $this->showUploadModal = false;
        $this->resetUploadState();
    }

    public function uploadAsset(): void
    {
        Gate::authorize('create', MediaAsset::class);

        $this->validate([
            'uploadFile' => ['required', 'file', 'max:12288'],
        ]);

        try {
            $file = $this->uploadFile instanceof TemporaryUploadedFile
                ? $this->uploadFile
                : null;

            if (! $file) {
                throw ValidationException::withMessages(['uploadFile' => 'Please choose a file to upload.']);
            }

            $asset = $this->getMediaServiceProperty()->upload($file, auth()->user());
            Notification::make()->title('Asset uploaded')->success()->send();
            $this->closeUploadModal();
            $this->openDetailDrawer($asset->id);
        } catch (ValidationException $e) {
            throw $e;
        }
    }

    public function saveMetadata(): void
    {
        $asset = $this->requireSelectedAsset('update');

        $tags = collect(explode(',', $this->metaTags))
            ->map(fn ($t) => trim($t))
            ->filter()
            ->values()
            ->all();

        $this->getMediaServiceProperty()->updateMetadata($asset, [
            'file_name' => $this->metaFileName,
            'alt_text' => $this->metaAltText ?: null,
            'caption' => $this->metaCaption ?: null,
            'description' => $this->metaDescription ?: null,
            'copyright' => $this->metaCopyright ?: null,
            'internal_notes' => $this->metaNotes ?: null,
            'tags' => $tags,
        ]);

        Notification::make()->title('Metadata saved')->success()->send();
    }

    public function replaceSelectedFile(): void
    {
        $asset = $this->requireSelectedAsset('update');

        $this->validate([
            'replaceFile' => ['required', 'file', 'max:12288'],
        ]);

        $file = $this->replaceFile instanceof TemporaryUploadedFile ? $this->replaceFile : null;
        if (! $file) {
            throw ValidationException::withMessages(['replaceFile' => 'Please choose a replacement file.']);
        }

        $this->getMediaServiceProperty()->replaceFile($asset, $file, auth()->user());
        $this->replaceFile = null;
        Notification::make()->title('File replaced')->body('All linked products and articles now use the new file.')->success()->send();
    }

    public function archiveSelected(): void
    {
        $asset = $this->requireSelectedAsset('update');
        $this->getMediaServiceProperty()->archive($asset);
        Notification::make()->title('Asset archived')->success()->send();
    }

    public function deleteSelected(): void
    {
        $asset = $this->requireSelectedAsset('delete');

        try {
            $this->getMediaServiceProperty()->delete($asset);
            Notification::make()->title('Asset deleted')->success()->send();
            $this->closeDetailDrawer();
        } catch (ValidationException $e) {
            Notification::make()
                ->title('Cannot delete asset')
                ->body(collect($e->errors())->flatten()->first() ?? 'Asset is still in use.')
                ->danger()
                ->send();
        }
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->typeFilter = [];
        $this->usageFilter = null;
        $this->formatFilter = null;
        $this->uploaderFilter = null;
        $this->dateFrom = null;
        $this->dateUntil = null;
        $this->sortField = 'created_at';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedUsageFilter(): void
    {
        $this->resetPage();
    }

    public function updatedFormatFilter(): void
    {
        $this->resetPage();
    }

    public function updatedUploaderFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateUntil(): void
    {
        $this->resetPage();
    }

    public function hasActiveFilters(): bool
    {
        return filled($this->search)
            || filled($this->typeFilter)
            || filled($this->usageFilter)
            || filled($this->formatFilter)
            || filled($this->uploaderFilter)
            || filled($this->dateFrom)
            || filled($this->dateUntil);
    }

    public function getExportUrl(string $format): string
    {
        return route('filament.admin.marketing.media.export', [
            'format' => $format,
            'search' => $this->search ?: null,
            'type' => $this->typeFilter ?: null,
            'usage' => $this->usageFilter,
            'format_filter' => $this->formatFilter,
            'uploader' => $this->uploaderFilter,
            'date_from' => $this->dateFrom,
            'date_until' => $this->dateUntil,
        ]);
    }

    protected function requireSelectedAsset(string $ability): MediaAsset
    {
        $asset = MediaAsset::query()->findOrFail($this->selectedMediaId);
        Gate::authorize($ability, $asset);

        return $asset;
    }

    protected function hydrateMetaFromSelected(): void
    {
        $item = $this->getSelectedMediaProperty();
        if (! $item) {
            return;
        }

        $this->metaFileName = (string) ($item['file_name'] ?? '');
        $this->metaAltText = (string) ($item['alt_text'] ?? '');
        $this->metaCaption = (string) ($item['caption'] ?? '');
        $this->metaDescription = (string) ($item['description'] ?? '');
        $this->metaCopyright = (string) ($item['copyright'] ?? '');
        $this->metaNotes = (string) ($item['internal_notes'] ?? '');
        $this->metaTags = implode(', ', $item['tags'] ?? []);
    }

    protected function resetUploadState(): void
    {
        $this->uploadFile = null;
        $this->resetValidation();
    }
}

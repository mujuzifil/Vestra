<?php

namespace App\Livewire\Admin;

use App\Models\MediaAsset;
use App\Services\Admin\MediaAdminService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class MediaAssetPicker extends Component
{
    use WithFileUploads;
    use WithPagination;

    public bool $open = false;

    /** choice|browse|upload */
    public string $step = 'choice';

    public string $search = '';

    public ?int $selectedId = null;

    public $uploadFile = null;

    /** Event name bubbled to parent with selected asset id */
    public string $eventName = 'media-asset-selected';

    public string $context = 'default';

    protected string $paginationTheme = 'tailwind';

    #[On('open-media-picker')]
    public function openPicker(?string $context = null, ?string $eventName = null): void
    {
        Gate::authorize('viewAny', MediaAsset::class);

        if ($context) {
            $this->context = $context;
        }
        if ($eventName) {
            $this->eventName = $eventName;
        }

        $this->open = true;
        $this->step = 'choice';
        $this->selectedId = null;
        $this->uploadFile = null;
        $this->search = '';
        $this->resetPage();
    }

    public function close(): void
    {
        $this->open = false;
        $this->step = 'choice';
        $this->selectedId = null;
        $this->uploadFile = null;
    }

    public function chooseExisting(): void
    {
        $this->step = 'browse';
        $this->resetPage();
    }

    public function chooseUpload(): void
    {
        Gate::authorize('create', MediaAsset::class);
        $this->step = 'upload';
    }

    public function backToChoice(): void
    {
        $this->step = 'choice';
        $this->selectedId = null;
        $this->uploadFile = null;
    }

    public function selectAsset(int $id): void
    {
        $this->selectedId = $id;
    }

    public function confirmSelection(): void
    {
        if (! $this->selectedId) {
            return;
        }

        $asset = MediaAsset::query()->findOrFail($this->selectedId);
        Gate::authorize('view', $asset);

        $this->dispatch($this->eventName, id: $asset->id, context: $this->context, url: $asset->url());
        $this->close();
    }

    public function uploadAndSelect(): void
    {
        Gate::authorize('create', MediaAsset::class);

        $this->validate([
            'uploadFile' => ['required', 'file', 'max:12288'],
        ]);

        $file = $this->uploadFile instanceof TemporaryUploadedFile ? $this->uploadFile : null;
        if (! $file) {
            return;
        }

        $asset = app(MediaAdminService::class)->upload($file, auth()->user());
        $this->dispatch($this->eventName, id: $asset->id, context: $this->context, url: $asset->url());
        $this->close();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function getAssetsProperty()
    {
        return app(MediaAdminService::class)->pickerPaginate([
            'search' => $this->search,
            'sort' => 'created_at',
            'direction' => 'desc',
        ], 18);
    }

    public function render()
    {
        return view('livewire.admin.media-asset-picker');
    }
}

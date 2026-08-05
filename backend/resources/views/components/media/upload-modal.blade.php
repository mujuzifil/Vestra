@props(['show' => false])

<div
    class="vestra-media-upload @if ($show) vestra-media-upload--open @endif"
    x-data="{ open: @entangle('showUploadModal') }"
    x-show="open"
    x-cloak
    @keydown.escape.window="if (open) $wire.closeUploadModal()"
    role="dialog"
    aria-modal="true"
    aria-label="Upload asset"
>
    <div class="vestra-media-upload__overlay" wire:click="closeUploadModal"></div>
    <div class="vestra-media-upload__panel">
        <div class="vestra-media-upload__header">
            <h2>Upload Asset</h2>
            <button type="button" wire:click="closeUploadModal" aria-label="Close">&times;</button>
        </div>
        <div class="vestra-media-upload__body">
            <label class="vestra-media-upload__dropzone">
                <input type="file" wire:model="uploadFile" accept="image/jpeg,image/png,image/webp,image/gif,.pdf,video/mp4,video/webm" class="vestra-media__file-input" />
                <x-filament::icon icon="heroicon-o-cloud-arrow-up" class="h-8 w-8" />
                <span>Drag and drop a file here, or click to browse.</span>
                <span class="vestra-media__hint">Images, PDF, or video up to 12MB.</span>
            </label>
            <div wire:loading wire:target="uploadFile">Preparing upload…</div>
            @error('uploadFile') <p class="vestra-media-detail__empty-usage">{{ $message }}</p> @enderror
            @error('file') <p class="vestra-media-detail__empty-usage">{{ $message }}</p> @enderror
            <div class="vestra-media-upload__actions">
                <button type="button" wire:click="closeUploadModal" class="vestra-button vestra-button--secondary">Cancel</button>
                <button type="button" wire:click="uploadAsset" class="vestra-button vestra-button--primary" wire:loading.attr="disabled">Upload</button>
            </div>
        </div>
    </div>
</div>

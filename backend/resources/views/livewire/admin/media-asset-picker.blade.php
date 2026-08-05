<div>
    @if ($open)
        <div class="vestra-media-picker" wire:key="media-picker-root" role="dialog" aria-modal="true" aria-label="Select media asset">
            <div class="vestra-media-picker__overlay" wire:click="close"></div>
            <div class="vestra-media-picker__panel">
                <div class="vestra-media-picker__header">
                    <h2>
                        @if ($step === 'choice')
                            Select Image
                        @elseif ($step === 'browse')
                            Choose Existing Asset
                        @else
                            Upload New Asset
                        @endif
                    </h2>
                    <button type="button" wire:click="close" aria-label="Close">&times;</button>
                </div>

                <div class="vestra-media-picker__body">
                    @if ($step === 'choice')
                        <div class="vestra-media-picker__choices">
                            <button type="button" class="vestra-media-picker__choice" wire:click="chooseExisting">
                                <strong>Choose Existing Asset</strong>
                                <span>Search the Media Library and reuse an uploaded file without creating a duplicate.</span>
                            </button>
                            <button type="button" class="vestra-media-picker__choice" wire:click="chooseUpload">
                                <strong>Upload New Asset</strong>
                                <span>Upload a file to the Media Library and link it to this record automatically.</span>
                            </button>
                        </div>
                    @elseif ($step === 'browse')
                        <div class="vestra-media-picker__toolbar">
                            <input
                                type="search"
                                wire:model.live.debounce.300ms="search"
                                class="vestra-media__filter-input"
                                placeholder="Search assets…"
                                style="flex:1"
                            />
                            <button type="button" class="vestra-button vestra-button--secondary" wire:click="backToChoice">Back</button>
                        </div>

                        <div class="vestra-media-picker__grid">
                            @forelse ($this->assets as $asset)
                                <button
                                    type="button"
                                    class="vestra-media-picker__tile @if ($selectedId === $asset->id) vestra-media-picker__tile--selected @endif"
                                    wire:click="selectAsset({{ $asset->id }})"
                                >
                                    @if ($asset->url())
                                        <img src="{{ $asset->url() }}" alt="{{ $asset->alt_text ?: $asset->file_name }}" loading="lazy" />
                                    @endif
                                    <figcaption>{{ $asset->file_name }}</figcaption>
                                </button>
                            @empty
                                <p>No assets found. Upload a new asset instead.</p>
                            @endforelse
                        </div>

                        <div style="margin-top:1rem">
                            {{ $this->assets->links() }}
                        </div>
                    @else
                        <label class="vestra-media-upload__dropzone">
                            <input type="file" wire:model="uploadFile" accept="image/jpeg,image/png,image/webp,image/gif" />
                            <span>Drag and drop an image, or click to browse.</span>
                        </label>
                        <div wire:loading wire:target="uploadFile">Preparing…</div>
                        @error('uploadFile') <p>{{ $message }}</p> @enderror
                    @endif
                </div>

                <div class="vestra-media-picker__footer">
                    <button type="button" class="vestra-button vestra-button--secondary" wire:click="close">Cancel</button>
                    <div style="display:flex;gap:0.5rem">
                        @if ($step === 'browse')
                            <button type="button" class="vestra-button vestra-button--primary" wire:click="confirmSelection" @disabled(! $selectedId)>Confirm Selection</button>
                        @elseif ($step === 'upload')
                            <button type="button" class="vestra-button vestra-button--secondary" wire:click="backToChoice">Back</button>
                            <button type="button" class="vestra-button vestra-button--primary" wire:click="uploadAndSelect">Upload & Select</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@props([
    'show' => false,
    'item' => null,
])

<div
    class="vestra-media-detail @if ($show) vestra-media-detail--open @endif"
    x-data="{ open: @js($show) }"
    x-show="open"
    x-cloak
    @keydown.escape.window="if (open) $wire.closeDetailDrawer()"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 translate-x-4"
    x-transition:enter-end="opacity-100 translate-x-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-x-0"
    x-transition:leave-end="opacity-0 translate-x-4"
    aria-label="File details"
    role="dialog"
    aria-modal="true"
>
    <div class="vestra-media-detail__overlay" wire:click="closeDetailDrawer"></div>

    <div class="vestra-media-detail__panel">
        @if ($item)
            <div class="vestra-media-detail__header">
                <div class="vestra-media-detail__header-main">
                    <span class="vestra-media-detail__avatar">
                        <x-filament::icon icon="heroicon-o-photo" class="h-5 w-5" />
                    </span>
                    <div class="vestra-media-detail__header-text">
                        <h2 class="vestra-media-detail__title">{{ $item['name'] ?? 'File' }}</h2>
                        <p class="vestra-media-detail__subtitle">{{ $item['owner_label'] ?? '' }}</p>
                    </div>
                </div>

                <button
                    type="button"
                    wire:click="closeDetailDrawer"
                    class="vestra-media-detail__close"
                    aria-label="Close details"
                >
                    <x-filament::icon icon="heroicon-o-x-mark" class="h-5 w-5" />
                </button>
            </div>

            <div class="vestra-media-detail__body">
                <div class="vestra-media-detail__badges">
                    <x-media.type-badge :type="$item['type'] ?? 'other'" />
                    <x-media.source-badge :source="$item['source'] ?? null" />
                </div>

                @if (($item['type'] ?? null) === 'image' && ($item['url'] ?? null))
                    <div class="vestra-media-detail__preview">
                        <img
                            src="{{ $item['url'] }}"
                            alt="{{ $item['name'] ?? '' }}"
                            loading="lazy"
                            onerror="this.onerror=null;this.src='{{ asset('images/placeholder.svg') }}'"
                        />
                    </div>
                @else
                    <div class="vestra-media-detail__preview vestra-media-detail__preview--icon">
                        <x-filament::icon
                            icon="{{ ($item['type'] ?? null) === 'document' ? 'heroicon-o-document-text' : (($item['type'] ?? null) === 'video' ? 'heroicon-o-film' : 'heroicon-o-document') }}"
                            class="h-12 w-12"
                        />
                    </div>
                @endif

                <div class="vestra-media-detail__section">
                    <h3 class="vestra-media-detail__section-title">File Details</h3>
                    <dl class="vestra-media-detail__definition-list">
                        <div class="vestra-media-detail__definition-row">
                            <dt>Name</dt>
                            <dd>{{ $item['name'] ?? '—' }}</dd>
                        </div>
                        <div class="vestra-media-detail__definition-row">
                            <dt>MIME Type</dt>
                            <dd>{{ $item['mime'] ?? 'Unknown' }}</dd>
                        </div>
                        <div class="vestra-media-detail__definition-row">
                            <dt>Size</dt>
                            <dd><x-media.file-size :bytes="$item['size_bytes'] ?? null" /></dd>
                        </div>
                        <div class="vestra-media-detail__definition-row">
                            <dt>Path</dt>
                            <dd class="vestra-media-detail__path">{{ $item['path'] ?? '—' }}</dd>
                        </div>
                        <div class="vestra-media-detail__definition-row">
                            <dt>Uploaded</dt>
                            <dd>{{ $item['created_at']?->format('M j, Y g:i A') ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="vestra-media-detail__section">
                    <h3 class="vestra-media-detail__section-title">Ownership</h3>
                    <dl class="vestra-media-detail__definition-list">
                        <div class="vestra-media-detail__definition-row">
                            <dt>Owner</dt>
                            <dd>{{ $item['owner_label'] ?? '—' }}</dd>
                        </div>
                    </dl>
                    @if ($item['owner_url'] ?? null)
                        <a href="{{ $item['owner_url'] }}" class="vestra-button vestra-button--primary vestra-media-detail__owner-cta">
                            <x-filament::icon icon="heroicon-o-arrow-top-right-on-square" class="h-4 w-4" />
                            <span>Open Owner Record</span>
                        </a>
                    @endif
                </div>

                @if ($item['url'] ?? null)
                    <div class="vestra-media-detail__section">
                        <a href="{{ $item['url'] }}" target="_blank" rel="noopener" class="vestra-button vestra-button--secondary">
                            <x-filament::icon icon="heroicon-o-arrow-down-tray" class="h-4 w-4" />
                            <span>Open Original File</span>
                        </a>
                    </div>
                @endif
            </div>
        @else
            <div class="vestra-media-detail__empty">Select a file to view details.</div>
        @endif
    </div>
</div>

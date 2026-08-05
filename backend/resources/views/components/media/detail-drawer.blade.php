@props([
    'show' => false,
    'item' => null,
    'canManage' => false,
])

<div
    class="vestra-media-detail @if ($show) vestra-media-detail--open @endif"
    x-data="{ open: @entangle('showDetailDrawer'), zoom: false }"
    x-show="open"
    x-cloak
    @keydown.escape.window="if (open) $wire.closeDetailDrawer()"
    aria-label="Asset details"
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
                        <h2 class="vestra-media-detail__title">{{ $item['file_name'] ?? 'Asset' }}</h2>
                        <p class="vestra-media-detail__subtitle">{{ $item['original_file_name'] ?? '' }}</p>
                    </div>
                </div>
                <button type="button" wire:click="closeDetailDrawer" class="vestra-media-detail__close" aria-label="Close details">
                    <x-filament::icon icon="heroicon-o-x-mark" class="h-5 w-5" />
                </button>
            </div>

            <div class="vestra-media-detail__body">
                <div class="vestra-media-detail__badges">
                    <x-media.type-badge :type="$item['media_type'] ?? 'other'" />
                    <span class="vestra-media__status-pill">{{ $item['status_label'] ?? 'Active' }}</span>
                    <span class="vestra-media__usage-pill">{{ (int) ($item['usages_count'] ?? 0) }} references</span>
                </div>

                @if (($item['media_type'] ?? null) === 'image' && ($item['url'] ?? null))
                    <div class="vestra-media-detail__preview" @click="zoom = !zoom" :class="{ 'vestra-media-detail__preview--zoom': zoom }">
                        <img src="{{ $item['url'] }}" alt="{{ $item['alt_text'] ?: ($item['file_name'] ?? '') }}" loading="lazy" />
                    </div>
                @endif

                <div class="vestra-media-detail__actions-row">
                    @if ($item['url'] ?? null)
                        <a href="{{ $item['url'] }}" target="_blank" rel="noopener" class="vestra-button vestra-button--secondary">Open Full</a>
                        <a href="{{ $item['url'] }}" download class="vestra-button vestra-button--secondary">Download</a>
                        <button
                            type="button"
                            class="vestra-button vestra-button--secondary"
                            x-data
                            @click="navigator.clipboard.writeText(@js($item['url'])); $dispatch('notify', { message: 'URL copied' })"
                        >
                            Copy URL
                        </button>
                    @endif
                </div>

                <div class="vestra-media-detail__section">
                    <h3 class="vestra-media-detail__section-title">File Details</h3>
                    <dl class="vestra-media-detail__definition-list">
                        <div class="vestra-media-detail__definition-row"><dt>Filename</dt><dd>{{ $item['file_name'] ?? '—' }}</dd></div>
                        <div class="vestra-media-detail__definition-row"><dt>Original Filename</dt><dd>{{ $item['original_file_name'] ?? '—' }}</dd></div>
                        <div class="vestra-media-detail__definition-row"><dt>File Size</dt><dd>{{ $item['size_label'] ?? '—' }}</dd></div>
                        <div class="vestra-media-detail__definition-row"><dt>Dimensions</dt><dd>{{ $item['dimensions'] ?? '—' }}</dd></div>
                        <div class="vestra-media-detail__definition-row"><dt>Format</dt><dd>{{ $item['mime_type'] ?? '—' }}</dd></div>
                        <div class="vestra-media-detail__definition-row"><dt>Storage Location</dt><dd class="vestra-media-detail__path">{{ $item['path'] ?? '—' }}</dd></div>
                        <div class="vestra-media-detail__definition-row"><dt>Public URL</dt>
                            <dd>
                                @if ($item['url'] ?? null)
                                    <a href="{{ $item['url'] }}" target="_blank" rel="noopener">{{ $item['url'] }}</a>
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                        <div class="vestra-media-detail__definition-row"><dt>Uploaded By</dt><dd>{{ $item['uploaded_by']['name'] ?? '—' }}</dd></div>
                        <div class="vestra-media-detail__definition-row"><dt>Upload Date</dt><dd>{{ $item['created_at']?->format('M j, Y g:i A') ?? '—' }}</dd></div>
                        <div class="vestra-media-detail__definition-row"><dt>Last Modified</dt><dd>{{ $item['updated_at']?->format('M j, Y g:i A') ?? '—' }}</dd></div>
                        <div class="vestra-media-detail__definition-row"><dt>Reference Count</dt><dd>{{ (int) ($item['usages_count'] ?? 0) }}</dd></div>
                    </dl>
                </div>

                <div class="vestra-media-detail__section">
                    <h3 class="vestra-media-detail__section-title">Used In</h3>
                    @forelse ($item['usage_groups'] ?? [] as $group)
                        <div class="vestra-media-detail__usage-group">
                            <h4>{{ $group['group'] }}</h4>
                            <ul>
                                @foreach ($group['items'] as $usage)
                                    <li>{{ $usage['label'] }} <span>({{ $usage['context_label'] }})</span></li>
                                @endforeach
                            </ul>
                        </div>
                    @empty
                        <p class="vestra-media-detail__empty-usage">This asset is not used anywhere yet.</p>
                    @endforelse
                </div>

                @if ($canManage)
                    <div class="vestra-media-detail__section">
                        <h3 class="vestra-media-detail__section-title">Metadata</h3>
                        <div class="vestra-media-detail__meta-form">
                            <label>Filename<input type="text" wire:model="metaFileName" class="vestra-media__filter-input" /></label>
                            <label>Alt Text<input type="text" wire:model="metaAltText" class="vestra-media__filter-input" /></label>
                            <label>Caption<input type="text" wire:model="metaCaption" class="vestra-media__filter-input" /></label>
                            <label>Description<textarea wire:model="metaDescription" rows="2" class="vestra-media__filter-input"></textarea></label>
                            <label>Tags (comma separated)<input type="text" wire:model="metaTags" class="vestra-media__filter-input" /></label>
                            <label>Copyright<input type="text" wire:model="metaCopyright" class="vestra-media__filter-input" /></label>
                            <label>Internal Notes<textarea wire:model="metaNotes" rows="2" class="vestra-media__filter-input"></textarea></label>
                            <button type="button" wire:click="saveMetadata" class="vestra-button vestra-button--primary">Save Metadata</button>
                        </div>
                    </div>

                    <div class="vestra-media-detail__section">
                        <h3 class="vestra-media-detail__section-title">Replace File</h3>
                        <input type="file" wire:model="replaceFile" accept="image/*,.pdf,video/*" />
                        <div wire:loading wire:target="replaceFile">Uploading…</div>
                        <button type="button" wire:click="replaceSelectedFile" class="vestra-button vestra-button--secondary" wire:loading.attr="disabled">Replace</button>
                    </div>

                    <div class="vestra-media-detail__section vestra-media-detail__danger">
                        <button type="button" wire:click="archiveSelected" class="vestra-button vestra-button--secondary">Archive</button>
                        <button
                            type="button"
                            wire:click="deleteSelected"
                            wire:confirm="Delete this asset permanently? This only works if it is unused."
                            class="vestra-button vestra-button--primary"
                            style="background:#b91c1c"
                        >
                            Delete
                        </button>
                    </div>
                @endif
            </div>
        @else
            <div class="vestra-media-detail__empty">Select an asset to view details.</div>
        @endif
    </div>
</div>

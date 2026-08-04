@props([
    'item',
    'selectedIds' => [],
])

@php
$isSelected = in_array($item['id'], $selectedIds, true);
$isImage = $item['type'] === 'image';
@endphp

<div class="vestra-media__card-item" wire:key="media-card-{{ $item['id'] }}">
    <label class="vestra-media__card-select">
        <input
            type="checkbox"
            wire:model.live="selectedIds"
            value="{{ $item['id'] }}"
            @checked($isSelected)
            aria-label="Select {{ $item['name'] }}"
        />
    </label>

    <button
        type="button"
        wire:click="openDetailDrawer('{{ $item['id'] }}')"
        class="vestra-media__card-preview"
    >
        @if ($isImage && $item['url'])
            <img
                src="{{ $item['url'] }}"
                alt="{{ $item['name'] }}"
                loading="lazy"
                class="vestra-media__card-image"
                onerror="this.onerror=null;this.src='{{ asset('images/placeholder.svg') }}'"
            />
        @else
            <span class="vestra-media__card-icon">
                <x-filament::icon
                    icon="{{ $item['type'] === 'document' ? 'heroicon-o-document-text' : ($item['type'] === 'video' ? 'heroicon-o-film' : 'heroicon-o-document') }}"
                    class="h-8 w-8"
                />
            </span>
        @endif
    </button>

    <div class="vestra-media__card-body">
        <button
            type="button"
            wire:click="openDetailDrawer('{{ $item['id'] }}')"
            class="vestra-media__card-name"
            title="{{ $item['name'] }}"
        >
            {{ $item['name'] }}
        </button>
        <p class="vestra-media__card-owner" title="{{ $item['owner_label'] }}">{{ $item['owner_label'] }}</p>

        <div class="vestra-media__card-footer">
            <x-media.type-badge :type="$item['type']" />
            <x-media.source-badge :source="$item['source']" />
        </div>
    </div>
</div>

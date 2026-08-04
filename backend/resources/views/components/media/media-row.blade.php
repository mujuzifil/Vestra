@props([
    'item',
    'selectedIds' => [],
])

@php
$isSelected = in_array($item['id'], $selectedIds, true);
$isImage = $item['type'] === 'image';
@endphp

<tr class="vestra-media__row" wire:key="media-row-{{ $item['id'] }}">
    <td class="vestra-media__td vestra-media__td--select">
        <input
            type="checkbox"
            class="vestra-media__filter-checkbox"
            wire:model.live="selectedIds"
            value="{{ $item['id'] }}"
            @checked($isSelected)
            aria-label="Select {{ $item['name'] }}"
        />
    </td>

    <td class="vestra-media__td vestra-media__td--file">
        <button
            type="button"
            wire:click="openDetailDrawer('{{ $item['id'] }}')"
            class="vestra-media__file-link"
        >
            @if ($isImage && $item['url'])
                <img
                    src="{{ $item['url'] }}"
                    alt=""
                    class="vestra-media__thumb"
                    loading="lazy"
                    onerror="this.onerror=null;this.src='{{ asset('images/placeholder.svg') }}'"
                />
            @else
                <span class="vestra-media__thumb vestra-media__thumb--icon">
                    <x-filament::icon
                        icon="{{ $item['type'] === 'document' ? 'heroicon-o-document-text' : ($item['type'] === 'video' ? 'heroicon-o-film' : 'heroicon-o-document') }}"
                        class="h-5 w-5"
                    />
                </span>
            @endif
            <span class="vestra-media__file-text">
                <span class="vestra-media__file-name">{{ $item['name'] }}</span>
                <span class="vestra-media__row-meta">{{ $item['mime'] ?? 'Unknown type' }}</span>
            </span>
        </button>
    </td>

    <td class="vestra-media__td vestra-media__td--type">
        <x-media.type-badge :type="$item['type']" />
    </td>

    <td class="vestra-media__td vestra-media__td--source">
        <x-media.source-badge :source="$item['source']" />
    </td>

    <td class="vestra-media__td vestra-media__td--owner">
        @if ($item['owner_url'])
            <a href="{{ $item['owner_url'] }}" class="vestra-media__owner-link">{{ $item['owner_label'] }}</a>
        @else
            <span class="vestra-media__cell-text">{{ $item['owner_label'] }}</span>
        @endif
    </td>

    <td class="vestra-media__td vestra-media__td--size">
        <span class="vestra-media__cell-text"><x-media.file-size :bytes="$item['size_bytes']" /></span>
    </td>

    <td class="vestra-media__td vestra-media__td--created">
        <span class="vestra-media__created">{{ $item['created_at']?->format('M j, Y') ?? '—' }}</span>
        <span class="vestra-media__row-meta">{{ $item['created_at']?->format('g:i A') }}</span>
    </td>

    <td class="vestra-media__td vestra-media__td--actions">
        <button
            type="button"
            wire:click="openDetailDrawer('{{ $item['id'] }}')"
            class="vestra-media__action-trigger"
            aria-label="View file details"
        >
            <x-filament::icon icon="heroicon-o-eye" class="h-5 w-5" />
        </button>
    </td>
</tr>

@props(['item'])

@php
$isImage = ($item['media_type'] ?? '') === 'image';
@endphp

<tr class="vestra-media__row" wire:key="media-row-{{ $item['id'] }}">
    <td class="vestra-media__td vestra-media__td--file">
        <button type="button" wire:click="openDetailDrawer({{ (int) $item['id'] }})" class="vestra-media__file-link">
            @if ($isImage && ($item['url'] ?? null))
                <img src="{{ $item['url'] }}" alt="" class="vestra-media__thumb" loading="lazy" />
            @else
                <span class="vestra-media__thumb vestra-media__thumb--icon">
                    <x-filament::icon icon="heroicon-o-document" class="h-5 w-5" />
                </span>
            @endif
            <span class="vestra-media__file-text">
                <span class="vestra-media__file-name">{{ $item['file_name'] ?? 'Untitled' }}</span>
                <span class="vestra-media__row-meta">{{ $item['dimensions'] ?? ($item['mime_type'] ?? '') }}</span>
            </span>
        </button>
    </td>
    <td class="vestra-media__td"><x-media.type-badge :type="$item['media_type'] ?? 'other'" /></td>
    <td class="vestra-media__td"><span class="vestra-media__cell-text">{{ $item['size_label'] ?? '—' }}</span></td>
    <td class="vestra-media__td"><span class="vestra-media__usage-pill">{{ (int) ($item['usages_count'] ?? 0) }}</span></td>
    <td class="vestra-media__td"><span class="vestra-media__status-pill">{{ $item['status_label'] ?? 'Active' }}</span></td>
    <td class="vestra-media__td">
        <span class="vestra-media__created">{{ $item['created_at']?->format('M j, Y') ?? '—' }}</span>
    </td>
    <td class="vestra-media__td vestra-media__td--actions">
        <button type="button" wire:click="openDetailDrawer({{ (int) $item['id'] }})" class="vestra-media__action-trigger" aria-label="View asset details">
            <x-filament::icon icon="heroicon-o-eye" class="h-5 w-5" />
        </button>
    </td>
</tr>

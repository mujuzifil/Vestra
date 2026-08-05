@props(['item'])

@php
$isImage = ($item['media_type'] ?? '') === 'image';
@endphp

<div class="vestra-media__card-item" wire:key="media-card-{{ $item['id'] }}">
    <button type="button" wire:click="openDetailDrawer({{ (int) $item['id'] }})" class="vestra-media__card-preview">
        @if ($isImage && ($item['url'] ?? null))
            <img
                src="{{ $item['url'] }}"
                alt="{{ $item['alt_text'] ?: ($item['file_name'] ?? '') }}"
                loading="lazy"
                class="vestra-media__card-image"
            />
        @else
            <span class="vestra-media__card-icon">
                <x-filament::icon
                    icon="{{ ($item['media_type'] ?? '') === 'document' ? 'heroicon-o-document-text' : (($item['media_type'] ?? '') === 'video' ? 'heroicon-o-film' : 'heroicon-o-document') }}"
                    class="h-8 w-8"
                />
            </span>
        @endif
    </button>

    <div class="vestra-media__card-body">
        <button type="button" wire:click="openDetailDrawer({{ (int) $item['id'] }})" class="vestra-media__card-name" title="{{ $item['file_name'] ?? '' }}">
            {{ $item['file_name'] ?? 'Untitled' }}
        </button>
        <p class="vestra-media__card-owner">
            {{ $item['dimensions'] ?? '—' }} · {{ $item['size_label'] ?? '—' }}
        </p>
        <div class="vestra-media__card-footer">
            <x-media.type-badge :type="$item['media_type'] ?? 'other'" />
            <span class="vestra-media__usage-pill">{{ (int) ($item['usages_count'] ?? 0) }} used</span>
            <span class="vestra-media__status-pill">{{ $item['status_label'] ?? 'Active' }}</span>
        </div>
        <p class="vestra-media__card-date">{{ $item['created_at']?->format('M j, Y') ?? '' }}</p>
    </div>
</div>

@props([
    'items' => null,
    'selectedIds' => [],
])

<div class="vestra-media__grid" role="region" aria-label="Media files">
    @foreach ($items as $item)
        <x-media.media-card :item="$item" :selected-ids="$selectedIds" />
    @endforeach
</div>

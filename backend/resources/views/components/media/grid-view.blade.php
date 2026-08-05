@props(['items' => null])

<div class="vestra-media__grid" role="region" aria-label="Media assets">
    @foreach ($items as $item)
        <x-media.media-card :item="$item" />
    @endforeach
</div>

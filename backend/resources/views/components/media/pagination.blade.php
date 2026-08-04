@props(['paginator'])

@if ($paginator->hasPages() || $paginator->total() > 0)
    <nav class="vestra-media__pagination" aria-label="Media pagination">
        <div class="vestra-media__pagination-info">
            <span>Showing <strong>{{ $paginator->firstItem() ?? 0 }}</strong> to <strong>{{ $paginator->lastItem() ?? 0 }}</strong> of <strong>{{ $paginator->total() }}</strong> files</span>
        </div>

        @if ($paginator->hasPages())
            <div class="vestra-media__pagination-controls">
                <button
                    type="button"
                    wire:click="previousPage"
                    @disabled($paginator->onFirstPage())
                    class="vestra-media__pagination-btn"
                    aria-label="Previous page"
                >
                    <x-filament::icon icon="heroicon-o-chevron-left" class="h-4 w-4" />
                </button>

                @foreach ($paginator->linkCollection() as $element)
                    @if (is_string($element))
                        <span class="vestra-media__pagination-ellipsis">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="vestra-media__pagination-btn vestra-media__pagination-btn--active" aria-current="page">{{ $page }}</span>
                            @else
                                <button
                                    type="button"
                                    wire:click="gotoPage({{ $page }})"
                                    class="vestra-media__pagination-btn"
                                    aria-label="Go to page {{ $page }}"
                                >{{ $page }}</button>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                <button
                    type="button"
                    wire:click="nextPage"
                    @disabled(! $paginator->hasMorePages())
                    class="vestra-media__pagination-btn"
                    aria-label="Next page"
                >
                    <x-filament::icon icon="heroicon-o-chevron-right" class="h-4 w-4" />
                </button>
            </div>
        @endif
    </nav>
@endif

@props(['paginator'])

@if ($paginator->hasPages())
    <nav class="vestra-activity__pagination" aria-label="Activity pagination">
        <div class="vestra-activity__pagination-info">
            <span>Showing <strong>{{ $paginator->firstItem() }}</strong> to <strong>{{ $paginator->lastItem() }}</strong> of <strong>{{ $paginator->total() }}</strong> activities</span>
        </div>

        <div class="vestra-activity__pagination-controls">
            <button
                type="button"
                wire:click="previousPage"
                @disabled($paginator->onFirstPage())
                class="vestra-activity__pagination-btn"
                aria-label="Previous page"
            >
                <x-filament::icon icon="heroicon-o-chevron-left" class="h-4 w-4" />
            </button>

            @foreach ($paginator->linkCollection() as $element)
                @if (is_string($element))
                    <span class="vestra-activity__pagination-ellipsis">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="vestra-activity__pagination-btn vestra-activity__pagination-btn--active" aria-current="page">{{ $page }}</span>
                        @else
                            <button
                                type="button"
                                wire:click="gotoPage({{ $page }})"
                                class="vestra-activity__pagination-btn"
                                aria-label="Go to page {{ $page }}"
                            >
                                {{ $page }}
                            </button>
                        @endif
                    @endforeach
                @endif
            @endforeach

            <button
                type="button"
                wire:click="nextPage"
                @disabled(! $paginator->hasMorePages())
                class="vestra-activity__pagination-btn"
                aria-label="Next page"
            >
                <x-filament::icon icon="heroicon-o-chevron-right" class="h-4 w-4" />
            </button>
        </div>
    </nav>
@endif

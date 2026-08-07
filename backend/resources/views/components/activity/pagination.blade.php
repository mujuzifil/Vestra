@props(['paginator'])

@php
$current = $paginator->currentPage();
$last = $paginator->lastPage();
$pages = [];

if ($last <= 7) {
    $pages = range(1, $last);
} else {
    $pages = [1];
    $start = max(2, $current - 1);
    $end = min($last - 1, $current + 1);

    if ($start > 2) {
        $pages[] = '...';
    }

    for ($page = $start; $page <= $end; $page++) {
        $pages[] = $page;
    }

    if ($end < $last - 1) {
        $pages[] = '...';
    }

    $pages[] = $last;
}
@endphp

@if ($paginator->hasPages() || $paginator->total() > 0)
    <nav class="vestra-activity__pagination" aria-label="Activity pagination" wire:key="activity-pagination-{{ $current }}-{{ $last }}">
        <div class="vestra-activity__pagination-info">
            <span>
                Showing
                <strong>{{ $paginator->firstItem() ?? 0 }}</strong>
                to
                <strong>{{ $paginator->lastItem() ?? 0 }}</strong>
                of
                <strong>{{ $paginator->total() }}</strong>
                results
            </span>
        </div>

        @if ($paginator->hasPages())
            <div class="vestra-activity__pagination-controls">
                <button
                    type="button"
                    wire:click="previousPage"
                    wire:loading.attr="disabled"
                    @disabled($paginator->onFirstPage())
                    class="vestra-activity__pagination-btn"
                    aria-label="Previous page"
                >
                    <x-filament::icon icon="heroicon-o-chevron-left" class="h-4 w-4" />
                </button>

                @foreach ($pages as $page)
                    @if ($page === '...')
                        <span class="vestra-activity__pagination-ellipsis">…</span>
                    @elseif ($page == $current)
                        <span class="vestra-activity__pagination-btn vestra-activity__pagination-btn--active" aria-current="page">{{ $page }}</span>
                    @else
                        <button
                            type="button"
                            wire:click="gotoPage({{ $page }})"
                            wire:loading.attr="disabled"
                            class="vestra-activity__pagination-btn"
                            aria-label="Go to page {{ $page }}"
                        >
                            {{ $page }}
                        </button>
                    @endif
                @endforeach

                <button
                    type="button"
                    wire:click="nextPage"
                    wire:loading.attr="disabled"
                    @disabled(! $paginator->hasMorePages())
                    class="vestra-activity__pagination-btn"
                    aria-label="Next page"
                >
                    <x-filament::icon icon="heroicon-o-chevron-right" class="h-4 w-4" />
                </button>
            </div>
        @endif
    </nav>
@endif

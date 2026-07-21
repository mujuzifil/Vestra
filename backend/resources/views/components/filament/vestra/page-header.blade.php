@props([
    'heading',
    'subheading' => null,
    'breadcrumbs' => [],
    'actions' => [],
])

<header class="fi-header flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-6">
    <div class="min-w-0 flex-1">
        @if (! empty($breadcrumbs))
            <nav aria-label="Breadcrumb" class="mb-2 hidden sm:block">
                <ol class="fi-breadcrumbs flex flex-wrap items-center gap-x-1.5 text-sm text-neutral-500">
                    <li class="fi-breadcrumbs-item">
                        <a href="{{ filament()->getUrl() }}" class="hover:text-neutral-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-400 rounded-sm">
                            {{ __('Dashboard') }}
                        </a>
                    </li>
                    @foreach ($breadcrumbs as $url => $label)
                        <li class="fi-breadcrumbs-separator">
                            <x-filament::icon icon="heroicon-s-chevron-right" class="h-3.5 w-3.5 text-neutral-300" />
                        </li>
                        <li class="fi-breadcrumbs-item {{ $loop->last ? 'fi-breadcrumbs-item-current' : '' }}">
                            @if ($loop->last)
                                <span class="truncate font-medium text-neutral-700" aria-current="page">{{ $label }}</span>
                            @else
                                <a href="{{ $url }}" class="truncate hover:text-neutral-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-400 rounded-sm">{{ $label }}</a>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>
        @endif

        <h1 class="fi-header-heading text-2xl font-bold tracking-tight text-neutral-950 sm:text-3xl">{{ $heading }}</h1>

        @if ($subheading)
            <p class="fi-header-subheading mt-2 max-w-2xl text-base text-neutral-500">{{ $subheading }}</p>
        @endif
    </div>

    @if (! empty($actions))
        <div class="flex shrink-0 flex-wrap items-center gap-3 {{ ! empty($breadcrumbs) ? 'sm:mt-7' : '' }}">
            {{ $actions }}
        </div>
    @endif
</header>

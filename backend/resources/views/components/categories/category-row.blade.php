@props(['category'])

@php
use App\Filament\Resources\CategoryResource;
$canUpdate = auth()->user()?->can('update', $category) ?? false;
$editUrl = $canUpdate ? CategoryResource::getUrl('edit', ['record' => $category]) : null;
@endphp

<tr class="vestra-categories__row" wire:key="category-{{ $category->id }}">
    <td class="vestra-categories__td vestra-categories__td--name">
        <button
            type="button"
            wire:click="openDetailDrawer({{ $category->id }})"
            class="vestra-categories__name-link"
        >
            {{ $category->name }}
        </button>
        @if ($category->description)
            <span class="vestra-categories__row-meta">{{ Str::limit($category->description, 60) }}</span>
        @endif
    </td>

    <td class="vestra-categories__td vestra-categories__td--slug">
        <code class="vestra-categories__slug">{{ $category->slug }}</code>
    </td>

    <td class="vestra-categories__td vestra-categories__td--products">
        <span class="vestra-categories__count-badge">{{ number_format($category->products_count) }}</span>
    </td>

    <td class="vestra-categories__td vestra-categories__td--sort">
        <span class="vestra-categories__sort-order">{{ $category->sort_order }}</span>
    </td>

    <td class="vestra-categories__td vestra-categories__td--status">
        <x-categories.status-badge :status="$category->status" />
    </td>

    <td class="vestra-categories__td vestra-categories__td--updated">
        <span class="vestra-categories__created">{{ $category->updated_at?->format('M j, Y') ?? '—' }}</span>
        <span class="vestra-categories__row-meta">{{ $category->updated_at?->diffForHumans() }}</span>
    </td>

    <td class="vestra-categories__td vestra-categories__td--actions">
        <div class="vestra-categories__actions" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-categories__action-trigger" aria-label="Category actions" aria-haspopup="true">
                <x-filament::icon icon="heroicon-m-ellipsis-vertical" class="h-5 w-5" />
            </button>
            <div x-show="open" x-transition class="vestra-categories__action-menu" role="menu">
                <button type="button" wire:click="openDetailDrawer({{ $category->id }})" class="vestra-categories__action-item" role="menuitem">
                    <x-filament::icon icon="heroicon-o-eye" class="h-4 w-4" />
                    <span>View</span>
                </button>
                @if ($editUrl)
                    <a href="{{ $editUrl }}" class="vestra-categories__action-item" role="menuitem">
                        <x-filament::icon icon="heroicon-o-pencil-square" class="h-4 w-4" />
                        <span>Edit</span>
                    </a>
                @endif
            </div>
        </div>
    </td>
</tr>

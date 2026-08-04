@props(['post'])

@php
use App\Enums\BlogPostStatus;

$statusValue = $post->status instanceof BlogPostStatus ? $post->status->value : (string) $post->status;
$imageUrl = $post->featured_image
    ? (str_starts_with($post->featured_image, 'http') ? $post->featured_image : asset('storage/'.$post->featured_image))
    : asset('images/placeholder.svg');
@endphp

<tr class="vestra-blog__row" wire:key="blog-post-{{ $post->id }}">
    <td class="vestra-blog__td vestra-blog__td--title">
        <button
            type="button"
            wire:click="openDetailDrawer({{ $post->id }})"
            class="vestra-blog__post-link"
        >
            <img
                src="{{ $imageUrl }}"
                alt=""
                class="vestra-blog__thumb"
                loading="lazy"
                onerror="this.src='{{ asset('images/placeholder.svg') }}'"
            />
            <span class="vestra-blog__post-text">
                <span class="vestra-blog__post-title">{{ $post->title }}</span>
                <span class="vestra-blog__row-meta">{{ $post->slug }}</span>
            </span>
        </button>
    </td>

    <td class="vestra-blog__td vestra-blog__td--author">
        <span class="vestra-blog__cell-text">{{ $post->author?->name ?? '—' }}</span>
    </td>

    <td class="vestra-blog__td vestra-blog__td--category">
        @forelse ($post->categories as $category)
            <span class="vestra-blog__category-pill">{{ $category->name }}</span>
        @empty
            <span class="vestra-blog__empty-cell">—</span>
        @endforelse
    </td>

    <td class="vestra-blog__td vestra-blog__td--status">
        <x-blog.status-badge :status="$statusValue" />
    </td>

    <td class="vestra-blog__td vestra-blog__td--views">
        <span class="vestra-blog__views">{{ number_format((int) $post->view_count) }}</span>
    </td>

    <td class="vestra-blog__td vestra-blog__td--published">
        <span class="vestra-blog__created">{{ $post->published_at?->format('M j, Y') ?? '—' }}</span>
    </td>

    <td class="vestra-blog__td vestra-blog__td--updated">
        <span class="vestra-blog__created">{{ $post->updated_at?->format('M j, Y') ?? '—' }}</span>
        <span class="vestra-blog__row-meta">{{ $post->updated_at?->format('g:i A') }}</span>
    </td>

    <td class="vestra-blog__td vestra-blog__td--actions">
        <div class="vestra-blog__actions" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-blog__action-trigger" aria-label="Article actions" aria-haspopup="true">
                <x-filament::icon icon="heroicon-m-ellipsis-vertical" class="h-5 w-5" />
            </button>
            <div x-show="open" x-transition class="vestra-blog__action-menu" role="menu">
                <button
                    type="button"
                    wire:click="openDetailDrawer({{ $post->id }})"
                    class="vestra-blog__action-item"
                    role="menuitem"
                >
                    <x-filament::icon icon="heroicon-o-eye" class="h-4 w-4" />
                    <span>View Details</span>
                </button>
            </div>
        </div>
    </td>
</tr>

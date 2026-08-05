@props([
    'show' => false,
    'post' => null,
    'canEdit' => false,
    'canDelete' => false,
])

@php
$display = function ($value, string $fallback = 'Not provided') {
    if ($value === null) {
        return $fallback;
    }
    if (is_string($value) && trim($value) === '') {
        return $fallback;
    }

    return $value;
};
@endphp

<div
    class="vestra-blog-detail @if ($show) vestra-blog-detail--open @endif"
    x-data="{ open: @entangle('showDetailDrawer') }"
    x-show="open"
    x-cloak
    @keydown.escape.window="if (open) $wire.closeDetailDrawer()"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 translate-x-4"
    x-transition:enter-end="opacity-100 translate-x-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-x-0"
    x-transition:leave-end="opacity-0 translate-x-4"
    aria-label="Article details"
    role="dialog"
    aria-modal="true"
>
    <div class="vestra-blog-detail__overlay" wire:click="closeDetailDrawer"></div>

    <div class="vestra-blog-detail__panel">
        @if ($post)
            <div class="vestra-blog-detail__header">
                <div class="vestra-blog-detail__header-main">
                    <span class="vestra-blog-detail__avatar">
                        <x-filament::icon icon="heroicon-o-newspaper" class="h-5 w-5" />
                    </span>
                    <div class="vestra-blog-detail__header-text">
                        <h2 class="vestra-blog-detail__title">{{ $display($post['title'] ?? null, 'Article') }}</h2>
                        <p class="vestra-blog-detail__subtitle">{{ $display($post['slug'] ?? null) }}</p>
                    </div>
                </div>

                <button type="button" wire:click="closeDetailDrawer" class="vestra-blog-detail__close" aria-label="Close details">
                    <x-filament::icon icon="heroicon-o-x-mark" class="h-5 w-5" />
                </button>
            </div>

            <div class="vestra-blog-detail__body">
                <div class="vestra-blog-detail__badges">
                    <x-blog.status-badge :status="$post['status'] ?? null" />
                    <x-blog.visibility-badge :visibility="$post['visibility'] ?? null" />
                    @if ($post['is_featured'] ?? false)
                        <span class="vestra-blog__featured-pill">Featured</span>
                    @endif
                    @if ($post['is_pinned'] ?? false)
                        <span class="vestra-blog__featured-pill">Pinned</span>
                    @endif
                </div>

                <div class="vestra-blog-detail__quick-actions">
                    @if ($canEdit && ! empty($post['edit_url']))
                        <a href="{{ $post['edit_url'] }}" class="vestra-blog-detail__quick-action">
                            <x-filament::icon icon="heroicon-o-pencil-square" class="h-4 w-4" />
                            <span>Edit Article</span>
                        </a>
                    @endif
                    @if ($canDelete)
                        <button
                            type="button"
                            wire:click="deleteSelectedPost"
                            wire:confirm="Delete this article permanently? It will be removed from the public website."
                            class="vestra-blog-detail__quick-action vestra-blog-detail__quick-action--danger"
                        >
                            <x-filament::icon icon="heroicon-o-trash" class="h-4 w-4" />
                            <span>Delete</span>
                        </button>
                    @endif
                </div>

                @if (! empty($post['featured_image']))
                    <div class="vestra-blog-detail__section">
                        <figure class="vestra-blog-detail__hero-image">
                            <img src="{{ $post['featured_image'] }}" alt="{{ $post['title'] ?? 'Article image' }}" loading="lazy" />
                        </figure>
                    </div>
                @endif

                <div class="vestra-blog-detail__section">
                    <h3 class="vestra-blog-detail__section-title">Details</h3>
                    <dl class="vestra-blog-detail__definition-list">
                        <div class="vestra-blog-detail__definition-row"><dt>Title</dt><dd>{{ $display($post['title'] ?? null) }}</dd></div>
                        <div class="vestra-blog-detail__definition-row"><dt>Slug</dt><dd>{{ $display($post['slug'] ?? null) }}</dd></div>
                        <div class="vestra-blog-detail__definition-row"><dt>Author</dt><dd>{{ $display($post['author']['name'] ?? null) }}</dd></div>
                        <div class="vestra-blog-detail__definition-row"><dt>Category</dt><dd>{{ ! empty($post['categories']) ? collect($post['categories'])->pluck('name')->implode(', ') : 'Not provided' }}</dd></div>
                        <div class="vestra-blog-detail__definition-row"><dt>Publish Status</dt><dd>{{ $display($post['status_label'] ?? null) }}</dd></div>
                        <div class="vestra-blog-detail__definition-row"><dt>Featured</dt><dd>{{ ($post['is_featured'] ?? false) ? 'Yes' : 'No' }}</dd></div>
                        <div class="vestra-blog-detail__definition-row"><dt>Homepage</dt><dd>{{ ($post['show_on_homepage'] ?? false) ? 'Yes' : 'No' }}</dd></div>
                        <div class="vestra-blog-detail__definition-row"><dt>Pinned</dt><dd>{{ ($post['is_pinned'] ?? false) ? 'Yes' : 'No' }}</dd></div>
                        <div class="vestra-blog-detail__definition-row"><dt>Views</dt><dd>{{ number_format((int) ($post['view_count'] ?? 0)) }}</dd></div>
                        <div class="vestra-blog-detail__definition-row"><dt>Public URL</dt>
                            <dd>
                                @if (! empty($post['public_path']))
                                    <a href="{{ $post['public_url'] }}" target="_blank" rel="noopener noreferrer">{{ $post['public_path'] }}</a>
                                @else
                                    Not provided
                                @endif
                            </dd>
                        </div>
                        <div class="vestra-blog-detail__definition-row"><dt>Created Date</dt><dd>{{ $post['created_at']?->format('M j, Y g:i A') ?? 'Not provided' }}</dd></div>
                        <div class="vestra-blog-detail__definition-row"><dt>Updated Date</dt><dd>{{ $post['updated_at']?->format('M j, Y g:i A') ?? 'Not provided' }}</dd></div>
                        <div class="vestra-blog-detail__definition-row"><dt>Scheduled Date</dt><dd>{{ $post['scheduled_at']?->format('M j, Y g:i A') ?? 'Not provided' }}</dd></div>
                        <div class="vestra-blog-detail__definition-row"><dt>Published Date</dt><dd>{{ $post['published_at']?->format('M j, Y g:i A') ?? 'Not provided' }}</dd></div>
                    </dl>
                </div>

                <div class="vestra-blog-detail__section">
                    <h3 class="vestra-blog-detail__section-title">Excerpt</h3>
                    <p class="vestra-blog-detail__message">{{ $display($post['excerpt'] ?? null) }}</p>
                </div>

                <div class="vestra-blog-detail__section">
                    <h3 class="vestra-blog-detail__section-title">Content</h3>
                    <div class="vestra-blog-detail__content">{!! $post['content'] ?: '<p>Not provided</p>' !!}</div>
                </div>

                <div class="vestra-blog-detail__section">
                    <h3 class="vestra-blog-detail__section-title">SEO</h3>
                    <dl class="vestra-blog-detail__definition-list">
                        <div class="vestra-blog-detail__definition-row"><dt>Meta Title</dt><dd>{{ $display($post['meta_title'] ?? null) }}</dd></div>
                        <div class="vestra-blog-detail__definition-row"><dt>Meta Description</dt><dd>{{ $display($post['meta_description'] ?? null) }}</dd></div>
                        <div class="vestra-blog-detail__definition-row"><dt>OG Title</dt><dd>{{ $display($post['og_title'] ?? null) }}</dd></div>
                        <div class="vestra-blog-detail__definition-row"><dt>OG Description</dt><dd>{{ $display($post['og_description'] ?? null) }}</dd></div>
                        <div class="vestra-blog-detail__definition-row"><dt>Canonical URL</dt><dd>{{ $display($post['canonical_url'] ?? null) }}</dd></div>
                    </dl>
                </div>

                <div class="vestra-blog-detail__section">
                    <h3 class="vestra-blog-detail__section-title">Tags</h3>
                    @if (! empty($post['tags']))
                        <div class="vestra-blog-detail__tags">
                            @foreach ($post['tags'] as $tag)
                                <span class="vestra-blog__category-pill">{{ $tag['name'] }}</span>
                            @endforeach
                        </div>
                    @else
                        <p class="vestra-blog-detail__text">Not provided</p>
                    @endif
                </div>
            </div>
        @else
            <div class="vestra-blog-detail__empty">
                <p>Select an article to view details.</p>
            </div>
        @endif
    </div>
</div>

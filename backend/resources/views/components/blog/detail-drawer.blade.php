@props([
    'show' => false,
    'post' => null,
])

<div
    class="vestra-blog-detail @if ($show) vestra-blog-detail--open @endif"
    x-data="{ open: @js($show) }"
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
                        <h2 class="vestra-blog-detail__title">{{ $post['title'] ?? 'Article' }}</h2>
                        <p class="vestra-blog-detail__subtitle">{{ $post['slug'] ?? '' }}</p>
                    </div>
                </div>

                <button
                    type="button"
                    wire:click="closeDetailDrawer"
                    class="vestra-blog-detail__close"
                    aria-label="Close details"
                >
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
                        <div class="vestra-blog-detail__definition-row">
                            <dt>Title</dt>
                            <dd>{{ $post['title'] ?? '—' }}</dd>
                        </div>
                        <div class="vestra-blog-detail__definition-row">
                            <dt>Slug</dt>
                            <dd>{{ $post['slug'] ?? '—' }}</dd>
                        </div>
                        <div class="vestra-blog-detail__definition-row">
                            <dt>Author</dt>
                            <dd>{{ $post['author']['name'] ?? 'No author' }}</dd>
                        </div>
                        <div class="vestra-blog-detail__definition-row">
                            <dt>Reading time</dt>
                            <dd>{{ $post['reading_time_minutes'] ?? 0 }} min</dd>
                        </div>
                        <div class="vestra-blog-detail__definition-row">
                            <dt>Views</dt>
                            <dd>{{ number_format((int) ($post['view_count'] ?? 0)) }}</dd>
                        </div>
                        <div class="vestra-blog-detail__definition-row">
                            <dt>Published</dt>
                            <dd>{{ $post['published_at']?->format('M j, Y g:i A') ?? '—' }}</dd>
                        </div>
                        <div class="vestra-blog-detail__definition-row">
                            <dt>Scheduled</dt>
                            <dd>{{ $post['scheduled_at']?->format('M j, Y g:i A') ?? '—' }}</dd>
                        </div>
                        <div class="vestra-blog-detail__definition-row">
                            <dt>Created</dt>
                            <dd>{{ $post['created_at']?->format('M j, Y g:i A') ?? '—' }}</dd>
                        </div>
                        <div class="vestra-blog-detail__definition-row">
                            <dt>Updated</dt>
                            <dd>{{ $post['updated_at']?->format('M j, Y g:i A') ?? '—' }}</dd>
                        </div>
                    </dl>
                    @if (! empty($post['excerpt']))
                        <p class="vestra-blog-detail__text">{{ $post['excerpt'] }}</p>
                    @endif
                </div>

                <div class="vestra-blog-detail__section">
                    <h3 class="vestra-blog-detail__section-title">Categories</h3>
                    @if (! empty($post['categories']))
                        <div class="vestra-blog-detail__badges">
                            @foreach ($post['categories'] as $category)
                                <span class="vestra-blog__category-pill">{{ $category['name'] }}</span>
                            @endforeach
                        </div>
                    @else
                        <p class="vestra-blog-detail__text">No categories assigned.</p>
                    @endif
                </div>

                <div class="vestra-blog-detail__section">
                    <h3 class="vestra-blog-detail__section-title">Tags</h3>
                    @if (! empty($post['tags']))
                        <div class="vestra-blog-detail__badges">
                            @foreach ($post['tags'] as $tag)
                                <span class="vestra-blog__category-pill">{{ $tag['name'] }}</span>
                            @endforeach
                        </div>
                    @else
                        <p class="vestra-blog-detail__text">No tags assigned.</p>
                    @endif
                </div>

                <div class="vestra-blog-detail__section">
                    <h3 class="vestra-blog-detail__section-title">SEO</h3>
                    <dl class="vestra-blog-detail__definition-list">
                        <div class="vestra-blog-detail__definition-row">
                            <dt>Meta Title</dt>
                            <dd>{{ $post['meta_title'] ?? '—' }}</dd>
                        </div>
                        <div class="vestra-blog-detail__definition-row">
                            <dt>Meta Description</dt>
                            <dd>{{ $post['meta_description'] ?? '—' }}</dd>
                        </div>
                        <div class="vestra-blog-detail__definition-row">
                            <dt>Canonical URL</dt>
                            <dd>{{ $post['canonical_url'] ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>

                @if (! empty($post['gallery']))
                    <div class="vestra-blog-detail__section">
                        <h3 class="vestra-blog-detail__section-title">Gallery</h3>
                        <div class="vestra-blog-detail__images">
                            @foreach ($post['gallery'] as $image)
                                <figure class="vestra-blog-detail__image">
                                    <img src="{{ $image }}" alt="" loading="lazy" onerror="this.style.display='none'" />
                                </figure>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if (! empty($post['edit_url']))
                    <div class="vestra-blog-detail__section">
                        <a href="{{ $post['edit_url'] }}" class="vestra-button vestra-button--primary">
                            <x-filament::icon icon="heroicon-o-pencil-square" class="h-4 w-4" />
                            <span>Edit Article</span>
                        </a>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>

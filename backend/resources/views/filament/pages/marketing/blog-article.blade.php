@php
$options = $this->formOptions;
$isEditing = $this->isEditing;
$wordCount = $this->wordCount;
$existingImage = $this->existingFeaturedImageUrl;
$authors = $options['authors'] ?? [];
$categories = $options['categories'] ?? [];
$statuses = $options['statuses'] ?? [];
$visibilities = $options['visibilities'] ?? [];
$hasAuthors = $options['has_authors'] ?? false;
$backUrl = \App\Filament\Pages\Marketing\BlogPage::getUrl();
@endphp

<div class="vestra-workspace vestra-blog-article">
    <section class="vestra-blog-article__hero">
        <div class="vestra-blog-article__hero-main">
            <nav class="vestra-blog-article__breadcrumb" aria-label="Breadcrumb">
                <a href="{{ $backUrl }}">Blog</a>
                <span>/</span>
                <a href="{{ $backUrl }}">Articles</a>
                <span>/</span>
                <span>{{ $isEditing ? 'Edit Article' : 'New Article' }}</span>
            </nav>
            <h1 class="vestra-workspace__title">{{ $isEditing ? 'Edit Article' : 'New Article' }}</h1>
            <p class="vestra-workspace__welcome">
                {{ $isEditing
                    ? 'Update the article details below. Changes sync to the public website when saved.'
                    : 'Create a new blog article. Once published, it will be visible on the public website.' }}
            </p>
        </div>

        <div class="vestra-blog-article__hero-actions">
            <button type="button" wire:click="saveDraft" class="vestra-button vestra-button--secondary" wire:loading.attr="disabled">
                Save as Draft
            </button>
            <button type="button" wire:click="publish" class="vestra-button vestra-button--primary" wire:loading.attr="disabled">
                {{ ($form['status'] ?? '') === 'scheduled' ? 'Schedule' : 'Publish' }}
            </button>
            <div class="vestra-blog-article__more" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" class="vestra-button vestra-button--secondary vestra-blog-article__more-btn" @click="open = !open" aria-label="More actions">
                    <x-filament::icon icon="heroicon-m-ellipsis-horizontal" class="h-5 w-5" />
                </button>
                <div x-show="open" x-cloak class="vestra-blog-article__more-menu" role="menu">
                    <a href="{{ $backUrl }}" class="vestra-blog-article__more-item" role="menuitem">Back to Blog</a>
                    @if ($this->canDelete)
                        <button type="button" wire:click="deleteArticle" wire:confirm="Delete this article permanently? It will be removed from the public website." class="vestra-blog-article__more-item vestra-blog-article__more-item--danger" role="menuitem">
                            Delete Article
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <form wire:submit.prevent="save" class="vestra-blog-article__layout">
        <div class="vestra-blog-article__main">
            <section class="vestra-card vestra-blog-article__card">
                <h2 class="vestra-blog-article__card-title">Article Information</h2>

                <div class="vestra-blog-article__field">
                    <label for="article-title" class="vestra-blog-article__label">Article Title <span class="vestra-blog-article__required">*</span></label>
                    <input id="article-title" type="text" wire:model.live.debounce.300ms="form.title" class="vestra-blog-article__input @error('form.title') vestra-blog-article__input--error @enderror" placeholder="Enter article title" />
                    <span class="vestra-blog-article__hint">This title will appear on the blog and search results.</span>
                    @error('form.title')<span class="vestra-blog-article__error">{{ $message }}</span>@enderror
                </div>

                <div class="vestra-blog-article__field">
                    <label for="article-slug" class="vestra-blog-article__label">Slug <span class="vestra-blog-article__required">*</span></label>
                    <input id="article-slug" type="text" wire:model="form.slug" class="vestra-blog-article__input @error('form.slug') vestra-blog-article__input--error @enderror" placeholder="Enter slug (e.g. industry-trends-2026)" />
                    <span class="vestra-blog-article__hint">URL-friendly version. Use lowercase letters, numbers and hyphens.</span>
                    @error('form.slug')<span class="vestra-blog-article__error">{{ $message }}</span>@enderror
                </div>

                <div class="vestra-blog-article__field">
                    <label for="article-excerpt" class="vestra-blog-article__label">Excerpt</label>
                    <textarea id="article-excerpt" rows="3" wire:model="form.excerpt" class="vestra-blog-article__textarea" placeholder="Write a short summary of the article (optional)"></textarea>
                    <span class="vestra-blog-article__hint">This summary will appear in article lists and previews.</span>
                    @error('form.excerpt')<span class="vestra-blog-article__error">{{ $message }}</span>@enderror
                </div>

                <div class="vestra-blog-article__field">
                    <label class="vestra-blog-article__label">Content <span class="vestra-blog-article__required">*</span></label>
                    <div
                        class="vestra-blog-article__editor"
                        wire:ignore
                        x-data="blogRichEditor(@js($form['content'] ?? ''))"
                        x-init="init()"
                    >
                        <div class="vestra-blog-article__toolbar" role="toolbar" aria-label="Formatting">
                            <button type="button" @click="format('bold')" title="Bold"><strong>B</strong></button>
                            <button type="button" @click="format('italic')" title="Italic"><em>I</em></button>
                            <button type="button" @click="format('underline')" title="Underline"><u>U</u></button>
                            <span class="vestra-blog-article__toolbar-sep"></span>
                            <button type="button" @click="formatBlock('h2')" title="Heading">H2</button>
                            <button type="button" @click="formatBlock('p')" title="Paragraph">P</button>
                            <span class="vestra-blog-article__toolbar-sep"></span>
                            <button type="button" @click="format('insertUnorderedList')" title="Bullets">• List</button>
                            <button type="button" @click="format('insertOrderedList')" title="Numbered">1. List</button>
                            <button type="button" @click="formatBlock('blockquote')" title="Quote">Quote</button>
                            <span class="vestra-blog-article__toolbar-sep"></span>
                            <button type="button" @click="insertLink()" title="Link">Link</button>
                            <button type="button" @click="insertImage()" title="Image">Image</button>
                            <button type="button" @click="insertTable()" title="Table">Table</button>
                            <button type="button" @click="formatBlock('pre')" title="Code">Code</button>
                        </div>
                        <div
                            x-ref="editor"
                            class="vestra-blog-article__editor-body"
                            contenteditable="true"
                            role="textbox"
                            aria-multiline="true"
                            aria-label="Article content"
                            data-placeholder="Write your article content here..."
                            @input="sync()"
                            @blur="sync()"
                        ></div>
                        <div class="vestra-blog-article__editor-footer">
                            <span>{{ number_format($wordCount) }} WORDS</span>
                        </div>
                    </div>
                    @error('form.content')<span class="vestra-blog-article__error">{{ $message }}</span>@enderror
                </div>
            </section>

            <section class="vestra-card vestra-blog-article__card">
                <h2 class="vestra-blog-article__card-title">Featured Image</h2>
                @if ($existingImage)
                    <div class="vestra-blog-article__image-preview">
                        <img src="{{ $existingImage }}" alt="Featured image preview" />
                        <button type="button" wire:click="clearFeaturedImage" class="vestra-button vestra-button--secondary">Remove</button>
                    </div>
                @endif
                <button type="button" wire:click="openFeaturedMediaPicker" class="vestra-blog-article__dropzone" style="width:100%;cursor:pointer;border:0;background:transparent">
                    <x-filament::icon icon="heroicon-o-photo" class="h-8 w-8" />
                    <span>Choose Existing or Upload New from Media Library</span>
                    <span class="vestra-blog-article__hint">Recommended size: 1200x630px (JPG, PNG or WebP)</span>
                </button>
                <p class="vestra-blog-article__hint">This image will appear on the blog and when shared on social media.</p>
            </section>

            <section class="vestra-card vestra-blog-article__card">
                <h2 class="vestra-blog-article__card-title">Article Options</h2>
                <div class="vestra-blog-article__toggles">
                    <label class="vestra-blog-article__toggle">
                        <input type="checkbox" wire:model="form.is_featured" class="vestra-blog-article__toggle-input" />
                        <span class="vestra-blog-article__toggle-track" aria-hidden="true"></span>
                        <span>
                            <strong>Featured Article</strong>
                            <small>Featured articles may be shown on the homepage.</small>
                        </span>
                    </label>
                    <label class="vestra-blog-article__toggle">
                        <input type="checkbox" wire:model="form.show_on_homepage" class="vestra-blog-article__toggle-input" />
                        <span class="vestra-blog-article__toggle-track" aria-hidden="true"></span>
                        <span>
                            <strong>Show in Homepage</strong>
                            <small>Display this article in the blog section on homepage.</small>
                        </span>
                    </label>
                    <label class="vestra-blog-article__toggle">
                        <input type="checkbox" wire:model="form.is_pinned" class="vestra-blog-article__toggle-input" />
                        <span class="vestra-blog-article__toggle-track" aria-hidden="true"></span>
                        <span>
                            <strong>Pin Article</strong>
                            <small>Pinned articles will stay at the top of the list.</small>
                        </span>
                    </label>
                </div>
            </section>
        </div>

        <aside class="vestra-blog-article__sidebar">
            <section class="vestra-card vestra-blog-article__card">
                <h2 class="vestra-blog-article__card-title">Publishing</h2>
                <div class="vestra-blog-article__field">
                    <label for="article-status" class="vestra-blog-article__label">Status <span class="vestra-blog-article__required">*</span></label>
                    <select id="article-status" wire:model.live="form.status" class="vestra-blog-article__select @error('form.status') vestra-blog-article__input--error @enderror">
                        @foreach ($statuses as $status)
                            <option value="{{ $status['value'] }}">{{ $status['label'] }}</option>
                        @endforeach
                    </select>
                    <span class="vestra-blog-article__hint">Set the current status of this article.</span>
                    @error('form.status')<span class="vestra-blog-article__error">{{ $message }}</span>@enderror
                </div>
                <div class="vestra-blog-article__field">
                    <label for="article-schedule" class="vestra-blog-article__label">Publish Date</label>
                    <input
                        id="article-schedule"
                        type="datetime-local"
                        wire:model="form.scheduled_at"
                        class="vestra-blog-article__input @error('form.scheduled_at') vestra-blog-article__input--error @enderror"
                    />
                    <span class="vestra-blog-article__hint">When set with Scheduled status, the article will be published automatically.</span>
                    @error('form.scheduled_at')<span class="vestra-blog-article__error">{{ $message }}</span>@enderror
                </div>
                <label class="vestra-blog-article__toggle">
                    <input type="checkbox" wire:model="form.allow_comments" class="vestra-blog-article__toggle-input" />
                    <span class="vestra-blog-article__toggle-track" aria-hidden="true"></span>
                    <span>
                        <strong>Allow Comments</strong>
                        <small>Allow readers to comment on this article.</small>
                    </span>
                </label>
            </section>

            <section class="vestra-card vestra-blog-article__card">
                <h2 class="vestra-blog-article__card-title">Organization</h2>
                <div class="vestra-blog-article__field">
                    <label for="article-categories" class="vestra-blog-article__label">Categories <span class="vestra-blog-article__required">*</span></label>
                    <select id="article-categories" wire:model="categoryIds" multiple class="vestra-blog-article__select vestra-blog-article__select--multi @error('categoryIds') vestra-blog-article__input--error @enderror">
                        @forelse ($categories as $category)
                            <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                        @empty
                            <option value="" disabled>No categories available</option>
                        @endforelse
                    </select>
                    <span class="vestra-blog-article__hint">Choose one or more categories.</span>
                    @error('categoryIds')<span class="vestra-blog-article__error">{{ $message }}</span>@enderror
                </div>
                <div class="vestra-blog-article__field">
                    <label for="article-author" class="vestra-blog-article__label">Author <span class="vestra-blog-article__required">*</span></label>
                    <select id="article-author" wire:model="form.author_id" class="vestra-blog-article__select @error('form.author_id') vestra-blog-article__input--error @enderror">
                        <option value="">{{ $hasAuthors ? 'Select author' : 'No authors available' }}</option>
                        @foreach ($authors as $author)
                            <option value="{{ $author['id'] }}">{{ $author['name'] }}</option>
                        @endforeach
                    </select>
                    <span class="vestra-blog-article__hint">Select the author of this article.</span>
                    @error('form.author_id')<span class="vestra-blog-article__error">{{ $message }}</span>@enderror
                </div>
                <div class="vestra-blog-article__field">
                    <label for="article-tags" class="vestra-blog-article__label">Tags</label>
                    <div class="vestra-blog-article__tags">
                        @foreach ($tagInput as $index => $tag)
                            <span class="vestra-blog-article__tag">
                                {{ $tag }}
                                <button type="button" wire:click="removeTag({{ $index }})" aria-label="Remove tag">&times;</button>
                            </span>
                        @endforeach
                    </div>
                    <div class="vestra-blog-article__tag-input">
                        <input id="article-tags" type="text" wire:model="tagDraft" wire:keydown.enter.prevent="addTag" class="vestra-blog-article__input" placeholder="Enter tags and press Enter" />
                        <button type="button" wire:click="addTag" class="vestra-button vestra-button--secondary">Add</button>
                    </div>
                    <span class="vestra-blog-article__hint">Add relevant tags to help readers find your article.</span>
                </div>
            </section>

            <section class="vestra-card vestra-blog-article__card">
                <h2 class="vestra-blog-article__card-title">SEO & Visibility</h2>
                <div class="vestra-blog-article__field">
                    <label for="article-meta-title" class="vestra-blog-article__label">Meta Title</label>
                    <input id="article-meta-title" type="text" wire:model="form.meta_title" class="vestra-blog-article__input" placeholder="Enter meta title (optional)" />
                    <span class="vestra-blog-article__hint">Recommended: 50-60 characters.</span>
                </div>
                <div class="vestra-blog-article__field">
                    <label for="article-meta-description" class="vestra-blog-article__label">Meta Description</label>
                    <textarea id="article-meta-description" rows="3" wire:model="form.meta_description" class="vestra-blog-article__textarea" placeholder="Enter meta description (optional)"></textarea>
                    <span class="vestra-blog-article__hint">Recommended: 150-160 characters.</span>
                </div>
                <div class="vestra-blog-article__field">
                    <label for="article-og-title" class="vestra-blog-article__label">Open Graph Title</label>
                    <input id="article-og-title" type="text" wire:model="form.og_title" class="vestra-blog-article__input" placeholder="Optional social sharing title" />
                </div>
                <div class="vestra-blog-article__field">
                    <label for="article-og-description" class="vestra-blog-article__label">Open Graph Description</label>
                    <textarea id="article-og-description" rows="2" wire:model="form.og_description" class="vestra-blog-article__textarea" placeholder="Optional social sharing description"></textarea>
                </div>
                <div class="vestra-blog-article__field">
                    <label for="article-canonical" class="vestra-blog-article__label">Canonical URL</label>
                    <input id="article-canonical" type="url" wire:model="form.canonical_url" class="vestra-blog-article__input" placeholder="https://…" />
                </div>
                <div class="vestra-blog-article__field">
                    <label class="vestra-blog-article__label">Slug Preview</label>
                    <code class="vestra-blog-article__slug-preview">/blog/{{ $form['slug'] ?: 'your-slug' }}</code>
                </div>
                <div class="vestra-blog-article__field">
                    <label for="article-visibility" class="vestra-blog-article__label">Visibility</label>
                    <select id="article-visibility" wire:model="form.visibility" class="vestra-blog-article__select">
                        @foreach ($visibilities as $visibility)
                            <option value="{{ $visibility['value'] }}">{{ $visibility['label'] }}</option>
                        @endforeach
                    </select>
                    <span class="vestra-blog-article__hint">Public articles are visible to everyone on the website.</span>
                </div>
            </section>
        </aside>
    </form>
</div>

@livewire(\App\Livewire\Admin\MediaAssetPicker::class)

@script
<script>
    Alpine.data('blogRichEditor', (initial) => ({
        html: initial || '',
        init() {
            this.$refs.editor.innerHTML = this.html || '';
            this.sync();
            Livewire.on('blog-insert-image-url', (payload) => {
                const url = payload?.url ?? payload?.[0]?.url ?? null;
                if (url) {
                    document.execCommand('insertImage', false, url);
                    this.sync();
                }
            });
        },
        format(cmd) {
            document.execCommand(cmd, false, null);
            this.sync();
        },
        formatBlock(tag) {
            document.execCommand('formatBlock', false, tag);
            this.sync();
        },
        insertLink() {
            const url = window.prompt('Enter URL');
            if (url) {
                document.execCommand('createLink', false, url);
                this.sync();
            }
        },
        insertImage() {
            this.$wire.openInlineMediaPicker();
        },
        insertTable() {
            document.execCommand(
                'insertHTML',
                false,
                '<table><thead><tr><th>Header</th><th>Header</th></tr></thead><tbody><tr><td>Cell</td><td>Cell</td></tr></tbody></table><p></p>'
            );
            this.sync();
        },
        sync() {
            this.html = this.$refs.editor.innerHTML;
            this.$wire.set('form.content', this.html === '<br>' ? '' : this.html);
        },
    }));
</script>
@endscript

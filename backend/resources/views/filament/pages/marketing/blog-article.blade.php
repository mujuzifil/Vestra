@php
$options = $this->formOptions;
$isEditing = $this->isEditing;
$wordCount = $this->wordCount;
$existingImage = $this->existingFeaturedImageUrl;
$authors = $options['authors'] ?? [];
$statuses = $options['statuses'] ?? [];
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
                            <button type="button" @click="format('bold')" @mouseup="updateToolbarState()" @keyup="updateToolbarState()" title="Bold" aria-pressed="false" :aria-pressed="active.bold" :class="{ 'is-active': active.bold }"><strong>B</strong></button>
                            <button type="button" @click="format('italic')" @mouseup="updateToolbarState()" @keyup="updateToolbarState()" title="Italic" aria-pressed="false" :aria-pressed="active.italic" :class="{ 'is-active': active.italic }"><em>I</em></button>
                            <button type="button" @click="format('underline')" @mouseup="updateToolbarState()" @keyup="updateToolbarState()" title="Underline" aria-pressed="false" :aria-pressed="active.underline" :class="{ 'is-active': active.underline }"><u>U</u></button>
                            <span class="vestra-blog-article__toolbar-sep"></span>
                            <button type="button" @click="formatBlock('h2')" @mouseup="updateToolbarState()" @keyup="updateToolbarState()" title="Heading" aria-pressed="false" :aria-pressed="active.h2" :class="{ 'is-active': active.h2 }">H2</button>
                            <button type="button" @click="formatBlock('p')" @mouseup="updateToolbarState()" @keyup="updateToolbarState()" title="Paragraph" aria-pressed="false" :aria-pressed="active.p" :class="{ 'is-active': active.p }">P</button>
                            <span class="vestra-blog-article__toolbar-sep"></span>
                            <button type="button" @click="format('insertUnorderedList')" @mouseup="updateToolbarState()" @keyup="updateToolbarState()" title="Bullets" aria-pressed="false" :aria-pressed="active.ul" :class="{ 'is-active': active.ul }">• List</button>
                            <button type="button" @click="format('insertOrderedList')" @mouseup="updateToolbarState()" @keyup="updateToolbarState()" title="Numbered" aria-pressed="false" :aria-pressed="active.ol" :class="{ 'is-active': active.ol }">1. List</button>
                            <button type="button" @click="formatBlock('blockquote')" @mouseup="updateToolbarState()" @keyup="updateToolbarState()" title="Quote" aria-pressed="false" :aria-pressed="active.blockquote" :class="{ 'is-active': active.blockquote }">Quote</button>
                            <span class="vestra-blog-article__toolbar-sep"></span>
                            <button type="button" @click="insertLink()" @mouseup="updateToolbarState()" @keyup="updateToolbarState()" title="Link">Link</button>
                            <button type="button" @click="insertImage()" @mouseup="updateToolbarState()" @keyup="updateToolbarState()" title="Image">Image</button>
                            <button type="button" @click="insertTable()" @mouseup="updateToolbarState()" @keyup="updateToolbarState()" title="Table">Table</button>
                            <button type="button" @click="formatBlock('pre')" @mouseup="updateToolbarState()" @keyup="updateToolbarState()" title="Code" aria-pressed="false" :aria-pressed="active.pre" :class="{ 'is-active': active.pre }">Code</button>
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
                            @keyup="updateToolbarState()"
                            @mouseup="updateToolbarState()"
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
        </aside>
    </form>
</div>

@livewire(\App\Livewire\Admin\MediaAssetPicker::class)

@script
<script>
    Alpine.data('blogRichEditor', (initial) => ({
        html: initial || '',
        active: {
            bold: false,
            italic: false,
            underline: false,
            h2: false,
            p: false,
            ul: false,
            ol: false,
            blockquote: false,
            pre: false,
        },
        init() {
            this.$refs.editor.innerHTML = this.html || '';
            this.sync();
            document.addEventListener('selectionchange', () => this.updateToolbarState());
            Livewire.on('blog-insert-image-url', (payload) => {
                const url = payload?.url ?? payload?.[0]?.url ?? null;
                if (url) {
                    document.execCommand('insertImage', false, url);
                    this.sync();
                }
            });
        },
        updateToolbarState() {
            const editor = this.$refs.editor;
            if (! editor) {
                return;
            }

            const selection = window.getSelection();
            if (! selection || selection.rangeCount === 0) {
                return;
            }

            const anchor = selection.anchorNode;
            if (! anchor || ! editor.contains(anchor)) {
                Object.keys(this.active).forEach((key) => {
                    this.active[key] = false;
                });

                return;
            }

            this.active.bold = document.queryCommandState('bold');
            this.active.italic = document.queryCommandState('italic');
            this.active.underline = document.queryCommandState('underline');
            this.active.ul = document.queryCommandState('insertUnorderedList');
            this.active.ol = document.queryCommandState('insertOrderedList');

            const block = (document.queryCommandValue('formatBlock') || '').toLowerCase();
            this.active.h2 = block.includes('h2');
            this.active.p = block.includes('p') || block === 'div';
            this.active.blockquote = block.includes('blockquote');
            this.active.pre = block.includes('pre');
        },
        format(cmd) {
            document.execCommand(cmd, false, null);
            this.sync();
            this.updateToolbarState();
        },
        formatBlock(tag) {
            document.execCommand('formatBlock', false, tag);
            this.sync();
            this.updateToolbarState();
        },
        insertLink() {
            const url = window.prompt('Enter URL');
            if (url) {
                document.execCommand('createLink', false, url);
                this.sync();
                this.updateToolbarState();
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
            this.updateToolbarState();
        },
        sync() {
            this.html = this.$refs.editor.innerHTML;
            this.$wire.set('form.content', this.html === '<br>' ? '' : this.html);
        },
    }));
</script>
@endscript

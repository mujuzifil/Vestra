<?php

namespace App\Filament\Pages\Marketing;

use App\Enums\BlogPostStatus;
use App\Enums\BlogPostVisibility;
use App\Models\BlogPost;
use App\Services\Admin\BlogAdminService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;

class BlogArticlePage extends Page
{
    protected static string $layout = 'filament.layouts.crm';

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?string $navigationLabel = 'New Article';

    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';

    protected static ?int $navigationSort = 2;

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.marketing.blog-article';

    protected static ?string $slug = 'marketing/blog/article';

    #[Url]
    public ?int $id = null;

    /**
     * @var array<string, mixed>
     */
    public array $form = [];

    /** @var array<int, int|string> */
    public array $categoryIds = [];

    /** @var array<int, string> */
    public array $tagInput = [];

    public string $tagDraft = '';

    public ?int $featuredMediaAssetId = null;

    public ?string $featuredMediaUrl = null;

    public bool $removeFeaturedImage = false;

    /** @var array<int, int> */
    public array $pendingInlineAssetIds = [];

    public string $pickerTarget = 'featured';

    public function getTitle(): string
    {
        return $this->id ? 'Edit Article' : 'New Article';
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }

    public function mount(): void
    {
        if ($this->id) {
            $post = BlogPost::query()->findOrFail($this->id);
            Gate::authorize('update', $post);
            $this->hydrateFromPost($post);
        } else {
            Gate::authorize('create', BlogPost::class);
            $this->resetForm();
        }
    }

    public function getBlogServiceProperty(): BlogAdminService
    {
        return app(BlogAdminService::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function getFormOptionsProperty(): array
    {
        return $this->getBlogServiceProperty()->getFormOptions(auth()->user());
    }

    public function getExistingFeaturedImageUrlProperty(): ?string
    {
        if ($this->removeFeaturedImage) {
            return null;
        }

        if ($this->featuredMediaUrl) {
            return $this->featuredMediaUrl;
        }

        if (! $this->id) {
            return null;
        }

        $post = BlogPost::query()->with('featuredMediaAsset')->find($this->id);

        return $post?->featuredMediaAsset?->url()
            ?? ($post?->featured_image ? asset('storage/'.$post->featured_image) : null);
    }

    public function getWordCountProperty(): int
    {
        return str_word_count(strip_tags((string) ($this->form['content'] ?? '')));
    }

    public function getIsEditingProperty(): bool
    {
        return $this->id !== null;
    }

    public function getCanDeleteProperty(): bool
    {
        if (! $this->id) {
            return false;
        }

        $post = BlogPost::query()->find($this->id);

        return $post !== null && Gate::allows('delete', $post);
    }

    public function updatedFormTitle($value): void
    {
        if ($this->id) {
            return;
        }

        $this->form['slug'] = Str::slug((string) $value);
    }

    public function addTag(): void
    {
        $tag = trim($this->tagDraft);
        if ($tag === '') {
            return;
        }

        if (! in_array($tag, $this->tagInput, true)) {
            $this->tagInput[] = $tag;
        }

        $this->tagDraft = '';
    }

    public function removeTag(int $index): void
    {
        unset($this->tagInput[$index]);
        $this->tagInput = array_values($this->tagInput);
    }

    public function openFeaturedMediaPicker(): void
    {
        $this->pickerTarget = 'featured';
        $this->dispatch('open-media-picker', context: 'blog-featured');
    }

    public function openInlineMediaPicker(): void
    {
        $this->pickerTarget = 'inline';
        $this->dispatch('open-media-picker', context: 'blog-inline');
    }

    #[On('media-asset-selected')]
    public function handleMediaAssetSelected(int $id, string $context = 'default', ?string $url = null): void
    {
        if ($context === 'blog-inline' || $this->pickerTarget === 'inline') {
            $this->pendingInlineAssetIds[] = $id;
            $this->pendingInlineAssetIds = array_values(array_unique($this->pendingInlineAssetIds));
            $this->dispatch('blog-insert-image-url', url: $url);

            return;
        }

        if ($context !== 'blog-featured' && $context !== 'default' && $this->pickerTarget !== 'featured') {
            return;
        }

        $this->featuredMediaAssetId = $id;
        $this->featuredMediaUrl = $url;
        $this->removeFeaturedImage = false;
    }

    public function clearFeaturedImage(): void
    {
        $this->featuredMediaAssetId = null;
        $this->featuredMediaUrl = null;
        $this->removeFeaturedImage = true;
    }

    public function saveDraft(): void
    {
        $this->form['status'] = BlogPostStatus::DRAFT->value;
        $this->save(redirectAfter: true);
    }

    public function publish(): void
    {
        if (($this->form['status'] ?? '') === BlogPostStatus::SCHEDULED->value) {
            $this->save(redirectAfter: true);

            return;
        }

        $this->form['status'] = BlogPostStatus::PUBLISHED->value;
        $this->save(redirectAfter: true);
    }

    public function save(bool $redirectAfter = true): void
    {
        $validated = $this->validate($this->formRules());
        $service = $this->getBlogServiceProperty();

        $featuredId = $this->removeFeaturedImage ? null : $this->featuredMediaAssetId;

        if ($this->id) {
            $post = BlogPost::query()->findOrFail($this->id);
            Gate::authorize('update', $post);
            $post = $service->updatePost(
                $post,
                $validated['form'],
                $this->categoryIds,
                $this->tagInput,
                $featuredId,
                $this->removeFeaturedImage,
                $this->pendingInlineAssetIds
            );
            Notification::make()->title('Article updated')->success()->send();
        } else {
            Gate::authorize('create', BlogPost::class);
            $post = $service->createPost(
                $validated['form'],
                $this->categoryIds,
                $this->tagInput,
                $featuredId,
                $this->pendingInlineAssetIds
            );
            Notification::make()->title('Article created')->success()->send();
            $this->id = $post->id;
        }

        $this->removeFeaturedImage = false;
        $this->pendingInlineAssetIds = [];
        $this->hydrateFromPost($post);

        if ($redirectAfter) {
            $this->redirect(BlogPage::getUrl(), navigate: true);
        }
    }

    public function deleteArticle(): void
    {
        if (! $this->id) {
            return;
        }

        $post = BlogPost::query()->findOrFail($this->id);
        Gate::authorize('delete', $post);

        $this->getBlogServiceProperty()->deletePost($post);

        Notification::make()->title('Article deleted')->success()->send();

        $this->redirect(BlogPage::getUrl(), navigate: true);
    }

    /**
     * @return array<string, mixed>
     */
    protected function formRules(): array
    {
        return [
            'form.title' => ['required', 'string', 'max:255'],
            'form.slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('blog_posts', 'slug')->ignore($this->id),
            ],
            'form.excerpt' => ['nullable', 'string', 'max:2000'],
            'form.content' => ['required', 'string'],
            'form.author_id' => ['required', 'integer', 'exists:blog_authors,id'],
            'form.status' => ['required', Rule::in(array_column(BlogPostStatus::cases(), 'value'))],
            'form.visibility' => ['required', Rule::in(array_column(BlogPostVisibility::cases(), 'value'))],
            'form.scheduled_at' => [
                Rule::requiredIf(fn () => ($this->form['status'] ?? '') === BlogPostStatus::SCHEDULED->value),
                'nullable',
                'date',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (($this->form['status'] ?? '') !== BlogPostStatus::SCHEDULED->value) {
                        return;
                    }

                    if (! filled($value)) {
                        return;
                    }

                    if (\Carbon\Carbon::parse((string) $value)->lte(now())) {
                        $fail('The scheduled publish time must be in the future.');
                    }
                },
            ],
            'form.published_at' => ['nullable', 'date'],
            'form.is_featured' => ['sometimes', 'boolean'],
            'form.show_on_homepage' => ['sometimes', 'boolean'],
            'form.is_pinned' => ['sometimes', 'boolean'],
            'form.allow_comments' => ['sometimes', 'boolean'],
            'form.meta_title' => ['nullable', 'string', 'max:255'],
            'form.meta_description' => ['nullable', 'string', 'max:2000'],
            'form.canonical_url' => ['nullable', 'string', 'max:500'],
            'form.og_title' => ['nullable', 'string', 'max:255'],
            'form.og_description' => ['nullable', 'string', 'max:2000'],
            'categoryIds' => ['required', 'array', 'min:1'],
            'categoryIds.*' => ['integer', 'exists:blog_categories,id'],
            'featuredMediaAssetId' => ['nullable', 'integer', 'exists:media_assets,id'],
        ];
    }

    protected function hydrateFromPost(BlogPost $post): void
    {
        $post->load(['categories', 'tags', 'featuredMediaAsset']);

        $this->form = [
            'title' => $post->title ?? '',
            'slug' => $post->slug ?? '',
            'excerpt' => $post->excerpt ?? '',
            'content' => $post->content ?? '',
            'author_id' => $post->author_id,
            'status' => $post->status instanceof BlogPostStatus ? $post->status->value : (string) $post->status,
            'visibility' => $post->visibility instanceof BlogPostVisibility ? $post->visibility->value : (string) $post->visibility,
            'scheduled_at' => $post->scheduled_at?->format('Y-m-d\TH:i') ?? '',
            'published_at' => $post->published_at?->format('Y-m-d\TH:i') ?? '',
            'is_featured' => (bool) $post->is_featured,
            'show_on_homepage' => (bool) $post->show_on_homepage,
            'is_pinned' => (bool) $post->is_pinned,
            'allow_comments' => (bool) ($post->allow_comments ?? true),
            'meta_title' => $post->meta_title ?? '',
            'meta_description' => $post->meta_description ?? '',
            'canonical_url' => $post->canonical_url ?? '',
            'og_title' => $post->og_title ?? '',
            'og_description' => $post->og_description ?? '',
        ];
        $this->categoryIds = $post->categories->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->tagInput = $post->tags->pluck('name')->all();
        $this->featuredMediaAssetId = $post->featured_media_asset_id;
        $this->featuredMediaUrl = $post->featuredMediaAsset?->url();
        $this->removeFeaturedImage = false;
        $this->pendingInlineAssetIds = [];
    }

    protected function resetForm(): void
    {
        $options = $this->getFormOptionsProperty();

        $this->form = [
            'title' => '',
            'slug' => '',
            'excerpt' => '',
            'content' => '',
            'author_id' => $options['default_author_id'] ?? null,
            'status' => BlogPostStatus::DRAFT->value,
            'visibility' => BlogPostVisibility::PUBLIC->value,
            'scheduled_at' => '',
            'published_at' => '',
            'is_featured' => false,
            'show_on_homepage' => false,
            'is_pinned' => false,
            'allow_comments' => true,
            'meta_title' => '',
            'meta_description' => '',
            'canonical_url' => '',
            'og_title' => '',
            'og_description' => '',
        ];
        $this->categoryIds = [];
        $this->tagInput = [];
        $this->tagDraft = '';
        $this->featuredMediaAssetId = null;
        $this->featuredMediaUrl = null;
        $this->removeFeaturedImage = false;
        $this->pendingInlineAssetIds = [];
    }
}

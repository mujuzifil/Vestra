<?php

namespace App\Filament\Pages\Marketing;

use App\Filament\Resources\BlogPostResource;
use App\Models\BlogPost;
use App\Services\Admin\BlogAdminService;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class BlogPage extends Page
{
    use WithPagination;

    protected static string $layout = 'filament.layouts.crm';

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?string $navigationLabel = 'Blog';

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.marketing.blog';

    protected static ?string $slug = 'marketing/blog';

    #[Url(as: 'search')]
    public string $search = '';

    #[Url(as: 'status')]
    public array $statusFilter = [];

    #[Url(as: 'author')]
    public ?int $authorFilter = null;

    #[Url(as: 'category')]
    public array $categoryFilter = [];

    #[Url(as: 'date_from')]
    public ?string $dateFrom = null;

    #[Url(as: 'date_until')]
    public ?string $dateUntil = null;

    #[Url(as: 'sort')]
    public string $sortField = 'created_at';

    #[Url(as: 'direction')]
    public string $sortDirection = 'desc';

    public int $perPage = 15;

    public bool $showDetailDrawer = false;

    public ?int $selectedPostId = null;

    public function getTitle(): string
    {
        return 'Blog';
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }

    public function mount(): void
    {
        Gate::authorize('viewAny', BlogPost::class);
    }

    public function getBlogServiceProperty(): BlogAdminService
    {
        return app(BlogAdminService::class);
    }

    public function getPostsProperty(): mixed
    {
        return $this->getBlogServiceProperty()
            ->paginatePosts($this->buildFilters(), $this->sortField, $this->sortDirection, $this->perPage);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getKpiCardsProperty(): array
    {
        return $this->getBlogServiceProperty()->getKpiCards();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getSelectedPostProperty(): ?array
    {
        if (empty($this->selectedPostId)) {
            return null;
        }

        $post = BlogPost::query()->find($this->selectedPostId);

        if ($post === null) {
            return null;
        }

        Gate::authorize('view', $post);

        return $this->getBlogServiceProperty()->getDetail($post);
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilterOptionsProperty(): array
    {
        return $this->getBlogServiceProperty()->getFilterOptions();
    }

    public function getCanCreateProperty(): bool
    {
        return Gate::allows('create', BlogPost::class);
    }

    public function getCreateUrlProperty(): string
    {
        return BlogPostResource::getUrl('create');
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildFilters(): array
    {
        return [
            'search' => $this->search,
            'status' => $this->statusFilter,
            'author' => $this->authorFilter,
            'category' => $this->categoryFilter,
            'date_from' => $this->dateFrom,
            'date_until' => $this->dateUntil,
        ];
    }

    public function openDetailDrawer(int $id): void
    {
        $post = BlogPost::query()->findOrFail($id);
        Gate::authorize('view', $post);

        $this->selectedPostId = $id;
        $this->showDetailDrawer = true;
    }

    public function closeDetailDrawer(): void
    {
        $this->showDetailDrawer = false;
        $this->selectedPostId = null;
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->statusFilter = [];
        $this->authorFilter = null;
        $this->categoryFilter = [];
        $this->dateFrom = null;
        $this->dateUntil = null;
        $this->sortField = 'created_at';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedAuthorFilter(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateUntil(): void
    {
        $this->resetPage();
    }

    public function hasActiveFilters(): bool
    {
        return filled($this->search)
            || filled($this->statusFilter)
            || filled($this->authorFilter)
            || filled($this->categoryFilter)
            || filled($this->dateFrom)
            || filled($this->dateUntil);
    }

    public function getExportUrl(string $format): string
    {
        return route('filament.admin.marketing.blog.export', [
            'format' => $format,
            'search' => $this->search ?: null,
            'status' => $this->statusFilter ?: null,
            'author' => $this->authorFilter,
            'category' => $this->categoryFilter ?: null,
            'date_from' => $this->dateFrom,
            'date_until' => $this->dateUntil,
        ]);
    }
}

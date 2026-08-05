<?php

namespace App\Filament\Pages\Products;

use App\Models\Category;
use App\Services\Admin\CategoryAdminService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class CategoriesPage extends Page
{
    use WithPagination;

    protected static string $layout = 'filament.layouts.crm';

    protected static ?string $navigationGroup = 'Products';

    protected static ?string $navigationLabel = 'Categories';

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.products.categories';

    protected static ?string $slug = 'products/categories';

    #[Url(as: 'search')]
    public string $search = '';

    #[Url(as: 'status')]
    public array $statusFilter = [];

    #[Url(as: 'date_from')]
    public ?string $dateFrom = null;

    #[Url(as: 'date_until')]
    public ?string $dateUntil = null;

    #[Url(as: 'sort')]
    public string $sortField = 'sort_order';

    #[Url(as: 'direction')]
    public string $sortDirection = 'asc';

    public int $perPage = 15;

    public bool $showDetailDrawer = false;

    public ?int $selectedCategoryId = null;

    public bool $showFormModal = false;

    public ?int $editingCategoryId = null;

    /**
     * @var array<string, mixed>
     */
    public array $form = [];

    public function getTitle(): string
    {
        return 'Categories';
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }

    public function mount(): void
    {
        Gate::authorize('viewAny', Category::class);
        $this->resetForm();
    }

    public function getCategoryServiceProperty(): CategoryAdminService
    {
        return app(CategoryAdminService::class);
    }

    public function getCategoriesProperty(): mixed
    {
        return $this->getCategoryServiceProperty()
            ->paginateCategories($this->buildFilters(), $this->sortField, $this->sortDirection, $this->perPage);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getKpiCardsProperty(): array
    {
        return $this->getCategoryServiceProperty()->getKpiCards();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getSelectedCategoryProperty(): ?array
    {
        if (empty($this->selectedCategoryId)) {
            return null;
        }

        $category = Category::query()->find($this->selectedCategoryId);

        if ($category === null) {
            return null;
        }

        Gate::authorize('view', $category);

        return $this->getCategoryServiceProperty()->getDetail($category);
    }

    /**
     * @return array<string, mixed>
     */
    public function getFormOptionsProperty(): array
    {
        if (! $this->showFormModal) {
            return [
                'parents' => [],
                'statuses' => [
                    ['value' => 'active', 'label' => 'Active'],
                    ['value' => 'inactive', 'label' => 'Inactive'],
                ],
            ];
        }

        return $this->getCategoryServiceProperty()->getFormOptions($this->editingCategoryId);
    }

    public function getCanCreateProperty(): bool
    {
        return Gate::allows('create', Category::class);
    }

    public function getCanUpdateSelectedProperty(): bool
    {
        $id = $this->editingCategoryId ?? $this->selectedCategoryId;
        if (empty($id)) {
            return false;
        }

        $category = Category::query()->find($id);

        return $category !== null && Gate::allows('update', $category);
    }

    public function getCanDeleteSelectedProperty(): bool
    {
        if (empty($this->editingCategoryId)) {
            return false;
        }

        $category = Category::query()->find($this->editingCategoryId);

        return $category !== null && Gate::allows('delete', $category);
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildFilters(): array
    {
        return [
            'search' => $this->search,
            'status' => $this->statusFilter,
            'date_from' => $this->dateFrom,
            'date_until' => $this->dateUntil,
        ];
    }

    public function openDetailDrawer(int $id): void
    {
        $category = Category::query()->findOrFail($id);
        Gate::authorize('view', $category);

        $this->selectedCategoryId = $id;
        $this->showDetailDrawer = true;
    }

    public function closeDetailDrawer(): void
    {
        $this->showDetailDrawer = false;
        $this->selectedCategoryId = null;
    }

    public function openCreateModal(): void
    {
        Gate::authorize('create', Category::class);

        $this->resetForm();
        $this->editingCategoryId = null;
        $this->showFormModal = true;
    }

    public function openEditModal(?int $id = null): void
    {
        $id ??= $this->selectedCategoryId;
        $category = Category::query()->findOrFail($id);
        Gate::authorize('update', $category);

        $this->editingCategoryId = $category->id;
        $this->form = [
            'name' => $category->name ?? '',
            'slug' => $category->slug ?? '',
            'description' => $category->description ?? '',
            'parent_id' => $category->parent_id,
            'sort_order' => (string) ($category->sort_order ?? 0),
            'status' => $category->status ?? 'active',
        ];
        $this->resetErrorBag();
        $this->showFormModal = true;
    }

    public function closeFormModal(): void
    {
        $this->showFormModal = false;
        $this->editingCategoryId = null;
        $this->resetForm();
        $this->resetErrorBag();
    }

    public function updatedFormName($value): void
    {
        if ($this->editingCategoryId !== null) {
            return;
        }

        $this->form['slug'] = Str::slug((string) $value);
    }

    public function saveCategory(): void
    {
        $validated = $this->validate($this->formRules());
        $service = $this->getCategoryServiceProperty();
        $wasEditing = $this->editingCategoryId !== null;
        $keepDetailsOpen = $this->showDetailDrawer;

        if ($wasEditing) {
            $category = Category::query()->findOrFail($this->editingCategoryId);
            Gate::authorize('update', $category);
            $category = $service->updateCategory($category, $validated['form']);
            Notification::make()->title('Category updated')->success()->send();
        } else {
            Gate::authorize('create', Category::class);
            $category = $service->createCategory($validated['form']);
            Notification::make()->title('Category created')->success()->send();
        }

        $this->closeFormModal();
        $this->selectedCategoryId = $category->id;

        if ($keepDetailsOpen || ! $wasEditing) {
            $this->showDetailDrawer = true;
        }
    }

    public function deleteCategory(): void
    {
        if (! $this->editingCategoryId) {
            return;
        }

        $category = Category::query()->findOrFail($this->editingCategoryId);
        Gate::authorize('delete', $category);

        $this->getCategoryServiceProperty()->deleteCategory($category);

        Notification::make()->title('Category deleted')->success()->send();

        $this->closeFormModal();
        $this->closeDetailDrawer();
    }

    /**
     * @return array<string, mixed>
     */
    protected function formRules(): array
    {
        $categoryId = $this->editingCategoryId;

        return [
            'form.name' => ['required', 'string', 'max:255'],
            'form.slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('categories', 'slug')->ignore($categoryId),
            ],
            'form.description' => ['nullable', 'string', 'max:65535'],
            'form.parent_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where(function ($query) use ($categoryId) {
                    if ($categoryId) {
                        $query->where('id', '!=', $categoryId);
                    }
                }),
            ],
            'form.sort_order' => ['required', 'integer', 'min:0'],
            'form.status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    public function resetForm(): void
    {
        $this->form = [
            'name' => '',
            'slug' => '',
            'description' => '',
            'parent_id' => null,
            'sort_order' => '0',
            'status' => 'active',
        ];
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
        $this->dateFrom = null;
        $this->dateUntil = null;
        $this->sortField = 'sort_order';
        $this->sortDirection = 'asc';
        $this->resetPage();
    }

    public function hasActiveFilters(): bool
    {
        return filled($this->search)
            || filled($this->statusFilter)
            || filled($this->dateFrom)
            || filled($this->dateUntil);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
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

    public function getExportUrl(string $format): string
    {
        return route('filament.admin.products.categories.export', [
            'format' => $format,
            'search' => $this->search ?: null,
            'status' => $this->statusFilter ?: null,
            'date_from' => $this->dateFrom,
            'date_until' => $this->dateUntil,
        ]);
    }
}

<?php

namespace App\Filament\Pages\Administration;

use App\Filament\Resources\RoleResource;
use App\Services\Admin\RoleAdminService;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class RolesPage extends Page
{
    use WithPagination;

    protected static string $layout = 'filament.layouts.crm';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Roles';

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.administration.roles';

    protected static ?string $slug = 'administration/roles';

    #[Url(as: 'search')]
    public string $search = '';

    #[Url(as: 'type')]
    public ?string $typeFilter = null;

    #[Url(as: 'sort')]
    public string $sortField = 'name';

    #[Url(as: 'direction')]
    public string $sortDirection = 'asc';

    public int $perPage = 15;

    public bool $showDetailDrawer = false;

    public ?int $selectedRoleId = null;

    public function getTitle(): string
    {
        return 'Roles';
    }

    public function mount(): void
    {
        Gate::authorize('viewAny', Role::class);
    }

    public function getRoleServiceProperty(): RoleAdminService
    {
        return app(RoleAdminService::class);
    }

    public function getRolesProperty(): mixed
    {
        return $this->getRoleServiceProperty()
            ->paginateRoles($this->buildFilters(), $this->sortField, $this->sortDirection, $this->perPage);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getKpiCardsProperty(): array
    {
        return $this->getRoleServiceProperty()->getKpiCards();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getSelectedRoleProperty(): ?array
    {
        if (empty($this->selectedRoleId)) {
            return null;
        }

        $role = Role::query()->find($this->selectedRoleId);

        if ($role === null) {
            return null;
        }

        Gate::authorize('view', $role);

        return $this->getRoleServiceProperty()->getDetail($role);
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilterOptionsProperty(): array
    {
        return $this->getRoleServiceProperty()->getFilterOptions();
    }

    public function getCanCreateProperty(): bool
    {
        return Gate::allows('create', Role::class);
    }

    public function getCreateUrlProperty(): string
    {
        return RoleResource::getUrl('create');
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildFilters(): array
    {
        return [
            'search' => $this->search,
            'type' => $this->typeFilter,
        ];
    }

    public function openDetailDrawer(int $id): void
    {
        $role = Role::query()->findOrFail($id);
        Gate::authorize('view', $role);

        $this->selectedRoleId = $id;
        $this->showDetailDrawer = true;
    }

    public function closeDetailDrawer(): void
    {
        $this->showDetailDrawer = false;
        $this->selectedRoleId = null;
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
        $this->typeFilter = null;
        $this->sortField = 'name';
        $this->sortDirection = 'asc';
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function hasActiveFilters(): bool
    {
        return filled($this->search) || filled($this->typeFilter);
    }

    public function getExportUrl(string $format): string
    {
        return route('filament.admin.administration.roles.export', [
            'format' => $format,
            'search' => $this->search ?: null,
            'type' => $this->typeFilter,
        ]);
    }
}

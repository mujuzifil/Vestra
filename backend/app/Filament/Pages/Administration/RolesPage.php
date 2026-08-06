<?php

namespace App\Filament\Pages\Administration;

use App\Models\Role;
use App\Models\User;
use App\Services\Admin\RoleAdminService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

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

    #[Url(as: 'status')]
    public ?string $statusFilter = null;

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

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }

    public function mount(): void
    {
        abort_unless(Gate::allows('viewAny', Role::class), 403);
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
        return RoleFormPage::getUrl();
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildFilters(): array
    {
        return [
            'search' => $this->search,
            'type' => $this->typeFilter,
            'status' => $this->statusFilter,
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

    public function enableRole(int $id): void
    {
        $this->mutateRole($id, fn (Role $role, $actor) => $this->getRoleServiceProperty()->setStatus($role, 'active', $actor), 'Role enabled');
    }

    public function disableRole(int $id): void
    {
        $this->mutateRole($id, fn (Role $role, $actor) => $this->getRoleServiceProperty()->setStatus($role, 'disabled', $actor), 'Role disabled');
    }

    public function duplicateRole(int $id): void
    {
        $role = Role::query()->findOrFail($id);
        Gate::authorize('create', Role::class);
        $copy = $this->getRoleServiceProperty()->duplicateRole($role, auth()->user());
        Notification::make()->title('Role duplicated')->success()->send();
        $this->redirect(RoleFormPage::getUrl(['id' => $copy->id]));
    }

    public function deleteRole(int $id): void
    {
        $role = Role::query()->findOrFail($id);
        Gate::authorize('delete', $role);
        $this->getRoleServiceProperty()->deleteRole($role, auth()->user());
        $this->closeDetailDrawer();
        Notification::make()->title('Role deleted')->success()->send();
    }

    public function removeUserFromRole(int $roleId, int $userId): void
    {
        $role = Role::query()->findOrFail($roleId);
        Gate::authorize('update', $role);
        $user = User::query()->findOrFail($userId);
        $this->getRoleServiceProperty()->removeUser($role, $user, auth()->user());
        Notification::make()->title('User removed from role')->success()->send();
        $this->selectedRoleId = $roleId;
        $this->showDetailDrawer = true;
    }

    /**
     * @param  callable(Role, mixed): mixed  $callback
     */
    private function mutateRole(int $id, callable $callback, string $message): void
    {
        $role = Role::query()->findOrFail($id);
        Gate::authorize('update', $role);
        $callback($role, auth()->user());
        Notification::make()->title($message)->success()->send();
        $this->selectedRoleId = $id;
        $this->showDetailDrawer = true;
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
        $this->statusFilter = null;
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

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function hasActiveFilters(): bool
    {
        return filled($this->search) || filled($this->typeFilter) || filled($this->statusFilter);
    }

    public function getExportUrl(string $format): string
    {
        return route('filament.admin.administration.roles.export', [
            'format' => $format,
            'search' => $this->search ?: null,
            'type' => $this->typeFilter,
            'status' => $this->statusFilter,
        ]);
    }
}

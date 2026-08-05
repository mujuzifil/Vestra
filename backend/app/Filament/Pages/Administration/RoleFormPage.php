<?php

namespace App\Filament\Pages\Administration;

use App\Models\Role;
use App\Services\Admin\PermissionDiscoveryService;
use App\Services\Admin\RoleAdminService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Url;

class RoleFormPage extends Page
{
    protected static string $layout = 'filament.layouts.crm';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'New Role';

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?int $navigationSort = 3;

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.administration.role-form';

    protected static ?string $slug = 'administration/roles/form';

    #[Url]
    public ?int $id = null;

    /**
     * @var array<string, mixed>
     */
    public array $form = [];

    /** @var array<int, string> */
    public array $selectedPermissions = [];

    public string $permissionSearch = '';

    /** @var array<string, bool> */
    public array $expandedGroups = [];

    public bool $slugManual = false;

    public function getTitle(): string
    {
        return $this->id ? 'Edit Role' : 'New Role';
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }

    public function mount(): void
    {
        if ($this->id) {
            $role = Role::query()->findOrFail($this->id);
            Gate::authorize('update', $role);
            $this->hydrateFromRole($role);
        } else {
            Gate::authorize('create', Role::class);
            $this->resetForm();
        }
    }

    public function getRoleServiceProperty(): RoleAdminService
    {
        return app(RoleAdminService::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function getFormOptionsProperty(): array
    {
        return $this->getRoleServiceProperty()->getFormOptions();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getPermissionTreeProperty(): array
    {
        return app(PermissionDiscoveryService::class)->getPermissionTree(
            filled($this->permissionSearch) ? $this->permissionSearch : null
        );
    }

    public function getIsEditingProperty(): bool
    {
        return filled($this->id);
    }

    public function getCancelUrlProperty(): string
    {
        return RolesPage::getUrl();
    }

    public function updatedFormName($value): void
    {
        if ($this->slugManual) {
            return;
        }

        $this->form['slug'] = Str::slug((string) $value);
    }

    public function updatedFormSlug(): void
    {
        $this->slugManual = true;
        $this->form['slug'] = Str::slug((string) ($this->form['slug'] ?? ''));
    }

    public function toggleGroup(string $groupKey): void
    {
        $this->expandedGroups[$groupKey] = ! ($this->expandedGroups[$groupKey] ?? false);
    }

    public function expandAll(): void
    {
        foreach ($this->permissionTree as $group) {
            $this->expandedGroups[$group['key']] = true;
        }
    }

    public function collapseAll(): void
    {
        $this->expandedGroups = [];
    }

    public function toggleGroupPermissions(string $groupKey, array $permissionNames): void
    {
        $allSelected = collect($permissionNames)->every(
            fn (string $name) => in_array($name, $this->selectedPermissions, true)
        );

        if ($allSelected) {
            $this->selectedPermissions = array_values(array_diff($this->selectedPermissions, $permissionNames));
        } else {
            $this->selectedPermissions = array_values(array_unique(array_merge($this->selectedPermissions, $permissionNames)));
        }
    }

    public function togglePermission(string $permissionName): void
    {
        if (in_array($permissionName, $this->selectedPermissions, true)) {
            $this->selectedPermissions = array_values(array_diff($this->selectedPermissions, [$permissionName]));
        } else {
            $this->selectedPermissions[] = $permissionName;
        }
    }

    public function save(): void
    {
        $validated = $this->validate($this->rules());
        $actor = auth()->user();

        try {
            if ($this->isEditing) {
                $role = Role::query()->findOrFail($this->id);
                Gate::authorize('update', $role);
                $this->getRoleServiceProperty()->updateRole($role, $validated['form'], $this->selectedPermissions, $actor);
                Notification::make()->title('Role updated')->success()->send();
            } else {
                Gate::authorize('create', Role::class);
                $role = $this->getRoleServiceProperty()->createRole($validated['form'], $this->selectedPermissions, $actor);
                Notification::make()->title('Role created')->success()->send();
                $this->redirect(RolesPage::getUrl(['search' => $role->name]));

                return;
            }
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            Notification::make()->title('Unable to save role')->danger()->send();

            return;
        }

        $this->redirect(RolesPage::getUrl());
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $roleId = $this->id;

        return [
            'form.name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($roleId),
            ],
            'form.slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('roles', 'slug')->ignore($roleId),
            ],
            'form.description' => ['nullable', 'string', 'max:1000'],
            'form.status' => ['required', Rule::in(['active', 'disabled'])],
            'form.notes' => ['nullable', 'string', 'max:2000'],
            'selectedPermissions' => ['array'],
            'selectedPermissions.*' => ['string'],
        ];
    }

    private function resetForm(): void
    {
        $this->form = [
            'name' => '',
            'slug' => '',
            'description' => '',
            'status' => 'active',
            'notes' => '',
        ];
        $this->selectedPermissions = [];
        $this->permissionSearch = '';
        $this->expandedGroups = [];
        $this->slugManual = false;
    }

    private function hydrateFromRole(Role $role): void
    {
        $role->load('permissions');
        $this->form = [
            'name' => $role->name,
            'slug' => $role->slug ?: Str::slug($role->name),
            'description' => $role->description,
            'status' => $role->status ?: 'active',
            'notes' => $role->notes,
        ];
        $this->selectedPermissions = $role->permissions->pluck('name')->all();
        $this->slugManual = true;
        foreach ($this->selectedPermissions as $permission) {
            $group = explode('.', $permission)[0] ?? null;
            if ($group) {
                $this->expandedGroups[$group] = true;
            }
        }
    }
}

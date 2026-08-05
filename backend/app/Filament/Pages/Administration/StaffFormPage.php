<?php

namespace App\Filament\Pages\Administration;

use App\Models\User;
use App\Services\Admin\PermissionDiscoveryService;
use App\Services\Admin\StaffAdminService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Url;
use Livewire\WithFileUploads;
use Spatie\Permission\Models\Role;

class StaffFormPage extends Page
{
    use WithFileUploads;

    protected static string $layout = 'filament.layouts.crm';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Create Staff';

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?int $navigationSort = 2;

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.administration.staff-form';

    protected static ?string $slug = 'administration/staff/form';

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

    public $avatar = null;

    public bool $removeAvatar = false;

    public bool $showPassword = false;

    public bool $showPasswordConfirmation = false;

    public function getTitle(): string
    {
        return $this->id ? 'Edit Staff' : 'Create New Staff';
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }

    public function mount(): void
    {
        if ($this->id) {
            $staff = User::query()->where('is_admin', true)->findOrFail($this->id);
            Gate::authorize('update', $staff);
            $this->hydrateFromStaff($staff);
        } else {
            Gate::authorize('create', User::class);
            $this->resetForm();
        }
    }

    public function getStaffServiceProperty(): StaffAdminService
    {
        return app(StaffAdminService::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function getFormOptionsProperty(): array
    {
        return $this->getStaffServiceProperty()->getFormOptions();
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
        return StaffPage::getUrl();
    }

    public function getExistingAvatarUrlProperty(): ?string
    {
        if ($this->removeAvatar || $this->avatar) {
            return null;
        }

        if (! $this->id) {
            return null;
        }

        return User::query()->find($this->id)?->avatarUrl();
    }

    public function updatedFormRoleId($value): void
    {
        if (! filled($value)) {
            return;
        }

        $role = Role::query()->with('permissions')->find((int) $value);
        if (! $role) {
            return;
        }

        $rolePermissions = $role->permissions->pluck('name')->all();
        $this->selectedPermissions = array_values(array_unique(array_merge(
            $this->selectedPermissions,
            $rolePermissions
        )));

        foreach ($rolePermissions as $permission) {
            $group = explode('.', $permission)[0] ?? null;
            if ($group) {
                $this->expandedGroups[$group] = true;
            }
        }
    }

    public function toggleGroup(string $groupKey): void
    {
        $this->expandedGroups[$groupKey] = ! ($this->expandedGroups[$groupKey] ?? false);
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
        $actor = auth()->user();
        $key = 'staff-create:'.($actor?->id ?? 'guest').':'.request()->ip();

        if (! $this->isEditing && RateLimiter::tooManyAttempts($key, 10)) {
            throw ValidationException::withMessages([
                'form.email' => 'Too many staff accounts created. Please wait and try again.',
            ]);
        }

        $validated = $this->validate($this->rules());

        try {
            if ($this->isEditing) {
                $staff = User::query()->where('is_admin', true)->findOrFail($this->id);
                Gate::authorize('update', $staff);

                $this->getStaffServiceProperty()->updateStaff(
                    $staff,
                    $validated['form'],
                    $this->selectedPermissions,
                    $this->avatar,
                    $this->removeAvatar,
                    $actor
                );

                Notification::make()
                    ->title('Staff updated')
                    ->success()
                    ->send();
            } else {
                Gate::authorize('create', User::class);
                RateLimiter::hit($key, 60);

                $result = $this->getStaffServiceProperty()->createStaff(
                    $validated['form'],
                    $this->selectedPermissions,
                    $this->avatar,
                    $actor
                );

                Notification::make()
                    ->title('Staff created')
                    ->body('A welcome email with a temporary password has been sent.')
                    ->success()
                    ->send();

                $this->redirect(StaffPage::getUrl(['search' => $result['user']->email]));

                return;
            }
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            Notification::make()
                ->title('Unable to save staff')
                ->body('Please review the form and try again.')
                ->danger()
                ->send();

            return;
        }

        $this->redirect(StaffPage::getUrl());
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $staffId = $this->id;

        return [
            'form.name' => ['required', 'string', 'max:255'],
            'form.email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($staffId),
            ],
            'form.username' => [
                'nullable',
                'string',
                'max:100',
                'alpha_dash',
                Rule::unique('users', 'username')->ignore($staffId),
            ],
            'form.phone' => ['nullable', 'string', 'max:40', 'regex:/^[+0-9()\-\s]*$/'],
            'form.password' => [
                'nullable',
                'string',
                'confirmed',
                Rule::when(
                    fn () => filled($this->form['password'] ?? null),
                    [Password::min(12)->mixedCase()->numbers()->symbols()]
                ),
            ],
            'form.password_confirmation' => ['nullable', 'string'],
            'form.status' => ['required', Rule::in(['active', 'inactive'])],
            'form.role_id' => ['required', 'integer', Rule::exists('roles', 'id')],
            'form.department' => ['nullable', 'string', 'max:120'],
            'form.job_title' => ['nullable', 'string', 'max:120'],
            'form.employee_id' => [
                'nullable',
                'string',
                'max:60',
                Rule::unique('users', 'employee_id')->ignore($staffId),
            ],
            'form.notes' => ['nullable', 'string', 'max:500'],
            'avatar' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'selectedPermissions' => ['array'],
            'selectedPermissions.*' => ['string'],
        ];
    }

    private function resetForm(): void
    {
        $this->form = [
            'name' => '',
            'email' => '',
            'username' => '',
            'phone' => '',
            'password' => '',
            'password_confirmation' => '',
            'status' => 'active',
            'role_id' => null,
            'department' => '',
            'job_title' => '',
            'employee_id' => '',
            'notes' => '',
        ];
        $this->selectedPermissions = [];
        $this->permissionSearch = '';
        $this->expandedGroups = [];
        $this->avatar = null;
        $this->removeAvatar = false;
    }

    private function hydrateFromStaff(User $staff): void
    {
        $staff->load(['roles', 'permissions']);
        $role = $staff->roles->first();

        $this->form = [
            'name' => $staff->name,
            'email' => $staff->email,
            'username' => $staff->username,
            'phone' => $staff->phone,
            'password' => '',
            'password_confirmation' => '',
            'status' => $staff->status ?: 'active',
            'role_id' => $role?->id,
            'department' => $staff->department,
            'job_title' => $staff->job_title,
            'employee_id' => $staff->employee_id,
            'notes' => $staff->notes,
        ];

        $this->selectedPermissions = $staff->getAllPermissions()->pluck('name')->all();
        $this->expandedGroups = [];
        foreach ($this->selectedPermissions as $permission) {
            $group = explode('.', $permission)[0] ?? null;
            if ($group) {
                $this->expandedGroups[$group] = true;
            }
        }
    }
}

<?php

namespace App\Services\Admin;

use App\Models\AuditLog;
use App\Models\User;
use App\Notifications\StaffWelcomeNotification;
use App\Services\AuditService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class StaffAdminService
{
    public function __construct(
        private readonly PermissionDiscoveryService $permissions
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateStaff(array $filters = [], string $sort = 'created_at', string $direction = 'desc', int $perPage = 15): LengthAwarePaginator
    {
        return $this->queryStaff($filters, $sort, $direction)->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function queryStaff(array $filters = [], string $sort = 'created_at', string $direction = 'desc'): Builder
    {
        $query = User::query()
            ->where('is_admin', true)
            ->with('roles')
            ->when($filters['search'] ?? null, function (Builder $q, string $term): void {
                $q->where(function (Builder $inner) use ($term): void {
                    $inner->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhere('username', 'like', "%{$term}%")
                        ->orWhere('employee_id', 'like', "%{$term}%")
                        ->orWhere('department', 'like', "%{$term}%")
                        ->orWhere('job_title', 'like', "%{$term}%")
                        ->orWhereHas('roles', fn (Builder $r) => $r->where('name', 'like', "%{$term}%"));
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $q, array $statuses) => $q->whereIn('status', $statuses))
            ->when($filters['role'] ?? null, function (Builder $q, array $roleIds): void {
                $q->whereHas('roles', fn (Builder $inner) => $inner->whereIn('roles.id', $roleIds));
            });

        return $this->applySorting($query, $sort, $direction);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getKpiCards(): array
    {
        $staffQuery = fn () => User::query()->where('is_admin', true);

        return [
            $this->buildCard('Total Staff', $staffQuery()->count(), 'heroicon-o-users', 'primary'),
            $this->buildCard('Active', $staffQuery()->where('status', 'active')->count(), 'heroicon-o-check-circle', 'success'),
            $this->buildCard('Inactive', $staffQuery()->where('status', 'inactive')->count(), 'heroicon-o-x-circle', 'danger'),
            $this->buildCard('Roles', Role::query()->where('name', '!=', 'customer')->count(), 'heroicon-o-shield-check', 'info'),
            $this->buildCard('Pending Password Reset', $staffQuery()->whereNotNull('force_password_change_at')->count(), 'heroicon-o-key', 'warning'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getDetail(User $staff): array
    {
        $staff->load(['roles.permissions', 'permissions', 'creator:id,name', 'updater:id,name']);
        $actor = auth()->user();

        $direct = $staff->getDirectPermissions()->pluck('name')->all();
        $viaRoles = $staff->getPermissionsViaRoles()->pluck('name')->all();

        return [
            'id' => $staff->id,
            'name' => $staff->name,
            'email' => $staff->email,
            'username' => $staff->username,
            'phone' => $staff->phone,
            'initials' => $staff->initials(),
            'avatar_url' => $staff->avatarUrl(),
            'status' => $staff->status,
            'status_label' => $staff->status === 'active' ? 'Active' : 'Inactive',
            'status_color' => $staff->status === 'active' ? 'success' : 'danger',
            'department' => $staff->department,
            'job_title' => $staff->job_title,
            'employee_id' => $staff->employee_id,
            'notes' => $staff->notes,
            'roles' => $staff->roles->map(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
            ])->values()->toArray(),
            'permissions' => $staff->getAllPermissions()->map(fn (Permission $p) => [
                'name' => $p->name,
                'group' => $p->group ?: 'General',
                'via_role' => in_array($p->name, $viaRoles, true),
                'override' => in_array($p->name, $direct, true),
            ])->values()->toArray(),
            'permission_overrides' => $direct,
            'last_login_at' => $staff->last_login_at,
            'force_password_change_at' => $staff->force_password_change_at,
            'password_changed_at' => $staff->password_changed_at,
            'password_reset_pending' => $staff->force_password_change_at !== null,
            'locked_at' => $staff->locked_at,
            'is_locked' => $staff->locked_at !== null,
            'created_at' => $staff->created_at,
            'updated_at' => $staff->updated_at,
            'created_by' => $staff->creator?->name,
            'updated_by' => $staff->updater?->name,
            'edit_url' => \App\Filament\Pages\Administration\StaffFormPage::getUrl(['id' => $staff->id]),
            'audit' => $this->auditTimeline($staff),
            'actions' => $this->availableActions($staff, $actor),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilterOptions(): array
    {
        return [
            'statuses' => [
                ['value' => 'active', 'label' => 'Active'],
                ['value' => 'inactive', 'label' => 'Inactive'],
            ],
            'roles' => Role::query()
                ->where('name', '!=', 'customer')
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Role $role) => ['id' => $role->id, 'name' => $role->name])
                ->values()
                ->toArray(),
            'departments' => $this->departmentOptions(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getFormOptions(): array
    {
        $this->permissions->syncToDatabase();

        return [
            'roles' => Role::query()
                ->where('name', '!=', 'customer')
                ->orderBy('name')
                ->get(['id', 'name', 'description'])
                ->map(fn (Role $role) => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'description' => $role->description,
                ])
                ->values()
                ->all(),
            'statuses' => [
                ['value' => 'active', 'label' => 'Active'],
                ['value' => 'inactive', 'label' => 'Inactive'],
            ],
            'departments' => $this->departmentOptions(),
            'permission_tree' => $this->permissions->getPermissionTree(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $permissionNames
     * @return array{user: User, temporary_password: string}
     */
    public function createStaff(array $data, array $permissionNames = [], ?UploadedFile $avatar = null, ?User $actor = null): array
    {
        return DB::transaction(function () use ($data, $permissionNames, $avatar, $actor) {
            $temporaryPassword = filled($data['password'] ?? null)
                ? (string) $data['password']
                : $this->generateTemporaryPassword();

            $user = new User;
            $user->fill($this->preparePayload($data, $actor, creating: true));
            $user->is_admin = true;
            $user->password = $temporaryPassword;
            $user->email_verified_at = now();
            $user->force_password_change_at = now();
            $user->password_changed_at = null;
            $user->save();

            if ($avatar) {
                $this->storeAvatar($user, $avatar);
            }

            $this->syncRoleAndPermissions($user, $data['role_id'] ?? null, $permissionNames);

            AuditService::log($actor, 'staff.created', $user, [
                'email' => $user->email,
                'role_id' => $data['role_id'] ?? null,
            ]);

            try {
                $user->notify(new StaffWelcomeNotification($temporaryPassword));
            } catch (\Throwable) {
                // Mail may be unconfigured in local/test; creation still succeeds.
            }

            return ['user' => $user->fresh(['roles']), 'temporary_password' => $temporaryPassword];
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $permissionNames
     */
    public function updateStaff(User $staff, array $data, array $permissionNames = [], ?UploadedFile $avatar = null, bool $removeAvatar = false, ?User $actor = null): User
    {
        return DB::transaction(function () use ($staff, $data, $permissionNames, $avatar, $removeAvatar, $actor) {
            $staff->fill($this->preparePayload($data, $actor, creating: false));
            if (filled($data['password'] ?? null)) {
                $staff->password = $data['password'];
                $staff->force_password_change_at = now();
                $staff->password_changed_at = null;
            }
            $staff->save();

            if ($removeAvatar && $staff->avatar_path) {
                Storage::disk('public')->delete($staff->avatar_path);
                $staff->update(['avatar_path' => null]);
            }

            if ($avatar) {
                $this->storeAvatar($staff, $avatar);
            }

            $this->syncRoleAndPermissions($staff, $data['role_id'] ?? null, $permissionNames);

            AuditService::log($actor, 'staff.updated', $staff);

            return $staff->fresh(['roles']);
        });
    }

    public function setStatus(User $staff, string $status, ?User $actor = null): User
    {
        if (! in_array($status, ['active', 'inactive'], true)) {
            throw ValidationException::withMessages(['status' => 'Invalid status.']);
        }

        $staff->update([
            'status' => $status,
            'updated_by' => $actor?->id,
        ]);

        AuditService::log($actor, $status === 'active' ? 'staff.enabled' : 'staff.disabled', $staff);

        return $staff;
    }

    public function resetPassword(User $staff, ?User $actor = null): string
    {
        $temporaryPassword = $this->generateTemporaryPassword();
        $staff->password = $temporaryPassword;
        $staff->force_password_change_at = now();
        $staff->password_changed_at = null;
        $staff->updated_by = $actor?->id;
        $staff->save();

        AuditService::log($actor, 'staff.password_reset', $staff);

        try {
            $staff->notify(new StaffWelcomeNotification($temporaryPassword, isReset: true));
        } catch (\Throwable) {
        }

        return $temporaryPassword;
    }

    public function forcePasswordChange(User $staff, ?User $actor = null): void
    {
        $staff->requirePasswordChange();
        $staff->update(['updated_by' => $actor?->id]);
        AuditService::log($actor, 'staff.force_password_change', $staff);
    }

    public function lock(User $staff, ?User $actor = null): void
    {
        $staff->update([
            'locked_at' => now(),
            'status' => 'inactive',
            'updated_by' => $actor?->id,
        ]);
        AuditService::log($actor, 'staff.locked', $staff);
    }

    public function unlock(User $staff, ?User $actor = null): void
    {
        $staff->update([
            'locked_at' => null,
            'status' => 'active',
            'updated_by' => $actor?->id,
        ]);
        AuditService::log($actor, 'staff.unlocked', $staff);
    }

    public function deleteStaff(User $staff, ?User $actor = null): void
    {
        if ($actor && $actor->id === $staff->id) {
            throw ValidationException::withMessages(['staff' => 'You cannot delete your own account.']);
        }

        DB::transaction(function () use ($staff, $actor) {
            AuditService::log($actor, 'staff.deleted', $staff, ['email' => $staff->email]);
            $staff->roles()->detach();
            $staff->permissions()->detach();
            $staff->delete();
        });
    }

    public function resendWelcome(User $staff, ?User $actor = null): string
    {
        $password = $this->resetPassword($staff, $actor);
        AuditService::log($actor, 'staff.welcome_resent', $staff);

        return $password;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    public function exportRows(array $filters = []): array
    {
        return $this->queryStaff($filters, 'name', 'asc')
            ->get()
            ->map(fn (User $staff) => [
                'name' => $staff->name,
                'email' => $staff->email,
                'username' => $staff->username,
                'department' => $staff->department,
                'job_title' => $staff->job_title,
                'employee_id' => $staff->employee_id,
                'status' => $staff->status === 'active' ? 'Active' : 'Inactive',
                'roles' => $staff->roles->pluck('name')->implode(', '),
                'last_login_at' => $staff->last_login_at?->format('Y-m-d H:i:s'),
                'password_reset_pending' => $staff->force_password_change_at !== null ? 'Yes' : 'No',
                'created_at' => $staff->created_at?->format('Y-m-d H:i:s'),
            ])
            ->toArray();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function auditTimeline(User $staff): array
    {
        return AuditLog::query()
            ->where(function (Builder $q) use ($staff): void {
                $q->where(function (Builder $inner) use ($staff): void {
                    $inner->where('subject_type', $staff->getMorphClass())
                        ->where('subject_id', $staff->id);
                })->orWhere(function (Builder $inner) use ($staff): void {
                    $inner->where('user_id', $staff->id)
                        ->whereIn('action', [
                            'login',
                            'logout',
                            'password_changed',
                            'password_change.required',
                            'password_change.bypass_attempt',
                        ]);
                });
            })
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn (AuditLog $log) => [
                'action' => $log->action,
                'user' => $log->user?->name,
                'ip' => $log->ip_address,
                'device' => Str::limit((string) $log->user_agent, 80),
                'timestamp' => $log->created_at,
                'details' => $log->details,
            ])
            ->all();
    }

    /**
     * @return array<string, bool>
     */
    private function availableActions(User $staff, ?User $actor): array
    {
        $canUpdate = $actor && Gate::forUser($actor)->allows('update', $staff);
        $canDelete = $actor && Gate::forUser($actor)->allows('delete', $staff);

        return [
            'edit' => $canUpdate,
            'disable' => $canUpdate && $staff->status === 'active',
            'enable' => $canUpdate && $staff->status !== 'active',
            'reset_password' => $canUpdate,
            'force_password_change' => $canUpdate,
            'lock' => $canUpdate && $staff->locked_at === null,
            'unlock' => $canUpdate && $staff->locked_at !== null,
            'delete' => $canDelete && (! $actor || $actor->id !== $staff->id),
            'resend_welcome' => $canUpdate,
            'view_audit' => $actor && Gate::forUser($actor)->allows('view', $staff),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function preparePayload(array $data, ?User $actor, bool $creating): array
    {
        $payload = [
            'name' => trim((string) ($data['name'] ?? '')),
            'email' => trim((string) ($data['email'] ?? '')),
            'username' => filled($data['username'] ?? null) ? trim((string) $data['username']) : null,
            'phone' => filled($data['phone'] ?? null) ? trim((string) $data['phone']) : null,
            'status' => $data['status'] ?? 'active',
            'department' => filled($data['department'] ?? null) ? trim((string) $data['department']) : null,
            'job_title' => filled($data['job_title'] ?? null) ? trim((string) $data['job_title']) : null,
            'employee_id' => filled($data['employee_id'] ?? null) ? trim((string) $data['employee_id']) : null,
            'notes' => filled($data['notes'] ?? null) ? trim((string) $data['notes']) : null,
            'updated_by' => $actor?->id,
        ];

        if ($creating) {
            $payload['created_by'] = $actor?->id;
        }

        return $payload;
    }

    /**
     * @param  array<int, string>  $permissionNames
     */
    private function syncRoleAndPermissions(User $user, mixed $roleId, array $permissionNames): void
    {
        $rolePermissionNames = [];

        if (filled($roleId)) {
            $role = Role::query()->findOrFail((int) $roleId);
            $user->syncRoles([$role->name]);
            $rolePermissionNames = $role->permissions()->pluck('name')->all();
        } else {
            $user->syncRoles([]);
        }

        $valid = Permission::query()->whereIn('name', $permissionNames)->pluck('name')->all();

        // Persist only overrides beyond the role so role changes apply immediately.
        $overrides = array_values(array_diff($valid, $rolePermissionNames));
        $user->syncPermissions($overrides);
    }

    private function storeAvatar(User $user, UploadedFile $avatar): void
    {
        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $path = $avatar->store('avatars/staff', 'public');
        $user->update(['avatar_path' => $path]);
    }

    private function generateTemporaryPassword(): string
    {
        return Str::password(14, letters: true, numbers: true, symbols: true, spaces: false);
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function departmentOptions(): array
    {
        $fromDb = User::query()
            ->where('is_admin', true)
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->orderBy('department')
            ->pluck('department')
            ->all();

        $defaults = ['Administration', 'Sales', 'Operations', 'Marketing', 'Finance', 'Support', 'Warehouse'];
        $all = collect(array_merge($defaults, $fromDb))->unique()->sort()->values();

        return $all->map(fn ($name) => ['value' => $name, 'label' => $name])->all();
    }

    private function applySorting(Builder $query, string $sort, string $direction): Builder
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return match ($sort) {
            'name' => $query->orderBy('name', $direction),
            'email' => $query->orderBy('email', $direction),
            'status' => $query->orderBy('status', $direction),
            'last_login_at' => $query->orderBy('last_login_at', $direction),
            default => $query->orderBy('created_at', $direction),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function buildCard(string $label, float $current, string $icon, string $color): array
    {
        return [
            'label' => $label,
            'value' => number_format($current),
            'icon' => $icon,
            'color' => $color,
            'trend' => '—',
            'trend_label' => 'Live count',
            'trend_positive' => true,
            'trend_available' => false,
        ];
    }
}

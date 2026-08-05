<?php

namespace App\Services\Admin;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleAdminService
{
    /**
     * @var array<int, string>
     */
    public const SYSTEM_ROLE_NAMES = [
        'Super Administrator',
        'Administrator',
        'Manager',
        'customer',
    ];

    /**
     * @var array<int, string>
     */
    public const RESERVED_ROLE_NAMES = [
        'Super Administrator',
        'Administrator',
        'Manager',
        'customer',
        'super-admin',
        'admin',
    ];

    public function __construct(
        private readonly PermissionDiscoveryService $permissions
    ) {}

    public static function isSystemRole(Role|\Spatie\Permission\Models\Role $role): bool
    {
        return in_array($role->name, self::SYSTEM_ROLE_NAMES, true);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateRoles(array $filters = [], string $sort = 'name', string $direction = 'asc', int $perPage = 15): LengthAwarePaginator
    {
        return $this->queryRoles($filters, $sort, $direction)->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function queryRoles(array $filters = [], string $sort = 'name', string $direction = 'asc'): Builder
    {
        $query = Role::query()
            ->where('name', '!=', 'customer')
            ->withCount(['users', 'permissions'])
            ->when($filters['search'] ?? null, function (Builder $q, string $term): void {
                $q->where(function (Builder $inner) use ($term): void {
                    $inner->where('name', 'like', "%{$term}%")
                        ->orWhere('slug', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%");
                });
            })
            ->when($filters['status'] ?? null, function (Builder $q, string|array $statuses): void {
                $statuses = is_array($statuses) ? $statuses : [$statuses];
                $q->whereIn('status', $statuses);
            })
            ->when($filters['type'] ?? null, function (Builder $q, string|array $types): void {
                $types = is_array($types) ? $types : [$types];

                $q->where(function (Builder $inner) use ($types): void {
                    if (in_array('system', $types, true)) {
                        $inner->orWhereIn('name', self::SYSTEM_ROLE_NAMES);
                    }

                    if (in_array('custom', $types, true)) {
                        $inner->orWhereNotIn('name', self::SYSTEM_ROLE_NAMES);
                    }
                });
            });

        return $this->applySorting($query, $sort, $direction);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getKpiCards(): array
    {
        $base = fn () => Role::query()->where('name', '!=', 'customer');
        $totalRoles = $base()->count();
        $systemRoles = $base()->whereIn('name', self::SYSTEM_ROLE_NAMES)->count();
        $customRoles = $totalRoles - $systemRoles;
        $usersAssigned = (int) DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('roles.name', '!=', 'customer')
            ->distinct('model_has_roles.model_id')
            ->count('model_has_roles.model_id');
        $permissionCount = Permission::query()->count();
        $activeRoles = $base()->where('status', 'active')->count();

        return [
            $this->buildCard('Total Roles', $totalRoles, 'heroicon-o-shield-check', 'primary'),
            $this->buildCard('Active', $activeRoles, 'heroicon-o-check-circle', 'success'),
            $this->buildCard('System Roles', $systemRoles, 'heroicon-o-lock-closed', 'info'),
            $this->buildCard('Custom Roles', $customRoles, 'heroicon-o-adjustments-horizontal', 'success'),
            $this->buildCard('Users Assigned', $usersAssigned, 'heroicon-o-users', 'warning'),
            $this->buildCard('Permissions', $permissionCount, 'heroicon-o-key', 'gray'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getDetail(Role $role): array
    {
        $role->loadCount(['users', 'permissions']);
        $role->load(['permissions', 'users', 'creator:id,name', 'updater:id,name']);
        $actor = auth()->user();
        $isSystem = self::isSystemRole($role);
        $tree = $this->permissions->getPermissionTree();
        $granted = $role->permissions->pluck('name')->all();

        $comparison = collect($tree)->map(function (array $group) use ($granted) {
            return [
                'key' => $group['key'],
                'label' => $group['label'],
                'permissions' => collect($group['permissions'])->map(fn (array $permission) => [
                    'name' => $permission['name'],
                    'label' => $permission['label'],
                    'granted' => in_array($permission['name'], $granted, true),
                ])->values()->all(),
            ];
        })->values()->all();

        $canUpdate = $actor && Gate::forUser($actor)->allows('update', $role);
        $canDelete = $actor && Gate::forUser($actor)->allows('delete', $role);

        return [
            'id' => $role->id,
            'name' => $role->name,
            'slug' => $role->slug,
            'description' => $role->description,
            'notes' => $role->notes,
            'status' => $role->status ?: 'active',
            'status_label' => ($role->status ?: 'active') === 'active' ? 'Active' : 'Disabled',
            'is_system' => $isSystem,
            'type_label' => $isSystem ? 'System' : 'Custom',
            'users_count' => $role->users_count,
            'permissions_count' => $role->permissions_count,
            'modules_count' => collect($comparison)->filter(fn ($g) => collect($g['permissions'])->contains('granted', true))->count(),
            'permissions' => $role->permissions
                ->map(fn (Permission $permission): array => [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'group' => filled($permission->group) ? (string) $permission->group : 'General',
                ])
                ->values()
                ->all(),
            'permission_comparison' => $comparison,
            'users' => $role->users
                ->map(fn (User $user): array => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'department' => $user->department,
                    'status' => $user->status,
                    'avatar_url' => $user->avatarUrl(),
                    'initials' => $user->initials(),
                    'last_login_at' => $user->last_login_at,
                    'staff_url' => \App\Filament\Pages\Administration\StaffFormPage::getUrl(['id' => $user->id]),
                ])
                ->values()
                ->all(),
            'created_at' => $role->created_at,
            'updated_at' => $role->updated_at,
            'created_by' => $role->creator?->name,
            'updated_by' => $role->updater?->name,
            'edit_url' => \App\Filament\Pages\Administration\RoleFormPage::getUrl(['id' => $role->id]),
            'audit' => $this->auditTimeline($role),
            'actions' => [
                'edit' => $canUpdate,
                'duplicate' => $actor && Gate::forUser($actor)->allows('create', Role::class),
                'enable' => $canUpdate && ($role->status ?: 'active') !== 'active',
                'disable' => $canUpdate && ($role->status ?: 'active') === 'active' && ! $isSystem,
                'delete' => $canDelete,
                'export' => $actor && Gate::forUser($actor)->allows('export', Role::class),
                'view_audit' => $actor && Gate::forUser($actor)->allows('view', $role),
                'assign_users' => $canUpdate,
                'remove_users' => $canUpdate,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getFormOptions(): array
    {
        $this->permissions->syncToDatabase();

        return [
            'statuses' => [
                ['value' => 'active', 'label' => 'Active'],
                ['value' => 'disabled', 'label' => 'Disabled'],
            ],
            'permission_tree' => $this->permissions->getPermissionTree(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilterOptions(): array
    {
        return [
            'types' => [
                ['value' => 'system', 'label' => 'System'],
                ['value' => 'custom', 'label' => 'Custom'],
            ],
            'statuses' => [
                ['value' => 'active', 'label' => 'Active'],
                ['value' => 'disabled', 'label' => 'Disabled'],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $permissionNames
     */
    public function createRole(array $data, array $permissionNames = [], ?User $actor = null): Role
    {
        return DB::transaction(function () use ($data, $permissionNames, $actor) {
            $this->permissions->syncToDatabase();
            $name = trim((string) ($data['name'] ?? ''));
            $slug = $this->resolveSlug($data['slug'] ?? null, $name);

            $this->assertNameAvailable($name);
            $this->assertSlugAvailable($slug);

            $role = Role::query()->create([
                'name' => $name,
                'guard_name' => 'web',
                'slug' => $slug,
                'description' => filled($data['description'] ?? null) ? trim((string) $data['description']) : null,
                'status' => ($data['status'] ?? 'active') === 'disabled' ? 'disabled' : 'active',
                'notes' => filled($data['notes'] ?? null) ? trim((string) $data['notes']) : null,
                'created_by' => $actor?->id,
                'updated_by' => $actor?->id,
            ]);

            $this->syncPermissions($role, $permissionNames);

            AuditService::log($actor, 'role.created', $role, [
                'permissions' => $permissionNames,
            ]);

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            return $role->fresh(['permissions']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $permissionNames
     */
    public function updateRole(Role $role, array $data, array $permissionNames = [], ?User $actor = null): Role
    {
        return DB::transaction(function () use ($role, $data, $permissionNames, $actor) {
            $this->permissions->syncToDatabase();
            $isSystem = self::isSystemRole($role);

            $name = trim((string) ($data['name'] ?? $role->name));
            if ($isSystem && $name !== $role->name) {
                throw ValidationException::withMessages(['name' => 'System roles cannot be renamed.']);
            }

            $slug = $this->resolveSlug($data['slug'] ?? $role->slug, $name);
            $this->assertNameAvailable($name, $role->id);
            $this->assertSlugAvailable($slug, $role->id);

            $before = $role->permissions()->pluck('name')->all();

            $role->fill([
                'name' => $name,
                'slug' => $slug,
                'description' => filled($data['description'] ?? null) ? trim((string) $data['description']) : null,
                'status' => ($data['status'] ?? $role->status ?? 'active') === 'disabled' ? 'disabled' : 'active',
                'notes' => filled($data['notes'] ?? null) ? trim((string) $data['notes']) : null,
                'updated_by' => $actor?->id,
            ]);
            $role->save();

            $this->syncPermissions($role, $permissionNames);
            $after = $role->permissions()->pluck('name')->all();

            $added = array_values(array_diff($after, $before));
            $removed = array_values(array_diff($before, $after));

            AuditService::log($actor, 'role.updated', $role, [
                'permissions_added' => $added,
                'permissions_removed' => $removed,
            ]);

            if ($added !== []) {
                AuditService::log($actor, 'role.permission_added', $role, ['permissions' => $added]);
            }
            if ($removed !== []) {
                AuditService::log($actor, 'role.permission_removed', $role, ['permissions' => $removed]);
            }

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            return $role->fresh(['permissions']);
        });
    }

    public function setStatus(Role $role, string $status, ?User $actor = null): Role
    {
        if (! in_array($status, ['active', 'disabled'], true)) {
            throw ValidationException::withMessages(['status' => 'Invalid status.']);
        }

        if (self::isSystemRole($role) && $status === 'disabled') {
            throw ValidationException::withMessages(['status' => 'System roles cannot be disabled.']);
        }

        $role->update([
            'status' => $status,
            'updated_by' => $actor?->id,
        ]);

        AuditService::log($actor, $status === 'active' ? 'role.enabled' : 'role.disabled', $role);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $role;
    }

    public function duplicateRole(Role $role, ?User $actor = null): Role
    {
        $baseName = $role->name.' Copy';
        $name = $baseName;
        $i = 2;
        while (Role::query()->where('name', $name)->exists()) {
            $name = $baseName.' '.$i;
            $i++;
        }

        return $this->createRole([
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $role->description,
            'status' => 'active',
            'notes' => $role->notes,
        ], $role->permissions()->pluck('name')->all(), $actor);
    }

    public function deleteRole(Role $role, ?User $actor = null): void
    {
        if (self::isSystemRole($role)) {
            throw ValidationException::withMessages(['role' => 'System roles cannot be deleted.']);
        }

        if ($role->users()->count() > 0) {
            throw ValidationException::withMessages(['role' => 'Remove all assigned users before deleting this role.']);
        }

        DB::transaction(function () use ($role, $actor) {
            AuditService::log($actor, 'role.deleted', $role, ['name' => $role->name]);
            $role->permissions()->detach();
            $role->delete();
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        });
    }

    public function removeUser(Role $role, User $user, ?User $actor = null): void
    {
        $user->removeRole($role->name);
        AuditService::log($actor, 'role.user_removed', $role, [
            'user_id' => $user->id,
            'user_email' => $user->email,
        ]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function assignUser(Role $role, User $user, ?User $actor = null): void
    {
        if (($role->status ?: 'active') !== 'active') {
            throw ValidationException::withMessages(['role' => 'Cannot assign users to a disabled role.']);
        }

        $user->assignRole($role->name);
        AuditService::log($actor, 'role.user_assigned', $role, [
            'user_id' => $user->id,
            'user_email' => $user->email,
        ]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    public function exportRows(array $filters = []): array
    {
        return $this->queryRoles($filters, 'name', 'asc')
            ->get()
            ->map(fn (Role $role) => [
                'name' => $role->name,
                'slug' => $role->slug,
                'type' => self::isSystemRole($role) ? 'System' : 'Custom',
                'status' => $role->status ?: 'active',
                'description' => $role->description,
                'users_count' => $role->users_count,
                'permissions_count' => $role->permissions_count,
                'created_at' => $role->created_at?->format('Y-m-d H:i:s'),
            ])
            ->toArray();
    }

    /**
     * @param  array<int, string>  $permissionNames
     */
    private function syncPermissions(Role $role, array $permissionNames): void
    {
        $valid = Permission::query()->whereIn('name', $permissionNames)->pluck('name')->all();
        $role->syncPermissions($valid);
    }

    private function resolveSlug(?string $slug, string $name): string
    {
        $resolved = filled($slug) ? Str::slug($slug) : Str::slug($name);

        if ($resolved === '') {
            throw ValidationException::withMessages(['slug' => 'A valid role slug is required.']);
        }

        return $resolved;
    }

    private function assertNameAvailable(string $name, ?int $ignoreId = null): void
    {
        if ($name === '') {
            throw ValidationException::withMessages(['name' => 'Role name is required.']);
        }

        if ($ignoreId === null) {
            foreach (self::RESERVED_ROLE_NAMES as $reserved) {
                if (strcasecmp($name, $reserved) === 0) {
                    throw ValidationException::withMessages(['name' => 'This role name is reserved.']);
                }
            }
        }

        $query = Role::query()->whereRaw('LOWER(name) = ?', [mb_strtolower($name)]);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages(['name' => 'A role with this name already exists.']);
        }
    }

    private function assertSlugAvailable(string $slug, ?int $ignoreId = null): void
    {
        $query = Role::query()->where('slug', $slug);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages(['slug' => 'A role with this slug already exists.']);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function auditTimeline(Role $role): array
    {
        return AuditLog::query()
            ->where('subject_type', $role->getMorphClass())
            ->where('subject_id', $role->id)
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

    private function applySorting(Builder $query, string $sort, string $direction): Builder
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return match ($sort) {
            'users_count' => $query->orderBy('users_count', $direction),
            'permissions_count' => $query->orderBy('permissions_count', $direction),
            'created_at' => $query->orderBy('created_at', $direction),
            'status' => $query->orderBy('status', $direction),
            default => $query->orderBy('name', $direction),
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

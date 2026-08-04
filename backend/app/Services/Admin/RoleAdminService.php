<?php

namespace App\Services\Admin;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAdminService
{
    /**
     * System roles are seeded by RolePermissionSeeder and cannot be deleted or renamed.
     *
     * @var array<int, string>
     */
    public const SYSTEM_ROLE_NAMES = [
        'Super Administrator',
        'Administrator',
        'Manager',
        'customer',
    ];

    public static function isSystemRole(Role $role): bool
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
            ->withCount(['users', 'permissions'])
            ->when($filters['search'] ?? null, function (Builder $q, string $term): void {
                $q->where('name', 'like', "%{$term}%");
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
        $totalRoles = Role::query()->count();
        $systemRoles = Role::query()->whereIn('name', self::SYSTEM_ROLE_NAMES)->count();
        $customRoles = $totalRoles - $systemRoles;
        $usersAssigned = Role::query()->withCount('users')->get()->sum('users_count');
        $permissionCount = Permission::query()->count();

        return [
            $this->buildCard('Total Roles', $totalRoles, 'heroicon-o-shield-check', 'primary'),
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
        $role->load(['permissions', 'users']);

        $isSystem = self::isSystemRole($role);

        return [
            'id' => $role->id,
            'name' => $role->name,
            'description' => $role->description,
            'is_system' => $isSystem,
            'type_label' => $isSystem ? 'System' : 'Custom',
            'users_count' => $role->users_count,
            'permissions_count' => $role->permissions_count,
            'permissions' => $role->permissions
                ->map(fn (Permission $permission): array => [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'group' => filled($permission->group) ? (string) $permission->group : 'General',
                ])
                ->values()
                ->all(),
            'users' => $role->users
                ->take(25)
                ->map(fn ($user): array => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ])
                ->values()
                ->all(),
            'created_at' => $role->created_at,
            'updated_at' => $role->updated_at,
            'edit_url' => \App\Filament\Resources\RoleResource::getUrl('edit', ['record' => $role]),
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
        ];
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
                'type' => self::isSystemRole($role) ? 'System' : 'Custom',
                'description' => $role->description,
                'users_count' => $role->users_count,
                'permissions_count' => $role->permissions_count,
                'created_at' => $role->created_at?->format('Y-m-d H:i:s'),
            ])
            ->toArray();
    }

    private function applySorting(Builder $query, string $sort, string $direction): Builder
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return match ($sort) {
            'users_count' => $query->orderBy('users_count', $direction),
            'permissions_count' => $query->orderBy('permissions_count', $direction),
            'created_at' => $query->orderBy('created_at', $direction),
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

<?php

namespace App\Services\Admin;

use App\Filament\Resources\RoleResource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAdminService
{
    /**
     * Role names treated as built-in platform roles (non-deletable, protected).
     *
     * @var array<int, string>
     */
    public const SYSTEM_ROLE_NAMES = [
        'Super Administrator',
        'Administrator',
        'Manager',
        'customer',
    ];

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
            ->with('permissions')
            ->withCount(['users', 'permissions'])
            ->when($filters['search'] ?? null, function (Builder $q, string $term): void {
                $q->where(function (Builder $inner) use ($term): void {
                    $inner->where('name', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%");
                });
            })
            ->when($filters['type'] ?? null, function (Builder $q, array $types): void {
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
        $total = Role::query()->count();
        $system = Role::query()->whereIn('name', self::SYSTEM_ROLE_NAMES)->count();
        $custom = max($total - $system, 0);
        $usersAssigned = (int) DB::table('model_has_roles')->distinct('model_id')->count('model_id');
        $permissions = Permission::query()->count();

        return [
            $this->buildCard('Total Roles', $total, 'heroicon-o-shield-check', 'primary'),
            $this->buildCard('System Roles', $system, 'heroicon-o-lock-closed', 'info'),
            $this->buildCard('Custom Roles', $custom, 'heroicon-o-adjustments-horizontal', 'success'),
            $this->buildCard('Users Assigned', $usersAssigned, 'heroicon-o-user-group', 'warning'),
            $this->buildCard('Permissions', $permissions, 'heroicon-o-key', 'gray'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getDetail(Role $role): array
    {
        $role->loadMissing('permissions');
        $role->loadCount(['users', 'permissions']);

        return [
            'id' => $role->id,
            'name' => $role->name,
            'description' => $role->description,
            'guard_name' => $role->guard_name,
            'is_system' => $this->isSystemRole($role),
            'users_count' => (int) $role->users_count,
            'permissions_count' => (int) $role->permissions_count,
            'permissions' => $role->permissions->map(fn (Permission $permission): array => [
                'id' => $permission->id,
                'name' => $permission->name,
                'group' => $permission->group ?? 'General',
            ])->values()->toArray(),
            'created_at' => $role->created_at,
            'updated_at' => $role->updated_at,
            'edit_url' => RoleResource::getUrl('edit', ['record' => $role]),
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
            ->map(fn (Role $role): array => [
                'name' => $role->name,
                'type' => $this->isSystemRole($role) ? 'System' : 'Custom',
                'description' => $role->description,
                'users_count' => (int) $role->users_count,
                'permissions_count' => (int) $role->permissions_count,
                'created_at' => $role->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $role->updated_at?->format('Y-m-d H:i:s'),
            ])
            ->toArray();
    }

    public function isSystemRole(Role $role): bool
    {
        return in_array($role->name, self::SYSTEM_ROLE_NAMES, true);
    }

    private function applySorting(Builder $query, string $sort, string $direction): Builder
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return match ($sort) {
            'users_count' => $query->orderBy('users_count', $direction),
            'permissions_count' => $query->orderBy('permissions_count', $direction),
            'created_at' => $query->orderBy('created_at', $direction),
            'updated_at' => $query->orderBy('updated_at', $direction),
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

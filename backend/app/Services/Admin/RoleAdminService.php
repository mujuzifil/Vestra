<?php

namespace App\Services\Admin;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAdminService
{
    /** @var list<string> */
    public const SYSTEM_ROLE_NAMES = ['Super Administrator', 'Administrator', 'Manager', 'customer'];

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
                $q->where(function (Builder $inner) use ($term): void {
                    $inner->where('name', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%");
                });
            })
            ->when(($filters['type'] ?? null) === 'system', fn (Builder $q) => $q->whereIn('name', self::SYSTEM_ROLE_NAMES))
            ->when(($filters['type'] ?? null) === 'custom', fn (Builder $q) => $q->whereNotIn('name', self::SYSTEM_ROLE_NAMES));

        return $this->applySorting($query, $sort, $direction);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getKpiCards(): array
    {
        $total = Role::query()->count();
        $system = Role::query()->whereIn('name', self::SYSTEM_ROLE_NAMES)->count();
        $custom = max(0, $total - $system);
        $usersAssigned = (int) Role::query()->withCount('users')->get()->sum('users_count');
        $permissions = Permission::query()->count();

        return [
            $this->buildCard('Total Roles', $total, 'heroicon-o-shield-check', 'primary'),
            $this->buildCard('System Roles', $system, 'heroicon-o-lock-closed', 'info'),
            $this->buildCard('Custom Roles', $custom, 'heroicon-o-puzzle-piece', 'warning'),
            $this->buildCard('Users Assigned', $usersAssigned, 'heroicon-o-users', 'success'),
            $this->buildCard('Permissions', $permissions, 'heroicon-o-key', 'gray'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getDetail(Role $role): array
    {
        $role->load(['permissions', 'users']);
        $isSystem = $this->isSystemRole($role);

        return [
            'id' => $role->id,
            'name' => $role->name,
            'description' => $role->description,
            'type' => $isSystem ? 'system' : 'custom',
            'type_label' => $isSystem ? 'System' : 'Custom',
            'users_count' => $role->users->count(),
            'permissions_count' => $role->permissions->count(),
            'permissions' => $role->permissions
                ->groupBy(fn (Permission $p) => $p->group ?: 'General')
                ->map(fn ($group, $name) => [
                    'group' => $name,
                    'items' => $group->pluck('name')->values()->toArray(),
                ])
                ->values()
                ->toArray(),
            'users' => $role->users->take(20)->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ])->values()->toArray(),
            'created_at' => $role->created_at,
            'updated_at' => $role->updated_at,
            'is_system' => $isSystem,
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
                'description' => $role->description,
                'type' => $this->isSystemRole($role) ? 'System' : 'Custom',
                'users_count' => $role->users_count,
                'permissions_count' => $role->permissions_count,
                'created_at' => $role->created_at?->format('Y-m-d H:i:s'),
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

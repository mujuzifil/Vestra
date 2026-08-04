<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

class StaffAdminService
{
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
                        ->orWhere('email', 'like', "%{$term}%");
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
            $this->buildCard('Roles', Role::query()->count(), 'heroicon-o-shield-check', 'info'),
            $this->buildCard('Pending Password Reset', $staffQuery()->whereNotNull('force_password_change_at')->count(), 'heroicon-o-key', 'warning'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getDetail(User $staff): array
    {
        $staff->load('roles');

        return [
            'id' => $staff->id,
            'name' => $staff->name,
            'email' => $staff->email,
            'initials' => $staff->initials(),
            'avatar_url' => $staff->avatarUrl(),
            'status' => $staff->status,
            'status_label' => $staff->status === 'active' ? 'Active' : 'Inactive',
            'status_color' => $staff->status === 'active' ? 'success' : 'danger',
            'roles' => $staff->roles->map(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
            ])->values()->toArray(),
            'last_login_at' => $staff->last_login_at,
            'force_password_change_at' => $staff->force_password_change_at,
            'password_reset_pending' => $staff->force_password_change_at !== null,
            'created_at' => $staff->created_at,
            'updated_at' => $staff->updated_at,
            'edit_url' => \App\Filament\Resources\UserResource::getUrl('edit', ['record' => $staff]),
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
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Role $role) => ['id' => $role->id, 'name' => $role->name])
                ->values()
                ->toArray(),
        ];
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
                'status' => $staff->status === 'active' ? 'Active' : 'Inactive',
                'roles' => $staff->roles->pluck('name')->implode(', '),
                'last_login_at' => $staff->last_login_at?->format('Y-m-d H:i:s'),
                'password_reset_pending' => $staff->force_password_change_at !== null ? 'Yes' : 'No',
                'created_at' => $staff->created_at?->format('Y-m-d H:i:s'),
            ])
            ->toArray();
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

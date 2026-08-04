@props(['role'])

@php
use App\Services\Admin\RoleAdminService;

$isSystem = in_array($role->name, RoleAdminService::SYSTEM_ROLE_NAMES, true);
@endphp

<tr class="vestra-roles__row" wire:key="role-{{ $role->id }}">
    <td class="vestra-roles__td vestra-roles__td--name">
        <button
            type="button"
            wire:click="openDetailDrawer({{ $role->id }})"
            class="vestra-roles__role-link"
        >
            <span class="vestra-roles__role-icon">
                <x-filament::icon icon="heroicon-o-shield-check" class="h-4 w-4" />
            </span>
            <span class="vestra-roles__role-text">
                <span class="vestra-roles__role-name">{{ $role->name }}</span>
                <span class="vestra-roles__row-meta">{{ $role->guard_name }}</span>
            </span>
        </button>
    </td>

    <td class="vestra-roles__td vestra-roles__td--type">
        <x-roles.type-badge :is-system="$isSystem" />
    </td>

    <td class="vestra-roles__td vestra-roles__td--description">
        <span class="vestra-roles__cell-text">{{ $role->description ?: '—' }}</span>
    </td>

    <td class="vestra-roles__td vestra-roles__td--users">
        <span class="vestra-roles__count">{{ number_format((int) $role->users_count) }}</span>
    </td>

    <td class="vestra-roles__td vestra-roles__td--permissions">
        <span class="vestra-roles__count">{{ number_format((int) $role->permissions_count) }}</span>
    </td>

    <td class="vestra-roles__td vestra-roles__td--updated">
        <span class="vestra-roles__created">{{ $role->updated_at?->format('M j, Y') ?? '—' }}</span>
        <span class="vestra-roles__row-meta">{{ $role->updated_at?->format('g:i A') }}</span>
    </td>

    <td class="vestra-roles__td vestra-roles__td--actions">
        <div class="vestra-roles__actions" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-roles__action-trigger" aria-label="Role actions" aria-haspopup="true">
                <x-filament::icon icon="heroicon-m-ellipsis-vertical" class="h-5 w-5" />
            </button>
            <div x-show="open" x-transition class="vestra-roles__action-menu" role="menu">
                <button
                    type="button"
                    wire:click="openDetailDrawer({{ $role->id }})"
                    class="vestra-roles__action-item"
                    role="menuitem"
                >
                    <x-filament::icon icon="heroicon-o-eye" class="h-4 w-4" />
                    <span>View Details</span>
                </button>
            </div>
        </div>
    </td>
</tr>

@props(['role', 'systemNames' => []])

@php
$isSystem = in_array($role->name, $systemNames, true);
@endphp

<tr class="vestra-roles__row" wire:key="role-{{ $role->id }}">
    <td class="vestra-roles__td">
        <button type="button" wire:click="openDetailDrawer({{ $role->id }})" class="vestra-roles__post-link">
            <span class="vestra-roles__post-title">{{ $role->name }}</span>
        </button>
    </td>
    <td class="vestra-roles__td"><span class="vestra-roles__cell-text">{{ $role->description ?: '—' }}</span></td>
    <td class="vestra-roles__td">
        <span class="vestra-roles__category-pill">{{ $isSystem ? 'System' : 'Custom' }}</span>
    </td>
    <td class="vestra-roles__td">{{ number_format((int) $role->users_count) }}</td>
    <td class="vestra-roles__td">{{ number_format((int) $role->permissions_count) }}</td>
    <td class="vestra-roles__td"><span class="vestra-roles__created">{{ $role->created_at?->format('M j, Y') ?? '—' }}</span></td>
    <td class="vestra-roles__td">
        <button type="button" wire:click="openDetailDrawer({{ $role->id }})" class="vestra-roles__action-trigger" aria-label="View role">
            <x-filament::icon icon="heroicon-o-eye" class="h-5 w-5" />
        </button>
    </td>
</tr>

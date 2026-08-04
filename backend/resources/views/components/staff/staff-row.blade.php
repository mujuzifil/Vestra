@props(['member'])

@php
$avatarUrl = $member->avatarUrl();
$pending = $member->force_password_change_at !== null && $member->force_password_change_at->isFuture();
@endphp

<tr class="vestra-staff__row" wire:key="staff-member-{{ $member->id }}">
    <td class="vestra-staff__td vestra-staff__td--name">
        <button
            type="button"
            wire:click="openDetailDrawer({{ $member->id }})"
            class="vestra-staff__member-link"
        >
            @if ($avatarUrl)
                <img src="{{ $avatarUrl }}" alt="" class="vestra-staff__avatar" loading="lazy" />
            @else
                <span class="vestra-staff__avatar vestra-staff__avatar--initials">{{ $member->initials() }}</span>
            @endif
            <span class="vestra-staff__member-text">
                <span class="vestra-staff__member-name">{{ $member->name }}</span>
                <span class="vestra-staff__row-meta">{{ $member->department ?? '—' }}</span>
            </span>
        </button>
    </td>

    <td class="vestra-staff__td vestra-staff__td--email">
        <span class="vestra-staff__cell-text">{{ $member->email }}</span>
    </td>

    <td class="vestra-staff__td vestra-staff__td--roles">
        @forelse ($member->roles as $role)
            <span class="vestra-staff__role-pill">{{ $role->name }}</span>
        @empty
            <span class="vestra-staff__empty-cell">—</span>
        @endforelse
    </td>

    <td class="vestra-staff__td vestra-staff__td--status">
        <x-staff.status-badge :status="$member->status" />
        @if ($pending)
            <span class="vestra-staff__pending-pill">Reset pending</span>
        @endif
    </td>

    <td class="vestra-staff__td vestra-staff__td--login">
        <span class="vestra-staff__created">{{ $member->last_login_at?->format('M j, Y') ?? 'Never' }}</span>
    </td>

    <td class="vestra-staff__td vestra-staff__td--created">
        <span class="vestra-staff__created">{{ $member->created_at?->format('M j, Y') ?? '—' }}</span>
    </td>

    <td class="vestra-staff__td vestra-staff__td--actions">
        <div class="vestra-staff__actions" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-staff__action-trigger" aria-label="Staff actions" aria-haspopup="true">
                <x-filament::icon icon="heroicon-m-ellipsis-vertical" class="h-5 w-5" />
            </button>
            <div x-show="open" x-transition class="vestra-staff__action-menu" role="menu">
                <button
                    type="button"
                    wire:click="openDetailDrawer({{ $member->id }})"
                    class="vestra-staff__action-item"
                    role="menuitem"
                >
                    <x-filament::icon icon="heroicon-o-eye" class="h-4 w-4" />
                    <span>View Details</span>
                </button>
            </div>
        </div>
    </td>
</tr>

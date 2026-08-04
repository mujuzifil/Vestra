@props(['member'])

<tr class="vestra-staff__row" wire:key="staff-{{ $member->id }}">
    <td class="vestra-staff__td">
        <button type="button" wire:click="openDetailDrawer({{ $member->id }})" class="vestra-staff__post-link">
            @if ($member->avatarUrl())
                <img src="{{ $member->avatarUrl() }}" alt="" class="vestra-staff__thumb" loading="lazy" />
            @else
                <span class="vestra-staff__thumb vestra-staff__thumb--initials">{{ $member->initials() }}</span>
            @endif
            <span class="vestra-staff__post-text">
                <span class="vestra-staff__post-title">{{ $member->name }}</span>
            </span>
        </button>
    </td>
    <td class="vestra-staff__td"><span class="vestra-staff__cell-text">{{ $member->email }}</span></td>
    <td class="vestra-staff__td">
        @forelse ($member->roles as $role)
            <span class="vestra-staff__category-pill">{{ $role->name }}</span>
        @empty
            <span class="vestra-staff__empty-cell">—</span>
        @endforelse
    </td>
    <td class="vestra-staff__td">
        <x-staff.status-badge :status="$member->status" />
    </td>
    <td class="vestra-staff__td">
        <span class="vestra-staff__created">{{ $member->last_login_at?->format('M j, Y') ?? '—' }}</span>
    </td>
    <td class="vestra-staff__td">
        <span class="vestra-staff__created">{{ $member->created_at?->format('M j, Y') ?? '—' }}</span>
    </td>
    <td class="vestra-staff__td">
        <button type="button" wire:click="openDetailDrawer({{ $member->id }})" class="vestra-staff__action-trigger" aria-label="View staff">
            <x-filament::icon icon="heroicon-o-eye" class="h-5 w-5" />
        </button>
    </td>
</tr>

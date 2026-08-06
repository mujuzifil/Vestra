@props([
    'application',
    'selectedIds' => [],
])

@php
use App\Enums\DistributorStatus;

$isSelected = in_array($application->id, $selectedIds, true);
$location = collect([$application->region, $application->country])->filter()->implode(', ') ?: '—';
@endphp

<tr class="vestra-applications__row" wire:key="application-{{ $application->id }}">
    <td class="vestra-applications__td vestra-applications__td--select">
        <input
            type="checkbox"
            class="vestra-applications__filter-checkbox"
            wire:model.live="selectedIds"
            value="{{ $application->id }}"
            @checked($isSelected)
            aria-label="Select application from {{ $application->company_name }}"
        />
    </td>

    <td class="vestra-applications__td vestra-applications__td--company">
        <button
            type="button"
            wire:click="openDetailDrawer({{ $application->id }})"
            class="vestra-applications__company-name"
        >
            {{ $application->company_name ?: '—' }}
        </button>
        <span class="vestra-applications__row-meta">{{ $application->business_type ?: 'Distributor application' }}</span>
    </td>

    <td class="vestra-applications__td vestra-applications__td--contact">
        <div class="vestra-applications__contact">
            <span class="vestra-applications__contact-name">{{ $application->contact_person ?: '—' }}</span>
            @if ($application->email)
                <span class="vestra-applications__contact-email">{{ $application->email }}</span>
            @endif
        </div>
    </td>

    <td class="vestra-applications__td vestra-applications__td--location">
        <span class="vestra-applications__cell-text">{{ $location }}</span>
    </td>

    <td class="vestra-applications__td vestra-applications__td--priority">
        <x-applications.priority-badge :priority="$application->priority" />
    </td>

    <td class="vestra-applications__td vestra-applications__td--status" wire:key="application-status-{{ $application->id }}-{{ $application->status?->value ?? 'unknown' }}">
        <x-applications.status-badge :status="$application->status" />
    </td>

    <td class="vestra-applications__td vestra-applications__td--submitted">
        <span class="vestra-applications__created">{{ $application->created_at?->format('M j, Y') ?? '—' }}</span>
        <span class="vestra-applications__row-meta">{{ $application->created_at?->format('g:i A') }}</span>
    </td>

    <td class="vestra-applications__td vestra-applications__td--actions">
        <div
            class="vestra-applications__actions"
            x-data="{
                open: false,
                menuStyle: {},
                toggle() {
                    this.open = !this.open;
                    if (!this.open) {
                        return;
                    }
                    const rect = this.$refs.trigger.getBoundingClientRect();
                    this.menuStyle = {
                        position: 'fixed',
                        top: (rect.bottom + 4) + 'px',
                        right: (window.innerWidth - rect.right) + 'px',
                        left: 'auto',
                        zIndex: 80,
                    };
                },
            }"
            @click.outside="open = false"
            @keydown.escape.window="open = false"
        >
            <button
                type="button"
                x-ref="trigger"
                @click="toggle()"
                class="vestra-applications__action-trigger"
                aria-label="Application actions"
                aria-haspopup="true"
                :aria-expanded="open.toString()"
            >
                <x-filament::icon icon="heroicon-m-ellipsis-vertical" class="h-5 w-5" />
            </button>
            <div
                x-show="open"
                x-cloak
                x-transition
                :style="menuStyle"
                class="vestra-applications__action-menu"
                role="menu"
            >
                <button type="button" wire:click="openDetailDrawer({{ $application->id }})" class="vestra-applications__action-item" role="menuitem">
                    <x-filament::icon icon="heroicon-o-eye" class="h-4 w-4" />
                    <span>View Details</span>
                </button>
                @if ($application->status !== DistributorStatus::APPROVED || $application->distributor === null)
                    <button
                        type="button"
                        wire:click="approve({{ $application->id }})"
                        wire:confirm="Approve this application and create a distributor account?"
                        class="vestra-applications__action-item"
                        role="menuitem"
                    >
                        <x-filament::icon icon="heroicon-o-check-circle" class="h-4 w-4" />
                        <span>{{ $application->status === DistributorStatus::APPROVED && $application->distributor === null ? 'Repair Account' : 'Approve' }}</span>
                    </button>
                @endif
                @if ($application->status !== DistributorStatus::REJECTED && $application->status !== DistributorStatus::APPROVED)
                    <button
                        type="button"
                        wire:click="reject({{ $application->id }})"
                        wire:confirm="Reject this application?"
                        class="vestra-applications__action-item vestra-applications__action-item--danger"
                        role="menuitem"
                    >
                        <x-filament::icon icon="heroicon-o-x-circle" class="h-4 w-4" />
                        <span>Reject</span>
                    </button>
                @endif
            </div>
        </div>
    </td>
</tr>

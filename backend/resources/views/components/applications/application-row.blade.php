@props([
    'application',
    'selectedIds' => [],
])

@php
use App\Enums\DistributorStatus;

$assignee = $application->assignedAdministrator;
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

    <td class="vestra-applications__td vestra-applications__td--status">
        <x-applications.status-badge :status="$application->status" />
    </td>

    <td class="vestra-applications__td vestra-applications__td--assigned">
        @if ($assignee)
            <div class="vestra-applications__assignee">
                <span class="vestra-applications__assignee-avatar">{{ $assignee->initials() }}</span>
                <span class="vestra-applications__assignee-name">{{ $assignee->name }}</span>
            </div>
        @else
            <span class="vestra-applications__empty-cell">Unassigned</span>
        @endif
    </td>

    <td class="vestra-applications__td vestra-applications__td--submitted">
        <span class="vestra-applications__created">{{ $application->created_at?->format('M j, Y') ?? '—' }}</span>
        <span class="vestra-applications__row-meta">{{ $application->created_at?->format('g:i A') }}</span>
    </td>

    <td class="vestra-applications__td vestra-applications__td--actions">
        <div class="vestra-applications__actions" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-applications__action-trigger" aria-label="Application actions" aria-haspopup="true">
                <x-filament::icon icon="heroicon-m-ellipsis-vertical" class="h-5 w-5" />
            </button>
            <div x-show="open" x-transition class="vestra-applications__action-menu" role="menu">
                <button type="button" wire:click="openDetailDrawer({{ $application->id }})" class="vestra-applications__action-item" role="menuitem">
                    <x-filament::icon icon="heroicon-o-eye" class="h-4 w-4" />
                    <span>View Details</span>
                </button>
                @if ($application->status !== DistributorStatus::APPROVED)
                    <button
                        type="button"
                        wire:click="approve({{ $application->id }})"
                        wire:confirm="Approve this application and create a distributor account?"
                        class="vestra-applications__action-item"
                        role="menuitem"
                    >
                        <x-filament::icon icon="heroicon-o-check-circle" class="h-4 w-4" />
                        <span>Approve</span>
                    </button>
                @endif
                @if ($application->status !== DistributorStatus::REJECTED)
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

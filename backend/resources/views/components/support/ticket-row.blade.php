@props([
    'ticket',
    'selectedIds' => [],
])

@php
$assignee = $ticket->assignedStaff;
$customer = $ticket->user;
$isSelected = in_array($ticket->id, $selectedIds, true);
@endphp

<tr class="vestra-support__row" wire:key="ticket-{{ $ticket->id }}">
    <td class="vestra-support__td vestra-support__td--select">
        <input
            type="checkbox"
            class="vestra-support__filter-checkbox"
            wire:model.live="selectedIds"
            value="{{ $ticket->id }}"
            @checked($isSelected)
            aria-label="Select ticket {{ $ticket->reference_number }}"
        />
    </td>

    <td class="vestra-support__td vestra-support__td--reference">
        <button
            type="button"
            wire:click="openDetailDrawer({{ $ticket->id }})"
            class="vestra-support__reference-link"
        >
            {{ $ticket->reference_number ?: '#'.$ticket->id }}
        </button>
    </td>

    <td class="vestra-support__td vestra-support__td--subject">
        <button
            type="button"
            wire:click="openDetailDrawer({{ $ticket->id }})"
            class="vestra-support__subject-name"
        >
            {{ $ticket->subject ?: '—' }}
        </button>
        @if ($ticket->enquiry_type)
            <span class="vestra-support__row-meta">{{ ucfirst(str_replace('_', ' ', $ticket->enquiry_type)) }}</span>
        @endif
    </td>

    <td class="vestra-support__td vestra-support__td--customer">
        @if ($customer)
            <div class="vestra-support__customer">
                <span class="vestra-support__customer-name">{{ $customer->name }}</span>
                <span class="vestra-support__customer-email">{{ $customer->email }}</span>
            </div>
        @else
            <span class="vestra-support__empty-cell">—</span>
        @endif
    </td>

    <td class="vestra-support__td vestra-support__td--type">
        <span class="vestra-support__cell-text">{{ ucfirst(str_replace('_', ' ', $ticket->enquiry_type ?? '—')) }}</span>
    </td>

    <td class="vestra-support__td vestra-support__td--priority">
        <x-support.priority-badge :priority="$ticket->priority" />
    </td>

    <td class="vestra-support__td vestra-support__td--status">
        <x-support.status-badge :status="$ticket->status" />
    </td>

    <td class="vestra-support__td vestra-support__td--assigned">
        @if ($assignee)
            <div class="vestra-support__assignee">
                <span class="vestra-support__assignee-avatar">{{ $assignee->initials() }}</span>
                <span class="vestra-support__assignee-name">{{ $assignee->name }}</span>
            </div>
        @else
            <span class="vestra-support__empty-cell">Unassigned</span>
        @endif
    </td>

    <td class="vestra-support__td vestra-support__td--submitted">
        <span class="vestra-support__created">{{ $ticket->created_at?->format('M j, Y') ?? '—' }}</span>
        <span class="vestra-support__row-meta">{{ $ticket->created_at?->format('g:i A') }}</span>
    </td>

    <td class="vestra-support__td vestra-support__td--actions">
        <div class="vestra-support__actions" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-support__action-trigger" aria-label="Ticket actions" aria-haspopup="true">
                <x-filament::icon icon="heroicon-m-ellipsis-vertical" class="h-5 w-5" />
            </button>
            <div x-show="open" x-transition class="vestra-support__action-menu" role="menu">
                <button
                    type="button"
                    wire:click="openDetailDrawer({{ $ticket->id }})"
                    class="vestra-support__action-item"
                    role="menuitem"
                >
                    <x-filament::icon icon="heroicon-o-eye" class="h-4 w-4" />
                    <span>View Details</span>
                </button>
            </div>
        </div>
    </td>
</tr>

@props([
    'enquiry',
    'sortField' => 'created_at',
])

@php
use App\Enums\ContactStatus;

$assignee = $enquiry->assignedTo;
@endphp

<tr class="vestra-enquiries__row @if(! $enquiry->read_at) vestra-enquiries__row--unread @endif" wire:key="enquiry-{{ $enquiry->id }}">

    <td class="vestra-enquiries__td vestra-enquiries__td--sender">
        <button
            type="button"
            wire:click="openDetailDrawer({{ $enquiry->id }})"
            class="vestra-enquiries__sender-name"
        >
            {{ $enquiry->name ?: '—' }}
        </button>
        @if ($enquiry->email)
            <span class="vestra-enquiries__row-meta">{{ $enquiry->email }}</span>
        @endif
        @if ($enquiry->company)
            <span class="vestra-enquiries__row-meta">{{ $enquiry->company }}</span>
        @endif
    </td>

    <td class="vestra-enquiries__td vestra-enquiries__td--subject">
        <span class="vestra-enquiries__subject">{{ \Illuminate\Support\Str::limit($enquiry->subject ?? '—', 50) }}</span>
    </td>

    <td class="vestra-enquiries__td vestra-enquiries__td--enquiry-type">
        @if ($enquiry->enquiry_type)
            <span class="vestra-enquiries__type-badge">{{ $enquiry->enquiry_type->label() }}</span>
        @else
            <span class="vestra-enquiries__empty-cell">—</span>
        @endif
    </td>

    <td class="vestra-enquiries__td vestra-enquiries__td--priority">
        <x-enquiries.priority-badge :priority="$enquiry->priority" />
    </td>

    <td class="vestra-enquiries__td vestra-enquiries__td--status">
        <x-enquiries.status-badge :status="$enquiry->status" />
    </td>

    <td class="vestra-enquiries__td vestra-enquiries__td--assigned">
        @if ($assignee)
            <div class="vestra-enquiries__assignee">
                <span class="vestra-enquiries__assignee-avatar">{{ $assignee->initials() }}</span>
                <span class="vestra-enquiries__assignee-name">{{ $assignee->name }}</span>
            </div>
        @else
            <span class="vestra-enquiries__empty-cell">Unassigned</span>
        @endif
    </td>

    <td class="vestra-enquiries__td vestra-enquiries__td--read">
        @if ($enquiry->read_at)
            <x-filament::icon icon="heroicon-o-envelope-open" class="h-4 w-4 text-success-500" aria-label="Read" />
        @else
            <x-filament::icon icon="heroicon-o-envelope" class="h-4 w-4 text-warning-500" aria-label="Unread" />
        @endif
    </td>

    <td class="vestra-enquiries__td vestra-enquiries__td--replied">
        @if ($enquiry->replied_at)
            <x-filament::icon icon="heroicon-o-check-circle" class="h-4 w-4 text-success-500" aria-label="Replied" />
        @else
            <x-filament::icon icon="heroicon-o-minus-small" class="h-4 w-4 text-gray-400" aria-label="Not replied" />
        @endif
    </td>

    <td class="vestra-enquiries__td vestra-enquiries__td--received">
        <span class="vestra-enquiries__created">{{ $enquiry->created_at?->format('M j, Y') ?? '—' }}</span>
        <span class="vestra-enquiries__row-meta">{{ $enquiry->created_at?->format('g:i A') }}</span>
    </td>

    <td class="vestra-enquiries__td vestra-enquiries__td--actions">
        <div class="vestra-enquiries__actions" x-data="{ open: false }" @click.outside="open = false">
            <button
                type="button"
                @click="open = !open"
                class="vestra-enquiries__action-trigger"
                aria-label="Enquiry actions"
                aria-haspopup="true"
            >
                <x-filament::icon icon="heroicon-m-ellipsis-vertical" class="h-5 w-5" />
            </button>
            <div x-show="open" x-transition class="vestra-enquiries__action-menu" role="menu">
                <button
                    type="button"
                    wire:click="openDetailDrawer({{ $enquiry->id }})"
                    class="vestra-enquiries__action-item"
                    role="menuitem"
                >
                    <x-filament::icon icon="heroicon-o-eye" class="h-4 w-4" />
                    <span>View Details</span>
                </button>
                @if ($enquiry->status !== ContactStatus::RESOLVED)
                    <button
                        type="button"
                        wire:click="markResolved({{ $enquiry->id }})"
                        wire:confirm="Mark this enquiry as resolved?"
                        class="vestra-enquiries__action-item"
                        role="menuitem"
                    >
                        <x-filament::icon icon="heroicon-o-check-circle" class="h-4 w-4" />
                        <span>Mark Resolved</span>
                    </button>
                @endif
            </div>
        </div>
    </td>
</tr>

@props([
    'enquiry',
    'sortField' => 'created_at',
])

@php
use App\Enums\ContactStatus;
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
        <span class="vestra-enquiries__subject">{{ \Illuminate\Support\Str::limit($enquiry->subject ?? '—', 60) }}</span>
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

    <td class="vestra-enquiries__td vestra-enquiries__td--received">
        <span class="vestra-enquiries__created">{{ $enquiry->created_at?->format('M j, Y') ?? '—' }}</span>
        <span class="vestra-enquiries__row-meta">{{ $enquiry->created_at?->format('g:i A') }}</span>
    </td>

    <td class="vestra-enquiries__td vestra-enquiries__td--actions">
        <div
            class="vestra-enquiries__actions"
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
                class="vestra-enquiries__action-trigger"
                aria-label="Enquiry actions"
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
                class="vestra-enquiries__action-menu"
                role="menu"
            >
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

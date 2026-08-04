@props(['feedback'])

@php
use App\Enums\FeedbackStatus;

$isRead = $feedback->isRead();
@endphp

<tr class="vestra-feedback__row @if(! $isRead) vestra-feedback__row--unread @endif" wire:key="feedback-{{ $feedback->id }}">
    <td class="vestra-feedback__td vestra-feedback__td--customer">
        <button
            type="button"
            wire:click="openDetailDrawer({{ $feedback->id }})"
            class="vestra-feedback__customer-name"
        >
            {{ $feedback->user?->name ?? 'Unknown' }}
        </button>
        @if ($feedback->user?->email)
            <span class="vestra-feedback__row-meta">{{ $feedback->user->email }}</span>
        @endif
    </td>

    <td class="vestra-feedback__td vestra-feedback__td--subject">
        <button
            type="button"
            wire:click="openDetailDrawer({{ $feedback->id }})"
            class="vestra-feedback__subject-link"
        >
            {{ Str::limit($feedback->subject, 50) }}
        </button>
    </td>

    <td class="vestra-feedback__td vestra-feedback__td--category">
        <x-feedback.category-badge :category="$feedback->category" />
    </td>

    <td class="vestra-feedback__td vestra-feedback__td--priority">
        <x-feedback.priority-badge :priority="$feedback->priority" />
    </td>

    <td class="vestra-feedback__td vestra-feedback__td--status">
        <x-feedback.status-badge :status="$feedback->status" />
    </td>

    <td class="vestra-feedback__td vestra-feedback__td--read">
        @if ($isRead)
            <x-filament::icon icon="heroicon-o-envelope-open" class="h-4 w-4 vestra-feedback__read-icon vestra-feedback__read-icon--read" />
        @else
            <x-filament::icon icon="heroicon-o-envelope" class="h-4 w-4 vestra-feedback__read-icon vestra-feedback__read-icon--unread" />
        @endif
    </td>

    <td class="vestra-feedback__td vestra-feedback__td--submitted">
        <span class="vestra-feedback__created">{{ $feedback->created_at?->format('M j, Y') ?? '—' }}</span>
        <span class="vestra-feedback__row-meta">{{ $feedback->created_at?->format('g:i A') }}</span>
    </td>

    <td class="vestra-feedback__td vestra-feedback__td--actions">
        <div class="vestra-feedback__actions" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-feedback__action-trigger" aria-label="Feedback actions" aria-haspopup="true">
                <x-filament::icon icon="heroicon-m-ellipsis-vertical" class="h-5 w-5" />
            </button>
            <div x-show="open" x-transition class="vestra-feedback__action-menu" role="menu">
                <button type="button" wire:click="openDetailDrawer({{ $feedback->id }})" class="vestra-feedback__action-item" role="menuitem">
                    <x-filament::icon icon="heroicon-o-eye" class="h-4 w-4" />
                    <span>View</span>
                </button>
                @if ($feedback->status !== FeedbackStatus::IN_PROGRESS->value)
                    <button
                        type="button"
                        wire:click="markInProgress({{ $feedback->id }})"
                        class="vestra-feedback__action-item"
                        role="menuitem"
                    >
                        <x-filament::icon icon="heroicon-o-arrow-path" class="h-4 w-4" />
                        <span>Mark In Progress</span>
                    </button>
                @endif
                @if ($feedback->status !== FeedbackStatus::RESOLVED->value)
                    <button
                        type="button"
                        wire:click="markResolved({{ $feedback->id }})"
                        wire:confirm="Mark this feedback as resolved?"
                        class="vestra-feedback__action-item"
                        role="menuitem"
                    >
                        <x-filament::icon icon="heroicon-o-check-circle" class="h-4 w-4" />
                        <span>Resolve</span>
                    </button>
                @endif
                @if ($isRead)
                    <button type="button" wire:click="markUnread({{ $feedback->id }})" class="vestra-feedback__action-item" role="menuitem">
                        <x-filament::icon icon="heroicon-o-envelope" class="h-4 w-4" />
                        <span>Mark Unread</span>
                    </button>
                @else
                    <button type="button" wire:click="markRead({{ $feedback->id }})" class="vestra-feedback__action-item" role="menuitem">
                        <x-filament::icon icon="heroicon-o-envelope-open" class="h-4 w-4" />
                        <span>Mark Read</span>
                    </button>
                @endif
            </div>
        </div>
    </td>
</tr>

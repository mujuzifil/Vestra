@props([
    'show' => false,
    'feedback' => null,
])

@php
use App\Enums\FeedbackStatus;
@endphp

<div
    class="vestra-feedback-detail @if ($show) vestra-feedback-detail--open @endif"
    x-data="{ open: @js($show) }"
    x-show="open"
    x-cloak
    @keydown.escape.window="if (open) $wire.closeDetailDrawer()"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 translate-x-4"
    x-transition:enter-end="opacity-100 translate-x-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-x-0"
    x-transition:leave-end="opacity-0 translate-x-4"
    aria-label="Feedback details"
    role="dialog"
    aria-modal="true"
>
    <div class="vestra-feedback-detail__overlay" wire:click="closeDetailDrawer"></div>

    <div class="vestra-feedback-detail__panel">
        @if ($feedback)
            @php
            $status = $feedback['status'];
            $user = $feedback['user'] ?? null;
            $initials = $user ? strtoupper(substr($user['name'] ?? '?', 0, 2)) : '?';
            @endphp

            <div class="vestra-feedback-detail__header">
                <div class="vestra-feedback-detail__header-main">
                    <span class="vestra-feedback-detail__avatar">{{ $initials }}</span>
                    <div class="vestra-feedback-detail__header-text">
                        <h2 class="vestra-feedback-detail__title">{{ $feedback['subject'] ?? 'Feedback' }}</h2>
                        <p class="vestra-feedback-detail__subtitle">
                            {{ $user['name'] ?? 'Unknown customer' }}
                            @if ($user['email'] ?? null)
                                &middot; {{ $user['email'] }}
                            @endif
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    wire:click="closeDetailDrawer"
                    class="vestra-feedback-detail__close"
                    aria-label="Close details"
                >
                    <x-filament::icon icon="heroicon-o-x-mark" class="h-5 w-5" />
                </button>
            </div>

            <div class="vestra-feedback-detail__body">
                <div class="vestra-feedback-detail__badges">
                    <x-feedback.status-badge :status="$status" />
                    <x-feedback.category-badge :category="$feedback['category'] ?? null" />
                    <x-feedback.priority-badge :priority="$feedback['priority'] ?? null" />
                </div>

                <div class="vestra-feedback-detail__quick-actions">
                    @if ($status !== FeedbackStatus::IN_PROGRESS->value)
                        <button
                            type="button"
                            wire:click="markInProgress({{ $feedback['id'] }})"
                            class="vestra-feedback-detail__quick-action"
                        >
                            <x-filament::icon icon="heroicon-o-arrow-path" class="h-4 w-4" />
                            <span>In Progress</span>
                        </button>
                    @endif
                    @if ($status !== FeedbackStatus::RESOLVED->value)
                        <button
                            type="button"
                            wire:click="markResolved({{ $feedback['id'] }})"
                            wire:confirm="Mark this feedback as resolved?"
                            class="vestra-feedback-detail__quick-action vestra-feedback-detail__quick-action--success"
                        >
                            <x-filament::icon icon="heroicon-o-check-circle" class="h-4 w-4" />
                            <span>Resolve</span>
                        </button>
                    @endif
                    @if ($feedback['read_at'])
                        <button
                            type="button"
                            wire:click="markUnread({{ $feedback['id'] }})"
                            class="vestra-feedback-detail__quick-action"
                        >
                            <x-filament::icon icon="heroicon-o-envelope" class="h-4 w-4" />
                            <span>Mark Unread</span>
                        </button>
                    @else
                        <button
                            type="button"
                            wire:click="markRead({{ $feedback['id'] }})"
                            class="vestra-feedback-detail__quick-action"
                        >
                            <x-filament::icon icon="heroicon-o-envelope-open" class="h-4 w-4" />
                            <span>Mark Read</span>
                        </button>
                    @endif
                </div>

                <div class="vestra-feedback-detail__section">
                    <h3 class="vestra-feedback-detail__section-title">Message</h3>
                    <p class="vestra-feedback-detail__message">{{ $feedback['message'] ?: 'No message provided.' }}</p>
                </div>

                <div class="vestra-feedback-detail__section">
                    <h3 class="vestra-feedback-detail__section-title">Customer</h3>
                    @if ($user)
                        <div class="vestra-feedback-detail__contact">
                            <p class="vestra-feedback-detail__contact-name">{{ $user['name'] }}</p>
                            <p class="vestra-feedback-detail__contact-meta">{{ $user['email'] }}</p>
                        </div>
                    @else
                        <p class="vestra-feedback-detail__text">No customer information available.</p>
                    @endif
                </div>

                <div class="vestra-feedback-detail__section">
                    <h3 class="vestra-feedback-detail__section-title">Details</h3>
                    <dl class="vestra-feedback-detail__definition-list">
                        <div class="vestra-feedback-detail__definition-row">
                            <dt>Category</dt>
                            <dd>{{ $feedback['category_label'] ?? '—' }}</dd>
                        </div>
                        <div class="vestra-feedback-detail__definition-row">
                            <dt>Priority</dt>
                            <dd>{{ $feedback['priority_label'] ?? '—' }}</dd>
                        </div>
                        <div class="vestra-feedback-detail__definition-row">
                            <dt>Read</dt>
                            <dd>{{ $feedback['read_at'] ? $feedback['read_at']->format('M j, Y g:i A') : 'Unread' }}</dd>
                        </div>
                        <div class="vestra-feedback-detail__definition-row">
                            <dt>Submitted</dt>
                            <dd>{{ $feedback['created_at']?->format('M j, Y g:i A') ?? '—' }}</dd>
                        </div>
                        <div class="vestra-feedback-detail__definition-row">
                            <dt>Last Updated</dt>
                            <dd>{{ $feedback['updated_at']?->format('M j, Y g:i A') ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        @else
            <div class="vestra-feedback-detail__empty">
                <p>Select a feedback item to view details.</p>
            </div>
        @endif
    </div>
</div>

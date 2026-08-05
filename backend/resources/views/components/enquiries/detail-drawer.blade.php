@props([
    'show' => false,
    'enquiry' => null,
])

@php
use App\Enums\ContactStatus;

$display = function ($value, string $fallback = 'Not provided') {
    if ($value === null) {
        return $fallback;
    }

    if (is_string($value) && trim($value) === '') {
        return $fallback;
    }

    return $value;
};
@endphp

<div
    class="vestra-enquiries-detail @if ($show) vestra-enquiries-detail--open @endif"
    x-data="{ open: @entangle('showDetailDrawer') }"
    x-show="open"
    x-cloak
    @keydown.escape.window="if (open) $wire.closeDetailDrawer()"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 translate-x-4"
    x-transition:enter-end="opacity-100 translate-x-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-x-0"
    x-transition:leave-end="opacity-0 translate-x-4"
    aria-label="Enquiry details"
    role="dialog"
    aria-modal="true"
>
    <div class="vestra-enquiries-detail__overlay" wire:click="closeDetailDrawer"></div>

    <div class="vestra-enquiries-detail__panel">
        @if ($enquiry)
            <div class="vestra-enquiries-detail__header">
                <div class="vestra-enquiries-detail__header-main">
                    <span class="vestra-enquiries-detail__avatar">{{ strtoupper(substr($enquiry['name'] ?? '?', 0, 2)) }}</span>
                    <div class="vestra-enquiries-detail__header-text">
                        <h2 class="vestra-enquiries-detail__title">{{ $display($enquiry['name'] ?? null, 'Enquiry') }}</h2>
                        <p class="vestra-enquiries-detail__subtitle">
                            {{ $display($enquiry['email'] ?? null, 'No email') }}
                            @if (filled($enquiry['company'] ?? null))
                                · {{ $enquiry['company'] }}
                            @endif
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    wire:click="closeDetailDrawer"
                    class="vestra-enquiries-detail__close"
                    aria-label="Close details"
                >
                    <x-filament::icon icon="heroicon-o-x-mark" class="h-5 w-5" />
                </button>
            </div>

            <div class="vestra-enquiries-detail__body">
                <div class="vestra-enquiries-detail__badges">
                    <x-enquiries.status-badge :status="$enquiry['status']" />
                    <x-enquiries.priority-badge :priority="$enquiry['priority']" />
                    @if ($enquiry['enquiry_type'] ?? null)
                        <span class="vestra-enquiries__type-badge">{{ $enquiry['enquiry_type_label'] }}</span>
                    @endif
                </div>

                <div class="vestra-enquiries-detail__quick-actions">
                    @if ($enquiry['status'] !== ContactStatus::RESOLVED)
                        <button
                            type="button"
                            wire:click="markResolved({{ $enquiry['id'] }})"
                            wire:confirm="Mark this enquiry as resolved?"
                            class="vestra-enquiries-detail__quick-action"
                        >
                            <x-filament::icon icon="heroicon-o-check-circle" class="h-4 w-4" />
                            <span>Mark Resolved</span>
                        </button>
                    @endif
                    <button type="button" onclick="window.print()" class="vestra-enquiries-detail__quick-action">
                        <x-filament::icon icon="heroicon-o-printer" class="h-4 w-4" />
                        <span>Print</span>
                    </button>
                </div>

                <div class="vestra-enquiries-detail__section">
                    <h3 class="vestra-enquiries-detail__section-title">Customer</h3>
                    <dl class="vestra-enquiries-detail__definition-list">
                        <div class="vestra-enquiries-detail__definition-row">
                            <dt>Customer Name</dt>
                            <dd>{{ $display($enquiry['name'] ?? null) }}</dd>
                        </div>
                        <div class="vestra-enquiries-detail__definition-row">
                            <dt>Company</dt>
                            <dd>{{ $display($enquiry['company'] ?? null) }}</dd>
                        </div>
                        <div class="vestra-enquiries-detail__definition-row">
                            <dt>Email</dt>
                            <dd>{{ $display($enquiry['email'] ?? null) }}</dd>
                        </div>
                        <div class="vestra-enquiries-detail__definition-row">
                            <dt>Telephone</dt>
                            <dd>{{ $display($enquiry['phone'] ?? null) }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="vestra-enquiries-detail__section">
                    <h3 class="vestra-enquiries-detail__section-title">Enquiry</h3>
                    <dl class="vestra-enquiries-detail__definition-list">
                        <div class="vestra-enquiries-detail__definition-row">
                            <dt>Subject</dt>
                            <dd>{{ $display($enquiry['subject'] ?? null) }}</dd>
                        </div>
                        <div class="vestra-enquiries-detail__definition-row">
                            <dt>Category</dt>
                            <dd>{{ $display($enquiry['enquiry_type_label'] ?? null) }}</dd>
                        </div>
                        <div class="vestra-enquiries-detail__definition-row">
                            <dt>Priority</dt>
                            <dd>{{ $display($enquiry['priority_label'] ?? null) }}</dd>
                        </div>
                        <div class="vestra-enquiries-detail__definition-row">
                            <dt>Current Status</dt>
                            <dd>{{ $display($enquiry['status_label'] ?? null) }}</dd>
                        </div>
                        <div class="vestra-enquiries-detail__definition-row">
                            <dt>Submitted Date</dt>
                            <dd>{{ $enquiry['created_at']?->format('M j, Y g:i A') ?? 'Not provided' }}</dd>
                        </div>
                        <div class="vestra-enquiries-detail__definition-row">
                            <dt>Source</dt>
                            <dd>{{ $display(isset($enquiry['source']) && $enquiry['source'] ? ucfirst($enquiry['source']) : null) }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="vestra-enquiries-detail__section">
                    <h3 class="vestra-enquiries-detail__section-title">Message</h3>
                    <p class="vestra-enquiries-detail__text">{{ $display($enquiry['message'] ?? null) }}</p>
                </div>

                <div class="vestra-enquiries-detail__section">
                    <h3 class="vestra-enquiries-detail__section-title">Attachments</h3>
                    @if (! empty($enquiry['attachments']))
                        <ul class="vestra-enquiries-detail__attachment-list">
                            @foreach ($enquiry['attachments'] as $attachment)
                                <li>
                                    @if (is_array($attachment) && ($attachment['url'] ?? null))
                                        <a href="{{ $attachment['url'] }}" target="_blank" rel="noopener noreferrer" class="vestra-enquiries-detail__link">
                                            <x-filament::icon icon="heroicon-o-paper-clip" class="h-3.5 w-3.5" />
                                            {{ $attachment['name'] ?? 'Attachment' }}
                                        </a>
                                    @else
                                        <span>{{ is_array($attachment) ? ($attachment['name'] ?? 'Attachment') : (is_string($attachment) ? $attachment : 'Attachment') }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="vestra-enquiries-detail__text">Not provided</p>
                    @endif
                </div>

                <div class="vestra-enquiries-detail__section">
                    <h3 class="vestra-enquiries-detail__section-title">Reply</h3>
                    @if ($enquiry['replied_at'] ?? null)
                        <p class="vestra-enquiries-detail__text vestra-enquiries-detail__text--muted">
                            Reply sent on {{ $enquiry['replied_at']->format('M j, Y g:i A') }}.
                        </p>
                        <blockquote class="vestra-enquiries-detail__reply-quote">{{ $display($enquiry['reply'] ?? null) }}</blockquote>
                    @else
                        <textarea
                            wire:model.live="replyDraft"
                            rows="5"
                            class="vestra-enquiries-detail__textarea"
                            placeholder="Type your reply here..."
                            aria-label="Reply draft"
                        ></textarea>
                        <div class="vestra-enquiries-detail__reply-actions">
                            <button type="button" wire:click="saveReply" class="vestra-button vestra-button--secondary">
                                Save Draft
                            </button>
                            <button
                                type="button"
                                wire:click="sendReply"
                                wire:confirm="Send reply email to {{ $enquiry['email'] }}?"
                                class="vestra-button vestra-button--primary"
                            >
                                <x-filament::icon icon="heroicon-o-paper-airplane" class="h-4 w-4" />
                                Send Reply
                            </button>
                        </div>
                    @endif
                </div>

                <div class="vestra-enquiries-detail__section">
                    <h3 class="vestra-enquiries-detail__section-title">Update Status</h3>
                    <div class="vestra-enquiries-detail__status-buttons">
                        <button type="button" wire:click="updateStatus({{ $enquiry['id'] }}, 'new')" class="vestra-button vestra-button--secondary">
                            New
                        </button>
                        <button type="button" wire:click="updateStatus({{ $enquiry['id'] }}, 'in_progress')" class="vestra-button vestra-button--secondary">
                            In Progress
                        </button>
                        <button type="button" wire:click="updateStatus({{ $enquiry['id'] }}, 'resolved')" class="vestra-button vestra-button--secondary">
                            Resolved
                        </button>
                    </div>
                </div>

                <div class="vestra-enquiries-detail__section">
                    <h3 class="vestra-enquiries-detail__section-title">Internal Notes</h3>
                    <div x-data="{ notes: @js($enquiry['internal_notes'] ?? '') }">
                        <textarea
                            x-model="notes"
                            rows="4"
                            class="vestra-enquiries-detail__textarea"
                            placeholder="Internal notes (not visible to customer)..."
                            aria-label="Internal notes"
                        ></textarea>
                        <button
                            type="button"
                            @click="$wire.saveInternalNotes({{ $enquiry['id'] }}, notes)"
                            class="vestra-button vestra-button--secondary"
                        >
                            Save Notes
                        </button>
                    </div>
                </div>

                <div class="vestra-enquiries-detail__section">
                    <h3 class="vestra-enquiries-detail__section-title">Activity</h3>
                    @if (! empty($enquiry['activity']))
                        <ul class="vestra-enquiries-detail__attachment-list">
                            @foreach ($enquiry['activity'] as $event)
                                <li>
                                    <strong>{{ $event['label'] }}</strong>
                                    <span class="vestra-enquiries-detail__text--muted"> — {{ $event['at_label'] ?? 'Not provided' }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="vestra-enquiries-detail__text">Not provided</p>
                    @endif
                </div>
            </div>
        @else
            <div class="vestra-enquiries-detail__empty">
                <p>Select an enquiry to view details.</p>
            </div>
        @endif
    </div>
</div>

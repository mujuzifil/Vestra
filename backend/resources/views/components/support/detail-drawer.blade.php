@props([
    'show' => false,
    'ticket' => null,
])

<div
    class="vestra-support-detail @if ($show) vestra-support-detail--open @endif"
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
    aria-label="Ticket details"
    role="dialog"
    aria-modal="true"
>
    <div class="vestra-support-detail__overlay" wire:click="closeDetailDrawer"></div>

    <div class="vestra-support-detail__panel">
        @if ($ticket)
            <div class="vestra-support-detail__header">
                <div class="vestra-support-detail__header-main">
                    <span class="vestra-support-detail__avatar">
                        <x-filament::icon icon="heroicon-o-lifebuoy" class="h-5 w-5" />
                    </span>
                    <div class="vestra-support-detail__header-text">
                        <h2 class="vestra-support-detail__title">{{ $ticket['subject'] ?? 'Ticket' }}</h2>
                        <p class="vestra-support-detail__subtitle">{{ $ticket['reference_number'] ?? '' }}</p>
                    </div>
                </div>

                <button
                    type="button"
                    wire:click="closeDetailDrawer"
                    class="vestra-support-detail__close"
                    aria-label="Close details"
                >
                    <x-filament::icon icon="heroicon-o-x-mark" class="h-5 w-5" />
                </button>
            </div>

            <div class="vestra-support-detail__body">
                <div class="vestra-support-detail__badges">
                    <x-support.status-badge :status="$ticket['status']" />
                    <x-support.priority-badge :priority="$ticket['priority']" />
                </div>

                <div class="vestra-support-detail__section">
                    <h3 class="vestra-support-detail__section-title">Update Status</h3>
                    <div class="vestra-support-detail__status-update">
                        <select
                            wire:model="updateStatus"
                            class="vestra-support__filter-input"
                            aria-label="Update ticket status"
                        >
                            <option value="open">Open</option>
                            <option value="in_progress">In Progress</option>
                            <option value="resolved">Resolved</option>
                            <option value="closed">Closed</option>
                        </select>
                        <button
                            type="button"
                            wire:click="updateTicketStatus({{ $ticket['id'] }})"
                            class="vestra-button vestra-button--primary vestra-button--sm"
                        >
                            Save
                        </button>
                    </div>
                </div>

                <div class="vestra-support-detail__section">
                    <h3 class="vestra-support-detail__section-title">Ticket Information</h3>
                    <dl class="vestra-support-detail__definition-list">
                        <div class="vestra-support-detail__definition-row">
                            <dt>Reference</dt>
                            <dd>{{ $ticket['reference_number'] ?? '—' }}</dd>
                        </div>
                        <div class="vestra-support-detail__definition-row">
                            <dt>Enquiry Type</dt>
                            <dd>{{ ucfirst(str_replace('_', ' ', $ticket['enquiry_type'] ?? '—')) }}</dd>
                        </div>
                        <div class="vestra-support-detail__definition-row">
                            <dt>Submitted</dt>
                            <dd>{{ $ticket['created_at']?->format('M j, Y g:i A') ?? '—' }}</dd>
                        </div>
                        @if ($ticket['resolved_at'] ?? null)
                            <div class="vestra-support-detail__definition-row">
                                <dt>Resolved At</dt>
                                <dd>{{ $ticket['resolved_at']->format('M j, Y g:i A') }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <div class="vestra-support-detail__section">
                    <h3 class="vestra-support-detail__section-title">Customer</h3>
                    @if ($ticket['user'] ?? null)
                        <div class="vestra-support-detail__contact">
                            <span class="vestra-support-detail__avatar-sm">{{ $ticket['user']['initials'] }}</span>
                            <div>
                                <p class="vestra-support-detail__contact-name">{{ $ticket['user']['name'] }}</p>
                                <p class="vestra-support-detail__contact-meta">{{ $ticket['user']['email'] }}</p>
                            </div>
                        </div>
                    @else
                        <p class="vestra-support-detail__text">No customer on record.</p>
                    @endif
                </div>

                <div class="vestra-support-detail__section">
                    <h3 class="vestra-support-detail__section-title">Original Message</h3>
                    <p class="vestra-support-detail__text vestra-support-detail__message">{{ $ticket['message'] ?? '—' }}</p>
                </div>

                @if (! empty($ticket['attachments']))
                    <div class="vestra-support-detail__section">
                        <h3 class="vestra-support-detail__section-title">Attachments</h3>
                        <ul class="vestra-support-detail__attachment-list">
                            @foreach ($ticket['attachments'] as $attachment)
                                <li>
                                    <a href="{{ asset('storage/'.$attachment) }}" target="_blank" rel="noopener noreferrer" class="vestra-support-detail__link">
                                        <x-filament::icon icon="heroicon-o-paper-clip" class="h-3.5 w-3.5" />
                                        {{ basename($attachment) }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="vestra-support-detail__section">
                    <h3 class="vestra-support-detail__section-title">Assigned Staff</h3>
                    @if ($ticket['assignee'] ?? null)
                        <div class="vestra-support-detail__contact">
                            <span class="vestra-support-detail__avatar-sm">{{ $ticket['assignee']['initials'] }}</span>
                            <div>
                                <p class="vestra-support-detail__contact-name">{{ $ticket['assignee']['name'] }}</p>
                                <p class="vestra-support-detail__contact-meta">{{ $ticket['assignee']['email'] }}</p>
                            </div>
                        </div>
                    @else
                        <p class="vestra-support-detail__text">No staff assigned.</p>
                    @endif
                </div>

                @if (! empty($ticket['replies']))
                    <div class="vestra-support-detail__section">
                        <h3 class="vestra-support-detail__section-title">Conversation ({{ count($ticket['replies']) }})</h3>
                        <div class="vestra-support-detail__replies">
                            @foreach ($ticket['replies'] as $reply)
                                <div class="vestra-support-detail__reply @if ($reply['is_internal']) vestra-support-detail__reply--internal @endif @if ($reply['is_staff']) vestra-support-detail__reply--staff @endif">
                                    <div class="vestra-support-detail__reply-header">
                                        <span class="vestra-support-detail__reply-author">{{ $reply['author_name'] }}</span>
                                        @if ($reply['is_internal'])
                                            <span class="vestra-support__badge vestra-support__badge--internal">Internal</span>
                                        @endif
                                        <span class="vestra-support-detail__reply-time">{{ $reply['created_at']?->diffForHumans() }}</span>
                                    </div>
                                    <p class="vestra-support-detail__reply-body">{{ $reply['message'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($ticket['status'] !== 'closed')
                    <div class="vestra-support-detail__section">
                        <h3 class="vestra-support-detail__section-title">Reply</h3>
                        <div class="vestra-support-detail__reply-form">
                            <textarea
                                wire:model="replyMessage"
                                rows="4"
                                placeholder="Type your reply..."
                                class="vestra-support__filter-input vestra-support-detail__reply-textarea"
                                aria-label="Reply message"
                            ></textarea>
                            <div class="vestra-support-detail__reply-options">
                                <label class="vestra-support__filter-option">
                                    <input
                                        type="checkbox"
                                        wire:model="replyIsInternal"
                                        class="vestra-support__filter-checkbox"
                                    />
                                    <span class="vestra-support__filter-option-label">Internal note (not visible to customer)</span>
                                </label>
                                <button
                                    type="button"
                                    wire:click="submitReply"
                                    wire:loading.attr="disabled"
                                    class="vestra-button vestra-button--primary"
                                >
                                    <span wire:loading.remove>Send Reply</span>
                                    <span wire:loading>Sending...</span>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @else
            <div class="vestra-support-detail__empty">
                <p>Select a ticket to view details.</p>
            </div>
        @endif
    </div>
</div>

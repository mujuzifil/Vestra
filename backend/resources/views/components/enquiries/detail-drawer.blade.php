@props([
    'show'    => false,
    'enquiry' => null,
])

@php
use App\Enums\ContactStatus;
use App\Models\User;

$adminUsers = User::query()->where('is_admin', true)->orderBy('name')->get(['id', 'name']);
@endphp

<div
    class="vestra-enquiries-detail @if ($show) vestra-enquiries-detail--open @endif"
    x-data="{ open: @js($show), replyTab: 'message' }"
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
                        <h2 class="vestra-enquiries-detail__title">{{ $enquiry['name'] ?? 'Enquiry' }}</h2>
                        <p class="vestra-enquiries-detail__subtitle">
                            {{ $enquiry['email'] ?? '' }}
                            @if ($enquiry['company'] ?? null)
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

                {{-- Quick actions --}}
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

                {{-- Contact info --}}
                <div class="vestra-enquiries-detail__section">
                    <h3 class="vestra-enquiries-detail__section-title">Contact Information</h3>
                    <dl class="vestra-enquiries-detail__definition-list">
                        @if ($enquiry['phone'] ?? null)
                            <div class="vestra-enquiries-detail__definition-row">
                                <dt>Phone</dt>
                                <dd>{{ $enquiry['phone'] }}</dd>
                            </div>
                        @endif
                        @if ($enquiry['source'] ?? null)
                            <div class="vestra-enquiries-detail__definition-row">
                                <dt>Source</dt>
                                <dd>{{ ucfirst($enquiry['source']) }}</dd>
                            </div>
                        @endif
                        <div class="vestra-enquiries-detail__definition-row">
                            <dt>Received</dt>
                            <dd>{{ $enquiry['created_at']?->format('M j, Y g:i A') ?? '—' }}</dd>
                        </div>
                        <div class="vestra-enquiries-detail__definition-row">
                            <dt>Read</dt>
                            <dd>{{ $enquiry['read_at'] ? $enquiry['read_at']->format('M j, Y g:i A') : 'Not yet' }}</dd>
                        </div>
                        @if ($enquiry['replied_at'] ?? null)
                            <div class="vestra-enquiries-detail__definition-row">
                                <dt>Replied</dt>
                                <dd>{{ $enquiry['replied_at']->format('M j, Y g:i A') }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                {{-- Subject & Message --}}
                <div class="vestra-enquiries-detail__section">
                    <h3 class="vestra-enquiries-detail__section-title">{{ $enquiry['subject'] ?: 'Message' }}</h3>
                    <p class="vestra-enquiries-detail__text">{{ $enquiry['message'] ?: 'No message content.' }}</p>
                </div>

                {{-- Attachments --}}
                @if (! empty($enquiry['attachments']))
                    <div class="vestra-enquiries-detail__section">
                        <h3 class="vestra-enquiries-detail__section-title">Attachments</h3>
                        <ul class="vestra-enquiries-detail__attachment-list">
                            @foreach ($enquiry['attachments'] as $attachment)
                                <li>
                                    @if (is_array($attachment) && ($attachment['url'] ?? null))
                                        <a href="{{ $attachment['url'] }}" target="_blank" rel="noopener noreferrer" class="vestra-enquiries-detail__link">
                                            <x-filament::icon icon="heroicon-o-paper-clip" class="h-3.5 w-3.5" />
                                            {{ $attachment['name'] ?? 'Attachment' }}
                                        </a>
                                    @elseif (is_string($attachment))
                                        <span>{{ $attachment }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Reply --}}
                <div class="vestra-enquiries-detail__section">
                    <h3 class="vestra-enquiries-detail__section-title">Reply</h3>
                    @if ($enquiry['replied_at'] ?? null)
                        <p class="vestra-enquiries-detail__text vestra-enquiries-detail__text--muted">Reply already sent on {{ $enquiry['replied_at']->format('M j, Y') }}.</p>
                        @if ($enquiry['reply'] ?? null)
                            <blockquote class="vestra-enquiries-detail__reply-quote">{{ $enquiry['reply'] }}</blockquote>
                        @endif
                    @else
                        <textarea
                            wire:model.live="replyDraft"
                            rows="5"
                            class="vestra-enquiries-detail__textarea"
                            placeholder="Type your reply here..."
                            aria-label="Reply draft"
                        ></textarea>
                        <div class="vestra-enquiries-detail__reply-actions">
                            <button
                                type="button"
                                wire:click="saveReply"
                                class="vestra-button vestra-button--secondary"
                            >
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

                {{-- Assign --}}
                <div class="vestra-enquiries-detail__section">
                    <h3 class="vestra-enquiries-detail__section-title">Assigned Administrator</h3>
                    @if ($enquiry['assignee'] ?? null)
                        <div class="vestra-enquiries-detail__assignee-info">
                            <span class="vestra-enquiries-detail__assignee-avatar">{{ $enquiry['assignee']['initials'] }}</span>
                            <div>
                                <p class="vestra-enquiries-detail__assignee-name">{{ $enquiry['assignee']['name'] }}</p>
                                <p class="vestra-enquiries-detail__assignee-email">{{ $enquiry['assignee']['email'] }}</p>
                            </div>
                        </div>
                    @else
                        <p class="vestra-enquiries-detail__text">No administrator assigned.</p>
                    @endif

                    <div class="vestra-enquiries-detail__assign-form" x-data="{ open: false }">
                        <button type="button" @click="open = !open" class="vestra-button vestra-button--secondary vestra-enquiries-detail__assign-btn">
                            <x-filament::icon icon="heroicon-o-user-plus" class="h-4 w-4" />
                            <span>Reassign</span>
                        </button>
                        <div x-show="open" x-transition class="vestra-enquiries-detail__assign-dropdown">
                            @foreach ($adminUsers as $admin)
                                <button
                                    type="button"
                                    wire:click="assign({{ $enquiry['id'] }}, {{ $admin->id }})"
                                    @click="open = false"
                                    class="vestra-enquiries-detail__assign-option"
                                >
                                    {{ $admin->name }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Status update --}}
                <div class="vestra-enquiries-detail__section">
                    <h3 class="vestra-enquiries-detail__section-title">Update Status</h3>
                    <div class="vestra-enquiries-detail__status-buttons">
                        <button
                            type="button"
                            wire:click="updateStatus({{ $enquiry['id'] }}, 'new')"
                            class="vestra-button vestra-button--secondary"
                        >
                            New
                        </button>
                        <button
                            type="button"
                            wire:click="updateStatus({{ $enquiry['id'] }}, 'in_progress')"
                            class="vestra-button vestra-button--secondary"
                        >
                            In Progress
                        </button>
                        <button
                            type="button"
                            wire:click="updateStatus({{ $enquiry['id'] }}, 'resolved')"
                            class="vestra-button vestra-button--secondary"
                        >
                            Resolved
                        </button>
                    </div>
                </div>

                {{-- Internal Notes --}}
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
            </div>
        @else
            <div class="vestra-enquiries-detail__empty">
                <p>Select an enquiry to view details.</p>
            </div>
        @endif
    </div>
</div>

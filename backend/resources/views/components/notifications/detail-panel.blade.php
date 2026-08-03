@props([
    'show' => false,
    'notification' => null,
])

<div
    class="vestra-notification-detail @if ($show) vestra-notification-detail--open @endif"
    x-data="{ open: @js($show) }"
    x-show="open"
    x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 translate-x-4"
    x-transition:enter-end="opacity-100 translate-x-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-x-0"
    x-transition:leave-end="opacity-0 translate-x-4"
    aria-label="Notification details"
    role="dialog"
>
    <div class="vestra-notification-detail__overlay" wire:click="closeDetailPanel"></div>

    <div class="vestra-notification-detail__panel">
        @if ($notification)
            @php
            $type = $notification['type'];
            $category = $notification['category'];
            $priority = $notification['priority'];
            @endphp

            <div class="vestra-notification-detail__header">
                <div class="vestra-notification-detail__header-main">
                    <span class="vestra-notification-detail__icon vestra-notification-detail__icon--{{ $priority->color() }}">
                        <x-filament::icon :icon="$type->icon()" class="h-6 w-6" />
                    </span>
                    <div>
                        <h2 class="vestra-notification-detail__title">{{ $notification['title'] }}</h2>
                        <p class="vestra-notification-detail__subtitle">{{ $type->label() }} • {{ $notification['created_at']->diffForHumans() }}</p>
                    </div>
                </div>

                <button
                    type="button"
                    wire:click="closeDetailPanel"
                    class="vestra-notification-detail__close"
                    aria-label="Close details"
                >
                    <x-filament::icon icon="heroicon-o-x-mark" class="h-5 w-5" />
                </button>
            </div>

            <div class="vestra-notification-detail__body">
                <div class="vestra-notification-detail__badges">
                    <x-notifications.badge :value="$category->label()" :color="$category->color()" />
                    <x-notifications.badge :value="$priority->label()" :color="$priority->color()" />
                    @if ($notification['read_at'])
                        <x-notifications.badge value="Read" color="success" />
                    @else
                        <x-notifications.badge value="Unread" color="warning" />
                    @endif
                </div>

                <div class="vestra-notification-detail__section">
                    <h3 class="vestra-notification-detail__section-title">Message</h3>
                    <p class="vestra-notification-detail__message">{{ $notification['message'] }}</p>
                </div>

                @if ($notification['related_entity'])
                    <div class="vestra-notification-detail__section">
                        <h3 class="vestra-notification-detail__section-title">Related Record</h3>
                        <div class="vestra-notification-detail__related">
                            <span>{{ $notification['related_entity']['label'] }}</span>
                            @if ($notification['action_url'])
                                <a href="{{ $notification['action_url'] }}" class="vestra-notification-detail__link">
                                    View
                                    <x-filament::icon icon="heroicon-o-arrow-top-right-on-square" class="h-3.5 w-3.5" />
                                </a>
                            @endif
                        </div>
                    </div>
                @elseif ($notification['action_url'])
                    <div class="vestra-notification-detail__section">
                        <a href="{{ $notification['action_url'] }}" class="vestra-notification-detail__link vestra-notification-detail__link--large">
                            View Related Record
                            <x-filament::icon icon="heroicon-o-arrow-top-right-on-square" class="h-4 w-4" />
                        </a>
                    </div>
                @endif

                @if ($notification['triggered_by'])
                    <div class="vestra-notification-detail__section">
                        <h3 class="vestra-notification-detail__section-title">Triggered By</h3>
                        <div class="vestra-notification-detail__triggered-by">
                            <span class="vestra-notification-detail__avatar">
                                {{ strtoupper(substr($notification['triggered_by']['name'], 0, 1)) }}
                            </span>
                            <div>
                                <p class="vestra-notification-detail__triggered-name">{{ $notification['triggered_by']['name'] }}</p>
                                <p class="vestra-notification-detail__triggered-email">{{ $notification['triggered_by']['email'] }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="vestra-notification-detail__section">
                    <h3 class="vestra-notification-detail__section-title">Timeline</h3>
                    <ul class="vestra-notification-detail__timeline">
                        <li class="vestra-notification-detail__timeline-item">
                            <span class="vestra-notification-detail__timeline-dot"></span>
                            <div>
                                <p class="vestra-notification-detail__timeline-title">Notification created</p>
                                <p class="vestra-notification-detail__timeline-time">{{ $notification['created_at']->format('M d, Y H:i') }}</p>
                            </div>
                        </li>
                        @if ($notification['read_at'])
                            <li class="vestra-notification-detail__timeline-item">
                                <span class="vestra-notification-detail__timeline-dot vestra-notification-detail__timeline-dot--read"></span>
                                <div>
                                    <p class="vestra-notification-detail__timeline-title">Marked as read</p>
                                    <p class="vestra-notification-detail__timeline-time">{{ $notification['read_at']->format('M d, Y H:i') }}</p>
                                </div>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>

            <div class="vestra-notification-detail__footer">
                @if ($notification['read_at'])
                    <button
                        type="button"
                        wire:click="markAsUnread('{{ $notification['id'] }}')"
                        class="vestra-button vestra-button--secondary"
                    >
                        Mark Unread
                    </button>
                @else
                    <button
                        type="button"
                        wire:click="markAsRead('{{ $notification['id'] }}')"
                        class="vestra-button vestra-button--secondary"
                    >
                        Mark Read
                    </button>
                @endif

                <button
                    type="button"
                    wire:click="deleteNotification('{{ $notification['id'] }}')"
                    class="vestra-button vestra-button--danger"
                >
                    Delete
                </button>
            </div>
        @else
            <div class="vestra-notification-detail__empty">
                <x-filament::icon icon="heroicon-o-bell-slash" class="h-10 w-10" />
                <p>No notification selected.</p>
            </div>
        @endif
    </div>
</div>

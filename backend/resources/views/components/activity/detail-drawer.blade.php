@props([
    'show' => false,
    'activity' => null,
])

<div
    class="vestra-activity-detail @if ($show) vestra-activity-detail--open @endif"
    x-data="{ open: @js($show) }"
    x-show="open"
    x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 translate-x-4"
    x-transition:enter-end="opacity-100 translate-x-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-x-0"
    x-transition:leave-end="opacity-0 translate-x-4"
    aria-label="Activity details"
    role="dialog"
>
    <div class="vestra-activity-detail__overlay" wire:click="closeDetailPanel"></div>

    <div class="vestra-activity-detail__panel">
        @if ($activity)
            @php
            $category = $activity['category'];
            $status = $activity['status'];
            $user = $activity['user'];
            $subject = $activity['subject'];
            @endphp

            <div class="vestra-activity-detail__header">
                <div class="vestra-activity-detail__header-main">
                    <span class="vestra-activity-detail__icon vestra-activity-detail__icon--{{ $activity['color'] }}">
                        <x-filament::icon :icon="$activity['icon']" class="h-6 w-6" />
                    </span>
                    <div>
                        <h2 class="vestra-activity-detail__title">{{ $activity['title'] }}</h2>
                        <p class="vestra-activity-detail__subtitle">{{ $activity['source'] === 'audit_log' ? 'Audit Log' : 'Login Activity' }} • {{ $activity['created_at']->diffForHumans() }}</p>
                    </div>
                </div>

                <button
                    type="button"
                    wire:click="closeDetailPanel"
                    class="vestra-activity-detail__close"
                    aria-label="Close details"
                >
                    <x-filament::icon icon="heroicon-o-x-mark" class="h-5 w-5" />
                </button>
            </div>

            <div class="vestra-activity-detail__body">
                <div class="vestra-activity-detail__badges">
                    <span class="vestra-activity-detail__badge vestra-activity-detail__badge--{{ $category->color() }}">
                        {{ $category->label() }}
                    </span>
                    <span class="vestra-activity-detail__badge vestra-activity-detail__badge--{{ $status->color() }}">
                        {{ $status->label() }}
                    </span>
                    <span class="vestra-activity-detail__badge vestra-activity-detail__badge--gray">
                        {{ $activity['module'] }}
                    </span>
                </div>

                <div class="vestra-activity-detail__section">
                    <h3 class="vestra-activity-detail__section-title">Description</h3>
                    <p class="vestra-activity-detail__message">{{ $activity['description'] }}</p>
                </div>

                @if ($subject && $subject['url'])
                    <div class="vestra-activity-detail__section">
                        <h3 class="vestra-activity-detail__section-title">Related Record</h3>
                        <div class="vestra-activity-detail__related">
                            <span>{{ $subject['label'] ?? ($subject['type'].' #'.$subject['id']) }}</span>
                            <a href="{{ $subject['url'] }}" class="vestra-activity-detail__link">
                                View
                                <x-filament::icon icon="heroicon-o-arrow-top-right-on-square" class="h-3.5 w-3.5" />
                            </a>
                        </div>
                    </div>
                @elseif ($subject)
                    <div class="vestra-activity-detail__section">
                        <h3 class="vestra-activity-detail__section-title">Related Record</h3>
                        <div class="vestra-activity-detail__related">
                            <span>{{ $subject['label'] ?? ($subject['type'].' #'.$subject['id']) }}</span>
                        </div>
                    </div>
                @endif

                @if ($user)
                    <div class="vestra-activity-detail__section">
                        <h3 class="vestra-activity-detail__section-title">Actor</h3>
                        <div class="vestra-activity-detail__triggered-by">
                            @if (! empty($user['avatar']))
                                <img src="{{ $user['avatar'] }}" alt="" class="vestra-activity-detail__avatar" />
                            @else
                                <span class="vestra-activity-detail__avatar">{{ $user['initials'] ?? strtoupper(substr($user['name'], 0, 1)) }}</span>
                            @endif
                            <div>
                                <p class="vestra-activity-detail__triggered-name">{{ $user['name'] }}</p>
                                <p class="vestra-activity-detail__triggered-email">{{ $user['email'] }}</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="vestra-activity-detail__section">
                        <h3 class="vestra-activity-detail__section-title">Actor</h3>
                        <div class="vestra-activity-detail__triggered-by">
                            <span class="vestra-activity-detail__avatar">S</span>
                            <div>
                                <p class="vestra-activity-detail__triggered-name">System / Automated</p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="vestra-activity-detail__section">
                    <h3 class="vestra-activity-detail__section-title">Technical Details</h3>
                    <dl class="vestra-activity-detail__definition-list">
                        <div class="vestra-activity-detail__definition-row">
                            <dt>Date</dt>
                            <dd>{{ $activity['created_at']->format('M d, Y H:i') }}</dd>
                        </div>
                        <div class="vestra-activity-detail__definition-row">
                            <dt>IP Address</dt>
                            <dd>{{ $activity['ip_address'] ?? '—' }}</dd>
                        </div>
                        <div class="vestra-activity-detail__definition-row">
                            <dt>User Agent</dt>
                            <dd>{{ $activity['user_agent'] ?? '—' }}</dd>
                        </div>
                        @if ($activity['device'])
                            <div class="vestra-activity-detail__definition-row">
                                <dt>Device</dt>
                                <dd>{{ $activity['device'] }}</dd>
                            </div>
                        @endif
                        @if ($activity['browser'])
                            <div class="vestra-activity-detail__definition-row">
                                <dt>Browser</dt>
                                <dd>{{ $activity['browser'] }}</dd>
                            </div>
                        @endif
                        @if ($activity['os'])
                            <div class="vestra-activity-detail__definition-row">
                                <dt>OS</dt>
                                <dd>{{ $activity['os'] }}</dd>
                            </div>
                        @endif
                        @if ($activity['location'])
                            <div class="vestra-activity-detail__definition-row">
                                <dt>Location</dt>
                                <dd>{{ $activity['location'] }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                @if (! empty($activity['metadata']))
                    <div class="vestra-activity-detail__section">
                        <h3 class="vestra-activity-detail__section-title">Metadata</h3>
                        <pre class="vestra-activity-detail__metadata">{{ json_encode($activity['metadata'], JSON_PRETTY_PRINT) }}</pre>
                    </div>
                @endif
            </div>
        @else
            <div class="vestra-activity-detail__empty">
                <x-filament::icon icon="heroicon-o-bolt-slash" class="h-10 w-10" />
                <p>No activity selected.</p>
            </div>
        @endif
    </div>
</div>

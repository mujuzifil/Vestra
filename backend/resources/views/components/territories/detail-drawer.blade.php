@props([
    'show' => false,
    'branch' => null,
])

<div
    class="vestra-territories-detail @if ($show) vestra-territories-detail--open @endif"
    x-data="{ open: @js($show) }"
    x-show="open"
    x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 translate-x-4"
    x-transition:enter-end="opacity-100 translate-x-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-x-0"
    x-transition:leave-end="opacity-0 translate-x-4"
    aria-label="Branch details"
    role="dialog"
>
    <div class="vestra-territories-detail__overlay" wire:click="closeDetailDrawer"></div>

    <div class="vestra-territories-detail__panel">
        @if ($branch)
            @php
            $initials = collect(explode(' ', (string) $branch['name']))
                ->take(2)
                ->map(fn ($part) => strtoupper(substr((string) $part, 0, 1)))
                ->implode('');
            $initials = $initials ?: '—';
            @endphp

            <div class="vestra-territories-detail__header">
                <div class="vestra-territories-detail__header-main">
                    <span class="vestra-territories-detail__avatar">{{ $initials }}</span>
                    <div class="vestra-territories-detail__header-text">
                        <h2 class="vestra-territories-detail__title">{{ $branch['name'] }}</h2>
                        <p class="vestra-territories-detail__subtitle">
                            {{ $branch['distributor']['company_name'] ?? 'Unlinked distributor' }} • {{ $branch['country'] ?? 'No country' }}
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    wire:click="closeDetailDrawer"
                    class="vestra-territories-detail__close"
                    aria-label="Close details"
                >
                    <x-filament::icon icon="heroicon-o-x-mark" class="h-5 w-5" />
                </button>
            </div>

            <div class="vestra-territories-detail__body">
                <div class="vestra-territories-detail__badges">
                    <span class="vestra-territories-detail__badge vestra-territories-detail__badge--{{ $branch['status'] === 'active' ? 'success' : 'gray' }}">
                        {{ ucfirst((string) $branch['status']) }}
                    </span>
                    @if ($branch['is_default'])
                        <span class="vestra-territories-detail__badge vestra-territories-detail__badge--info">Default Branch</span>
                    @endif
                    <span class="vestra-territories-detail__badge vestra-territories-detail__badge--{{ $branch['has_coordinates'] ? 'success' : 'gray' }}">
                        <x-filament::icon icon="heroicon-o-map-pin" class="h-3.5 w-3.5" />
                        {{ $branch['has_coordinates'] ? 'Geocoded' : 'No coordinates' }}
                    </span>
                </div>

                <div class="vestra-territories-detail__section">
                    <h3 class="vestra-territories-detail__section-title">Branch Contact</h3>
                    <dl class="vestra-territories-detail__definition-list">
                        <div class="vestra-territories-detail__definition-row">
                            <dt>Manager</dt>
                            <dd>{{ $branch['manager_name'] ?? '—' }}</dd>
                        </div>
                        <div class="vestra-territories-detail__definition-row">
                            <dt>Phone</dt>
                            <dd>{{ $branch['phone'] ?? '—' }}</dd>
                        </div>
                        <div class="vestra-territories-detail__definition-row">
                            <dt>Email</dt>
                            <dd>{{ $branch['email'] ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="vestra-territories-detail__section">
                    <h3 class="vestra-territories-detail__section-title">Address</h3>
                    <p class="vestra-territories-detail__text">{{ $branch['formatted_address'] ?? 'No address on file.' }}</p>

                    @if ($branch['has_coordinates'])
                        <dl class="vestra-territories-detail__definition-list">
                            <div class="vestra-territories-detail__definition-row">
                                <dt>Latitude</dt>
                                <dd>{{ $branch['latitude'] }}</dd>
                            </div>
                            <div class="vestra-territories-detail__definition-row">
                                <dt>Longitude</dt>
                                <dd>{{ $branch['longitude'] }}</dd>
                            </div>
                        </dl>
                    @else
                        <p class="vestra-territories-detail__empty-text">No coordinates on file — this branch is excluded from the map view.</p>
                    @endif

                    @if ($branch['delivery_notes'])
                        <p class="vestra-territories-detail__text">{{ $branch['delivery_notes'] }}</p>
                    @endif
                </div>

                <div class="vestra-territories-detail__section">
                    <h3 class="vestra-territories-detail__section-title">Parent Distributor</h3>
                    @if ($branch['distributor'])
                        <div class="vestra-territories-detail__related">
                            <div>
                                <p class="vestra-territories-detail__record-title">{{ $branch['distributor']['company_name'] }}</p>
                                @if ($branch['distributor']['trading_name'])
                                    <p class="vestra-territories-detail__record-meta">{{ $branch['distributor']['trading_name'] }}</p>
                                @endif
                            </div>
                            <span class="vestra-territories-detail__badge vestra-territories-detail__badge--gray">
                                {{ is_object($branch['distributor']['status']) ? $branch['distributor']['status']->value : $branch['distributor']['status'] }}
                            </span>
                        </div>
                    @else
                        <p class="vestra-territories-detail__empty-text">This branch is not linked to a distributor.</p>
                    @endif
                </div>

                <div class="vestra-territories-detail__section">
                    <h3 class="vestra-territories-detail__section-title">Service Areas</h3>
                    @if (! empty($branch['service_areas']))
                        <ul class="vestra-territories-detail__record-list">
                            @foreach ($branch['service_areas'] as $area)
                                <li class="vestra-territories-detail__record-item">
                                    <div>
                                        <p class="vestra-territories-detail__record-title">{{ $area['region'] ?? 'Unnamed region' }}</p>
                                        <p class="vestra-territories-detail__record-meta">{{ $area['district'] ?? '—' }}</p>
                                    </div>
                                    <span class="vestra-territories-detail__badge vestra-territories-detail__badge--gray">{{ ucfirst((string) ($area['status'] ?? 'active')) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="vestra-territories-detail__empty-text">No service areas assigned to this branch.</p>
                    @endif
                </div>

                <div class="vestra-territories-detail__section">
                    <dl class="vestra-territories-detail__definition-list">
                        <div class="vestra-territories-detail__definition-row">
                            <dt>Created</dt>
                            <dd>{{ $branch['created_at']?->format('M d, Y') ?? '—' }}</dd>
                        </div>
                        <div class="vestra-territories-detail__definition-row">
                            <dt>Updated</dt>
                            <dd>{{ $branch['updated_at']?->format('M d, Y') ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        @else
            <div class="vestra-territories-detail__empty">
                <x-filament::icon icon="heroicon-o-map" class="h-10 w-10" />
                <p>No branch selected.</p>
            </div>
        @endif
    </div>
</div>

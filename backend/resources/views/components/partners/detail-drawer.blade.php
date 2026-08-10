@props([
    'show' => false,
    'partner' => null,
])

@php
    $actions = $partner['actions'] ?? [];
@endphp

<div
    class="vestra-partners-detail @if ($show) vestra-partners-detail--open @endif"
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
    aria-label="Partner details"
    role="dialog"
    aria-modal="true"
>
    <div class="vestra-partners-detail__overlay" wire:click="closeDetailDrawer"></div>

    <div class="vestra-partners-detail__panel">
        @if ($partner)
            @php
            $status = $partner['status'];
            $credit = $partner['credit'];
            @endphp

            <div class="vestra-partners-detail__header">
                <div class="vestra-partners-detail__header-main">
                    <span class="vestra-partners-detail__avatar">{{ mb_strtoupper(mb_substr($partner['company_name'] ?? 'P', 0, 2)) }}</span>
                    <div class="vestra-partners-detail__header-text">
                        <h2 class="vestra-partners-detail__title">{{ $partner['company_name'] ?? 'Partner' }}</h2>
                        <p class="vestra-partners-detail__subtitle">
                            {{ $partner['trading_name'] ?? 'No trading name' }}
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    wire:click="closeDetailDrawer"
                    class="vestra-partners-detail__close"
                    aria-label="Close details"
                >
                    <x-filament::icon icon="heroicon-o-x-mark" class="h-5 w-5" />
                </button>
            </div>

            <div class="vestra-partners-detail__body">
                <div class="vestra-partners-detail__badges">
                    <x-partners.status-badge :status="$status" />
                    @if ($partner['tier'] ?? $partner['tier_label'] ?? null)
                        <x-partners.tier-badge :tier="$partner['tier'] ?? null" :label="$partner['tier_label'] ?? null" />
                    @endif
                    @if ($partner['stock_availability_label'] ?? null)
                        <span class="vestra-partners-detail__type-badge">{{ $partner['stock_availability_label'] }}</span>
                    @endif
                    @if ($partner['business_type'] ?? null)
                        <span class="vestra-partners-detail__type-badge">{{ $partner['business_type'] }}</span>
                    @endif
                </div>

                <div class="vestra-partners-detail__actions">
                    @if ($actions['suspend'] ?? false)
                        <button
                            type="button"
                            wire:click="suspendPartner({{ $partner['id'] }})"
                            wire:confirm="Suspend this partner and remove them from the public website?"
                            class="vestra-partners-detail__action-btn vestra-partners-detail__action-btn--danger"
                        >
                            Suspend
                        </button>
                    @endif
                    @if ($actions['activate'] ?? false)
                        <button
                            type="button"
                            wire:click="activatePartner({{ $partner['id'] }})"
                            class="vestra-partners-detail__action-btn"
                        >
                            Activate
                        </button>
                    @endif
                    @if (($actions['edit'] ?? false) && ($partner['edit_url'] ?? null))
                        <a href="{{ $partner['edit_url'] }}" class="vestra-partners-detail__action-btn">Edit</a>
                    @endif
                    @if (($actions['credit'] ?? false) && ($partner['credit_url'] ?? null))
                        <a href="{{ $partner['credit_url'] }}" class="vestra-partners-detail__action-btn">Credit</a>
                    @endif
                    @if (($actions['coverage'] ?? false) && ($partner['territories_url'] ?? null))
                        <a href="{{ $partner['territories_url'] }}" class="vestra-partners-detail__action-btn">Territories</a>
                    @endif
                    <button
                        type="button"
                        wire:click="deletePartner({{ $partner['id'] }})"
                        wire:confirm="Permanently delete this partner? They will disappear from the public website and distributor portal and must reapply."
                        class="vestra-partners-detail__action-btn vestra-partners-detail__action-btn--danger"
                    >
                        Delete
                    </button>
                </div>

                <div class="vestra-partners-detail__section">
                    <h3 class="vestra-partners-detail__section-title">Company</h3>
                    <dl class="vestra-partners-detail__definition-list">
                        <div class="vestra-partners-detail__definition-row">
                            <dt>Registration No.</dt>
                            <dd>{{ $partner['registration_number'] ?? '—' }}</dd>
                        </div>
                        <div class="vestra-partners-detail__definition-row">
                            <dt>Tax ID</dt>
                            <dd>{{ $partner['tax_identification'] ?? '—' }}</dd>
                        </div>
                        <div class="vestra-partners-detail__definition-row">
                            <dt>Industry</dt>
                            <dd>{{ $partner['industry'] ?? '—' }}</dd>
                        </div>
                        <div class="vestra-partners-detail__definition-row">
                            <dt>Years in Business</dt>
                            <dd>{{ $partner['years_in_business'] ?? '—' }}</dd>
                        </div>
                        <div class="vestra-partners-detail__definition-row">
                            <dt>Website</dt>
                            <dd>{{ $partner['website'] ?? '—' }}</dd>
                        </div>
                        <div class="vestra-partners-detail__definition-row">
                            <dt>Location</dt>
                            <dd>{{ collect([$partner['district'] ?? null, $partner['city'] ?? null, $partner['country'] ?? null])->filter()->implode(', ') ?: '—' }}</dd>
                        </div>
                        <div class="vestra-partners-detail__definition-row">
                            <dt>Approved</dt>
                            <dd>{{ $partner['approved_at']?->format('M j, Y') ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="vestra-partners-detail__section">
                    <h3 class="vestra-partners-detail__section-title">Primary Contact</h3>
                    <div class="vestra-partners-detail__contact">
                        <p class="vestra-partners-detail__contact-name">{{ $partner['primary_contact']['name'] ?? '—' }}</p>
                        <p class="vestra-partners-detail__contact-meta">{{ $partner['primary_contact']['email'] ?? '—' }}</p>
                        @if ($partner['primary_contact']['phone'] ?? null)
                            <p class="vestra-partners-detail__contact-meta">{{ $partner['primary_contact']['phone'] }}</p>
                        @endif
                        @if ($partner['whatsapp'] ?? null)
                            <p class="vestra-partners-detail__contact-meta">WhatsApp: {{ $partner['whatsapp'] }}</p>
                        @endif
                    </div>
                </div>

                <div class="vestra-partners-detail__section">
                    <h3 class="vestra-partners-detail__section-title">Public Locator</h3>
                    <dl class="vestra-partners-detail__definition-list">
                        <div class="vestra-partners-detail__definition-row">
                            <dt>Tier</dt>
                            <dd>{{ $partner['tier_label'] ?? '—' }}</dd>
                        </div>
                        <div class="vestra-partners-detail__definition-row">
                            <dt>Stock</dt>
                            <dd>{{ $partner['stock_availability_label'] ?? '—' }}</dd>
                        </div>
                        <div class="vestra-partners-detail__definition-row">
                            <dt>District</dt>
                            <dd>{{ $partner['district'] ?? '—' }}</dd>
                        </div>
                        <div class="vestra-partners-detail__definition-row">
                            <dt>Area / Town</dt>
                            <dd>{{ $partner['city'] ?? '—' }}</dd>
                        </div>
                        <div class="vestra-partners-detail__definition-row">
                            <dt>Google Maps</dt>
                            <dd>
                                @if ($partner['google_maps_url'] ?? null)
                                    <a href="{{ $partner['google_maps_url'] }}" target="_blank" rel="noopener noreferrer">View location</a>
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>

                @if ($partner['application'] ?? null)
                    <div class="vestra-partners-detail__section">
                        <h3 class="vestra-partners-detail__section-title">Application History</h3>
                        <dl class="vestra-partners-detail__definition-list">
                            <div class="vestra-partners-detail__definition-row">
                                <dt>Application ID</dt>
                                <dd>#{{ $partner['application']['id'] }}</dd>
                            </div>
                            <div class="vestra-partners-detail__definition-row">
                                <dt>Submitted</dt>
                                <dd>{{ $partner['application']['submitted_at']?->format('M j, Y') ?? '—' }}</dd>
                            </div>
                        </dl>
                    </div>
                @endif

                <div class="vestra-partners-detail__section">
                    <h3 class="vestra-partners-detail__section-title">Coverage</h3>
                    @if (! empty($partner['service_areas']))
                        <ul class="vestra-partners-detail__record-list">
                            @foreach ($partner['service_areas'] as $area)
                                <li class="vestra-partners-detail__record-item">
                                    <div>
                                        <p class="vestra-partners-detail__record-title">{{ $area['district'] }}</p>
                                        <p class="vestra-partners-detail__record-meta">{{ $area['region'] }} · {{ str_replace('_', ' ', $area['status'] ?? 'covered') }}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="vestra-partners-detail__text">No coverage configured yet. Assign coverage areas from the distributor profile.</p>
                    @endif
                </div>

                @if ($partner['sales_rep'])
                    <div class="vestra-partners-detail__section">
                        <h3 class="vestra-partners-detail__section-title">Account Manager</h3>
                        <div class="vestra-partners-detail__account-manager">
                            <span class="vestra-partners-detail__account-manager-avatar">{{ mb_strtoupper(mb_substr($partner['sales_rep']['name'], 0, 2)) }}</span>
                            <div>
                                <p class="vestra-partners-detail__account-manager-name">{{ $partner['sales_rep']['name'] }}</p>
                                <p class="vestra-partners-detail__account-manager-email">{{ $partner['sales_rep']['email'] }}</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="vestra-partners-detail__section">
                        <h3 class="vestra-partners-detail__section-title">Account Manager</h3>
                        <p class="vestra-partners-detail__text">No account manager assigned.</p>
                    </div>
                @endif

                <div class="vestra-partners-detail__section">
                    <h3 class="vestra-partners-detail__section-title">Credit</h3>
                    @if ($credit)
                        <dl class="vestra-partners-detail__definition-list">
                            <div class="vestra-partners-detail__definition-row">
                                <dt>Credit Limit</dt>
                                <dd>UGX {{ number_format($credit['limit'], 0) }}</dd>
                            </div>
                            <div class="vestra-partners-detail__definition-row">
                                <dt>Outstanding Balance</dt>
                                <dd>UGX {{ number_format($credit['balance'], 0) }}</dd>
                            </div>
                            <div class="vestra-partners-detail__definition-row">
                                <dt>Available Credit</dt>
                                <dd>UGX {{ number_format($credit['available_credit'], 0) }}</dd>
                            </div>
                            <div class="vestra-partners-detail__definition-row">
                                <dt>Utilization</dt>
                                <dd>{{ number_format($credit['utilization_percentage'], 0) }}%</dd>
                            </div>
                        </dl>
                    @else
                        <p class="vestra-partners-detail__text">No credit account on file.</p>
                    @endif
                </div>

                <div class="vestra-partners-detail__section">
                    <h3 class="vestra-partners-detail__section-title">Branches ({{ count($partner['branches']) }})</h3>
                    @if (! empty($partner['branches']))
                        <ul class="vestra-partners-detail__record-list">
                            @foreach ($partner['branches'] as $branch)
                                <li class="vestra-partners-detail__record-item">
                                    <div>
                                        <p class="vestra-partners-detail__record-title">{{ $branch['name'] }}{{ $branch['is_default'] ? ' (Default)' : '' }}</p>
                                        <p class="vestra-partners-detail__record-meta">{{ collect([$branch['district'], $branch['city'], $branch['country']])->filter()->implode(', ') ?: '—' }}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="vestra-partners-detail__text">No branches recorded.</p>
                    @endif
                </div>

                <div class="vestra-partners-detail__section">
                    <h3 class="vestra-partners-detail__section-title">Documents</h3>
                    @if (! empty($partner['documents']))
                        <ul class="vestra-partners-detail__document-list">
                            @foreach ($partner['documents'] as $document)
                                <li class="vestra-partners-detail__document-item">
                                    <a href="{{ $document['url'] }}" target="_blank" rel="noopener noreferrer" class="vestra-partners-detail__link">
                                        <x-filament::icon icon="heroicon-o-paper-clip" class="h-3.5 w-3.5" />
                                        {{ $document['title'] ?? $document['file_name'] }}
                                    </a>
                                    <span class="vestra-partners-detail__document-type">{{ $document['type'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="vestra-partners-detail__text">No documents uploaded.</p>
                    @endif
                </div>

                <div class="vestra-partners-detail__section">
                    <h3 class="vestra-partners-detail__section-title">Recent Orders</h3>
                    @if (! empty($partner['recent_orders']))
                        <ul class="vestra-partners-detail__record-list">
                            @foreach ($partner['recent_orders'] as $order)
                                <li class="vestra-partners-detail__record-item">
                                    <div>
                                        <p class="vestra-partners-detail__record-title">{{ $order['invoice_number'] }}</p>
                                        <p class="vestra-partners-detail__record-meta">{{ $order['created_at']?->diffForHumans() }} · UGX {{ number_format($order['total_amount'], 0) }}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="vestra-partners-detail__text">No orders placed yet.</p>
                    @endif
                </div>

                <div class="vestra-partners-detail__section">
                    <h3 class="vestra-partners-detail__section-title">Related Activity</h3>
                    @if (! empty($partner['recent_activity']))
                        <ul class="vestra-partners-detail__activity-list">
                            @foreach ($partner['recent_activity'] as $activity)
                                <li class="vestra-partners-detail__activity-item">
                                    <span class="vestra-partners-detail__activity-action">{{ str_replace(['_', '.'], ' ', $activity['action']) }}</span>
                                    <span class="vestra-partners-detail__activity-meta">by {{ $activity['user'] }} · {{ $activity['created_at']?->diffForHumans() }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="vestra-partners-detail__text">No related activity recorded yet.</p>
                    @endif
                </div>
            </div>
        @else
            <div class="vestra-partners-detail__empty">
                <p>Select a partner to view details.</p>
            </div>
        @endif
    </div>
</div>

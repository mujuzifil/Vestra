@props([
    'show' => false,
    'quote' => null,
])

<div
    class="vestra-quotes-detail @if ($show) vestra-quotes-detail--open @endif"
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
    aria-label="Quote details"
    role="dialog"
    aria-modal="true"
>
    <div class="vestra-quotes-detail__overlay" wire:click="closeDetailDrawer"></div>

    <div class="vestra-quotes-detail__panel" id="quote-print-area">
        @if ($quote)
            @php
            $status = $quote['status'];
            $valueLabel = $quote['estimated_value'] !== null
                ? 'UGX '.number_format((float) $quote['estimated_value'], 0)
                : '—';
            @endphp

            <div class="vestra-quotes-detail__header">
                <div class="vestra-quotes-detail__header-main">
                    <span class="vestra-quotes-detail__avatar">QR</span>
                    <div class="vestra-quotes-detail__header-text">
                        <h2 class="vestra-quotes-detail__title">{{ $quote['reference_number'] ?? 'Quote' }}</h2>
                        <p class="vestra-quotes-detail__subtitle">
                            {{ $quote['company']['name'] ?? 'No company' }}
                            @if ($quote['contact']['name'] ?? null)
                                • {{ $quote['contact']['name'] }}
                            @endif
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    wire:click="closeDetailDrawer"
                    class="vestra-quotes-detail__close"
                    aria-label="Close details"
                >
                    <x-filament::icon icon="heroicon-o-x-mark" class="h-5 w-5" />
                </button>
            </div>

            <div class="vestra-quotes-detail__body">
                <div class="vestra-quotes-detail__badges">
                    <x-quotes.status-badge :status="$status" />
                    <x-quotes.priority-badge :priority="$quote['priority'] ?? null" />
                </div>

                <div class="vestra-quotes-detail__quick-actions">
                    <button type="button" wire:click="openEditDrawer({{ $quote['id'] }})" class="vestra-quotes-detail__quick-action">
                        <x-filament::icon icon="heroicon-o-pencil-square" class="h-4 w-4" />
                        <span>Edit</span>
                    </button>
                    @if ($status !== \App\Enums\QuoteRequestStatus::APPROVED)
                        <button type="button" wire:click="updateStatus({{ $quote['id'] }}, 'approved')" class="vestra-quotes-detail__quick-action">
                            <x-filament::icon icon="heroicon-o-check-circle" class="h-4 w-4" />
                            <span>Approve</span>
                        </button>
                    @endif
                    @if ($status !== \App\Enums\QuoteRequestStatus::DECLINED)
                        <button type="button" wire:click="updateStatus({{ $quote['id'] }}, 'declined')" class="vestra-quotes-detail__quick-action">
                            <x-filament::icon icon="heroicon-o-x-circle" class="h-4 w-4" />
                            <span>Reject</span>
                        </button>
                    @endif
                    <button type="button" onclick="window.print()" class="vestra-quotes-detail__quick-action">
                        <x-filament::icon icon="heroicon-o-printer" class="h-4 w-4" />
                        <span>Print</span>
                    </button>
                    @if ($quote['company']['profile_id'] ?? null)
                        <a href="{{ \App\Filament\Pages\Sales\CompaniesPage::getUrl() }}" class="vestra-quotes-detail__quick-action">
                            <x-filament::icon icon="heroicon-o-building-office" class="h-4 w-4" />
                            <span>View Company</span>
                        </a>
                    @endif
                    @if ($quote['contact']['user_id'] ?? null)
                        <a href="/workspace/activity?user={{ $quote['contact']['user_id'] }}" class="vestra-quotes-detail__quick-action">
                            <x-filament::icon icon="heroicon-o-clock" class="h-4 w-4" />
                            <span>View Activity</span>
                        </a>
                    @endif
                </div>

                <div class="vestra-quotes-detail__section">
                    <h3 class="vestra-quotes-detail__section-title">General Information</h3>
                    <dl class="vestra-quotes-detail__definition-list">
                        <div class="vestra-quotes-detail__definition-row">
                            <dt>Estimated Value</dt>
                            <dd>{{ $valueLabel }}</dd>
                        </div>
                        <div class="vestra-quotes-detail__definition-row">
                            <dt>Expected Close</dt>
                            <dd>{{ $quote['expected_close_date']?->format('M j, Y') ?? '—' }}</dd>
                        </div>
                        <div class="vestra-quotes-detail__definition-row">
                            <dt>Preferred Delivery</dt>
                            <dd>{{ $quote['preferred_delivery_date']?->format('M j, Y') ?? '—' }}</dd>
                        </div>
                        <div class="vestra-quotes-detail__definition-row">
                            <dt>Source</dt>
                            <dd>{{ $quote['source'] ? ucfirst($quote['source']) : '—' }}</dd>
                        </div>
                        <div class="vestra-quotes-detail__definition-row">
                            <dt>Created</dt>
                            <dd>{{ $quote['created_at']?->format('M j, Y g:i A') ?? '—' }}</dd>
                        </div>
                        <div class="vestra-quotes-detail__definition-row">
                            <dt>Updated</dt>
                            <dd>{{ $quote['updated_at']?->format('M j, Y g:i A') ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="vestra-quotes-detail__section">
                    <h3 class="vestra-quotes-detail__section-title">Company</h3>
                    <p class="vestra-quotes-detail__text">{{ $quote['company']['name'] ?: 'No company recorded.' }}</p>
                    @if ($quote['company']['industry'] ?? null)
                        <p class="vestra-quotes-detail__contact-meta">{{ $quote['company']['industry'] }}</p>
                    @endif
                </div>

                <div class="vestra-quotes-detail__section">
                    <h3 class="vestra-quotes-detail__section-title">Primary Contact</h3>
                    <div class="vestra-quotes-detail__contact">
                        <p class="vestra-quotes-detail__contact-name">{{ $quote['contact']['name'] ?? '—' }}</p>
                        <p class="vestra-quotes-detail__contact-meta">{{ $quote['contact']['email'] ?? '—' }}</p>
                        @if ($quote['contact']['phone'] ?? null)
                            <p class="vestra-quotes-detail__contact-meta">{{ $quote['contact']['phone'] }}</p>
                        @endif
                    </div>
                </div>

                @if ($quote['sales_rep'])
                    <div class="vestra-quotes-detail__section">
                        <h3 class="vestra-quotes-detail__section-title">Sales Representative</h3>
                        <div class="vestra-quotes-detail__account-manager">
                            <span class="vestra-quotes-detail__account-manager-avatar">{{ $quote['sales_rep']['initials'] }}</span>
                            <div>
                                <p class="vestra-quotes-detail__account-manager-name">{{ $quote['sales_rep']['name'] }}</p>
                                <p class="vestra-quotes-detail__account-manager-email">{{ $quote['sales_rep']['email'] }}</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="vestra-quotes-detail__section">
                        <h3 class="vestra-quotes-detail__section-title">Sales Representative</h3>
                        <p class="vestra-quotes-detail__text">No sales representative assigned.</p>
                    </div>
                @endif

                <div class="vestra-quotes-detail__section">
                    <h3 class="vestra-quotes-detail__section-title">Requested Products</h3>
                    @if (! empty($quote['products']))
                        <ul class="vestra-quotes-detail__address-list">
                            @foreach ($quote['products'] as $product)
                                <li>
                                    <strong>{{ $product['product_name'] ?? 'Product' }}</strong>
                                    @if ($product['package_size'])
                                        — {{ $product['package_size'] }}
                                    @endif
                                    @if ($product['quantity'])
                                        × {{ $product['quantity'] }}
                                    @endif
                                    @if ($product['notes'])
                                        <span class="vestra-quotes-detail__contact-meta">{{ $product['notes'] }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="vestra-quotes-detail__text">No products requested for this quote.</p>
                    @endif
                </div>

                <div class="vestra-quotes-detail__section">
                    <h3 class="vestra-quotes-detail__section-title">Requirements</h3>
                    <p class="vestra-quotes-detail__text">{{ $quote['requirements'] ?: 'No requirements recorded.' }}</p>
                </div>

                <div class="vestra-quotes-detail__section">
                    <h3 class="vestra-quotes-detail__section-title">Internal Notes</h3>
                    <p class="vestra-quotes-detail__text">{{ $quote['admin_notes'] ?: 'No internal notes.' }}</p>
                </div>

                <div class="vestra-quotes-detail__section">
                    <h3 class="vestra-quotes-detail__section-title">Attachments</h3>
                    @if (! empty($quote['attachments']))
                        <ul class="vestra-quotes-detail__address-list">
                            @foreach ($quote['attachments'] as $attachment)
                                <li>
                                    <a href="{{ $attachment['url'] }}" target="_blank" rel="noopener noreferrer" class="vestra-quotes-detail__link">
                                        <x-filament::icon icon="heroicon-o-paper-clip" class="h-3.5 w-3.5" />
                                        {{ $attachment['name'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="vestra-quotes-detail__text">No attachments uploaded.</p>
                    @endif
                </div>

                <div class="vestra-quotes-detail__section">
                    <h3 class="vestra-quotes-detail__section-title">Location</h3>
                    <p class="vestra-quotes-detail__text">
                        {{ collect([$quote['address'], $quote['district'], $quote['city'], $quote['delivery_location']])->filter()->implode(', ') ?: 'No location on file.' }}
                    </p>
                </div>

                <div class="vestra-quotes-detail__section">
                    <h3 class="vestra-quotes-detail__section-title">Linked Support Tickets</h3>
                    @if (! empty($quote['support_tickets']))
                        <ul class="vestra-quotes-detail__address-list">
                            @foreach ($quote['support_tickets'] as $ticket)
                                <li>
                                    <strong>{{ $ticket['reference_number'] ?? 'Ticket' }}</strong>
                                    — {{ $ticket['subject'] }}
                                    <span class="vestra-quotes-detail__contact-meta">{{ $ticket['status'] }} · {{ $ticket['created_at']?->diffForHumans() }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="vestra-quotes-detail__text">No linked support tickets.</p>
                    @endif
                </div>

                <div class="vestra-quotes-detail__section">
                    <h3 class="vestra-quotes-detail__section-title">Approval History</h3>
                    <p class="vestra-quotes-detail__text">No formal approval workflow records are available for this quote.</p>
                </div>

                <div class="vestra-quotes-detail__section">
                    <h3 class="vestra-quotes-detail__section-title">Related Activity</h3>
                    @if (! empty($quote['recent_activity']))
                        <ul class="vestra-quotes-detail__address-list">
                            @foreach ($quote['recent_activity'] as $activity)
                                <li>
                                    <strong>{{ str_replace(['_', '.'], ' ', $activity['action']) }}</strong>
                                    by {{ $activity['user'] }}
                                    <span class="vestra-quotes-detail__contact-meta">{{ $activity['created_at']?->diffForHumans() }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="vestra-quotes-detail__text">No related activity recorded yet.</p>
                    @endif
                </div>
            </div>
        @else
            <div class="vestra-quotes-detail__empty">
                <p>Select a quote to view details.</p>
            </div>
        @endif
    </div>
</div>

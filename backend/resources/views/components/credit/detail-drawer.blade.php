@props([
    'show' => false,
    'account' => null,
])

<div
    class="vestra-credit-detail @if ($show) vestra-credit-detail--open @endif"
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
    aria-label="Credit account details"
    role="dialog"
    aria-modal="true"
>
    <div class="vestra-credit-detail__overlay" wire:click="closeDetailDrawer"></div>

    <div class="vestra-credit-detail__panel">
        @if ($account)
            <div class="vestra-credit-detail__header">
                <div class="vestra-credit-detail__header-main">
                    <span class="vestra-credit-detail__avatar">
                        <x-filament::icon icon="heroicon-o-banknotes" class="h-5 w-5" />
                    </span>
                    <div class="vestra-credit-detail__header-text">
                        <h2 class="vestra-credit-detail__title">{{ $account['distributor']['company_name'] ?? 'Credit Account' }}</h2>
                        <p class="vestra-credit-detail__subtitle">{{ $account['distributor']['email'] ?? '—' }}</p>
                    </div>
                </div>

                <button
                    type="button"
                    wire:click="closeDetailDrawer"
                    class="vestra-credit-detail__close"
                    aria-label="Close details"
                >
                    <x-filament::icon icon="heroicon-o-x-mark" class="h-5 w-5" />
                </button>
            </div>

            <div class="vestra-credit-detail__body">
                <div class="vestra-credit-detail__badges">
                    <x-credit.status-badge :status="$account['status']" />
                </div>

                <div class="vestra-credit-detail__quick-actions">
                    <button type="button" wire:click="openAdjustDrawer({{ $account['id'] }})" class="vestra-credit-detail__quick-action">
                        <x-filament::icon icon="heroicon-o-adjustments-horizontal" class="h-4 w-4" />
                        <span>Adjust Limit</span>
                    </button>
                </div>

                <div class="vestra-credit-detail__section">
                    <h3 class="vestra-credit-detail__section-title">Credit Summary</h3>
                    <dl class="vestra-credit-detail__definition-list">
                        <div class="vestra-credit-detail__definition-row">
                            <dt>Credit Limit</dt>
                            <dd>UGX {{ number_format($account['limit']) }}</dd>
                        </div>
                        <div class="vestra-credit-detail__definition-row">
                            <dt>Outstanding Balance</dt>
                            <dd>UGX {{ number_format($account['balance']) }}</dd>
                        </div>
                        <div class="vestra-credit-detail__definition-row">
                            <dt>Authorized Amount</dt>
                            <dd>UGX {{ number_format($account['authorized_amount']) }}</dd>
                        </div>
                        <div class="vestra-credit-detail__definition-row">
                            <dt>Available Credit</dt>
                            <dd>UGX {{ number_format($account['available_credit']) }}</dd>
                        </div>
                    </dl>

                    <x-credit.utilization-bar :percentage="$account['utilization_percentage']" />
                </div>

                <div class="vestra-credit-detail__section">
                    <h3 class="vestra-credit-detail__section-title">Distributor</h3>
                    <dl class="vestra-credit-detail__definition-list">
                        <div class="vestra-credit-detail__definition-row">
                            <dt>Company</dt>
                            <dd>{{ $account['distributor']['company_name'] ?? '—' }}</dd>
                        </div>
                        <div class="vestra-credit-detail__definition-row">
                            <dt>Phone</dt>
                            <dd>{{ $account['distributor']['phone'] ?? '—' }}</dd>
                        </div>
                        <div class="vestra-credit-detail__definition-row">
                            <dt>Location</dt>
                            <dd>{{ collect([$account['distributor']['city'] ?? null, $account['distributor']['country'] ?? null])->filter()->implode(', ') ?: '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="vestra-credit-detail__section">
                    <h3 class="vestra-credit-detail__section-title">Internal Notes</h3>
                    <p class="vestra-credit-detail__text">{{ $account['admin_notes'] ?: 'No internal notes.' }}</p>
                </div>

                <div class="vestra-credit-detail__section">
                    <h3 class="vestra-credit-detail__section-title">Transaction Timeline</h3>
                    @if (! empty($account['transactions']))
                        <ul class="vestra-credit-detail__timeline">
                            @foreach ($account['transactions'] as $transaction)
                                <li class="vestra-credit-detail__timeline-item">
                                    <div class="vestra-credit-detail__timeline-header">
                                        <span class="vestra-credit-detail__timeline-type">{{ $transaction['type_label'] }}</span>
                                        <span class="vestra-credit-detail__timeline-amount {{ $transaction['amount'] >= 0 ? 'vestra-credit-detail__timeline-amount--positive' : 'vestra-credit-detail__timeline-amount--negative' }}">
                                            {{ $transaction['amount'] >= 0 ? '+' : '' }}UGX {{ number_format($transaction['amount']) }}
                                        </span>
                                    </div>
                                    <p class="vestra-credit-detail__timeline-description">{{ $transaction['description'] ?: 'No description provided.' }}</p>
                                    <p class="vestra-credit-detail__timeline-meta">
                                        Balance after: UGX {{ number_format($transaction['balance_after']) }}
                                        · {{ $transaction['created_by'] ?? 'System' }}
                                        · {{ $transaction['created_at']?->diffForHumans() }}
                                    </p>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="vestra-credit-detail__text">No credit transactions recorded yet.</p>
                    @endif
                </div>
            </div>
        @else
            <div class="vestra-credit-detail__empty">
                <p>Select a credit account to view details.</p>
            </div>
        @endif
    </div>
</div>

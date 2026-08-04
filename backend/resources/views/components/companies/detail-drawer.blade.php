@props([
    'show' => false,
    'company' => null,
])

<div
    class="vestra-companies-detail @if ($show) vestra-companies-detail--open @endif"
    x-data="{ open: @js($show) }"
    x-show="open"
    x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 translate-x-4"
    x-transition:enter-end="opacity-100 translate-x-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-x-0"
    x-transition:leave-end="opacity-0 translate-x-4"
    aria-label="Company details"
    role="dialog"
>
    <div class="vestra-companies-detail__overlay" wire:click="closeDetailDrawer"></div>

    <div class="vestra-companies-detail__panel">
        @if ($company)
            @php
            $status = $company['status'];
            $initials = collect(explode(' ', (string) $company['company_name']))
                ->take(2)
                ->map(fn ($part) => strtoupper(substr((string) $part, 0, 1)))
                ->implode('');
            $initials = $initials ?: strtoupper(substr((string) $company['company_name'], 0, 1)) ?: '—';
            @endphp

            <div class="vestra-companies-detail__header">
                <div class="vestra-companies-detail__header-main">
                    <span class="vestra-companies-detail__avatar">{{ $initials }}</span>
                    <div class="vestra-companies-detail__header-text">
                        <h2 class="vestra-companies-detail__title">{{ $company['company_name'] ?? 'Unnamed company' }}</h2>
                        <p class="vestra-companies-detail__subtitle">
                            {{ $company['industry'] ?? 'No industry' }} • {{ $company['country'] ?? 'No country' }}
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    wire:click="closeDetailDrawer"
                    class="vestra-companies-detail__close"
                    aria-label="Close details"
                >
                    <x-filament::icon icon="heroicon-o-x-mark" class="h-5 w-5" />
                </button>
            </div>

            <div class="vestra-companies-detail__body">
                <div class="vestra-companies-detail__badges">
                    <span class="vestra-companies-detail__badge vestra-companies-detail__badge--{{ $status->color() }}">
                        <x-filament::icon :icon="$status->icon()" class="h-3.5 w-3.5" />
                        {{ $status->label() }}
                    </span>
                    @if ($company['business_type'])
                        <span class="vestra-companies-detail__badge vestra-companies-detail__badge--gray">{{ $company['business_type'] }}</span>
                    @endif
                    @if ($company['region'])
                        <span class="vestra-companies-detail__badge vestra-companies-detail__badge--gray">{{ $company['region'] }}</span>
                    @endif
                </div>

                <div class="vestra-companies-detail__quick-actions">
                    <a href="{{ \App\Filament\Pages\Sales\QuotesPage::getUrl() }}" class="vestra-companies-detail__quick-action">
                        <x-filament::icon icon="heroicon-o-document-plus" class="h-4 w-4" />
                        <span>Create Quote</span>
                    </a>
                    <a href="/workspace/activity?user={{ $company['primary_contact']['user_id'] }}" class="vestra-companies-detail__quick-action">
                        <x-filament::icon icon="heroicon-o-clock" class="h-4 w-4" />
                        <span>View Activity</span>
                    </a>
                    <button type="button" wire:click="openEditDrawer({{ $company['id'] }})" class="vestra-companies-detail__quick-action">
                        <x-filament::icon icon="heroicon-o-pencil-square" class="h-4 w-4" />
                        <span>Edit</span>
                    </button>
                    <button type="button" wire:click="deleteCompany({{ $company['id'] }})" wire:confirm="Are you sure you want to delete this company?" class="vestra-companies-detail__quick-action vestra-companies-detail__quick-action--danger">
                        <x-filament::icon icon="heroicon-o-trash" class="h-4 w-4" />
                        <span>Delete</span>
                    </button>
                </div>

                <div class="vestra-companies-detail__section">
                    <h3 class="vestra-companies-detail__section-title">Primary Contact</h3>
                    <div class="vestra-companies-detail__contact">
                        <p class="vestra-companies-detail__contact-name">{{ $company['primary_contact']['name'] ?? '—' }}</p>
                        <p class="vestra-companies-detail__contact-meta">{{ $company['primary_contact']['email'] ?? '—' }}</p>
                        @if ($company['primary_contact']['phone'])
                            <p class="vestra-companies-detail__contact-meta">{{ $company['primary_contact']['phone'] }}</p>
                        @endif
                    </div>
                </div>

                @if ($company['account_manager'])
                    <div class="vestra-companies-detail__section">
                        <h3 class="vestra-companies-detail__section-title">Account Manager</h3>
                        <div class="vestra-companies-detail__account-manager">
                            <span class="vestra-companies-detail__account-manager-avatar">{{ $company['account_manager']['initials'] }}</span>
                            <div>
                                <p class="vestra-companies-detail__account-manager-name">{{ $company['account_manager']['name'] }}</p>
                                <p class="vestra-companies-detail__account-manager-email">{{ $company['account_manager']['email'] }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="vestra-companies-detail__section">
                    <h3 class="vestra-companies-detail__section-title">Address</h3>
                    <p class="vestra-companies-detail__text">
                        {{ $company['address'] ?? 'No address on file.' }}
                    </p>
                    @if (! empty($company['addresses']))
                        <ul class="vestra-companies-detail__address-list">
                            @foreach ($company['addresses'] as $address)
                                <li>{{ $address['full_address'] }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                @if ($company['tax_identification'] || $company['registration_number'] || $company['website'])
                    <div class="vestra-companies-detail__section">
                        <h3 class="vestra-companies-detail__section-title">Company Details</h3>
                        <dl class="vestra-companies-detail__definition-list">
                            @if ($company['tax_identification'])
                                <div class="vestra-companies-detail__definition-row">
                                    <dt>Tax ID</dt>
                                    <dd>{{ $company['tax_identification'] }}</dd>
                                </div>
                            @endif
                            @if ($company['registration_number'])
                                <div class="vestra-companies-detail__definition-row">
                                    <dt>Registration</dt>
                                    <dd>{{ $company['registration_number'] }}</dd>
                                </div>
                            @endif
                            @if ($company['website'])
                                <div class="vestra-companies-detail__definition-row">
                                    <dt>Website</dt>
                                    <dd>
                                        <a href="{{ $company['website'] }}" target="_blank" rel="noopener" class="vestra-companies-detail__link">
                                            {{ $company['website'] }}
                                            <x-filament::icon icon="heroicon-o-arrow-top-right-on-square" class="h-3.5 w-3.5" />
                                        </a>
                                    </dd>
                                </div>
                            @endif
                            <div class="vestra-companies-detail__definition-row">
                                <dt>Created</dt>
                                <dd>{{ $company['created_at']?->format('M d, Y') ?? '—' }}</dd>
                            </div>
                        </dl>
                    </div>
                @endif

                @if ($company['distributor'])
                    <div class="vestra-companies-detail__section">
                        <h3 class="vestra-companies-detail__section-title">Distributor Relationship</h3>
                        <div class="vestra-companies-detail__related">
                            <span>{{ $company['distributor']['name'] }}</span>
                            <span class="vestra-companies-detail__badge vestra-companies-detail__badge--gray">{{ $company['distributor']['status'] ?? 'Pending' }}</span>
                        </div>
                    </div>
                @endif

                <div class="vestra-companies-detail__section">
                    <div class="vestra-companies-detail__section-header">
                        <h3 class="vestra-companies-detail__section-title">Recent Quotes</h3>
                        <a href="{{ \App\Filament\Pages\Sales\QuotesPage::getUrl() }}" class="vestra-companies-detail__section-action">
                            <x-filament::icon icon="heroicon-o-plus" class="h-3.5 w-3.5" />
                            <span>New</span>
                        </a>
                    </div>
                    @if (! empty($company['recent_quotes']))
                        <ul class="vestra-companies-detail__record-list">
                            @foreach ($company['recent_quotes'] as $quote)
                                <li class="vestra-companies-detail__record-item">
                                    <div>
                                        <p class="vestra-companies-detail__record-title">{{ $quote['reference_number'] }}</p>
                                        <p class="vestra-companies-detail__record-meta">{{ $quote['created_at']?->format('M d, Y') ?? '—' }}</p>
                                    </div>
                                    <span class="vestra-companies-detail__badge vestra-companies-detail__badge--{{ $quote['color'] }}">{{ $quote['status'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="vestra-companies-detail__empty-text">No recent quotes.</p>
                    @endif
                </div>

                <div class="vestra-companies-detail__section">
                    <div class="vestra-companies-detail__section-header">
                        <h3 class="vestra-companies-detail__section-title">Active Support Tickets</h3>
                    </div>
                    @if (! empty($company['active_tickets']))
                        <ul class="vestra-companies-detail__record-list">
                            @foreach ($company['active_tickets'] as $ticket)
                                <li class="vestra-companies-detail__record-item">
                                    <div>
                                        <p class="vestra-companies-detail__record-title">{{ $ticket['reference_number'] }}</p>
                                        <p class="vestra-companies-detail__record-meta">{{ $ticket['subject'] }}</p>
                                    </div>
                                    <span class="vestra-companies-detail__badge vestra-companies-detail__badge--warning">{{ $ticket['status'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="vestra-companies-detail__empty-text">No active support tickets.</p>
                    @endif
                </div>

                <div class="vestra-companies-detail__section">
                    <h3 class="vestra-companies-detail__section-title">Create Support Ticket</h3>
                    <form wire:submit.prevent="createSupportTicket" class="vestra-companies-detail__ticket-form">
                        <div class="vestra-companies-detail__form-field">
                            <label for="ticket-subject" class="vestra-companies-detail__form-label">Subject</label>
                            <input
                                id="ticket-subject"
                                type="text"
                                wire:model="ticketSubject"
                                class="vestra-companies-detail__form-input @error('ticketSubject') vestra-companies-detail__form-input--error @enderror"
                                placeholder="Ticket subject"
                            />
                            @error('ticketSubject')<span class="vestra-companies-detail__form-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="vestra-companies-detail__form-field">
                            <label for="ticket-message" class="vestra-companies-detail__form-label">Message</label>
                            <textarea
                                id="ticket-message"
                                wire:model="ticketMessage"
                                rows="3"
                                class="vestra-companies-detail__form-textarea @error('ticketMessage') vestra-companies-detail__form-input--error @enderror"
                                placeholder="Describe the issue..."
                            ></textarea>
                            @error('ticketMessage')<span class="vestra-companies-detail__form-error">{{ $message }}</span>@enderror
                        </div>
                        <button type="submit" class="vestra-button vestra-button--primary vestra-button--sm">
                            <x-filament::icon icon="heroicon-o-ticket" class="h-4 w-4" />
                            <span>Create Ticket</span>
                        </button>
                    </form>
                </div>

                <div class="vestra-companies-detail__section">
                    <h3 class="vestra-companies-detail__section-title">Documents</h3>
                    @if (! empty($company['documents']))
                        <ul class="vestra-companies-detail__document-list">
                            @foreach ($company['documents'] as $document)
                                <li class="vestra-companies-detail__document-item">
                                    <x-filament::icon icon="heroicon-o-document-text" class="h-4 w-4" />
                                    <a href="{{ $document['url'] }}" target="_blank" rel="noopener" class="vestra-companies-detail__link">
                                        {{ $document['title'] }}
                                    </a>
                                    <span class="vestra-companies-detail__document-type">{{ $document['type'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="vestra-companies-detail__empty-text">No documents available.</p>
                    @endif
                </div>

                @if ($company['notes'])
                    <div class="vestra-companies-detail__section">
                        <h3 class="vestra-companies-detail__section-title">Notes</h3>
                        <p class="vestra-companies-detail__text">{{ $company['notes'] }}</p>
                    </div>
                @endif

                <div class="vestra-companies-detail__section">
                    <h3 class="vestra-companies-detail__section-title">Recent Activity</h3>
                    @if (! empty($company['recent_activity']))
                        <ul class="vestra-companies-detail__activity-list">
                            @foreach ($company['recent_activity'] as $activity)
                                <li class="vestra-companies-detail__activity-item">
                                    <span class="vestra-companies-detail__activity-action">{{ $activity['action'] }}</span>
                                    <span class="vestra-companies-detail__activity-meta">{{ $activity['user'] }} • {{ $activity['created_at']?->diffForHumans() ?? '—' }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="vestra-companies-detail__empty-text">No recent activity.</p>
                    @endif
                </div>
            </div>
        @else
            <div class="vestra-companies-detail__empty">
                <x-filament::icon icon="heroicon-o-building-office" class="h-10 w-10" />
                <p>No company selected.</p>
            </div>
        @endif
    </div>
</div>

@props([
    'show' => false,
    'application' => null,
])

@php
use App\Enums\DistributorStatus;
@endphp

<div
    class="vestra-applications-detail @if ($show) vestra-applications-detail--open @endif"
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
    aria-label="Application details"
    role="dialog"
    aria-modal="true"
>
    <div class="vestra-applications-detail__overlay" wire:click="closeDetailDrawer"></div>

    <div class="vestra-applications-detail__panel">
        @if ($application)
            @php
            $status = $application['status'];
            @endphp

            <div class="vestra-applications-detail__header">
                <div class="vestra-applications-detail__header-main">
                    <span class="vestra-applications-detail__avatar">{{ strtoupper(substr($application['company_name'] ?? '?', 0, 2)) }}</span>
                    <div class="vestra-applications-detail__header-text">
                        <h2 class="vestra-applications-detail__title">{{ $application['company_name'] ?? 'Application' }}</h2>
                        <p class="vestra-applications-detail__subtitle">
                            {{ $application['contact_person'] ?? 'No contact' }}
                            @if ($application['business_type'] ?? null)
                                • {{ $application['business_type'] }}
                            @endif
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    wire:click="closeDetailDrawer"
                    class="vestra-applications-detail__close"
                    aria-label="Close details"
                >
                    <x-filament::icon icon="heroicon-o-x-mark" class="h-5 w-5" />
                </button>
            </div>

            <div class="vestra-applications-detail__body">
                <div class="vestra-applications-detail__badges">
                    <x-applications.status-badge :status="$status" />
                    <x-applications.priority-badge :priority="$application['priority'] ?? null" />
                </div>

                <div class="vestra-applications-detail__quick-actions">
                    @if ($status !== DistributorStatus::APPROVED)
                        <button
                            type="button"
                            wire:click="approve({{ $application['id'] }})"
                            wire:confirm="Approve this application and create a distributor account?"
                            class="vestra-applications-detail__quick-action"
                        >
                            <x-filament::icon icon="heroicon-o-check-circle" class="h-4 w-4" />
                            <span>Approve</span>
                        </button>
                    @endif
                    @if ($status !== DistributorStatus::REJECTED)
                        <button
                            type="button"
                            wire:click="reject({{ $application['id'] }})"
                            wire:confirm="Reject this application?"
                            class="vestra-applications-detail__quick-action vestra-applications-detail__quick-action--danger"
                        >
                            <x-filament::icon icon="heroicon-o-x-circle" class="h-4 w-4" />
                            <span>Reject</span>
                        </button>
                    @endif
                    <button type="button" onclick="window.print()" class="vestra-applications-detail__quick-action">
                        <x-filament::icon icon="heroicon-o-printer" class="h-4 w-4" />
                        <span>Print</span>
                    </button>
                </div>

                @if ($application['distributor'] ?? null)
                    <div class="vestra-applications-detail__section">
                        <h3 class="vestra-applications-detail__section-title">Distributor Account</h3>
                        <p class="vestra-applications-detail__text">
                            This application has already been approved and a distributor account (#{{ $application['distributor']['id'] }}) was created.
                        </p>
                    </div>
                @endif

                <div class="vestra-applications-detail__section">
                    <h3 class="vestra-applications-detail__section-title">Business Information</h3>
                    <dl class="vestra-applications-detail__definition-list">
                        <div class="vestra-applications-detail__definition-row">
                            <dt>Business Type</dt>
                            <dd>{{ $application['business_type'] ?: '—' }}</dd>
                        </div>
                        <div class="vestra-applications-detail__definition-row">
                            <dt>Years in Operation</dt>
                            <dd>{{ $application['years_in_operation'] ?? '—' }}</dd>
                        </div>
                        <div class="vestra-applications-detail__definition-row">
                            <dt>Existing Customer</dt>
                            <dd>{{ $application['existing_customer'] ? 'Yes' : 'No' }}</dd>
                        </div>
                        <div class="vestra-applications-detail__definition-row">
                            <dt>Previous Applications</dt>
                            <dd>{{ $application['previous_applications'] ?? 0 }}</dd>
                        </div>
                        <div class="vestra-applications-detail__definition-row">
                            <dt>Submitted</dt>
                            <dd>{{ $application['created_at']?->format('M j, Y g:i A') ?? '—' }}</dd>
                        </div>
                        <div class="vestra-applications-detail__definition-row">
                            <dt>Updated</dt>
                            <dd>{{ $application['updated_at']?->format('M j, Y g:i A') ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="vestra-applications-detail__section">
                    <h3 class="vestra-applications-detail__section-title">Primary Contact</h3>
                    <div class="vestra-applications-detail__contact">
                        <p class="vestra-applications-detail__contact-name">{{ $application['contact_person'] ?? '—' }}</p>
                        <p class="vestra-applications-detail__contact-meta">{{ $application['email'] ?? '—' }}</p>
                        @if ($application['phone'] ?? null)
                            <p class="vestra-applications-detail__contact-meta">{{ $application['phone'] }}</p>
                        @endif
                    </div>
                </div>

                <div class="vestra-applications-detail__section">
                    <h3 class="vestra-applications-detail__section-title">Location</h3>
                    <p class="vestra-applications-detail__text">{{ $application['formatted_address'] ?? 'No location on file.' }}</p>
                </div>

                <div class="vestra-applications-detail__section">
                    <h3 class="vestra-applications-detail__section-title">Business Description</h3>
                    <p class="vestra-applications-detail__text">{{ $application['business_description'] ?: 'No description provided.' }}</p>
                </div>

                <div class="vestra-applications-detail__section">
                    <h3 class="vestra-applications-detail__section-title">Products &amp; Volume</h3>
                    <dl class="vestra-applications-detail__definition-list">
                        <div class="vestra-applications-detail__definition-row">
                            <dt>Products of Interest</dt>
                            <dd>{{ $application['products_interested_in'] ?: '—' }}</dd>
                        </div>
                        <div class="vestra-applications-detail__definition-row">
                            <dt>Target Region</dt>
                            <dd>{{ $application['target_region'] ?: '—' }}</dd>
                        </div>
                        <div class="vestra-applications-detail__definition-row">
                            <dt>Estimated Volume</dt>
                            <dd>{{ $application['estimated_volume'] ?: '—' }}</dd>
                        </div>
                    </dl>
                </div>

                @if ($application['assignee'])
                    <div class="vestra-applications-detail__section">
                        <h3 class="vestra-applications-detail__section-title">Assigned Administrator</h3>
                        <div class="vestra-applications-detail__account-manager">
                            <span class="vestra-applications-detail__account-manager-avatar">{{ $application['assignee']['initials'] }}</span>
                            <div>
                                <p class="vestra-applications-detail__account-manager-name">{{ $application['assignee']['name'] }}</p>
                                <p class="vestra-applications-detail__account-manager-email">{{ $application['assignee']['email'] }}</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="vestra-applications-detail__section">
                        <h3 class="vestra-applications-detail__section-title">Assigned Administrator</h3>
                        <p class="vestra-applications-detail__text">No administrator assigned.</p>
                    </div>
                @endif

                <div class="vestra-applications-detail__section">
                    <h3 class="vestra-applications-detail__section-title">Documents</h3>
                    @if (! empty($application['documents']))
                        <ul class="vestra-applications-detail__address-list">
                            @foreach ($application['documents'] as $document)
                                <li>
                                    @if (is_array($document) && ($document['url'] ?? null))
                                        <a href="{{ $document['url'] }}" target="_blank" rel="noopener noreferrer" class="vestra-applications-detail__link">
                                            <x-filament::icon icon="heroicon-o-paper-clip" class="h-3.5 w-3.5" />
                                            {{ $document['name'] ?? 'Document' }}
                                        </a>
                                    @else
                                        <span>{{ is_string($document) ? $document : 'Document' }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="vestra-applications-detail__text">No documents uploaded.</p>
                    @endif
                </div>

                <div class="vestra-applications-detail__section">
                    <h3 class="vestra-applications-detail__section-title">Internal Notes</h3>
                    <p class="vestra-applications-detail__text">{{ $application['internal_notes'] ?: 'No internal notes.' }}</p>
                </div>
            </div>
        @else
            <div class="vestra-applications-detail__empty">
                <p>Select an application to view details.</p>
            </div>
        @endif
    </div>
</div>

@props([
    'show' => false,
    'application' => null,
])

@php
use App\Enums\DistributorStatus;

$display = function ($value, string $fallback = 'Not provided') {
    if ($value === null) {
        return $fallback;
    }

    if (is_string($value) && trim($value) === '') {
        return $fallback;
    }

    return $value;
};
@endphp

<div
    class="vestra-applications-detail @if ($show) vestra-applications-detail--open @endif"
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
    aria-label="Application details"
    role="dialog"
    aria-modal="true"
>
    <div class="vestra-applications-detail__overlay" wire:click="closeDetailDrawer"></div>

    <div class="vestra-applications-detail__panel">
        @if ($application)
            @php
            $status = $application['status'];
            $address = collect([
                $application['address'] ?? null,
                $application['region'] ?? null,
                $application['country'] ?? null,
            ])->filter(fn ($part) => filled($part))->implode(', ');
            @endphp

            <div class="vestra-applications-detail__header">
                <div class="vestra-applications-detail__header-main">
                    <span class="vestra-applications-detail__avatar">{{ strtoupper(substr($application['company_name'] ?? '?', 0, 2)) }}</span>
                    <div class="vestra-applications-detail__header-text">
                        <h2 class="vestra-applications-detail__title">{{ $display($application['company_name'] ?? null, 'Application') }}</h2>
                        <p class="vestra-applications-detail__subtitle">
                            {{ $display($application['contact_person'] ?? null, 'No contact') }}
                            @if (filled($application['business_type'] ?? null))
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
                    @if ($status !== DistributorStatus::APPROVED || empty($application['distributor']))
                        <button
                            type="button"
                            wire:click="approve({{ $application['id'] }})"
                            wire:confirm="Approve this application and create a distributor account?"
                            class="vestra-applications-detail__quick-action"
                        >
                            <x-filament::icon icon="heroicon-o-check-circle" class="h-4 w-4" />
                            <span>{{ empty($application['distributor']) && $status === DistributorStatus::APPROVED ? 'Repair Account' : 'Approve' }}</span>
                        </button>
                    @endif
                    @if ($status !== DistributorStatus::REJECTED && $status !== DistributorStatus::APPROVED)
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
                    <h3 class="vestra-applications-detail__section-title">Company</h3>
                    <dl class="vestra-applications-detail__definition-list">
                        <div class="vestra-applications-detail__definition-row">
                            <dt>Company</dt>
                            <dd>{{ $display($application['company_name'] ?? null) }}</dd>
                        </div>
                        <div class="vestra-applications-detail__definition-row">
                            <dt>Business Type</dt>
                            <dd>{{ $display($application['business_type'] ?? null) }}</dd>
                        </div>
                        <div class="vestra-applications-detail__definition-row">
                            <dt>Business Registration</dt>
                            <dd>Not provided</dd>
                        </div>
                        <div class="vestra-applications-detail__definition-row">
                            <dt>Years in Operation</dt>
                            <dd>{{ $display($application['years_in_operation'] ?? null) }}</dd>
                        </div>
                        <div class="vestra-applications-detail__definition-row">
                            <dt>Existing Customer</dt>
                            <dd>{{ ($application['existing_customer'] ?? false) ? 'Yes' : 'No' }}</dd>
                        </div>
                        <div class="vestra-applications-detail__definition-row">
                            <dt>Current Status</dt>
                            <dd>{{ $display($application['status_label'] ?? null) }}</dd>
                        </div>
                        <div class="vestra-applications-detail__definition-row">
                            <dt>Submitted Date</dt>
                            <dd>{{ $application['created_at']?->format('M j, Y g:i A') ?? 'Not provided' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="vestra-applications-detail__section">
                    <h3 class="vestra-applications-detail__section-title">Contact Person</h3>
                    <dl class="vestra-applications-detail__definition-list">
                        <div class="vestra-applications-detail__definition-row">
                            <dt>Contact Person</dt>
                            <dd>{{ $display($application['contact_person'] ?? null) }}</dd>
                        </div>
                        <div class="vestra-applications-detail__definition-row">
                            <dt>Email</dt>
                            <dd>{{ $display($application['email'] ?? null) }}</dd>
                        </div>
                        <div class="vestra-applications-detail__definition-row">
                            <dt>Telephone</dt>
                            <dd>{{ $display($application['phone'] ?? null) }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="vestra-applications-detail__section">
                    <h3 class="vestra-applications-detail__section-title">Address &amp; Territory</h3>
                    <dl class="vestra-applications-detail__definition-list">
                        <div class="vestra-applications-detail__definition-row">
                            <dt>Address</dt>
                            <dd>{{ $display($address !== '' ? $address : null) }}</dd>
                        </div>
                        <div class="vestra-applications-detail__definition-row">
                            <dt>Territory</dt>
                            <dd>{{ $display($application['territory'] ?? $application['target_region'] ?? $application['region'] ?? null) }}</dd>
                        </div>
                        <div class="vestra-applications-detail__definition-row">
                            <dt>Country</dt>
                            <dd>{{ $display($application['country'] ?? null) }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="vestra-applications-detail__section">
                    <h3 class="vestra-applications-detail__section-title">Business Description</h3>
                    <p class="vestra-applications-detail__text">{{ $display($application['business_description'] ?? null) }}</p>
                </div>

                <div class="vestra-applications-detail__section">
                    <h3 class="vestra-applications-detail__section-title">Product Interests</h3>
                    <dl class="vestra-applications-detail__definition-list">
                        <div class="vestra-applications-detail__definition-row">
                            <dt>Products of Interest</dt>
                            <dd>{{ $display($application['products_interested_in'] ?? null) }}</dd>
                        </div>
                        <div class="vestra-applications-detail__definition-row">
                            <dt>Estimated Volume</dt>
                            <dd>{{ $display($application['estimated_volume'] ?? null) }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="vestra-applications-detail__section">
                    <h3 class="vestra-applications-detail__section-title">Attachments</h3>
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
                                        <span>{{ is_array($document) ? ($document['name'] ?? 'Document') : (is_string($document) ? $document : 'Document') }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="vestra-applications-detail__text">Not provided</p>
                    @endif
                </div>

                <div class="vestra-applications-detail__section">
                    <h3 class="vestra-applications-detail__section-title">Internal Notes</h3>
                    <p class="vestra-applications-detail__text">{{ $display($application['internal_notes'] ?? null) }}</p>
                </div>
            </div>
        @else
            <div class="vestra-applications-detail__empty">
                <p>Select an application to view details.</p>
            </div>
        @endif
    </div>
</div>

@props([
    'quote',
    'selectedIds' => [],
])

@php
$rep = $quote->assignedUser;
$products = $quote->items->take(2)->pluck('product_name')->filter()->values();
$moreProducts = max(0, $quote->items_count - $products->count());
$closeDate = $quote->expected_close_date;
$daysLeft = $closeDate ? (int) now()->startOfDay()->diffInDays($closeDate->copy()->startOfDay(), false) : null;
$isSelected = in_array($quote->id, $selectedIds, true);

$valueLabel = $quote->estimated_value !== null
    ? 'UGX '.number_format((float) $quote->estimated_value, 0)
    : '—';
@endphp

<tr class="vestra-quotes__row" wire:key="quote-{{ $quote->id }}">
    <td class="vestra-quotes__td vestra-quotes__td--select">
        <input
            type="checkbox"
            class="vestra-quotes__filter-checkbox"
            wire:model.live="selectedQuoteIds"
            value="{{ $quote->id }}"
            @checked($isSelected)
            aria-label="Select quote {{ $quote->reference_number }}"
        />
    </td>

    <td class="vestra-quotes__td vestra-quotes__td--quote">
        <button
            type="button"
            wire:click="openDetailDrawer({{ $quote->id }})"
            class="vestra-quotes__quote-number"
        >
            {{ $quote->reference_number ?? '—' }}
        </button>
        <span class="vestra-quotes__quote-meta">{{ $quote->source ? ucfirst($quote->source) : 'Quote request' }}</span>
    </td>

    <td class="vestra-quotes__td vestra-quotes__td--company">
        <div class="vestra-quotes__company-info">
            <span class="vestra-quotes__company-name">{{ $quote->company_name ?: '—' }}</span>
            @if ($quote->companyProfile?->industry)
                <span class="vestra-quotes__company-industry">{{ $quote->companyProfile->industry }}</span>
            @endif
        </div>
    </td>

    <td class="vestra-quotes__td vestra-quotes__td--contact">
        <div class="vestra-quotes__contact">
            <span class="vestra-quotes__contact-name">{{ $quote->full_name ?: '—' }}</span>
            @if ($quote->email)
                <span class="vestra-quotes__contact-email">{{ $quote->email }}</span>
            @endif
        </div>
    </td>

    <td class="vestra-quotes__td vestra-quotes__td--products">
        @if ($products->isNotEmpty())
            <span class="vestra-quotes__cell-text">{{ $products->implode(', ') }}</span>
            @if ($moreProducts > 0)
                <span class="vestra-quotes__quote-meta">+{{ $moreProducts }} more</span>
            @endif
        @else
            <span class="vestra-quotes__empty-cell">No products</span>
        @endif
    </td>

    <td class="vestra-quotes__td vestra-quotes__td--sales-rep">
        @if ($rep)
            <div class="vestra-quotes__account-manager">
                <span class="vestra-quotes__account-manager-avatar">{{ $rep->initials() }}</span>
                <span class="vestra-quotes__account-manager-name">{{ $rep->name }}</span>
            </div>
        @else
            <span class="vestra-quotes__empty-cell">Unassigned</span>
        @endif
    </td>

    <td class="vestra-quotes__td vestra-quotes__td--value">
        <span class="vestra-quotes__cell-text vestra-quotes__value">{{ $valueLabel }}</span>
    </td>

    <td class="vestra-quotes__td vestra-quotes__td--priority">
        <x-quotes.priority-badge :priority="$quote->priority" />
    </td>

    <td class="vestra-quotes__td vestra-quotes__td--status">
        <x-quotes.status-badge :status="$quote->status" />
    </td>

    <td class="vestra-quotes__td vestra-quotes__td--expiry">
        @if ($closeDate)
            <span class="vestra-quotes__cell-text">{{ $closeDate->format('M j, Y') }}</span>
            @if ($daysLeft !== null)
                @if ($daysLeft < 0)
                    <span class="vestra-quotes__expiry-label vestra-quotes__expiry-label--danger">Expired</span>
                @elseif ($daysLeft === 0)
                    <span class="vestra-quotes__expiry-label vestra-quotes__expiry-label--warning">Today</span>
                @else
                    <span class="vestra-quotes__expiry-label vestra-quotes__expiry-label--success">{{ $daysLeft }} days left</span>
                @endif
            @endif
        @else
            <span class="vestra-quotes__empty-cell">—</span>
        @endif
    </td>

    <td class="vestra-quotes__td vestra-quotes__td--created">
        <span class="vestra-quotes__created">{{ $quote->created_at?->format('M j, Y') ?? '—' }}</span>
        <span class="vestra-quotes__quote-meta">{{ $quote->created_at?->format('g:i A') }}</span>
    </td>

    <td class="vestra-quotes__td vestra-quotes__td--actions">
        <div class="vestra-quotes__actions" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-quotes__action-trigger" aria-label="Quote actions" aria-haspopup="true">
                <x-filament::icon icon="heroicon-m-ellipsis-vertical" class="h-5 w-5" />
            </button>
            <div x-show="open" x-transition class="vestra-quotes__action-menu" role="menu">
                <button type="button" wire:click="openDetailDrawer({{ $quote->id }})" class="vestra-quotes__action-item" role="menuitem">
                    <x-filament::icon icon="heroicon-o-eye" class="h-4 w-4" />
                    <span>View Quote</span>
                </button>
                <button type="button" wire:click="openEditDrawer({{ $quote->id }})" class="vestra-quotes__action-item" role="menuitem">
                    <x-filament::icon icon="heroicon-o-pencil-square" class="h-4 w-4" />
                    <span>Edit Quote</span>
                </button>
                @if ($quote->status !== \App\Enums\QuoteRequestStatus::APPROVED)
                    <button type="button" wire:click="updateStatus({{ $quote->id }}, 'approved')" class="vestra-quotes__action-item" role="menuitem">
                        <x-filament::icon icon="heroicon-o-check-circle" class="h-4 w-4" />
                        <span>Approve</span>
                    </button>
                @endif
                @if ($quote->status !== \App\Enums\QuoteRequestStatus::DECLINED)
                    <button type="button" wire:click="updateStatus({{ $quote->id }}, 'declined')" class="vestra-quotes__action-item" role="menuitem">
                        <x-filament::icon icon="heroicon-o-x-circle" class="h-4 w-4" />
                        <span>Reject</span>
                    </button>
                @endif
                @if ($quote->user?->companyProfile)
                    <a href="{{ \App\Filament\Pages\Sales\CompaniesPage::getUrl(['search' => $quote->company_name]) }}" class="vestra-quotes__action-item" role="menuitem">
                        <x-filament::icon icon="heroicon-o-building-office" class="h-4 w-4" />
                        <span>View Company</span>
                    </a>
                @endif
                @if ($quote->user_id)
                    <a href="/workspace/activity?user={{ $quote->user_id }}" class="vestra-quotes__action-item" role="menuitem">
                        <x-filament::icon icon="heroicon-o-clock" class="h-4 w-4" />
                        <span>View Activity</span>
                    </a>
                @endif
            </div>
        </div>
    </td>
</tr>

@props(['company'])

@php
$status = $company->status;
$manager = $company->accountManager;
$initials = collect(explode(' ', (string) $company->company_name))
    ->take(2)
    ->map(fn ($part) => strtoupper(substr((string) $part, 0, 1)))
    ->implode('');
$initials = $initials ?: strtoupper(substr((string) $company->company_name, 0, 1)) ?: '—';
$openQuotes = $company->quoteRequests()->whereNotIn('status', ['closed', 'declined', 'approved'])->count();
$activeTickets = $company->supportTickets()->whereNotIn('status', ['resolved', 'closed'])->count();
@endphp

<tr class="vestra-companies__row" wire:key="company-{{ $company->id }}">
    <td class="vestra-companies__td vestra-companies__td--company">
        <div class="vestra-companies__company-primary">
            <span class="vestra-companies__avatar">{{ $initials }}</span>
            <div class="vestra-companies__company-info">
                <button
                    type="button"
                    wire:click="openDetailDrawer({{ $company->id }})"
                    class="vestra-companies__company-name"
                >
                    {{ $company->company_name ?? 'Unnamed company' }}
                </button>
                @if ($company->primary_contact_email)
                    <span class="vestra-companies__company-email">{{ $company->primary_contact_email }}</span>
                @endif
            </div>
        </div>
    </td>

    <td class="vestra-companies__td vestra-companies__td--industry">
        <span class="vestra-companies__cell-text">{{ $company->industry ?? '—' }}</span>
    </td>

    <td class="vestra-companies__td vestra-companies__td--country">
        <span class="vestra-companies__cell-text">{{ $company->country ?? '—' }}</span>
    </td>

    <td class="vestra-companies__td vestra-companies__td--account-manager">
        @if ($manager)
            <div class="vestra-companies__account-manager">
                <span class="vestra-companies__account-manager-avatar">{{ $manager->initials() }}</span>
                <span class="vestra-companies__account-manager-name">{{ $manager->name }}</span>
            </div>
        @else
            <span class="vestra-companies__empty-cell">Unassigned</span>
        @endif
    </td>

    <td class="vestra-companies__td vestra-companies__td--status">
        <span class="vestra-companies__badge vestra-companies__badge--{{ $status->color() }}">
            <x-filament::icon :icon="$status->icon()" class="h-3.5 w-3.5" />
            {{ $status->label() }}
        </span>
    </td>

    <td class="vestra-companies__td vestra-companies__td--quotes">
        <span class="vestra-companies__count @if ($openQuotes > 0) vestra-companies__count--active @endif">{{ $openQuotes }}</span>
    </td>

    <td class="vestra-companies__td vestra-companies__td--tickets">
        <span class="vestra-companies__count @if ($activeTickets > 0) vestra-companies__count--active @endif">{{ $activeTickets }}</span>
    </td>

    <td class="vestra-companies__td vestra-companies__td--created">
        <span class="vestra-companies__created">{{ $company->created_at?->diffForHumans() ?? '—' }}</span>
    </td>

    <td class="vestra-companies__td vestra-companies__td--actions">
        <div class="vestra-companies__actions" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-companies__action-trigger" aria-label="Company actions">
                <x-filament::icon icon="heroicon-m-ellipsis-vertical" class="h-5 w-5" />
            </button>
            <div x-show="open" x-transition class="vestra-companies__action-menu">
                <button type="button" wire:click="openDetailDrawer({{ $company->id }})" class="vestra-companies__action-item">
                    <x-filament::icon icon="heroicon-o-eye" class="h-4 w-4" />
                    <span>View</span>
                </button>
                <button type="button" wire:click="openEditDrawer({{ $company->id }})" class="vestra-companies__action-item">
                    <x-filament::icon icon="heroicon-o-pencil-square" class="h-4 w-4" />
                    <span>Edit</span>
                </button>
                <a href="/quote-requests/create?user_id={{ $company->user_id }}" class="vestra-companies__action-item">
                    <x-filament::icon icon="heroicon-o-document-plus" class="h-4 w-4" />
                    <span>Create Quote</span>
                </a>
                <a href="/workspace/activity?user={{ $company->user_id }}" class="vestra-companies__action-item">
                    <x-filament::icon icon="heroicon-o-clock" class="h-4 w-4" />
                    <span>View Activity</span>
                </a>
                <button type="button" wire:click="deleteCompany({{ $company->id }})" wire:confirm="Are you sure you want to delete this company?" class="vestra-companies__action-item vestra-companies__action-item--danger">
                    <x-filament::icon icon="heroicon-o-trash" class="h-4 w-4" />
                    <span>Delete</span>
                </button>
            </div>
        </div>
    </td>
</tr>

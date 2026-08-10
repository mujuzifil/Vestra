@props(['account' => null])

@php
$distributor = $account->distributor;
$name = $distributor?->company_name ?? 'Unknown Distributor';
$initials = collect(explode(' ', trim($name)))
    ->filter()
    ->take(2)
    ->map(fn ($word) => strtoupper(substr($word, 0, 1)))
    ->implode('');
$initials = $initials !== '' ? $initials : 'D';
@endphp

<tr class="vestra-credit__row">
    <td class="vestra-credit__td vestra-credit__td--distributor">
        <button type="button" wire:click="openDetailDrawer({{ $account->id }})" class="vestra-credit__account-primary">
            <span class="vestra-credit__avatar">{{ $initials }}</span>
            <span class="vestra-credit__account-info">
                <span class="vestra-credit__account-name">{{ $name }}</span>
                @if ($distributor?->email)
                    <span class="vestra-credit__account-email">{{ $distributor->email }}</span>
                @endif
            </span>
        </button>
    </td>
    <td class="vestra-credit__td vestra-credit__td--country">
        <span class="vestra-credit__cell-text">{{ $distributor?->country ?? '—' }}</span>
    </td>
    <td class="vestra-credit__td vestra-credit__td--limit">
        <span class="vestra-credit__value">UGX {{ number_format((float) $account->limit) }}</span>
    </td>
    <td class="vestra-credit__td vestra-credit__td--balance">
        <span class="vestra-credit__value">UGX {{ number_format((float) $account->balance) }}</span>
    </td>
    <td class="vestra-credit__td vestra-credit__td--available">
        <span class="vestra-credit__value">UGX {{ number_format($account->availableCredit()) }}</span>
    </td>
    <td class="vestra-credit__td vestra-credit__td--utilization">
        <x-credit.utilization-bar :percentage="$account->utilizationPercentage()" />
    </td>
    <td class="vestra-credit__td vestra-credit__td--status">
        <x-credit.status-badge :status="$account->status" />
    </td>
    <td class="vestra-credit__td vestra-credit__td--actions">
        <div
            class="vestra-credit__actions"
            x-data="{
                open: false,
                dropUp: false,
                toggle() {
                    this.open = !this.open;
                    if (!this.open) {
                        return;
                    }
                    this.$nextTick(() => {
                        const rect = this.$el.getBoundingClientRect();
                        this.dropUp = (window.innerHeight - rect.bottom) < 180;
                    });
                }
            }"
            @click.outside="open = false"
        >
            <button type="button" @click="toggle()" class="vestra-credit__action-trigger" aria-label="Row actions">
                <x-filament::icon icon="heroicon-o-ellipsis-vertical" class="h-4 w-4" />
            </button>
            <div x-show="open" x-transition class="vestra-credit__action-menu" role="menu" :class="{ 'vestra-credit__action-menu--up': dropUp }">
                <button type="button" wire:click="openDetailDrawer({{ $account->id }})" class="vestra-credit__action-item" role="menuitem" @click="open = false">
                    <x-filament::icon icon="heroicon-o-eye" class="h-4 w-4" />
                    <span>View Details</span>
                </button>
                <button type="button" wire:click="openAdjustDrawer({{ $account->id }})" class="vestra-credit__action-item" role="menuitem" @click="open = false">
                    <x-filament::icon icon="heroicon-o-adjustments-horizontal" class="h-4 w-4" />
                    <span>Adjust Limit</span>
                </button>
            </div>
        </div>
    </td>
</tr>

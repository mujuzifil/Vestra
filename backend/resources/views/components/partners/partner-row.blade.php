@props([
    'partner',
])

@php
$partnerCode = 'PART-'.str_pad((string) $partner->id, 4, '0', STR_PAD_LEFT);
$rep = $partner->salesRepresentative;
$credit = $partner->creditAccount;
$utilization = $credit ? $credit->utilizationPercentage() : null;
$territory = $partner->district ?: $partner->city ?: '—';

$utilizationClass = match (true) {
    $utilization === null => '',
    $utilization >= 90 => 'vestra-partners__utilization-bar--danger',
    $utilization >= 70 => 'vestra-partners__utilization-bar--warning',
    default => 'vestra-partners__utilization-bar--success',
};
@endphp

<tr class="vestra-partners__row" wire:key="partner-{{ $partner->id }}">
    <td class="vestra-partners__td vestra-partners__td--partner">
        <button
            type="button"
            wire:click="openDetailDrawer({{ $partner->id }})"
            class="vestra-partners__partner-name"
        >
            {{ $partner->company_name }}
        </button>
        <div class="vestra-partners__partner-meta" style="display:flex;align-items:center;gap:0.4rem;flex-wrap:wrap;">
            <span>{{ $partnerCode }}</span>
            <x-partners.tier-badge :tier="$partner->tier" />
        </div>
    </td>

    <td class="vestra-partners__td vestra-partners__td--territory">
        <span class="vestra-partners__cell-text">{{ $territory }}</span>
    </td>

    <td class="vestra-partners__td vestra-partners__td--country">
        <span class="vestra-partners__cell-text">{{ $partner->country ?: '—' }}</span>
    </td>

    <td class="vestra-partners__td vestra-partners__td--type">
        <span class="vestra-partners__cell-text">{{ $partner->business_type ?: '—' }}</span>
    </td>

    <td class="vestra-partners__td vestra-partners__td--rep">
        @if ($rep)
            <div class="vestra-partners__account-manager">
                <span class="vestra-partners__account-manager-avatar">{{ mb_strtoupper(mb_substr($rep->name, 0, 2)) }}</span>
                <span class="vestra-partners__account-manager-name">{{ $rep->name }}</span>
            </div>
        @else
            <span class="vestra-partners__empty-cell">Unassigned</span>
        @endif
    </td>

    <td class="vestra-partners__td vestra-partners__td--credit-limit">
        <span class="vestra-partners__cell-text">
            {{ $credit ? 'UGX '.number_format((float) $credit->limit, 0) : '—' }}
        </span>
    </td>

    <td class="vestra-partners__td vestra-partners__td--utilization">
        @if ($utilization !== null)
            <div class="vestra-partners__utilization">
                <div class="vestra-partners__utilization-bar">
                    <div class="vestra-partners__utilization-bar-fill {{ $utilizationClass }}" style="width: {{ min(100, $utilization) }}%"></div>
                </div>
                <span class="vestra-partners__utilization-value">{{ number_format($utilization, 0) }}%</span>
            </div>
        @else
            <span class="vestra-partners__empty-cell">No credit</span>
        @endif
    </td>

    <td class="vestra-partners__td vestra-partners__td--outstanding">
        <span class="vestra-partners__cell-text">
            {{ $credit ? 'UGX '.number_format((float) $credit->balance, 0) : '—' }}
        </span>
    </td>

    <td class="vestra-partners__td vestra-partners__td--status">
        <x-partners.status-badge :status="$partner->status" />
    </td>

    <td class="vestra-partners__td vestra-partners__td--actions">
        <div
            class="vestra-partners__actions"
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
            <button type="button" @click="toggle()" class="vestra-partners__action-trigger" aria-label="Partner actions" aria-haspopup="true">
                <x-filament::icon icon="heroicon-m-ellipsis-vertical" class="h-5 w-5" />
            </button>
            <div x-show="open" x-transition class="vestra-partners__action-menu" role="menu" :class="{ 'vestra-partners__action-menu--up': dropUp }">
                <button type="button" wire:click="openDetailDrawer({{ $partner->id }})" class="vestra-partners__action-item" role="menuitem" @click="open = false">
                    <x-filament::icon icon="heroicon-o-eye" class="h-4 w-4" />
                    <span>View Partner</span>
                </button>
                <a
                    href="{{ \App\Filament\Pages\Distributors\PartnerEditPage::getUrl(['partner' => $partner->id]) }}"
                    class="vestra-partners__action-item"
                    role="menuitem"
                    @click="open = false"
                >
                    <x-filament::icon icon="heroicon-o-pencil-square" class="h-4 w-4" />
                    <span>Edit Partner</span>
                </a>
                @can('delete', $partner)
                    <button
                        type="button"
                        wire:click="deletePartner({{ $partner->id }})"
                        wire:confirm="Permanently delete this partner? They will disappear from the public website and distributor portal and must reapply."
                        class="vestra-partners__action-item vestra-partners__action-item--danger"
                        role="menuitem"
                        @click="open = false"
                    >
                        <x-filament::icon icon="heroicon-o-trash" class="h-4 w-4" />
                        <span>Delete Partner</span>
                    </button>
                @endcan
            </div>
        </div>
    </td>
</tr>

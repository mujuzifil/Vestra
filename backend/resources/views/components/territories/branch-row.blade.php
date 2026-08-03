@props(['branch'])

@php
$initials = collect(explode(' ', (string) $branch->name))
    ->take(2)
    ->map(fn ($part) => strtoupper(substr((string) $part, 0, 1)))
    ->implode('');
$initials = $initials ?: '—';
$hasCoordinates = $branch->latitude !== null && $branch->longitude !== null;
$serviceAreaCount = $branch->serviceAreas->count();
@endphp

<tr class="vestra-territories__row" wire:key="branch-{{ $branch->id }}">
    <td class="vestra-territories__td vestra-territories__td--branch">
        <div class="vestra-territories__branch-primary">
            <span class="vestra-territories__avatar">{{ $initials }}</span>
            <div class="vestra-territories__branch-info">
                <button
                    type="button"
                    wire:click="openDetailDrawer({{ $branch->id }})"
                    class="vestra-territories__branch-name"
                >
                    {{ $branch->name }}
                </button>
                @if ($branch->is_default)
                    <span class="vestra-territories__default-tag">Default</span>
                @endif
            </div>
        </div>
    </td>

    <td class="vestra-territories__td vestra-territories__td--distributor">
        <span class="vestra-territories__cell-text">{{ $branch->distributor?->company_name ?? '—' }}</span>
    </td>

    <td class="vestra-territories__td vestra-territories__td--manager">
        <span class="vestra-territories__cell-text">{{ $branch->manager_name ?? '—' }}</span>
        @if ($branch->phone)
            <span class="vestra-territories__cell-subtext">{{ $branch->phone }}</span>
        @endif
    </td>

    <td class="vestra-territories__td vestra-territories__td--location">
        <span class="vestra-territories__cell-text">{{ collect([$branch->city, $branch->district, $branch->country])->filter()->implode(', ') ?: '—' }}</span>
    </td>

    <td class="vestra-territories__td vestra-territories__td--coordinates">
        @if ($hasCoordinates)
            <span class="vestra-territories__badge vestra-territories__badge--info">
                <x-filament::icon icon="heroicon-o-map-pin" class="h-3.5 w-3.5" />
                Geocoded
            </span>
        @else
            <span class="vestra-territories__empty-cell">No coordinates</span>
        @endif
    </td>

    <td class="vestra-territories__td vestra-territories__td--service_areas">
        <span class="vestra-territories__count @if ($serviceAreaCount > 0) vestra-territories__count--active @endif">{{ $serviceAreaCount }}</span>
    </td>

    <td class="vestra-territories__td vestra-territories__td--status">
        <span class="vestra-territories__badge vestra-territories__badge--{{ $branch->status === 'active' ? 'success' : 'gray' }}">
            {{ ucfirst((string) $branch->status) }}
        </span>
    </td>

    <td class="vestra-territories__td vestra-territories__td--actions">
        <button
            type="button"
            wire:click="openDetailDrawer({{ $branch->id }})"
            class="vestra-territories__action-trigger"
            aria-label="View {{ $branch->name }}"
        >
            <x-filament::icon icon="heroicon-o-eye" class="h-5 w-5" />
        </button>
    </td>
</tr>

@props([
    'tier' => null,
    'label' => null,
])

@php
    use App\Enums\DistributorTier;

    $resolved = $tier instanceof DistributorTier
        ? $tier
        : (is_string($tier) && $tier !== '' ? DistributorTier::tryFrom($tier) : null);

    $text = $label ?? $resolved?->label() ?? '—';
    $modifier = $resolved?->value ?? 'unknown';
@endphp

<span {{ $attributes->class(['vestra-tier-badge', 'vestra-tier-badge--'.$modifier]) }}>
    {{ $text }}
</span>

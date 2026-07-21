@php
    $customer = $getRecord()->user;
@endphp

<div class="flex items-center gap-3">
    <div class="fi-vestra-avatar h-9 w-9 text-xs">
        {{ $customer?->initials() ?? '?' }}
    </div>
    <div>
        <p class="text-sm font-semibold text-neutral-900">{{ $customer?->name ?? 'Guest' }}</p>
        <p class="text-xs text-neutral-500">{{ $customer?->email ?? '' }}</p>
    </div>
</div>

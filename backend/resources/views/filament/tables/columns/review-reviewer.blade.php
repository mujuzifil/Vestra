@php
    $reviewer = $getRecord()->user;
@endphp

<div class="flex items-center gap-3">
    <div class="fi-vestra-avatar h-9 w-9 text-xs">
        {{ $reviewer?->initials() ?? '?' }}
    </div>
    <div>
        <p class="text-sm font-semibold text-neutral-900">{{ $reviewer?->name ?? 'Guest' }}</p>
        <p class="text-xs text-neutral-500">{{ $reviewer?->email ?? '' }}</p>
    </div>
</div>

@php
    $record = $getRecord();
@endphp

<div class="flex items-center gap-3">
    <div class="fi-vestra-avatar h-9 w-9 text-xs">
        {{ strtoupper(substr($record->name, 0, 1)) }}
    </div>
    <div>
        <p class="text-sm font-semibold text-neutral-900">{{ $record->name }}</p>
        <p class="text-xs text-neutral-500">{{ $record->email }}</p>
    </div>
</div>

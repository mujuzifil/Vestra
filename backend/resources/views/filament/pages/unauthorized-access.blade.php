@php
$dashboardUrl = url('/');
$backUrl = url()->previous() !== url()->current() ? url()->previous() : $dashboardUrl;
@endphp

<div class="vestra-workspace vestra-unauthorized">
    <div class="vestra-card vestra-unauthorized__card">
        <div class="vestra-unauthorized__icon">
            <x-filament::icon icon="heroicon-o-lock-closed" class="h-10 w-10" />
        </div>
        <h1 class="vestra-workspace__title">Unauthorized</h1>
        <p class="vestra-workspace__welcome">{{ $message }}</p>
        <div class="vestra-unauthorized__actions">
            <a href="{{ $backUrl }}" class="vestra-button vestra-button--secondary">Back</a>
            <button type="button" class="vestra-button vestra-button--secondary" disabled title="Coming soon">Request Access</button>
            <a href="{{ $dashboardUrl }}" class="vestra-button vestra-button--primary">Return Dashboard</a>
        </div>
    </div>
</div>

<x-filament-panels::page class="vestra-administration-page">
    <div class="administration-security-page space-y-6">
        <div>
            <p class="text-sm text-neutral-600">
                Configure platform-wide security policies including password requirements, login limits, and session timeout.
            </p>
        </div>

        <x-filament-panels::form wire:submit="save">
            {{ $this->form }}

            <x-filament-panels::form.actions
                :actions="$this->getCachedFormActions()"
                :full-width="$this->hasFullWidthFormActions()"
            />
        </x-filament-panels::form>
    </div>
</x-filament-panels::page>

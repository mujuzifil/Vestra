<x-filament-panels::page class="vestra-settings-page">
    <div class="settings-edit-page space-y-6">
        <div class="settings-edit-header">
            <p class="text-sm text-neutral-600">
                Update {{ $this->getGroup()->label() }} configuration values. Changes are applied immediately after saving.
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

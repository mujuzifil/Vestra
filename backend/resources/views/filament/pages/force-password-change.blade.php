<x-filament-panels::page>
    <div class="mx-auto max-w-lg">
        <x-filament::section
            icon="heroicon-o-shield-exclamation"
            heading="Update Your Password"
        >
            <p class="text-sm text-neutral-600 dark:text-neutral-300">
                For security, please create a new password before continuing.
            </p>

            <x-filament-panels::form wire:submit="changePassword" class="mt-4">
                {{ $this->form }}

                <x-filament-panels::form.actions
                    :actions="$this->getCachedFormActions()"
                    :full-width="$this->hasFullWidthFormActions()"
                />
            </x-filament-panels::form>
        </x-filament::section>
    </div>
</x-filament-panels::page>

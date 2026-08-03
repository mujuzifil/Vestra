@props([
    'show' => false,
])

<div
    x-data="{ open: @entangle('showAdjustDrawer') }"
    x-show="open"
    x-cloak
    class="vestra-credit__drawer-backdrop"
    @keydown.escape.window="if (open) $wire.closeAdjustDrawer()"
>
    <div x-show="open" x-transition.opacity class="vestra-credit__drawer-overlay" wire:click="closeAdjustDrawer"></div>

    <aside
        x-show="open"
        x-transition:enter="transform transition ease-in-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transform transition ease-in-out duration-300"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="vestra-credit__drawer"
        role="dialog"
        aria-modal="true"
        aria-labelledby="credit-adjust-drawer-title"
    >
        <div class="vestra-credit__drawer-header">
            <div>
                <h2 id="credit-adjust-drawer-title" class="vestra-credit__drawer-title">Adjust Credit Limit</h2>
                <p class="vestra-credit__drawer-subtitle">Set a new credit limit and record the reason for this change.</p>
            </div>
            <button type="button" wire:click="closeAdjustDrawer" class="vestra-credit__drawer-close" aria-label="Close drawer">
                <x-filament::icon icon="heroicon-o-x-mark" class="h-5 w-5" />
            </button>
        </div>

        <form wire:submit.prevent="saveLimit" class="vestra-credit__drawer-body">
            <div class="vestra-credit__form-field">
                <label for="new_limit" class="vestra-credit__form-label">New Credit Limit <span>*</span></label>
                <input
                    type="number"
                    id="new_limit"
                    min="0"
                    step="0.01"
                    wire:model="form.new_limit"
                    class="vestra-credit__form-input @error('form.new_limit') vestra-credit__form-input--error @enderror"
                    placeholder="0.00"
                />
                @error('form.new_limit')
                    <span class="vestra-credit__form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="vestra-credit__form-field">
                <label for="reason" class="vestra-credit__form-label">Reason <span>*</span></label>
                <textarea
                    id="reason"
                    rows="4"
                    wire:model="form.reason"
                    class="vestra-credit__form-textarea @error('form.reason') vestra-credit__form-textarea--error @enderror"
                    placeholder="Explain why this credit limit is being adjusted..."
                ></textarea>
                @error('form.reason')
                    <span class="vestra-credit__form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="vestra-credit__drawer-footer">
                <button type="button" wire:click="closeAdjustDrawer" class="vestra-button vestra-button--secondary">Cancel</button>
                <button type="submit" class="vestra-button vestra-button--primary">Save Limit</button>
            </div>
        </form>
    </aside>
</div>

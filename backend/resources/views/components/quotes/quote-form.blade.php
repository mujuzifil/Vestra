@props([
    'show' => false,
    'editingQuoteId' => null,
    'assignees' => [],
    'statusOptions' => [],
    'priorityOptions' => [],
])

<div
    x-data="{ open: @entangle('showFormDrawer') }"
    x-show="open"
    x-cloak
    class="vestra-quotes__drawer-backdrop"
    @keydown.escape.window="if (open) $wire.closeFormDrawer()"
>
    <div x-show="open" x-transition.opacity class="vestra-quotes__drawer-overlay" wire:click="closeFormDrawer"></div>

    <aside
        x-show="open"
        x-transition:enter="transform transition ease-in-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transform transition ease-in-out duration-300"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="vestra-quotes__drawer"
        role="dialog"
        aria-modal="true"
        aria-labelledby="quote-drawer-title"
    >
        <div class="vestra-quotes__drawer-header">
            <div>
                <h2 id="quote-drawer-title" class="vestra-quotes__drawer-title">Edit Quote</h2>
                <p class="vestra-quotes__drawer-subtitle">Update status, assignment, value and notes.</p>
            </div>
            <button type="button" wire:click="closeFormDrawer" class="vestra-quotes__drawer-close" aria-label="Close drawer">
                <x-filament::icon icon="heroicon-o-x-mark" class="h-5 w-5" />
            </button>
        </div>

        <form wire:submit.prevent="saveQuote" class="vestra-quotes__drawer-body">
            <div class="vestra-quotes__form-grid">
                <div class="vestra-quotes__form-field">
                    <label for="quote-status" class="vestra-quotes__form-label">Status</label>
                    <select id="quote-status" wire:model="form.status" class="vestra-quotes__form-select">
                        @foreach ($statusOptions as $status)
                            <option value="{{ $status->value }}">{{ $status->label() }}</option>
                        @endforeach
                    </select>
                    @error('form.status')<span class="vestra-quotes__form-error">{{ $message }}</span>@enderror
                </div>

                <div class="vestra-quotes__form-field">
                    <label for="quote-priority" class="vestra-quotes__form-label">Priority</label>
                    <select id="quote-priority" wire:model="form.priority" class="vestra-quotes__form-select">
                        <option value="">—</option>
                        @foreach ($priorityOptions as $priority)
                            <option value="{{ $priority->value }}">{{ $priority->label() }}</option>
                        @endforeach
                    </select>
                    @error('form.priority')<span class="vestra-quotes__form-error">{{ $message }}</span>@enderror
                </div>

                <div class="vestra-quotes__form-field">
                    <label for="quote-value" class="vestra-quotes__form-label">Estimated Value (UGX)</label>
                    <input id="quote-value" type="number" min="0" step="0.01" wire:model="form.estimated_value" class="vestra-quotes__form-input" />
                    @error('form.estimated_value')<span class="vestra-quotes__form-error">{{ $message }}</span>@enderror
                </div>

                <div class="vestra-quotes__form-field">
                    <label for="quote-close" class="vestra-quotes__form-label">Expected Close Date</label>
                    <input id="quote-close" type="date" wire:model="form.expected_close_date" class="vestra-quotes__form-input" />
                    @error('form.expected_close_date')<span class="vestra-quotes__form-error">{{ $message }}</span>@enderror
                </div>

                <div class="vestra-quotes__form-field vestra-quotes__form-field--full">
                    <label for="quote-assignee" class="vestra-quotes__form-label">Sales Representative</label>
                    <select id="quote-assignee" wire:model="form.assigned_to" class="vestra-quotes__form-select">
                        <option value="">Unassigned</option>
                        @foreach ($assignees as $assignee)
                            <option value="{{ $assignee['id'] }}">{{ $assignee['name'] }}</option>
                        @endforeach
                    </select>
                    @error('form.assigned_to')<span class="vestra-quotes__form-error">{{ $message }}</span>@enderror
                </div>

                <div class="vestra-quotes__form-field vestra-quotes__form-field--full">
                    <label for="quote-requirements" class="vestra-quotes__form-label">Requirements</label>
                    <textarea id="quote-requirements" rows="3" wire:model="form.requirements" class="vestra-quotes__form-textarea"></textarea>
                    @error('form.requirements')<span class="vestra-quotes__form-error">{{ $message }}</span>@enderror
                </div>

                <div class="vestra-quotes__form-field vestra-quotes__form-field--full">
                    <label for="quote-notes" class="vestra-quotes__form-label">Internal Notes</label>
                    <textarea id="quote-notes" rows="4" wire:model="form.admin_notes" class="vestra-quotes__form-textarea"></textarea>
                    @error('form.admin_notes')<span class="vestra-quotes__form-error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="vestra-quotes__drawer-footer">
                <button type="button" wire:click="closeFormDrawer" class="vestra-button vestra-button--secondary">Cancel</button>
                <button type="submit" class="vestra-button vestra-button--primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="saveQuote">Save Changes</span>
                    <span wire:loading wire:target="saveQuote">Saving…</span>
                </button>
            </div>
        </form>
    </aside>
</div>

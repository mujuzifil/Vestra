@props([
    'show' => false,
])

<div
    x-data="{ open: @entangle('showImportDrawer') }"
    x-show="open"
    x-cloak
    class="vestra-companies__drawer-backdrop"
    @keydown.escape.window="open = false"
>
    <div x-show="open" x-transition.opacity class="vestra-companies__drawer-overlay" @click="open = false"></div>

    <aside
        x-show="open"
        x-transition:enter="transform transition ease-in-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transform transition ease-in-out duration-300"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="vestra-companies__drawer"
        role="dialog"
        aria-modal="true"
        aria-labelledby="company-import-title"
    >
        <div class="vestra-companies__drawer-header">
            <div>
                <h2 id="company-import-title" class="vestra-companies__drawer-title">Import Companies</h2>
                <p class="vestra-companies__drawer-subtitle">Upload a CSV file to bulk import or update companies.</p>
            </div>
            <button type="button" wire:click="$set('showImportDrawer', false)" class="vestra-companies__drawer-close" aria-label="Close drawer">
                <x-filament::icon icon="heroicon-o-x-mark" class="h-5 w-5" />
            </button>
        </div>

        <div class="vestra-companies__drawer-body">
            <div class="vestra-companies__import-instructions">
                <p class="vestra-companies__text">
                    Rows are matched by <strong>primary_contact_email</strong> to existing portal users. Unknown emails are skipped and reported.
                </p>
                <p class="vestra-companies__text">Required column: <code>primary_contact_email</code></p>
                <p class="vestra-companies__text">Optional columns: company_name, industry, business_type, tax_identification, registration_number, website, district, city, country, address, primary_contact_name, primary_contact_phone, status, region, notes</p>
            </div>

            <form wire:submit.prevent="import" class="vestra-companies__import-form">
                <div class="vestra-companies__form-field vestra-companies__form-field--full">
                    <label for="company-import-file" class="vestra-companies__form-label">CSV File</label>
                    <input
                        id="company-import-file"
                        type="file"
                        wire:model="importFile"
                        accept=".csv"
                        class="vestra-companies__form-input @error('importFile') vestra-companies__form-input--error @enderror"
                    />
                    <div wire:loading wire:target="importFile" class="vestra-companies__form-hint">Uploading...</div>
                    @error('importFile')<span class="vestra-companies__form-error">{{ $message }}</span>@enderror
                </div>

                <div class="vestra-companies__drawer-footer">
                    <button type="button" wire:click="$set('showImportDrawer', false)" class="vestra-button vestra-button--secondary">Cancel</button>
                    <button type="submit" class="vestra-button vestra-button--primary" wire:loading.attr="disabled">
                        <x-filament::icon icon="heroicon-o-arrow-up-on-square" class="h-4 w-4" />
                        <span>Import</span>
                    </button>
                </div>
            </form>

            <a
                href="data:text/csv;charset=utf-8,primary_contact_email,company_name,industry,business_type,tax_identification,registration_number,website,district,city,country,address,primary_contact_name,primary_contact_phone,status,region,notes"
                download="companies-import-template.csv"
                class="vestra-companies__template-link"
            >
                <x-filament::icon icon="heroicon-o-document-arrow-down" class="h-4 w-4" />
                <span>Download template CSV</span>
            </a>
        </div>
    </aside>
</div>

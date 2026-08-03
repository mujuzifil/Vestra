@props([
    'show' => false,
    'editingCompanyId' => null,
    'assignees' => [],
    'statusOptions' => [],
])

@php
$isEditing = $editingCompanyId !== null;
$title = $isEditing ? 'Edit Company' : 'New Company';
$submitLabel = $isEditing ? 'Save Changes' : 'Create Company';
@endphp

<div
    x-data="{ open: @entangle('showFormDrawer') }"
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
        aria-labelledby="company-drawer-title"
    >
        <div class="vestra-companies__drawer-header">
            <div>
                <h2 id="company-drawer-title" class="vestra-companies__drawer-title">{{ $title }}</h2>
                <p class="vestra-companies__drawer-subtitle">
                    {{ $isEditing ? 'Update the details of this company.' : 'Add a new company to your CRM.' }}
                </p>
            </div>
            <button type="button" wire:click="closeFormDrawer" class="vestra-companies__drawer-close" aria-label="Close drawer">
                <x-filament::icon icon="heroicon-o-x-mark" class="h-5 w-5" />
            </button>
        </div>

        <form wire:submit.prevent="saveCompany" class="vestra-companies__drawer-body">
            <div class="vestra-companies__form-grid">
                <div class="vestra-companies__form-field vestra-companies__form-field--full">
                    <label for="company-name" class="vestra-companies__form-label">Company Name <span aria-label="required">*</span></label>
                    <input
                        id="company-name"
                        type="text"
                        wire:model="form.company_name"
                        class="vestra-companies__form-input @error('form.company_name') vestra-companies__form-input--error @enderror"
                        placeholder="Enter company name"
                    />
                    @error('form.company_name')<span class="vestra-companies__form-error">{{ $message }}</span>@enderror
                </div>

                <div class="vestra-companies__form-field">
                    <label for="company-industry" class="vestra-companies__form-label">Industry</label>
                    <input
                        id="company-industry"
                        type="text"
                        wire:model="form.industry"
                        class="vestra-companies__form-input"
                        placeholder="e.g. Technology"
                    />
                </div>

                <div class="vestra-companies__form-field">
                    <label for="company-business-type" class="vestra-companies__form-label">Business Type</label>
                    <input
                        id="company-business-type"
                        type="text"
                        wire:model="form.business_type"
                        class="vestra-companies__form-input"
                        placeholder="e.g. Limited Company"
                    />
                </div>

                <div class="vestra-companies__form-field">
                    <label for="company-tax-id" class="vestra-companies__form-label">Tax ID</label>
                    <input
                        id="company-tax-id"
                        type="text"
                        wire:model="form.tax_identification"
                        class="vestra-companies__form-input"
                        placeholder="Tax identification number"
                    />
                </div>

                <div class="vestra-companies__form-field">
                    <label for="company-reg-number" class="vestra-companies__form-label">Registration Number</label>
                    <input
                        id="company-reg-number"
                        type="text"
                        wire:model="form.registration_number"
                        class="vestra-companies__form-input"
                        placeholder="Registration number"
                    />
                </div>

                <div class="vestra-companies__form-field vestra-companies__form-field--full">
                    <label for="company-website" class="vestra-companies__form-label">Website</label>
                    <input
                        id="company-website"
                        type="url"
                        wire:model="form.website"
                        class="vestra-companies__form-input @error('form.website') vestra-companies__form-input--error @enderror"
                        placeholder="https://example.com"
                    />
                    @error('form.website')<span class="vestra-companies__form-error">{{ $message }}</span>@enderror
                </div>

                <div class="vestra-companies__form-field">
                    <label for="company-country" class="vestra-companies__form-label">Country <span aria-label="required">*</span></label>
                    <input
                        id="company-country"
                        type="text"
                        wire:model="form.country"
                        class="vestra-companies__form-input @error('form.country') vestra-companies__form-input--error @enderror"
                        placeholder="Country"
                    />
                    @error('form.country')<span class="vestra-companies__form-error">{{ $message }}</span>@enderror
                </div>

                <div class="vestra-companies__form-field">
                    <label for="company-city" class="vestra-companies__form-label">City</label>
                    <input
                        id="company-city"
                        type="text"
                        wire:model="form.city"
                        class="vestra-companies__form-input"
                        placeholder="City"
                    />
                </div>

                <div class="vestra-companies__form-field">
                    <label for="company-district" class="vestra-companies__form-label">District</label>
                    <input
                        id="company-district"
                        type="text"
                        wire:model="form.district"
                        class="vestra-companies__form-input"
                        placeholder="District"
                    />
                </div>

                <div class="vestra-companies__form-field">
                    <label for="company-region" class="vestra-companies__form-label">Region</label>
                    <input
                        id="company-region"
                        type="text"
                        wire:model="form.region"
                        class="vestra-companies__form-input"
                        placeholder="Region"
                    />
                </div>

                <div class="vestra-companies__form-field vestra-companies__form-field--full">
                    <label for="company-address" class="vestra-companies__form-label">Address</label>
                    <textarea
                        id="company-address"
                        wire:model="form.address"
                        rows="3"
                        class="vestra-companies__form-textarea"
                        placeholder="Full address"
                    ></textarea>
                </div>

                <div class="vestra-companies__form-field">
                    <label for="company-contact-name" class="vestra-companies__form-label">Primary Contact <span aria-label="required">*</span></label>
                    <input
                        id="company-contact-name"
                        type="text"
                        wire:model="form.primary_contact_name"
                        class="vestra-companies__form-input @error('form.primary_contact_name') vestra-companies__form-input--error @enderror"
                        placeholder="Contact name"
                    />
                    @error('form.primary_contact_name')<span class="vestra-companies__form-error">{{ $message }}</span>@enderror
                </div>

                <div class="vestra-companies__form-field">
                    <label for="company-contact-email" class="vestra-companies__form-label">Contact Email <span aria-label="required">*</span></label>
                    <input
                        id="company-contact-email"
                        type="email"
                        wire:model="form.primary_contact_email"
                        class="vestra-companies__form-input @error('form.primary_contact_email') vestra-companies__form-input--error @enderror"
                        placeholder="email@example.com"
                    />
                    @error('form.primary_contact_email')<span class="vestra-companies__form-error">{{ $message }}</span>@enderror
                </div>

                <div class="vestra-companies__form-field">
                    <label for="company-contact-phone" class="vestra-companies__form-label">Contact Phone</label>
                    <input
                        id="company-contact-phone"
                        type="text"
                        wire:model="form.primary_contact_phone"
                        class="vestra-companies__form-input"
                        placeholder="Phone number"
                    />
                </div>

                <div class="vestra-companies__form-field">
                    <label for="company-status" class="vestra-companies__form-label">Status <span aria-label="required">*</span></label>
                    <select id="company-status" wire:model="form.status" class="vestra-companies__form-select @error('form.status') vestra-companies__form-input--error @enderror">
                        @foreach ($statusOptions as $status)
                            <option value="{{ $status->value }}">{{ $status->label() }}</option>
                        @endforeach
                    </select>
                    @error('form.status')<span class="vestra-companies__form-error">{{ $message }}</span>@enderror
                </div>

                <div class="vestra-companies__form-field">
                    <label for="company-account-manager" class="vestra-companies__form-label">Account Manager</label>
                    <select id="company-account-manager" wire:model="form.account_manager_id" class="vestra-companies__form-select">
                        <option value="">Unassigned</option>
                        @foreach ($assignees as $assignee)
                            <option value="{{ $assignee['id'] }}">{{ $assignee['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="vestra-companies__form-field vestra-companies__form-field--full">
                    <label for="company-notes" class="vestra-companies__form-label">Notes</label>
                    <textarea
                        id="company-notes"
                        wire:model="form.notes"
                        rows="3"
                        class="vestra-companies__form-textarea"
                        placeholder="Internal notes..."
                    ></textarea>
                </div>
            </div>

            <div class="vestra-companies__drawer-footer">
                <button type="button" wire:click="closeFormDrawer" class="vestra-button vestra-button--secondary">Cancel</button>
                <button type="submit" class="vestra-button vestra-button--primary">
                    <x-filament::icon icon="heroicon-o-check" class="h-4 w-4" />
                    <span>{{ $submitLabel }}</span>
                </button>
            </div>
        </form>
    </aside>
</div>

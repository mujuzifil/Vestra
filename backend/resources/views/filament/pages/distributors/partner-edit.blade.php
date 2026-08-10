@php
    $backUrl = $this->cancelUrl;
    $tierOptions = $this->tierOptions;
    $stockOptions = $this->stockOptions;
    $statusOptions = $this->statusOptions;
    $partnerName = $form['company_name'] ?? 'Partner';
@endphp

<div class="vestra-workspace vestra-staff-form vestra-partner-edit">
    <section class="vestra-staff-form__hero">
        <div class="vestra-staff-form__hero-main">
            <nav class="vestra-staff-form__breadcrumb" aria-label="Breadcrumb">
                <a href="{{ $backUrl }}">Active Partners</a>
                <span>/</span>
                <span>Edit Partner</span>
            </nav>
            <h1 class="vestra-workspace__title">Edit Partner</h1>
            <p class="vestra-workspace__welcome">
                Update {{ $partnerName }} identity, partnership rank, public locator details, and listing status without a code deploy.
            </p>
        </div>

        <div class="vestra-staff-form__hero-actions">
            <button
                type="button"
                class="vestra-button vestra-button--secondary"
                wire:click="deletePartner"
                wire:confirm="Permanently delete this partner? They will disappear from the public website and distributor portal and must reapply."
            >
                Delete Partner
            </button>
            <a href="{{ $backUrl }}" class="vestra-button vestra-button--secondary">Cancel</a>
            <button type="submit" form="partner-edit-form" class="vestra-button vestra-button--primary" wire:loading.attr="disabled">
                Save Partner
            </button>
        </div>
    </section>

    <form id="partner-edit-form" wire:submit.prevent="save" class="vestra-staff-form__grid">
        <section class="vestra-card vestra-staff-form__card">
            <h2 class="vestra-staff-form__card-title">Company Identity</h2>

            <div class="vestra-staff-form__field">
                <label for="partner-company" class="vestra-staff-form__label">Business Name <span class="vestra-staff-form__required">*</span></label>
                <input id="partner-company" type="text" wire:model="form.company_name" class="vestra-staff-form__input @error('form.company_name') vestra-staff-form__input--error @enderror" />
                @error('form.company_name')<span class="vestra-staff-form__error">{{ $message }}</span>@enderror
            </div>

            <div class="vestra-staff-form__field">
                <label for="partner-trading" class="vestra-staff-form__label">Trading Name</label>
                <input id="partner-trading" type="text" wire:model="form.trading_name" class="vestra-staff-form__input" />
            </div>

            <div class="vestra-staff-form__field">
                <label for="partner-contact" class="vestra-staff-form__label">Primary Contact</label>
                <input id="partner-contact" type="text" wire:model="form.primary_contact_name" class="vestra-staff-form__input" />
            </div>

            <div class="vestra-partner-edit__two-col">
                <div class="vestra-staff-form__field">
                    <label for="partner-email" class="vestra-staff-form__label">Email</label>
                    <input id="partner-email" type="email" wire:model="form.email" class="vestra-staff-form__input @error('form.email') vestra-staff-form__input--error @enderror" />
                    @error('form.email')<span class="vestra-staff-form__error">{{ $message }}</span>@enderror
                </div>
                <div class="vestra-staff-form__field">
                    <label for="partner-phone" class="vestra-staff-form__label">Phone</label>
                    <input id="partner-phone" type="tel" wire:model="form.phone" class="vestra-staff-form__input" />
                </div>
            </div>
        </section>

        <section class="vestra-card vestra-staff-form__card">
            <h2 class="vestra-staff-form__card-title">Partnership Rank</h2>
            <p class="vestra-partner-edit__hint">Rank controls the public badge on Where to Buy. Only admins can change this.</p>

            <div class="vestra-partner-edit__tier-grid" role="radiogroup" aria-label="Distributor tier">
                @foreach ($tierOptions as $tier)
                    <label class="vestra-partner-edit__tier-card vestra-partner-edit__tier-card--{{ $tier['value'] }} {{ ($form['tier'] ?? '') === $tier['value'] ? 'is-selected' : '' }}">
                        <input
                            type="radio"
                            class="sr-only"
                            wire:model.live="form.tier"
                            value="{{ $tier['value'] }}"
                            name="partner-tier"
                        />
                        <span class="vestra-partner-edit__tier-badge vestra-partner-edit__tier-badge--{{ $tier['value'] }}">{{ $tier['label'] }}</span>
                        <span class="vestra-partner-edit__tier-copy">{{ $tier['description'] }}</span>
                    </label>
                @endforeach
            </div>
            @error('form.tier')<span class="vestra-staff-form__error">{{ $message }}</span>@enderror
        </section>

        <section class="vestra-card vestra-staff-form__card">
            <h2 class="vestra-staff-form__card-title">Public Locator</h2>

            <div class="vestra-partner-edit__two-col">
                <div class="vestra-staff-form__field">
                    <label for="partner-district" class="vestra-staff-form__label">District</label>
                    <input id="partner-district" type="text" wire:model="form.district" class="vestra-staff-form__input" placeholder="e.g. Kampala" />
                </div>
                <div class="vestra-staff-form__field">
                    <label for="partner-city" class="vestra-staff-form__label">Area / Town</label>
                    <input id="partner-city" type="text" wire:model="form.city" class="vestra-staff-form__input" placeholder="e.g. Nakawa" />
                </div>
            </div>

            <div class="vestra-partner-edit__two-col">
                <div class="vestra-staff-form__field">
                    <label for="partner-country" class="vestra-staff-form__label">Country</label>
                    <input id="partner-country" type="text" wire:model="form.country" class="vestra-staff-form__input" />
                </div>
                <div class="vestra-staff-form__field">
                    <label for="partner-whatsapp" class="vestra-staff-form__label">WhatsApp Number</label>
                    <input id="partner-whatsapp" type="tel" wire:model="form.whatsapp" class="vestra-staff-form__input" placeholder="+2567..." />
                </div>
            </div>

            <div class="vestra-staff-form__field">
                <label for="partner-address" class="vestra-staff-form__label">Address</label>
                <textarea id="partner-address" rows="2" wire:model="form.address" class="vestra-staff-form__input"></textarea>
            </div>

            <div class="vestra-staff-form__field">
                <label for="partner-maps" class="vestra-staff-form__label">Google Maps URL</label>
                <input id="partner-maps" type="url" wire:model="form.google_maps_url" class="vestra-staff-form__input @error('form.google_maps_url') vestra-staff-form__input--error @enderror" placeholder="https://maps.google.com/..." />
                @error('form.google_maps_url')<span class="vestra-staff-form__error">{{ $message }}</span>@enderror
            </div>

            <div class="vestra-staff-form__field">
                <div class="vestra-partner-edit__hours-header">
                    <span class="vestra-staff-form__label">Opening Hours</span>
                    <button type="button" class="vestra-partner-edit__link-btn" wire:click="addHourRow">Add row</button>
                </div>
                <div class="vestra-partner-edit__hours-list">
                    @foreach ($hourRows as $index => $row)
                        <div class="vestra-partner-edit__hours-row" wire:key="hour-row-{{ $index }}">
                            <input type="text" wire:model="hourRows.{{ $index }}.day" class="vestra-staff-form__input" placeholder="Day / period" />
                            <input type="text" wire:model="hourRows.{{ $index }}.hours" class="vestra-staff-form__input" placeholder="e.g. 08:00-17:00" />
                            <button type="button" class="vestra-partner-edit__icon-btn" wire:click="removeHourRow({{ $index }})" aria-label="Remove hours row">
                                <x-filament::icon icon="heroicon-o-trash" class="h-4 w-4" />
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="vestra-staff-form__field">
                <span class="vestra-staff-form__label">Stock Availability</span>
                <div class="vestra-partner-edit__stock-grid" role="radiogroup" aria-label="Stock availability">
                    @foreach ($stockOptions as $stock)
                        <label class="vestra-partner-edit__stock-chip vestra-partner-edit__stock-chip--{{ $stock['value'] }} {{ ($form['stock_availability'] ?? '') === $stock['value'] ? 'is-selected' : '' }}">
                            <input type="radio" class="sr-only" wire:model.live="form.stock_availability" value="{{ $stock['value'] }}" name="partner-stock" />
                            {{ $stock['label'] }}
                        </label>
                    @endforeach
                </div>
                @error('form.stock_availability')<span class="vestra-staff-form__error">{{ $message }}</span>@enderror
            </div>
        </section>

        <section class="vestra-card vestra-staff-form__card">
            <h2 class="vestra-staff-form__card-title">Listing Status</h2>
            <p class="vestra-partner-edit__hint">Suspended partners are hidden from the public Where to Buy directory.</p>
            <div class="vestra-staff-form__field">
                <label for="partner-status" class="vestra-staff-form__label">Status</label>
                <select id="partner-status" wire:model="form.status" class="vestra-staff-form__select">
                    @foreach ($statusOptions as $status)
                        <option value="{{ $status['value'] }}">{{ $status['label'] }}</option>
                    @endforeach
                </select>
            </div>
        </section>
    </form>
</div>

@props([
    'show' => false,
    'editingProductId' => null,
    'formOptions' => [],
    'form' => [],
    'existingImages' => [],
    'pendingMediaAssets' => [],
])

@php
    $isEditing = $editingProductId !== null;
    $title = $isEditing ? 'Edit Product' : 'Add Product';
    $subtitle = $isEditing ? 'Update the product details below.' : 'Add a new product to your catalog.';
    $submitLabel = $isEditing ? 'Update Product' : 'Create Product';
    $categories = $formOptions['categories'] ?? [];
    $statuses = $formOptions['statuses'] ?? [];
    $stockStatuses = $formOptions['stock_statuses'] ?? [];
    $currencies = $formOptions['currencies'] ?? [];
    $units = $formOptions['units'] ?? [];
    $unitValues = collect($units)->pluck('value')->all();
    $currentUnit = $form['unit'] ?? '';
@endphp

<div
    class="vestra-products-modal @if ($show) vestra-products-modal--open @endif"
    x-data="{ open: @entangle('showFormModal') }"
    x-show="open"
    x-cloak
    @keydown.escape.window="if (open) $wire.closeFormModal()"
    role="dialog"
    aria-modal="true"
    aria-labelledby="product-form-title"
>
    <div class="vestra-products-modal__overlay" wire:click="closeFormModal"></div>

    <div class="vestra-products-modal__panel" x-show="open" x-transition.opacity>
        <div class="vestra-products-modal__header">
            <div>
                <h2 id="product-form-title" class="vestra-products-modal__title">{{ $title }}</h2>
                <p class="vestra-products-modal__subtitle">{{ $subtitle }}</p>
            </div>
            <button type="button" wire:click="closeFormModal" class="vestra-products-modal__close" aria-label="Close">
                <x-filament::icon icon="heroicon-o-x-mark" class="h-5 w-5" />
            </button>
        </div>

        <form wire:submit.prevent="saveProduct" class="vestra-products-modal__body">
            <div class="vestra-products-form__row vestra-products-form__row--2">
                <div class="vestra-products-form__field">
                    <label for="product-name" class="vestra-products-form__label">Product Name <span class="vestra-products-form__required">*</span></label>
                    <input id="product-name" type="text" wire:model="form.name" class="vestra-products-form__input @error('form.name') vestra-products-form__input--error @enderror" placeholder="Enter product name" />
                    @error('form.name')<span class="vestra-products-form__error">{{ $message }}</span>@enderror
                </div>
                <div class="vestra-products-form__field">
                    <label for="product-sku" class="vestra-products-form__label">SKU <span class="vestra-products-form__required">*</span></label>
                    <input id="product-sku" type="text" wire:model="form.sku" class="vestra-products-form__input @error('form.sku') vestra-products-form__input--error @enderror" placeholder="Enter SKU (e.g., PROD-001)" />
                    @error('form.sku')<span class="vestra-products-form__error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="vestra-products-form__field">
                <label for="product-short" class="vestra-products-form__label">Short Description</label>
                <input id="product-short" type="text" wire:model="form.short_description" class="vestra-products-form__input" placeholder="Brief description of the product" />
                @error('form.short_description')<span class="vestra-products-form__error">{{ $message }}</span>@enderror
            </div>

            <div class="vestra-products-form__field">
                <label for="product-description" class="vestra-products-form__label">Full Description</label>
                <textarea id="product-description" wire:model="form.description" rows="4" class="vestra-products-form__textarea" placeholder="Detailed description of the product..."></textarea>
                @error('form.description')<span class="vestra-products-form__error">{{ $message }}</span>@enderror
            </div>

            <div class="vestra-products-form__row vestra-products-form__row--3">
                <div class="vestra-products-form__field">
                    <label for="product-category" class="vestra-products-form__label">Category <span class="vestra-products-form__required">*</span></label>
                    <select id="product-category" wire:model="form.category_id" class="vestra-products-form__select @error('form.category_id') vestra-products-form__input--error @enderror">
                        <option value="">Select category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                        @endforeach
                    </select>
                    @error('form.category_id')<span class="vestra-products-form__error">{{ $message }}</span>@enderror
                </div>
                <div class="vestra-products-form__field">
                    <label for="product-price" class="vestra-products-form__label">Price <span class="vestra-products-form__required">*</span></label>
                    <div class="vestra-products-form__affix">
                        <input id="product-price" type="number" step="0.01" min="0" wire:model="form.price" class="vestra-products-form__input @error('form.price') vestra-products-form__input--error @enderror" placeholder="Enter price" />
                        <select wire:model="form.currency" class="vestra-products-form__affix-select" aria-label="Price currency">
                            @forelse ($currencies as $currency)
                                <option value="{{ $currency['value'] }}">{{ $currency['label'] }}</option>
                            @empty
                                <option value="">—</option>
                            @endforelse
                        </select>
                    </div>
                    @error('form.price')<span class="vestra-products-form__error">{{ $message }}</span>@enderror
                </div>
                <div class="vestra-products-form__field">
                    <label for="product-cost" class="vestra-products-form__label">Cost Price</label>
                    <div class="vestra-products-form__affix">
                        <input id="product-cost" type="number" step="0.01" min="0" wire:model="form.cost_price" class="vestra-products-form__input" placeholder="Enter cost price" />
                        <select wire:model="form.cost_currency" class="vestra-products-form__affix-select" aria-label="Cost currency">
                            @forelse ($currencies as $currency)
                                <option value="{{ $currency['value'] }}">{{ $currency['label'] }}</option>
                            @empty
                                <option value="">—</option>
                            @endforelse
                        </select>
                    </div>
                    @error('form.cost_price')<span class="vestra-products-form__error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="vestra-products-form__row vestra-products-form__row--3">
                <div class="vestra-products-form__field">
                    <label for="product-stock" class="vestra-products-form__label">Stock Quantity <span class="vestra-products-form__required">*</span></label>
                    <input id="product-stock" type="number" min="0" step="1" wire:model="form.stock_quantity" class="vestra-products-form__input @error('form.stock_quantity') vestra-products-form__input--error @enderror" placeholder="Enter stock quantity" />
                    @error('form.stock_quantity')<span class="vestra-products-form__error">{{ $message }}</span>@enderror
                </div>
                <div class="vestra-products-form__field">
                    <label for="product-threshold" class="vestra-products-form__label">Low Stock Threshold <span class="vestra-products-form__required">*</span></label>
                    <input id="product-threshold" type="number" min="0" step="1" wire:model="form.low_stock_threshold" class="vestra-products-form__input @error('form.low_stock_threshold') vestra-products-form__input--error @enderror" placeholder="Enter low stock alert level" />
                    @error('form.low_stock_threshold')<span class="vestra-products-form__error">{{ $message }}</span>@enderror
                </div>
                <div class="vestra-products-form__field">
                    <label for="product-stock-status" class="vestra-products-form__label">Stock Status <span class="vestra-products-form__required">*</span></label>
                    <select id="product-stock-status" wire:model="form.stock_status" class="vestra-products-form__select @error('form.stock_status') vestra-products-form__input--error @enderror">
                        <option value="">Select stock status</option>
                        @foreach ($stockStatuses as $stockStatus)
                            <option value="{{ $stockStatus['value'] }}">{{ $stockStatus['label'] }}</option>
                        @endforeach
                    </select>
                    @error('form.stock_status')<span class="vestra-products-form__error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="vestra-products-form__row vestra-products-form__row--3">
            <div class="vestra-products-form__field">
                <label for="product-unit" class="vestra-products-form__label">Unit</label>
                <select id="product-unit" wire:model="form.unit" class="vestra-products-form__select">
                    <option value="">Select unit</option>
                    @if (filled($currentUnit) && ! in_array($currentUnit, $unitValues, true))
                        <option value="{{ $currentUnit }}">{{ $currentUnit }}</option>
                    @endif
                    @foreach ($units as $unit)
                        <option value="{{ $unit['value'] }}">{{ $unit['label'] }}</option>
                    @endforeach
                </select>
                @error('form.unit')<span class="vestra-products-form__error">{{ $message }}</span>@enderror
            </div>
                <div class="vestra-products-form__field">
                    <label for="product-weight" class="vestra-products-form__label">Weight (kg)</label>
                    <input id="product-weight" type="number" step="0.001" min="0" wire:model="form.weight" class="vestra-products-form__input" placeholder="Enter weight (e.g., 0.50)" />
                    @error('form.weight')<span class="vestra-products-form__error">{{ $message }}</span>@enderror
                </div>
                <div class="vestra-products-form__field">
                    <label for="product-barcode" class="vestra-products-form__label">Barcode</label>
                    <input id="product-barcode" type="text" wire:model="form.barcode" class="vestra-products-form__input" placeholder="Enter barcode (optional)" />
                    @error('form.barcode')<span class="vestra-products-form__error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="vestra-products-form__row vestra-products-form__row--3">
                <div class="vestra-products-form__field">
                    <span class="vestra-products-form__label">Featured</span>
                    <label class="vestra-products-form__toggle">
                        <input type="checkbox" wire:model="form.featured" class="vestra-products-form__toggle-input" />
                        <span class="vestra-products-form__toggle-track" aria-hidden="true"></span>
                        <span class="vestra-products-form__toggle-text">Mark as featured product</span>
                    </label>
                </div>
                <div class="vestra-products-form__field">
                    <label for="product-status" class="vestra-products-form__label">Status <span class="vestra-products-form__required">*</span></label>
                    <select id="product-status" wire:model="form.status" class="vestra-products-form__select @error('form.status') vestra-products-form__input--error @enderror">
                        @foreach ($statuses as $status)
                            <option value="{{ $status['value'] }}">{{ $status['label'] }}</option>
                        @endforeach
                    </select>
                    @error('form.status')<span class="vestra-products-form__error">{{ $message }}</span>@enderror
                </div>
                <div class="vestra-products-form__field">
                    <label for="product-tax" class="vestra-products-form__label">Tax Rate (%)</label>
                    <div class="vestra-products-form__affix">
                        <input id="product-tax" type="number" step="0.01" min="0" max="100" wire:model="form.tax_rate" class="vestra-products-form__input" placeholder="Enter tax rate" />
                        <span class="vestra-products-form__affix-static">%</span>
                    </div>
                    @error('form.tax_rate')<span class="vestra-products-form__error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="vestra-products-form__field">
                <span class="vestra-products-form__label">Product Images</span>

                <div class="vestra-products-form__image-row">
                    @foreach ($existingImages as $image)
                        <div class="vestra-products-form__thumb" wire:key="existing-image-{{ $image['id'] }}">
                            <img src="{{ $image['url'] }}" alt="{{ $image['alt_text'] ?? 'Product image' }}" />
                            @if ($isEditing)
                                <button type="button" wire:click="removeProductImage({{ $image['id'] }})" class="vestra-products-form__thumb-remove" aria-label="Remove image">
                                    <x-filament::icon icon="heroicon-o-x-mark" class="h-3.5 w-3.5" />
                                </button>
                            @endif
                        </div>
                    @endforeach

                    @foreach ($pendingMediaAssets as $index => $pending)
                        <div class="vestra-products-form__thumb" wire:key="pending-media-{{ $pending['id'] }}">
                            @if (! empty($pending['url']))
                                <img src="{{ $pending['url'] }}" alt="Pending media" />
                            @endif
                            <button type="button" wire:click="removePendingMedia({{ $index }})" class="vestra-products-form__thumb-remove" aria-label="Remove pending image">
                                <x-filament::icon icon="heroicon-o-x-mark" class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    @endforeach

                    <button type="button" wire:click="openMediaPicker" class="vestra-products-form__add-image">
                        <x-filament::icon icon="heroicon-o-plus" class="h-5 w-5" />
                        <span>Add Image</span>
                    </button>
                </div>
                <p class="vestra-products-form__dropzone-hint">Choose an existing Media Library asset or upload a new one. Files are stored once and reused.</p>
            </div>

            <div class="vestra-products-modal__footer">
                <button type="button" wire:click="closeFormModal" class="vestra-button vestra-button--secondary">Cancel</button>
                <button type="submit" class="vestra-button vestra-button--primary">
                    <x-filament::icon icon="heroicon-o-check" class="h-4 w-4" />
                    <span>{{ $submitLabel }}</span>
                </button>
            </div>
        </form>
    </div>
</div>

@props([
    'show' => false,
    'editingCategoryId' => null,
    'formOptions' => [],
    'canDelete' => false,
])

@php
    $isEditing = $editingCategoryId !== null;
    $title = $isEditing ? 'Edit Category' : 'Add Category';
    $subtitle = $isEditing
        ? 'Update the category details below.'
        : 'Create a new product category to organize your catalog and make it visible on the public website.';
    $submitLabel = $isEditing ? 'Update Category' : 'Create Category';
    $parents = $formOptions['parents'] ?? [];
    $statuses = $formOptions['statuses'] ?? [];
@endphp

<div
    class="vestra-categories-modal"
    x-data="{ open: @entangle('showFormModal') }"
    x-show="open"
    x-cloak
    @keydown.escape.window="if (open) $wire.closeFormModal()"
    role="dialog"
    aria-modal="true"
    aria-labelledby="category-form-title"
>
    <div class="vestra-categories-modal__overlay" wire:click="closeFormModal"></div>

    <div class="vestra-categories-modal__panel" x-show="open" x-transition.opacity>
        <div class="vestra-categories-modal__header">
            <div>
                <h2 id="category-form-title" class="vestra-categories-modal__title">{{ $title }}</h2>
                <p class="vestra-categories-modal__subtitle">{{ $subtitle }}</p>
            </div>
            <button type="button" wire:click="closeFormModal" class="vestra-categories-modal__close" aria-label="Close">
                <x-filament::icon icon="heroicon-o-x-mark" class="h-5 w-5" />
            </button>
        </div>

        <form wire:submit.prevent="saveCategory" class="vestra-categories-modal__body">
            <div class="vestra-categories-form__row vestra-categories-form__row--2">
                <div class="vestra-categories-form__field">
                    <label for="category-name" class="vestra-categories-form__label">Category Name <span class="vestra-categories-form__required">*</span></label>
                    <input id="category-name" type="text" wire:model.live.debounce.300ms="form.name" class="vestra-categories-form__input @error('form.name') vestra-categories-form__input--error @enderror" placeholder="Enter category name" />
                    <span class="vestra-categories-form__hint">This name will be visible to customers on the website.</span>
                    @error('form.name')<span class="vestra-categories-form__error">{{ $message }}</span>@enderror
                </div>
                <div class="vestra-categories-form__field">
                    <label for="category-slug" class="vestra-categories-form__label">Slug <span class="vestra-categories-form__required">*</span></label>
                    <input id="category-slug" type="text" wire:model="form.slug" class="vestra-categories-form__input @error('form.slug') vestra-categories-form__input--error @enderror" placeholder="Enter slug (e.g. fabric-care)" />
                    <span class="vestra-categories-form__hint">Used in URLs. Use lowercase letters, numbers and hyphens.</span>
                    @error('form.slug')<span class="vestra-categories-form__error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="vestra-categories-form__field">
                <label for="category-description" class="vestra-categories-form__label">Description</label>
                <textarea id="category-description" wire:model="form.description" rows="3" class="vestra-categories-form__textarea" placeholder="Enter category description (optional)"></textarea>
                <span class="vestra-categories-form__hint">Briefly describe this category. This may be shown on the website.</span>
                @error('form.description')<span class="vestra-categories-form__error">{{ $message }}</span>@enderror
            </div>

            <div class="vestra-categories-form__row vestra-categories-form__row--2">
                <div class="vestra-categories-form__field">
                    <label for="category-parent" class="vestra-categories-form__label">Parent Category</label>
                    <select id="category-parent" wire:model="form.parent_id" class="vestra-categories-form__select @error('form.parent_id') vestra-categories-form__input--error @enderror">
                        <option value="">None (Top Level Category)</option>
                        @foreach ($parents as $parent)
                            <option value="{{ $parent['id'] }}">{{ $parent['name'] }}</option>
                        @endforeach
                    </select>
                    <span class="vestra-categories-form__hint">Choose a parent category to create a subcategory.</span>
                    @error('form.parent_id')<span class="vestra-categories-form__error">{{ $message }}</span>@enderror
                </div>
                <div class="vestra-categories-form__field">
                    <label for="category-sort" class="vestra-categories-form__label">Sort Order</label>
                    <input id="category-sort" type="number" min="0" step="1" wire:model="form.sort_order" class="vestra-categories-form__input @error('form.sort_order') vestra-categories-form__input--error @enderror" />
                    <span class="vestra-categories-form__hint">Lower numbers appear first in listings and menus.</span>
                    @error('form.sort_order')<span class="vestra-categories-form__error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="vestra-categories-form__field">
                <label for="category-status" class="vestra-categories-form__label">Status <span class="vestra-categories-form__required">*</span></label>
                <select id="category-status" wire:model="form.status" class="vestra-categories-form__select @error('form.status') vestra-categories-form__input--error @enderror">
                    @foreach ($statuses as $status)
                        <option value="{{ $status['value'] }}">{{ $status['label'] }}</option>
                    @endforeach
                </select>
                <span class="vestra-categories-form__hint">Inactive categories won't be visible on the public website.</span>
                @error('form.status')<span class="vestra-categories-form__error">{{ $message }}</span>@enderror
            </div>

            <div class="vestra-categories-form__info" role="note">
                <x-filament::icon icon="heroicon-o-information-circle" class="h-5 w-5" />
                <div>
                    <strong>Public Website Visibility</strong>
                    <p>Active categories will automatically appear on the public website in the product catalog navigation and category pages.</p>
                </div>
            </div>

            <div class="vestra-categories-modal__footer @if ($isEditing && $canDelete) vestra-categories-modal__footer--split @endif">
                @if ($isEditing && $canDelete)
                    <button
                        type="button"
                        wire:click="deleteCategory"
                        wire:confirm="Delete this category permanently? This cannot be undone."
                        class="vestra-button vestra-button--danger-outline"
                    >
                        <x-filament::icon icon="heroicon-o-trash" class="h-4 w-4" />
                        <span>Delete Category</span>
                    </button>
                @else
                    <span></span>
                @endif

                <div class="vestra-categories-modal__footer-actions">
                    <button type="button" wire:click="closeFormModal" class="vestra-button vestra-button--secondary">Cancel</button>
                    <button type="submit" class="vestra-button vestra-button--primary" wire:loading.attr="disabled">
                        @if ($isEditing)
                            <x-filament::icon icon="heroicon-o-check" class="h-4 w-4" />
                        @else
                            <x-filament::icon icon="heroicon-o-plus" class="h-4 w-4" />
                        @endif
                        <span>{{ $submitLabel }}</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

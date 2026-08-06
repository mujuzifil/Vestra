@php
$options = $this->formOptions;
$tree = $this->permissionTree;
$isEditing = $this->isEditing;
$backUrl = $this->cancelUrl;
$statuses = $options['statuses'] ?? [];
@endphp

<div class="vestra-workspace vestra-role-form">
    <section class="vestra-role-form__hero">
        <div class="vestra-role-form__hero-main">
            <nav class="vestra-role-form__breadcrumb" aria-label="Breadcrumb">
                <a href="{{ $backUrl }}">Roles</a>
                <span>/</span>
                <span>{{ $isEditing ? 'Edit Role' : 'New Role' }}</span>
            </nav>
            <h1 class="vestra-workspace__title">{{ $isEditing ? 'Edit Role' : 'New Role' }}</h1>
            <p class="vestra-workspace__welcome">
                {{ $isEditing
                    ? 'Update this role and the permissions granted to assigned users.'
                    : 'Create a new system role and define the permissions that users assigned to this role will have.' }}
            </p>
        </div>

        <div class="vestra-role-form__hero-actions">
            <a href="{{ $backUrl }}" class="vestra-button vestra-button--secondary">Cancel</a>
            <button type="submit" form="role-form" class="vestra-button vestra-button--primary" wire:loading.attr="disabled">
                {{ $isEditing ? 'Save Role' : 'Create Role' }}
            </button>
        </div>
    </section>

    <form id="role-form" wire:submit.prevent="save" class="vestra-role-form__layout">
        <div class="vestra-role-form__top-grid">
            <section class="vestra-card vestra-role-form__card">
                <h2 class="vestra-role-form__card-title">Role Information</h2>

                <div class="vestra-role-form__field">
                    <label for="role-name" class="vestra-role-form__label">Role Name <span class="vestra-role-form__required">*</span></label>
                    <input id="role-name" type="text" wire:model.live.debounce.200ms="form.name" class="vestra-role-form__input @error('form.name') vestra-role-form__input--error @enderror" placeholder="Enter role name" />
                    <span class="vestra-role-form__hint">This will be the display name for the role.</span>
                    @error('form.name')<span class="vestra-role-form__error">{{ $message }}</span>@enderror
                </div>

                <div class="vestra-role-form__field">
                    <label for="role-slug" class="vestra-role-form__label">Role Slug <span class="vestra-role-form__required">*</span></label>
                    <input id="role-slug" type="text" wire:model.live.debounce.200ms="form.slug" class="vestra-role-form__input @error('form.slug') vestra-role-form__input--error @enderror" placeholder="Enter role slug (e.g. product-manager)" />
                    <span class="vestra-role-form__hint">Used in URLs and APIs. Use lowercase letters, numbers and hyphens.</span>
                    @error('form.slug')<span class="vestra-role-form__error">{{ $message }}</span>@enderror
                </div>

                <div class="vestra-role-form__field">
                    <label for="role-description" class="vestra-role-form__label">Description</label>
                    <textarea id="role-description" rows="4" wire:model="form.description" class="vestra-role-form__textarea" placeholder="Enter role description"></textarea>
                    <span class="vestra-role-form__hint">Describe the purpose and access level of this role.</span>
                    @error('form.description')<span class="vestra-role-form__error">{{ $message }}</span>@enderror
                </div>
            </section>

            <section class="vestra-card vestra-role-form__card">
                <h2 class="vestra-role-form__card-title">Status &amp; Notes</h2>

                <div class="vestra-role-form__field">
                    <span class="vestra-role-form__label">Status <span class="vestra-role-form__required">*</span></span>
                    <div class="vestra-role-form__status-options">
                        @foreach ($statuses as $status)
                            <label class="vestra-role-form__status-option @if (($form['status'] ?? '') === $status['value']) vestra-role-form__status-option--active @endif">
                                <input type="radio" wire:model="form.status" value="{{ $status['value'] }}" />
                                <span>
                                    <strong>{{ $status['label'] }}</strong>
                                    <small>{{ $status['value'] === 'active' ? 'Users with this role can sign in and use granted permissions.' : 'Disabled roles cannot be newly assigned and block access.' }}</small>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('form.status')<span class="vestra-role-form__error">{{ $message }}</span>@enderror
                </div>

                <div class="vestra-role-form__field">
                    <label for="role-notes" class="vestra-role-form__label">Notes</label>
                    <textarea id="role-notes" rows="6" wire:model="form.notes" class="vestra-role-form__textarea" placeholder="Administrative notes (optional)"></textarea>
                    <span class="vestra-role-form__hint">Visible only to administrators.</span>
                    @error('form.notes')<span class="vestra-role-form__error">{{ $message }}</span>@enderror
                </div>
            </section>
        </div>

        <section class="vestra-card vestra-role-form__card vestra-role-form__permissions">
            <div class="vestra-role-form__permissions-header">
                <div>
                    <h2 class="vestra-role-form__card-title">Permissions</h2>
                    <p class="vestra-role-form__hint">Select the modules and actions this role can access. Permissions are discovered from the live application.</p>
                </div>
                <div class="vestra-role-form__permissions-tools">
                    <div class="vestra-role-form__search">
                        <x-filament::icon icon="heroicon-o-magnifying-glass" class="h-4 w-4 vestra-role-form__search-icon" />
                        <input type="search" wire:model.live.debounce.250ms="permissionSearch" class="vestra-role-form__input vestra-role-form__search-input" placeholder="Search permissions..." />
                    </div>
                    <button type="button" class="vestra-role-form__link-btn" wire:click="expandAll">Expand All</button>
                    <button type="button" class="vestra-role-form__link-btn" wire:click="collapseAll">Collapse All</button>
                </div>
            </div>

            <div class="vestra-role-form__permission-layout">
                <div class="vestra-role-form__module-list" role="tablist">
                    @foreach ($tree as $group)
                        @php
                            $names = collect($group['permissions'])->pluck('name')->all();
                            $selectedCount = collect($names)->filter(fn ($n) => in_array($n, $selectedPermissions, true))->count();
                            $expanded = ($expandedGroups[$group['key']] ?? false) || filled($permissionSearch);
                        @endphp
                        <button
                            type="button"
                            class="vestra-role-form__module-item @if ($expanded) vestra-role-form__module-item--active @endif"
                            wire:click="toggleGroup(@js($group['key']))"
                            wire:key="module-{{ $group['key'] }}"
                        >
                            <span>{{ $group['label'] }}</span>
                            <span class="vestra-role-form__module-count">{{ $selectedCount }}/{{ count($names) }}</span>
                        </button>
                    @endforeach
                </div>

                <div class="vestra-role-form__permission-panels">
                    @forelse ($tree as $group)
                        @php
                            $names = collect($group['permissions'])->pluck('name')->all();
                            $selectedCount = collect($names)->filter(fn ($n) => in_array($n, $selectedPermissions, true))->count();
                            $allChecked = $selectedCount > 0 && $selectedCount === count($names);
                            $expanded = ($expandedGroups[$group['key']] ?? false) || filled($permissionSearch);
                        @endphp
                        @if ($expanded)
                            <div class="vestra-role-form__perm-panel" wire:key="panel-{{ $group['key'] }}">
                                <div class="vestra-role-form__perm-panel-head">
                                    <div>
                                        <h3>{{ $group['label'] }}</h3>
                                        <p class="vestra-role-form__hint">{{ $group['description'] ?? '' }}</p>
                                    </div>
                                    <label class="vestra-role-form__perm-check">
                                        <input type="checkbox" @checked($allChecked) wire:click.prevent="toggleGroupPermissions(@js($group['key']), @js($names))" />
                                        <span>Select Module</span>
                                    </label>
                                </div>
                                <ul class="vestra-role-form__perm-grid">
                                    @foreach ($group['permissions'] as $permission)
                                        <li>
                                            <label class="vestra-role-form__perm-card">
                                                <input type="checkbox" @checked(in_array($permission['name'], $selectedPermissions, true)) wire:click.prevent="togglePermission(@js($permission['name']))" />
                                                <span>
                                                    <strong>{{ $permission['label'] }}</strong>
                                                    <small>{{ $permission['description'] ?? $permission['name'] }}</small>
                                                </span>
                                            </label>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    @empty
                        <p class="vestra-role-form__hint">No permissions match your search.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </form>
</div>

@php
$options = $this->formOptions;
$tree = $this->permissionTree;
$isEditing = $this->isEditing;
$backUrl = $this->cancelUrl;
$roles = $options['roles'] ?? [];
$statuses = $options['statuses'] ?? [];
$departments = $options['departments'] ?? [];
$existingAvatar = $this->existingAvatarUrl;
$notesLength = mb_strlen((string) ($form['notes'] ?? ''));
@endphp

<div class="vestra-workspace vestra-staff-form">
    <section class="vestra-staff-form__hero">
        <div class="vestra-staff-form__hero-main">
            <nav class="vestra-staff-form__breadcrumb" aria-label="Breadcrumb">
                <a href="{{ $backUrl }}">Staff</a>
                <span>/</span>
                <span>{{ $isEditing ? 'Edit Staff' : 'Create New Staff' }}</span>
            </nav>
            <h1 class="vestra-workspace__title">{{ $isEditing ? 'Edit Staff' : 'Create New Staff' }}</h1>
            <p class="vestra-workspace__welcome">
                {{ $isEditing
                    ? 'Update staff profile, role, and access permissions.'
                    : 'Add a new staff member and define their role and access permissions.' }}
            </p>
        </div>

        <div class="vestra-staff-form__hero-actions">
            <a href="{{ $backUrl }}" class="vestra-button vestra-button--secondary">Cancel</a>
            <button type="submit" form="staff-form" class="vestra-button vestra-button--primary" wire:loading.attr="disabled">
                {{ $isEditing ? 'Save Staff' : 'Create Staff' }}
            </button>
        </div>
    </section>

    <form id="staff-form" wire:submit.prevent="save" class="vestra-staff-form__grid">
        <section class="vestra-card vestra-staff-form__card">
            <h2 class="vestra-staff-form__card-title">Personal Information</h2>

            <div class="vestra-staff-form__field">
                <label for="staff-name" class="vestra-staff-form__label">Full Name <span class="vestra-staff-form__required">*</span></label>
                <input id="staff-name" type="text" wire:model="form.name" class="vestra-staff-form__input @error('form.name') vestra-staff-form__input--error @enderror" placeholder="Enter full name" />
                @error('form.name')<span class="vestra-staff-form__error">{{ $message }}</span>@enderror
            </div>

            <div class="vestra-staff-form__field">
                <label for="staff-email" class="vestra-staff-form__label">Email Address <span class="vestra-staff-form__required">*</span></label>
                <input id="staff-email" type="email" wire:model="form.email" class="vestra-staff-form__input @error('form.email') vestra-staff-form__input--error @enderror" placeholder="Enter email address" autocomplete="off" />
                @error('form.email')<span class="vestra-staff-form__error">{{ $message }}</span>@enderror
            </div>

            <div class="vestra-staff-form__field">
                <label for="staff-phone" class="vestra-staff-form__label">Phone Number</label>
                <input id="staff-phone" type="tel" wire:model="form.phone" class="vestra-staff-form__input @error('form.phone') vestra-staff-form__input--error @enderror" placeholder="+254 700 000 000" />
                @error('form.phone')<span class="vestra-staff-form__error">{{ $message }}</span>@enderror
            </div>

            <div class="vestra-staff-form__field">
                <span class="vestra-staff-form__label">Profile Photo</span>
                <label class="vestra-staff-form__upload" for="staff-avatar">
                    <x-filament::icon icon="heroicon-o-cloud-arrow-up" class="h-6 w-6" />
                    <span>Click to upload or drag and drop</span>
                    <span class="vestra-staff-form__upload-hint">PNG, JPG or WEBP (Max 2MB)</span>
                    <input id="staff-avatar" type="file" wire:model="avatar" accept="image/png,image/jpeg,image/webp" class="sr-only" />
                </label>
                @if ($avatar)
                    <p class="vestra-staff-form__hint">New photo selected.</p>
                @elseif ($existingAvatar)
                    <div class="vestra-staff-form__avatar-preview">
                        <img src="{{ $existingAvatar }}" alt="" />
                        <button type="button" wire:click="$set('removeAvatar', true)" class="vestra-staff-form__link-btn">Remove</button>
                    </div>
                @endif
                @error('avatar')<span class="vestra-staff-form__error">{{ $message }}</span>@enderror
            </div>
        </section>

        <section class="vestra-card vestra-staff-form__card">
            <h2 class="vestra-staff-form__card-title">Account Information</h2>

            <div class="vestra-staff-form__field">
                <label for="staff-username" class="vestra-staff-form__label">Username</label>
                <input id="staff-username" type="text" wire:model="form.username" class="vestra-staff-form__input @error('form.username') vestra-staff-form__input--error @enderror" placeholder="Enter username" autocomplete="off" />
                @error('form.username')<span class="vestra-staff-form__error">{{ $message }}</span>@enderror
            </div>

            <div class="vestra-staff-form__field">
                <label for="staff-password" class="vestra-staff-form__label">
                    {{ $isEditing ? 'New Password' : 'Temporary Password' }}
                </label>
                <div class="vestra-staff-form__password">
                    <input
                        id="staff-password"
                        type="{{ $showPassword ? 'text' : 'password' }}"
                        wire:model="form.password"
                        class="vestra-staff-form__input @error('form.password') vestra-staff-form__input--error @enderror"
                        placeholder="{{ $isEditing ? 'Leave blank to keep current' : 'Leave blank to auto-generate' }}"
                        autocomplete="new-password"
                    />
                    <button type="button" class="vestra-staff-form__password-toggle" wire:click="$toggle('showPassword')" aria-label="Toggle password visibility">
                        <x-filament::icon :icon="$showPassword ? 'heroicon-o-eye-slash' : 'heroicon-o-eye'" class="h-5 w-5" />
                    </button>
                </div>
                <span class="vestra-staff-form__hint">Min 12 characters with upper, lower, number and symbol. First login requires a change.</span>
                @error('form.password')<span class="vestra-staff-form__error">{{ $message }}</span>@enderror
            </div>

            <div class="vestra-staff-form__field">
                <label for="staff-password-confirmation" class="vestra-staff-form__label">Confirm Password</label>
                <div class="vestra-staff-form__password">
                    <input
                        id="staff-password-confirmation"
                        type="{{ $showPasswordConfirmation ? 'text' : 'password' }}"
                        wire:model="form.password_confirmation"
                        class="vestra-staff-form__input"
                        placeholder="Confirm password"
                        autocomplete="new-password"
                    />
                    <button type="button" class="vestra-staff-form__password-toggle" wire:click="$toggle('showPasswordConfirmation')" aria-label="Toggle confirmation visibility">
                        <x-filament::icon :icon="$showPasswordConfirmation ? 'heroicon-o-eye-slash' : 'heroicon-o-eye'" class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <div class="vestra-staff-form__field">
                <label for="staff-status" class="vestra-staff-form__label">Status <span class="vestra-staff-form__required">*</span></label>
                <select id="staff-status" wire:model="form.status" class="vestra-staff-form__select @error('form.status') vestra-staff-form__input--error @enderror">
                    @foreach ($statuses as $status)
                        <option value="{{ $status['value'] }}">{{ $status['label'] }}</option>
                    @endforeach
                </select>
                <span class="vestra-staff-form__hint">Inactive accounts will not be able to log in.</span>
                @error('form.status')<span class="vestra-staff-form__error">{{ $message }}</span>@enderror
            </div>
        </section>

        <section class="vestra-card vestra-staff-form__card">
            <h2 class="vestra-staff-form__card-title">Role &amp; Permissions</h2>

            <div class="vestra-staff-form__field">
                <label for="staff-role" class="vestra-staff-form__label">Role <span class="vestra-staff-form__required">*</span></label>
                <select id="staff-role" wire:model.live="form.role_id" class="vestra-staff-form__select @error('form.role_id') vestra-staff-form__input--error @enderror">
                    <option value="">Select role</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role['id'] }}">{{ $role['name'] }}</option>
                    @endforeach
                </select>
                <span class="vestra-staff-form__hint">Define the staff member's role in the system.</span>
                @error('form.role_id')<span class="vestra-staff-form__error">{{ $message }}</span>@enderror
            </div>

            <div class="vestra-staff-form__field">
                <label for="permission-search" class="vestra-staff-form__label">Permissions</label>
                <div class="vestra-staff-form__search">
                    <x-filament::icon icon="heroicon-o-magnifying-glass" class="h-4 w-4 vestra-staff-form__search-icon" />
                    <input id="permission-search" type="search" wire:model.live.debounce.250ms="permissionSearch" class="vestra-staff-form__input vestra-staff-form__search-input" placeholder="Search permissions..." />
                </div>
            </div>

            <div class="vestra-staff-form__permission-tree" role="tree">
                @forelse ($tree as $group)
                    @php
                        $names = collect($group['permissions'])->pluck('name')->all();
                        $selectedCount = collect($names)->filter(fn ($n) => in_array($n, $selectedPermissions, true))->count();
                        $allChecked = $selectedCount > 0 && $selectedCount === count($names);
                        $expanded = ($expandedGroups[$group['key']] ?? false) || filled($permissionSearch);
                    @endphp
                    <div class="vestra-staff-form__perm-group" wire:key="perm-group-{{ $group['key'] }}">
                        <div class="vestra-staff-form__perm-group-head">
                            <label class="vestra-staff-form__perm-check">
                                <input
                                    type="checkbox"
                                    @checked($allChecked)
                                    wire:click.prevent="toggleGroupPermissions(@js($group['key']), @js($names))"
                                />
                                <span>{{ $group['label'] }}</span>
                            </label>
                            <button type="button" class="vestra-staff-form__perm-toggle" wire:click="toggleGroup(@js($group['key']))" aria-expanded="{{ $expanded ? 'true' : 'false' }}">
                                <x-filament::icon :icon="$expanded ? 'heroicon-m-chevron-up' : 'heroicon-m-chevron-down'" class="h-4 w-4" />
                            </button>
                        </div>
                        @if ($expanded)
                            <ul class="vestra-staff-form__perm-list">
                                @foreach ($group['permissions'] as $permission)
                                    <li>
                                        <label class="vestra-staff-form__perm-check">
                                            <input
                                                type="checkbox"
                                                @checked(in_array($permission['name'], $selectedPermissions, true))
                                                wire:click.prevent="togglePermission(@js($permission['name']))"
                                            />
                                            <span>{{ $permission['label'] }}</span>
                                        </label>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @empty
                    <p class="vestra-staff-form__hint">No permissions match your search.</p>
                @endforelse
            </div>
        </section>

        <section class="vestra-card vestra-staff-form__card">
            <h2 class="vestra-staff-form__card-title">Additional Information</h2>

            <div class="vestra-staff-form__field">
                <label for="staff-department" class="vestra-staff-form__label">Department</label>
                <select id="staff-department" wire:model="form.department" class="vestra-staff-form__select">
                    <option value="">Select department</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department['value'] }}">{{ $department['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="vestra-staff-form__field">
                <label for="staff-job-title" class="vestra-staff-form__label">Job Title</label>
                <input id="staff-job-title" type="text" wire:model="form.job_title" class="vestra-staff-form__input" placeholder="Enter job title" />
            </div>

            <div class="vestra-staff-form__field">
                <label for="staff-employee-id" class="vestra-staff-form__label">Employee ID</label>
                <input id="staff-employee-id" type="text" wire:model="form.employee_id" class="vestra-staff-form__input @error('form.employee_id') vestra-staff-form__input--error @enderror" placeholder="Enter employee ID (optional)" />
                @error('form.employee_id')<span class="vestra-staff-form__error">{{ $message }}</span>@enderror
            </div>

            <div class="vestra-staff-form__field">
                <label for="staff-notes" class="vestra-staff-form__label">Notes</label>
                <textarea id="staff-notes" rows="5" maxlength="500" wire:model.live="form.notes" class="vestra-staff-form__textarea" placeholder="Enter any additional notes (optional)"></textarea>
                <div class="vestra-staff-form__counter">{{ $notesLength }}/500</div>
                @error('form.notes')<span class="vestra-staff-form__error">{{ $message }}</span>@enderror
            </div>
        </section>
    </form>

    <div class="vestra-staff-form__sticky-footer">
        <a href="{{ $backUrl }}" class="vestra-button vestra-button--secondary">Cancel</a>
        <button type="submit" form="staff-form" class="vestra-button vestra-button--primary" wire:loading.attr="disabled">
            {{ $isEditing ? 'Save Staff' : 'Create Staff' }}
        </button>
    </div>
</div>

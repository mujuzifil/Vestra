@props([
    'show' => false,
    'editingTaskId' => null,
    'assignees' => [],
    'statusOptions' => [],
    'priorityOptions' => [],
])

@php
$isEditing = $editingTaskId !== null;
$title = $isEditing ? 'Edit Task' : 'Create Task';
$submitLabel = $isEditing ? 'Save Changes' : 'Create Task';
@endphp

<div
    x-data="{ open: @entangle('showDrawer') }"
    x-show="open"
    x-cloak
    class="vestra-tasks__drawer-backdrop"
    @keydown.escape.window="open = false"
>
    <div x-show="open" x-transition.opacity class="vestra-tasks__drawer-overlay" @click="open = false"></div>

    <aside
        x-show="open"
        x-transition:enter="transform transition ease-in-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transform transition ease-in-out duration-300"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="vestra-tasks__drawer"
        role="dialog"
        aria-modal="true"
        aria-labelledby="task-drawer-title"
    >
        <div class="vestra-tasks__drawer-header">
            <div>
                <h2 id="task-drawer-title" class="vestra-tasks__drawer-title">{{ $title }}</h2>
                <p class="vestra-tasks__drawer-subtitle">
                    {{ $isEditing ? 'Update the details of this task.' : 'Add a new task to your workspace.' }}
                </p>
            </div>
            <button type="button" wire:click="closeDrawer" class="vestra-tasks__drawer-close" aria-label="Close drawer">
                <x-filament::icon icon="heroicon-o-x-mark" class="h-5 w-5" />
            </button>
        </div>

        <form wire:submit.prevent="saveTask" class="vestra-tasks__drawer-body">
            <div class="vestra-tasks__form-grid">
                <div class="vestra-tasks__form-field vestra-tasks__form-field--full">
                    <label for="task-title" class="vestra-tasks__form-label">Title <span aria-label="required">*</span></label>
                    <input
                        id="task-title"
                        type="text"
                        wire:model="form.title"
                        class="vestra-tasks__form-input @error('form.title') vestra-tasks__form-input--error @enderror"
                        placeholder="Enter task title"
                    />
                    @error('form.title')<span class="vestra-tasks__form-error">{{ $message }}</span>@enderror
                </div>

                <div class="vestra-tasks__form-field vestra-tasks__form-field--full">
                    <label for="task-description" class="vestra-tasks__form-label">Description</label>
                    <textarea
                        id="task-description"
                        wire:model="form.description"
                        rows="3"
                        class="vestra-tasks__form-textarea"
                        placeholder="Add a description..."
                    ></textarea>
                </div>

                <div class="vestra-tasks__form-field">
                    <label for="task-status" class="vestra-tasks__form-label">Status <span aria-label="required">*</span></label>
                    <select id="task-status" wire:model="form.status" class="vestra-tasks__form-select">
                        @foreach ($statusOptions as $status)
                            <option value="{{ $status->value }}">{{ $status->label() }}</option>
                        @endforeach
                    </select>
                    @error('form.status')<span class="vestra-tasks__form-error">{{ $message }}</span>@enderror
                </div>

                <div class="vestra-tasks__form-field">
                    <label for="task-priority" class="vestra-tasks__form-label">Priority <span aria-label="required">*</span></label>
                    <select id="task-priority" wire:model="form.priority" class="vestra-tasks__form-select">
                        @foreach ($priorityOptions as $priority)
                            <option value="{{ $priority->value }}">{{ $priority->label() }}</option>
                        @endforeach
                    </select>
                    @error('form.priority')<span class="vestra-tasks__form-error">{{ $message }}</span>@enderror
                </div>

                <div class="vestra-tasks__form-field">
                    <label for="task-assignee" class="vestra-tasks__form-label">Assignee</label>
                    <select id="task-assignee" wire:model="form.assignee_id" class="vestra-tasks__form-select">
                        <option value="">Unassigned</option>
                        @foreach ($assignees as $assignee)
                            <option value="{{ $assignee['id'] }}">{{ $assignee['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="vestra-tasks__form-field">
                    <label for="task-due-date" class="vestra-tasks__form-label">Due Date</label>
                    <input
                        id="task-due-date"
                        type="datetime-local"
                        wire:model="form.due_date"
                        class="vestra-tasks__form-input"
                    />
                </div>

                <div class="vestra-tasks__form-field vestra-tasks__form-field--full">
                    <label for="task-internal-notes" class="vestra-tasks__form-label">Internal Notes</label>
                    <textarea
                        id="task-internal-notes"
                        wire:model="form.internal_notes"
                        rows="3"
                        class="vestra-tasks__form-textarea"
                        placeholder="Internal notes are only visible to staff..."
                    ></textarea>
                </div>
            </div>

            <div class="vestra-tasks__drawer-footer">
                <button type="button" wire:click="closeDrawer" class="vestra-button vestra-button--secondary">Cancel</button>
                <button type="submit" class="vestra-button vestra-button--primary">
                    <x-filament::icon icon="heroicon-o-check" class="h-4 w-4" />
                    <span>{{ $submitLabel }}</span>
                </button>
            </div>
        </form>
    </aside>
</div>

@props(['task'])

@php
$status = $task->status;
$priority = $task->priority;
$assignee = $task->assignee;
$isOverdue = $task->isOverdue();
$relatedLabel = $task->displayRelatedTo();
$relatedType = $task->relatedTypeLabel();
@endphp

<tr class="vestra-tasks__row @if ($isOverdue) vestra-tasks__row--overdue @endif" wire:key="task-{{ $task->id }}">
    <td class="vestra-tasks__td vestra-tasks__td--task">
        <div class="vestra-tasks__task-primary">
            <button
                type="button"
                wire:click="completeTask({{ $task->id }})"
                class="vestra-tasks__complete-btn"
                aria-label="Mark task completed"
                title="Mark completed"
            >
                <x-filament::icon
                    icon="{{ $status === \App\Enums\TaskStatus::COMPLETED ? 'heroicon-s-check-circle' : 'heroicon-o-check-circle' }}"
                    class="h-5 w-5"
                />
            </button>
            <div class="vestra-tasks__task-info">
                <span class="vestra-tasks__task-title @if ($status === \App\Enums\TaskStatus::COMPLETED) vestra-tasks__task-title--completed @endif">
                    {{ $task->title }}
                </span>
                @if ($task->description)
                    <span class="vestra-tasks__task-description">{{ str($task->description)->limit(80) }}</span>
                @endif
            </div>
        </div>
    </td>

    <td class="vestra-tasks__td vestra-tasks__td--related">
        @if ($relatedLabel)
            <div class="vestra-tasks__related">
                @if ($relatedType)
                    <span class="vestra-tasks__related-type">{{ $relatedType }}</span>
                @endif
                <span class="vestra-tasks__related-name">{{ $relatedLabel }}</span>
            </div>
        @else
            <span class="vestra-tasks__empty-cell">—</span>
        @endif
    </td>

    <td class="vestra-tasks__td vestra-tasks__td--assignee">
        @if ($assignee)
            <div class="vestra-tasks__assignee">
                <span class="vestra-tasks__avatar">{{ $assignee->initials() }}</span>
                <span class="vestra-tasks__assignee-name">{{ $assignee->name }}</span>
            </div>
        @else
            <span class="vestra-tasks__empty-cell">Unassigned</span>
        @endif
    </td>

    <td class="vestra-tasks__td vestra-tasks__td--priority">
        <span class="vestra-tasks__badge vestra-tasks__badge--{{ $priority->color() }}">
            <x-filament::icon :icon="$priority->icon()" class="h-3.5 w-3.5" />
            {{ $priority->label() }}
        </span>
    </td>

    <td class="vestra-tasks__td vestra-tasks__td--status">
        <span class="vestra-tasks__badge vestra-tasks__badge--{{ $status->color() }}">
            <x-filament::icon :icon="$status->icon()" class="h-3.5 w-3.5" />
            {{ $status->label() }}
        </span>
    </td>

    <td class="vestra-tasks__td vestra-tasks__td--due">
        <div class="vestra-tasks__due @if ($isOverdue) vestra-tasks__due--overdue @endif">
            <x-filament::icon icon="heroicon-o-calendar" class="h-3.5 w-3.5" />
            <span>{{ $task->due_date?->format('M d, Y') ?? 'No due date' }}</span>
        </div>
    </td>

    <td class="vestra-tasks__td vestra-tasks__td--created">
        <span class="vestra-tasks__created">{{ $task->created_at->diffForHumans() }}</span>
    </td>

    <td class="vestra-tasks__td vestra-tasks__td--actions">
        <div class="vestra-tasks__actions" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" class="vestra-tasks__action-trigger" aria-label="Task actions">
                <x-filament::icon icon="heroicon-m-ellipsis-vertical" class="h-5 w-5" />
            </button>
            <div x-show="open" x-transition class="vestra-tasks__action-menu">
                <button type="button" wire:click="openEditDrawer({{ $task->id }})" class="vestra-tasks__action-item">
                    <x-filament::icon icon="heroicon-o-pencil-square" class="h-4 w-4" />
                    <span>Edit</span>
                </button>
                @if ($status !== \App\Enums\TaskStatus::COMPLETED)
                    <button type="button" wire:click="completeTask({{ $task->id }})" class="vestra-tasks__action-item">
                        <x-filament::icon icon="heroicon-o-check-circle" class="h-4 w-4" />
                        <span>Mark completed</span>
                    </button>
                @endif
                @if ($status !== \App\Enums\TaskStatus::ARCHIVED)
                    <button type="button" wire:click="archiveTask({{ $task->id }})" class="vestra-tasks__action-item">
                        <x-filament::icon icon="heroicon-o-archive-box" class="h-4 w-4" />
                        <span>Archive</span>
                    </button>
                @endif
                <button type="button" wire:click="deleteTask({{ $task->id }})" wire:confirm="Are you sure you want to delete this task?" class="vestra-tasks__action-item vestra-tasks__action-item--danger">
                    <x-filament::icon icon="heroicon-o-trash" class="h-4 w-4" />
                    <span>Delete</span>
                </button>
            </div>
        </div>
    </td>
</tr>

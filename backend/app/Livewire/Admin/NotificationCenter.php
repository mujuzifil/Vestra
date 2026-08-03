<?php

namespace App\Livewire\Admin;

use App\Enums\NotificationPriority;
use App\Enums\NotificationType;
use App\Services\Admin\NotificationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationCenter extends Component
{
    public bool $isOpen = false;

    public function toggle(): void
    {
        $this->isOpen = ! $this->isOpen;
    }

    public function close(): void
    {
        $this->isOpen = false;
    }

    public function markAllRead(): void
    {
        app(NotificationService::class)->markAllRead();
    }

    public function getUnreadCountProperty(): int
    {
        return app(NotificationService::class)->getUnreadCount();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getNotificationsProperty(): array
    {
        $user = Auth::user();

        if ($user === null) {
            return [];
        }

        return $user->notifications()
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn ($notification) => $this->formatNotification($notification))
            ->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    private function formatNotification($notification): array
    {
        $data = $notification->data ?? [];
        $type = NotificationType::tryFromString($data['type'] ?? null) ?? NotificationType::SYSTEM;
        $priority = NotificationPriority::tryFromString($data['priority'] ?? null) ?? NotificationPriority::INFORMATION;

        return [
            'id' => $notification->id,
            'type' => $type->value,
            'priority' => $priority->value,
            'title' => $data['title'] ?? $type->label(),
            'message' => $data['message'] ?? '',
            'time' => $notification->created_at?->diffForHumans() ?? '',
            'read' => $notification->read_at !== null,
            'icon' => $type->icon(),
            'action_url' => $data['action_url'] ?? null,
        ];
    }

    public function render()
    {
        return view('livewire.admin.notification-center');
    }
}

<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class NotificationsWidget extends Widget
{
    protected static string $view = 'filament.widgets.notifications';

    protected static bool $isLazy = true;

    protected int | string | array $columnSpan = ['lg' => 1];

    public function getUnreadCount(): int
    {
        $user = Auth::user();

        return $user ? $user->unreadNotifications()->count() : 0;
    }

    public function getNotifications(): array
    {
        $user = Auth::user();

        if (! $user) {
            return [];
        }

        return $user->notifications()
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn ($notification) => [
                'id' => $notification->id,
                'title' => $notification->data['title'] ?? $notification->data['subject'] ?? 'Notification',
                'body' => $notification->data['body'] ?? $notification->data['message'] ?? null,
                'read' => ! is_null($notification->read_at),
                'time' => $notification->created_at?->diffForHumans() ?? '',
                'icon' => $notification->data['icon'] ?? 'heroicon-o-bell',
            ])
            ->toArray();
    }
}

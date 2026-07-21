<?php

namespace App\Livewire\Admin;

use Livewire\Component;

class NotificationCenter extends Component
{
    public bool $isOpen = false;

    public int $unreadCount = 3;

    public array $notifications = [
        [
            'id' => 1,
            'type' => 'order',
            'priority' => 'info',
            'title' => 'New order received',
            'message' => 'Order #10042 from Clarissa Mraz',
            'time' => '2 minutes ago',
            'read' => false,
            'icon' => 'heroicon-o-shopping-cart',
        ],
        [
            'id' => 2,
            'type' => 'inventory',
            'priority' => 'warning',
            'title' => 'Low stock alert',
            'message' => 'EcoSuit Cleaner is below threshold',
            'time' => '1 hour ago',
            'read' => false,
            'icon' => 'heroicon-o-exclamation-triangle',
        ],
        [
            'id' => 3,
            'type' => 'message',
            'priority' => 'success',
            'title' => 'Feedback resolved',
            'message' => 'Customer feedback #128 marked resolved',
            'time' => '3 hours ago',
            'read' => true,
            'icon' => 'heroicon-o-check-circle',
        ],
    ];

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
        $this->notifications = array_map(function (array $notification): array {
            $notification['read'] = true;

            return $notification;
        }, $this->notifications);

        $this->unreadCount = 0;
    }

    public function render()
    {
        return view('livewire.admin.notification-center');
    }
}

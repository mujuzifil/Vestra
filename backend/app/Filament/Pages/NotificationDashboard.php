<?php

namespace App\Filament\Pages;

use App\Models\Announcement;
use App\Models\NotificationDelivery;
use App\Models\NotificationTemplate;
use App\Services\NotificationDeliveryService;
use Filament\Pages\Page;

class NotificationDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 79;

    protected static string $view = 'filament.pages.notification-dashboard';

    public function getTitle(): string
    {
        return 'Notification Center';
    }

    public function getViewData(): array
    {
        $deliveryService = app(NotificationDeliveryService::class);

        return [
            'deliveriesToday' => NotificationDelivery::whereDate('created_at', today())->count(),
            'emailsToday' => NotificationDelivery::where('channel', 'email')->whereDate('created_at', today())->count(),
            'smsToday' => NotificationDelivery::where('channel', 'sms')->whereDate('created_at', today())->count(),
            'inAppToday' => NotificationDelivery::where('channel', 'in_app')->whereDate('created_at', today())->count(),
            'statusCounts' => $deliveryService->countsByStatus(),
            'activeTemplates' => NotificationTemplate::where('is_active', true)->count(),
            'totalTemplates' => NotificationTemplate::count(),
            'activeAnnouncements' => Announcement::active()->count(),
            'recentDeliveries' => $deliveryService->recent(10),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}

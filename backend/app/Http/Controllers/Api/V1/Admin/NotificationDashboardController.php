<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\NotificationDelivery;
use App\Models\NotificationTemplate;
use App\Services\NotificationDeliveryService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class NotificationDashboardController extends Controller
{
    use RespondsWithJson;

    public function __construct(
        protected NotificationDeliveryService $deliveryService
    ) {}

    public function index(): JsonResponse
    {
        $statusCounts = $this->deliveryService->countsByStatus();

        return $this->successResponse([
            'deliveries_today' => NotificationDelivery::whereDate('created_at', today())->count(),
            'deliveries_total' => NotificationDelivery::count(),
            'status_counts' => $statusCounts,
            'emails_today' => NotificationDelivery::where('channel', 'email')->whereDate('created_at', today())->count(),
            'sms_today' => NotificationDelivery::where('channel', 'sms')->whereDate('created_at', today())->count(),
            'in_app_today' => NotificationDelivery::where('channel', 'in_app')->whereDate('created_at', today())->count(),
            'active_templates' => NotificationTemplate::where('is_active', true)->count(),
            'total_templates' => NotificationTemplate::count(),
            'active_announcements' => Announcement::active()->count(),
            'recent_deliveries' => $this->deliveryService->recent(10),
        ]);
    }
}

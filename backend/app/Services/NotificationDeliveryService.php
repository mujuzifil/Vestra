<?php

namespace App\Services;

use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use App\Models\NotificationDelivery;
use App\Models\NotificationTemplate;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class NotificationDeliveryService
{
    /**
     * Create a delivery record.
     */
    public function create(
        User $user,
        NotificationTemplate $template,
        NotificationChannel $channel,
        array $variables = [],
        array $context = []
    ): NotificationDelivery {
        return NotificationDelivery::create([
            'user_id' => $user->id,
            'notification_template_id' => $template->exists ? $template->id : null,
            'channel' => $channel->value,
            'recipient' => $this->recipientFor($user, $channel, $context),
            'subject' => $context['subject'] ?? null,
            'content' => $context['content'] ?? null,
            'variables_json' => $variables,
            'status' => NotificationStatus::PENDING->value,
            'error_message' => null,
        ]);
    }

    /**
     * Update delivery status.
     */
    public function markStatus(NotificationDelivery $delivery, NotificationStatus $status, ?string $notes = null): NotificationDelivery
    {
        $payload = [
            'status' => $status->value,
            'error_message' => $notes,
        ];

        if (in_array($status, [NotificationStatus::SENT, NotificationStatus::QUEUED, NotificationStatus::PROCESSING], true)) {
            $payload['sent_at'] = $delivery->sent_at ?? now();
        }

        $delivery->update($payload);

        return $delivery->fresh();
    }

    /**
     * Mark delivery as failed.
     */
    public function markFailed(NotificationDelivery $delivery, string $reason): NotificationDelivery
    {
        return $this->markStatus($delivery, NotificationStatus::FAILED, $reason);
    }

    /**
     * Mark delivery as delivered (e.g., email opened or webhook confirmed).
     */
    public function markDelivered(NotificationDelivery $delivery, ?string $notes = null): NotificationDelivery
    {
        $delivery->update([
            'status' => NotificationStatus::DELIVERED->value,
            'opened_at' => $delivery->opened_at ?? now(),
            'error_message' => $notes,
        ]);

        return $delivery->fresh();
    }

    /**
     * Get delivery history for a user.
     */
    public function historyFor(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return NotificationDelivery::where('user_id', $user->id)
            ->with('template')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Recent deliveries for dashboard.
     */
    public function recent(int $limit = 50): Collection
    {
        return NotificationDelivery::with(['user', 'template'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Count deliveries by status.
     */
    public function countsByStatus(): array
    {
        return NotificationDelivery::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
    }

    protected function recipientFor(User $user, NotificationChannel $channel, array $context): ?string
    {
        return match ($channel) {
            NotificationChannel::EMAIL => $user->email,
            NotificationChannel::SMS => $user->phone ?? ($context['phone'] ?? null),
            NotificationChannel::PUSH => $context['device_token'] ?? null,
            NotificationChannel::IN_APP => (string) $user->id,
        };
    }
}

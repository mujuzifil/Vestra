<?php

namespace App\Services;

use App\Enums\AnnouncementAudience;
use App\Enums\NotificationChannel;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AnnouncementService
{
    public function __construct(
        protected NotificationDispatcherService $dispatcher
    ) {}

    /**
     * Create an announcement.
     */
    public function create(array $data): Announcement
    {
        $announcement = Announcement::create([
            'title' => $data['title'],
            'body' => $data['body'],
            'target_audience' => $data['target_audience'] ?? $data['audience'] ?? AnnouncementAudience::EVERYONE->value,
            'priority' => $data['priority'] ?? 'medium',
            'start_at' => $data['start_at'] ?? $data['starts_at'] ?? now(),
            'end_at' => $data['end_at'] ?? $data['ends_at'] ?? null,
            'sent_at' => $data['sent_at'] ?? ($data['is_published'] ?? false ? now() : null),
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'pinned' => $data['pinned'] ?? $data['is_pinned'] ?? false,
            'created_by' => $data['created_by'] ?? null,
        ]);

        if ($announcement->sent_at !== null && $announcement->isActive()) {
            $this->broadcast($announcement);
        }

        return $announcement;
    }

    /**
     * Update an announcement.
     */
    public function update(Announcement $announcement, array $data): Announcement
    {
        $wasPublished = $announcement->sent_at !== null;

        $announcement->update([
            'title' => $data['title'] ?? $announcement->title,
            'body' => $data['body'] ?? $announcement->body,
            'target_audience' => $data['target_audience'] ?? $data['audience'] ?? $announcement->target_audience,
            'priority' => $data['priority'] ?? $announcement->priority,
            'start_at' => $data['start_at'] ?? $data['starts_at'] ?? $announcement->start_at,
            'end_at' => $data['end_at'] ?? $data['ends_at'] ?? $announcement->end_at,
            'sent_at' => $data['sent_at'] ?? (($data['is_published'] ?? $wasPublished) ? ($announcement->sent_at ?? now()) : null),
            'scheduled_at' => $data['scheduled_at'] ?? $announcement->scheduled_at,
            'pinned' => $data['pinned'] ?? $data['is_pinned'] ?? $announcement->pinned,
        ]);

        if (! $wasPublished && $announcement->sent_at !== null && $announcement->isActive()) {
            $this->broadcast($announcement);
        }

        return $announcement->fresh();
    }

    /**
     * Delete an announcement.
     */
    public function delete(Announcement $announcement): bool
    {
        return $announcement->delete();
    }

    /**
     * Get active announcements for a user.
     */
    public function activeFor(User $user, ?string $audience = null): Collection
    {
        $query = Announcement::active();

        if ($audience) {
            $query->forAudience($audience);
        }

        return $query->orderBy('pinned', 'desc')
            ->orderBy('start_at', 'desc')
            ->get();
    }

    /**
     * Paginate announcements for admin.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Announcement::with('creator')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Broadcast the announcement to the target audience.
     */
    public function broadcast(Announcement $announcement): void
    {
        try {
            if ($announcement->sent_at === null) {
                $announcement->update(['sent_at' => now()]);
            }

            $audience = $announcement->target_audience instanceof AnnouncementAudience
                ? $announcement->target_audience
                : AnnouncementAudience::tryFrom($announcement->target_audience) ?? AnnouncementAudience::EVERYONE;

            $users = $this->targetUsers($audience);

            foreach ($users->cursor() as $user) {
                if (! $announcement->isVisibleTo($user)) {
                    continue;
                }

                $this->dispatcher->dispatch(
                    user: $user,
                    templateKey: 'admin.announcement',
                    variables: [
                        'announcement_title' => $announcement->title,
                        'announcement_body' => strip_tags($announcement->body),
                        'audience' => $audience->label(),
                    ],
                    channels: [NotificationChannel::IN_APP, NotificationChannel::EMAIL],
                    topic: 'system_alert',
                    metadata: ['announcement_id' => $announcement->id, 'subject' => $announcement->title]
                );
            }
        } catch (\Throwable $e) {
            Log::error('Failed to broadcast announcement', [
                'announcement_id' => $announcement->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Process scheduled announcements that should become active.
     */
    public function publishScheduled(): int
    {
        $count = 0;

        Announcement::query()
            ->whereNull('sent_at')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->chunk(100, function (Collection $announcements) use (&$count) {
                foreach ($announcements as $announcement) {
                    $this->broadcast($announcement);
                    $count++;
                }
            });

        return $count;
    }

    /**
     * Resolve target users for an audience.
     */
    protected function targetUsers(AnnouncementAudience $audience)
    {
        $query = User::query();

        return match ($audience) {
            AnnouncementAudience::EVERYONE => $query,
            AnnouncementAudience::CUSTOMERS => $query->where('is_admin', false)->whereDoesntHave('distributor'),
            AnnouncementAudience::DISTRIBUTORS => $query->whereHas('distributor'),
            AnnouncementAudience::ADMINS => $query->where('is_admin', true),
        };
    }
}

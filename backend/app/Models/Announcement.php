<?php

namespace App\Models;

use App\Enums\AnnouncementAudience;
use App\Enums\AnnouncementPriority;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'target_audience',
        'priority',
        'pinned',
        'start_at',
        'end_at',
        'sent_at',
        'scheduled_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'target_audience' => AnnouncementAudience::class,
            'priority' => AnnouncementPriority::class,
            'pinned' => 'boolean',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'sent_at' => 'datetime',
            'scheduled_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query
            ->whereNotNull('sent_at')
            ->where(function ($q) {
                $q->whereNull('start_at')->orWhere('start_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_at')->orWhere('end_at', '>=', now());
            });
    }

    public function scopePinned($query)
    {
        return $query->where('pinned', true);
    }

    public function scopeForAudience($query, string $audience)
    {
        return $query->where(function ($q) use ($audience) {
            $q->where('target_audience', AnnouncementAudience::EVERYONE->value)
              ->orWhere('target_audience', $audience);
        });
    }

    public function isVisibleTo(User $user): bool
    {
        if ($this->target_audience === AnnouncementAudience::EVERYONE) {
            return true;
        }

        $audience = $this->target_audience->value;

        if ($audience === 'customers' && ! $user->isDistributor() && ! $user->isAdmin()) {
            return true;
        }

        if ($audience === 'distributors' && $user->isDistributor()) {
            return true;
        }

        if ($audience === 'admins' && $user->isAdmin()) {
            return true;
        }

        return false;
    }
}

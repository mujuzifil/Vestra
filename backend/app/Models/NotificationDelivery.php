<?php

namespace App\Models;

use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'notification_template_id',
        'user_id',
        'channel',
        'recipient',
        'subject',
        'content',
        'variables_json',
        'status',
        'error_message',
        'sent_at',
        'opened_at',
    ];

    protected function casts(): array
    {
        return [
            'channel' => NotificationChannel::class,
            'status' => NotificationStatus::class,
            'variables_json' => 'array',
            'sent_at' => 'datetime',
            'opened_at' => 'datetime',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(NotificationTemplate::class, 'notification_template_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markAsProcessing(): void
    {
        $this->update(['status' => NotificationStatus::PROCESSING]);
    }

    public function markAsSent(?string $reference = null): void
    {
        $this->update([
            'status' => NotificationStatus::SENT,
            'sent_at' => now(),
        ]);
    }

    public function markAsFailed(string $reason): void
    {
        $this->update([
            'status' => NotificationStatus::FAILED,
            'error_message' => $reason,
        ]);
    }
}

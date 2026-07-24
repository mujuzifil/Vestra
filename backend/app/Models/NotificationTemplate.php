<?php

namespace App\Models;

use App\Enums\NotificationChannel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_key',
        'name',
        'category',
        'description',
        'subject',
        'email_body',
        'sms_body',
        'in_app_body',
        'channels_json',
        'variables_json',
        'priority',
        'is_active',
        'version',
    ];

    protected function casts(): array
    {
        return [
            'channels_json' => 'array',
            'variables_json' => 'array',
            'is_active' => 'boolean',
            'version' => 'integer',
        ];
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(NotificationDelivery::class);
    }

    public function channels(): array
    {
        $channels = $this->channels_json ?? [];

        return array_map(
            fn (string $channel) => NotificationChannel::tryFrom($channel),
            $channels
        );
    }

    public function hasChannel(NotificationChannel $channel): bool
    {
        return in_array($channel->value, $this->channels_json ?? [], true);
    }

    public function variables(): array
    {
        return $this->variables_json ?? [];
    }

    public function incrementVersion(): void
    {
        $this->increment('version');
    }
}

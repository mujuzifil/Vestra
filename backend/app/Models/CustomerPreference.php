<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerPreference extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerPreferenceFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'notification_preferences',
        'account_preferences',
        'system_alerts',
        'emergency_alerts',
    ];

    protected function casts(): array
    {
        return [
            'notification_preferences' => 'array',
            'account_preferences' => 'array',
            'system_alerts' => 'boolean',
            'emergency_alerts' => 'boolean',
        ];
    }

    public function wantsEmail(): bool
    {
        return (bool) ($this->notification_preferences['email_notifications'] ?? true);
    }

    public function wantsSms(): bool
    {
        return (bool) ($this->notification_preferences['sms_notifications'] ?? true);
    }

    public function wantsPush(): bool
    {
        return (bool) ($this->notification_preferences['push_notifications'] ?? false);
    }

    public function wantsOrderUpdates(): bool
    {
        return (bool) ($this->notification_preferences['order_updates'] ?? true);
    }

    public function wantsMarketing(): bool
    {
        return (bool) ($this->notification_preferences['marketing_emails'] ?? false);
    }

    public function wantsPromotions(): bool
    {
        return (bool) ($this->notification_preferences['promotional_sms'] ?? false);
    }

    public function wantsNewsletter(): bool
    {
        return (bool) ($this->notification_preferences['newsletter'] ?? false);
    }

    public function wantsSystemAlerts(): bool
    {
        return $this->system_alerts;
    }

    public function wantsEmergencyAlerts(): bool
    {
        return $this->emergency_alerts;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

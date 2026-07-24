<?php

namespace App\Services;

use App\Enums\NotificationChannel;
use App\Models\CustomerPreference;
use App\Models\User;

class NotificationPreferenceService
{
    /**
     * Get or create the customer preference record for a user.
     */
    public function preferencesFor(User $user): CustomerPreference
    {
        return CustomerPreference::firstOrCreate(
            ['user_id' => $user->id],
            [
                'notification_preferences' => $this->defaultNotificationPreferences(),
                'account_preferences' => [],
                'system_alerts' => true,
                'emergency_alerts' => true,
            ]
        );
    }

    /**
     * Determine whether the user wants to receive a notification on a given channel/topic.
     */
    public function wantsChannel(User $user, NotificationChannel $channel, string $topic = 'order_updates'): bool
    {
        $preferences = $this->preferencesFor($user);

        // System and emergency alerts bypass marketing/topic preferences.
        if (in_array($topic, ['system_alert', 'emergency_alert'], true)) {
            return $topic === 'emergency_alert'
                ? $preferences->wantsEmergencyAlerts()
                : $preferences->wantsSystemAlerts();
        }

        $prefs = $preferences->notification_preferences ?? [];

        return match ($channel) {
            NotificationChannel::EMAIL => (bool) ($prefs['email_notifications'] ?? true),
            NotificationChannel::SMS => (bool) ($prefs['sms_notifications'] ?? true),
            NotificationChannel::PUSH => (bool) ($prefs['push_notifications'] ?? false),
            NotificationChannel::IN_APP => true,
            default => true,
        };
    }

    /**
     * Check whether a specific topic is enabled for the user.
     */
    public function wantsTopic(User $user, string $topic): bool
    {
        $prefs = $this->preferencesFor($user)->notification_preferences ?? [];

        return match ($topic) {
            'order_updates' => (bool) ($prefs['order_updates'] ?? true),
            'marketing' => (bool) ($prefs['marketing_emails'] ?? false),
            'promotions' => (bool) ($prefs['promotional_sms'] ?? false),
            'newsletter' => (bool) ($prefs['newsletter'] ?? false),
            default => true,
        };
    }

    /**
     * Update notification preferences for a user.
     */
    public function update(User $user, array $data): CustomerPreference
    {
        $preferences = $this->preferencesFor($user);

        $existing = $preferences->notification_preferences ?? [];
        $updated = array_merge($existing, $data['notification_preferences'] ?? []);

        $preferences->update([
            'notification_preferences' => $updated,
            'system_alerts' => $data['system_alerts'] ?? $preferences->system_alerts,
            'emergency_alerts' => $data['emergency_alerts'] ?? $preferences->emergency_alerts,
        ]);

        return $preferences->fresh();
    }

    /**
     * Default preference structure.
     */
    public function defaultNotificationPreferences(): array
    {
        return [
            'email_notifications' => true,
            'sms_notifications' => true,
            'push_notifications' => false,
            'order_updates' => true,
            'marketing_emails' => false,
            'promotional_sms' => false,
            'newsletter' => false,
        ];
    }
}

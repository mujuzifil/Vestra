<?php

namespace App\Services;

use App\Models\NotificationTemplate;
use Illuminate\Support\Str;

class NotificationTemplateService
{
    /**
     * Find an active template by its unique event key.
     */
    public function findByKey(string $key): ?NotificationTemplate
    {
        return NotificationTemplate::where('event_key', $key)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Resolve a template by event key. Creates a generic fallback if missing.
     */
    public function resolve(string $key, string $defaultSubject = 'VESTRA Notification'): NotificationTemplate
    {
        $template = $this->findByKey($key);

        if ($template) {
            return $template;
        }

        return $this->fallbackTemplate($key, $defaultSubject);
    }

    /**
     * Replace {{variable}} placeholders in a template body.
     */
    public function render(string $body, array $variables = []): string
    {
        return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_.]+)\s*\}\}/', function ($matches) use ($variables) {
            $key = $matches[1];

            return (string) ($this->dotGet($variables, $key) ?? $matches[0]);
        }, $body);
    }

    /**
     * Render all channels for a template.
     */
    public function renderTemplate(NotificationTemplate $template, array $variables = []): array
    {
        return [
            'subject' => $this->render($template->subject ?? 'VESTRA Notification', $variables),
            'email_body' => $this->render($template->email_body ?? '', $variables),
            'sms_body' => $this->render($template->sms_body ?? '', $variables),
            'in_app_body' => $this->render($template->in_app_body ?? '', $variables),
        ];
    }

    /**
     * Create or update a template.
     */
    public function upsert(array $data): NotificationTemplate
    {
        $data['event_key'] = $data['event_key'] ?? $data['key'] ?? Str::slug($data['name']);

        return NotificationTemplate::updateOrCreate(
            ['event_key' => $data['event_key']],
            [
                'name' => $data['name'],
                'category' => $data['category'] ?? 'system',
                'description' => $data['description'] ?? null,
                'subject' => $data['subject'] ?? 'VESTRA Notification',
                'email_body' => $data['email_body'] ?? '',
                'sms_body' => $data['sms_body'] ?? '',
                'in_app_body' => $data['in_app_body'] ?? $data['push_body'] ?? '',
                'channels_json' => $data['channels_json'] ?? $data['channels'] ?? [],
                'variables_json' => $data['variables_json'] ?? $data['variables'] ?? [],
                'priority' => $data['priority'] ?? 'normal',
                'is_active' => $data['is_active'] ?? true,
            ]
        );
    }

    /**
     * Build a transient fallback template for events without a configured template.
     */
    protected function fallbackTemplate(string $key, string $defaultSubject): NotificationTemplate
    {
        $template = new NotificationTemplate;
        $template->event_key = $key;
        $template->name = Str::title(str_replace(['.', '_'], ' ', $key));
        $template->category = 'system';
        $template->description = 'Auto-generated fallback template.';
        $template->subject = $defaultSubject;
        $template->email_body = '<p>A new notification has been generated: <strong>'.$key.'</strong></p>';
        $template->sms_body = 'VESTRA: '.$key;
        $template->in_app_body = $key;
        $template->channels_json = [];
        $template->variables_json = [];
        $template->priority = 'normal';
        $template->is_active = true;
        $template->version = 1;

        return $template;
    }

    /**
     * Dot-notation access for nested variables.
     */
    protected function dotGet(array $array, string $key): mixed
    {
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (! is_array($array) || ! array_key_exists($segment, $array)) {
                return null;
            }
            $array = $array[$segment];
        }

        return $array;
    }
}

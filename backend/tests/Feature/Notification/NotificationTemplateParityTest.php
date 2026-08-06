<?php

namespace Tests\Feature\Notification;

use Database\Seeders\NotificationTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class NotificationTemplateParityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Template keys referenced by DispatchNotificationListener must exist in the seeder
     * (or NotificationTemplateService falls back to generic copy — unacceptable for ship flows).
     *
     * @return array<int, string>
     */
    public static function listenerTemplateKeys(): array
    {
        $source = File::get(app_path('Listeners/Notification/DispatchNotificationListener.php'));

        preg_match_all("/'template'\s*=>\s*'([^']+)'/", $source, $matches);

        return array_values(array_unique($matches[1] ?? []));
    }

    public function test_dispatch_listener_template_keys_are_seeded(): void
    {
        $this->seed(NotificationTemplateSeeder::class);

        $seededKeys = \App\Models\NotificationTemplate::query()
            ->pluck('event_key')
            ->all();

        $missing = array_diff(self::listenerTemplateKeys(), $seededKeys);

        $this->assertSame(
            [],
            array_values($missing),
            'Missing notification templates for listener keys: '.implode(', ', $missing)
        );
    }

    public function test_quote_distributor_and_security_templates_define_required_variables(): void
    {
        $this->seed(NotificationTemplateSeeder::class);

        $required = [
            'quote_request.admin_notification' => ['reference_number', 'customer_name', 'company_name', 'email', 'product_summary'],
            'quote_request.customer_confirmation' => ['reference_number', 'customer_name', 'company_name'],
            'quote_request.approved' => ['reference_number', 'new_status'],
            'distributor.application_submitted' => ['customer_name', 'company_name'],
            'distributor.application_admin_notification' => ['reference_number', 'company_name', 'email'],
            'security.password_reset_requested' => ['reset_url', 'token', 'email'],
            'security.password_changed' => ['customer_name'],
            'credit.limit_updated' => ['company_name', 'credit_limit'],
            'customer.profile_updated' => ['customer_name'],
        ];

        foreach ($required as $key => $variables) {
            $template = \App\Models\NotificationTemplate::query()->where('event_key', $key)->first();
            $this->assertNotNull($template, "Template {$key} missing");

            foreach ($variables as $variable) {
                $this->assertContains(
                    $variable,
                    $template->variables_json ?? [],
                    "Template {$key} missing variable {$variable} in variables_json"
                );
            }
        }
    }
}

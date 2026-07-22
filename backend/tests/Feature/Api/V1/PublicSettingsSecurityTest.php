<?php

namespace Tests\Feature\Api\V1;

use App\Models\Setting;
use App\Enums\SettingGroup;
use App\Enums\SettingType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSettingsSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_public_settings_response_contains_no_ciphertext(): void
    {
        $response = $this->getJson('/api/v1/settings');

        $response->assertOk();

        foreach ($response->json('data') as $setting) {
            $value = (string) ($setting['value'] ?? '');
            $this->assertStringStartsNotWith(
                'eyJpdiI6',
                $value,
                "Setting [{$setting['key']}] appears to contain encrypted ciphertext."
            );
        }
    }

    public function test_public_settings_excludes_sensitive_keys(): void
    {
        $response = $this->getJson('/api/v1/settings');

        $response->assertOk();

        $keys = collect($response->json('data'))->pluck('key')->all();

        $this->assertNotContains('smtp_password', $keys);
        $this->assertNotContains('smtp_username', $keys);
        $this->assertNotContains('smtp_host', $keys);
        $this->assertNotContains('flutterwave_secret_key', $keys);
        $this->assertNotContains('flutterwave_webhook_secret', $keys);
    }

    public function test_sensitive_settings_are_encrypted_at_rest(): void
    {
        $setting = Setting::create([
            'key' => 'test_api_secret',
            'value' => 'super-secret-value',
            'type' => SettingType::STRING,
            'group' => SettingGroup::GENERAL,
            'label' => 'Test API Secret',
            'is_public' => false,
            'is_sensitive' => true,
        ]);

        $raw = \DB::table('settings')->where('key', 'test_api_secret')->value('value');

        $this->assertNotSame('super-secret-value', $raw);
        $this->assertSame('super-secret-value', $setting->fresh()->value);
    }
}

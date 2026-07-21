<?php

namespace Tests\Unit;

use App\Enums\SettingGroup;
use App\Enums\SettingType;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SettingEncryptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_sensitive_values_are_encrypted_at_rest(): void
    {
        $setting = Setting::create([
            'key' => 'test_secret',
            'value' => 'super-secret-value',
            'type' => SettingType::STRING,
            'group' => SettingGroup::GENERAL,
            'label' => 'Test Secret',
            'is_public' => false,
            'is_sensitive' => true,
        ]);

        $raw = DB::table('settings')->where('key', 'test_secret')->value('value');

        $this->assertNotSame('super-secret-value', $raw);
        $this->assertNotEmpty($raw);

        $fresh = $setting->fresh();
        $this->assertSame('super-secret-value', $fresh->value);
        $this->assertSame('super-secret-value', $fresh->typedValue());
    }

    public function test_non_sensitive_values_remain_plaintext(): void
    {
        $setting = Setting::create([
            'key' => 'test_public',
            'value' => 'public-value',
            'type' => SettingType::STRING,
            'group' => SettingGroup::GENERAL,
            'label' => 'Test Public',
            'is_public' => true,
            'is_sensitive' => false,
        ]);

        $raw = DB::table('settings')->where('key', 'test_public')->value('value');

        $this->assertSame('public-value', $raw);
        $this->assertSame('public-value', $setting->fresh()->value);
    }

    public function test_placeholder_is_never_persisted(): void
    {
        $setting = Setting::create([
            'key' => 'test_placeholder',
            'value' => 'initial-value',
            'type' => SettingType::STRING,
            'group' => SettingGroup::GENERAL,
            'label' => 'Test Placeholder',
            'is_public' => false,
            'is_sensitive' => true,
        ]);

        $setting->value = Setting::ENCRYPTED_PLACEHOLDER;
        $setting->save();

        $raw = DB::table('settings')->where('key', 'test_placeholder')->value('value');

        $this->assertNotSame(Setting::ENCRYPTED_PLACEHOLDER, $raw);
        $this->assertSame('initial-value', $setting->fresh()->value);
    }

    public function test_updating_a_sensitive_value_re_encrypts_it(): void
    {
        $setting = Setting::create([
            'key' => 'test_update_secret',
            'value' => 'old-value',
            'type' => SettingType::STRING,
            'group' => SettingGroup::GENERAL,
            'label' => 'Test Update Secret',
            'is_public' => false,
            'is_sensitive' => true,
        ]);

        $setting->value = 'new-value';
        $setting->save();

        $raw = DB::table('settings')->where('key', 'test_update_secret')->value('value');

        $this->assertNotSame('new-value', $raw);
        $this->assertSame('new-value', $setting->fresh()->value);
    }
}

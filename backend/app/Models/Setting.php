<?php

namespace App\Models;

use App\Enums\SettingGroup;
use App\Enums\SettingType;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Setting extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    /** Placeholder sent by masked Filament fields when the secret is unchanged. */
    public const ENCRYPTED_PLACEHOLDER = '__encrypted__';

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
        'options',
        'sort_order',
        'is_public',
        'is_sensitive',
    ];

    protected function casts(): array
    {
        return [
            'type' => SettingType::class,
            'group' => SettingGroup::class,
            'options' => 'array',
            'sort_order' => 'integer',
            'is_public' => 'boolean',
            'is_sensitive' => 'boolean',
        ];
    }

    public function isSensitive(): bool
    {
        return (bool) $this->is_sensitive;
    }

    protected static function booted(): void
    {
        // Safety net: ensure sensitive values are encrypted before persistence even
        // when mass-assignment ordering places `value` before `is_sensitive`.
        static::saving(function (Setting $setting): void {
            $value = $setting->getAttributes()['value'] ?? null;

            if (! $setting->isSensitive() || blank($value)) {
                return;
            }

            if (str_starts_with((string) $value, 'eyJpdiI6')) {
                return;
            }

            $setting->attributes['value'] = Crypt::encryptString($value);
        });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('settings')
            ->acceptsMimeTypes(['image/png', 'image/jpeg', 'image/jpg', 'image/webp'])
            ->singleFile();
    }

    /**
     * Raw value accessor used by existing public API and list views.
     * For image settings, returns the public media URL.
     * For sensitive settings, automatically decrypts the stored ciphertext.
     */
    public function getValueAttribute(?string $value): mixed
    {
        if ($this->type === SettingType::IMAGE) {
            $media = $this->getFirstMedia('settings');

            return $media?->getUrl();
        }

        if ($this->isSensitive() && $value !== null && $value !== '') {
            try {
                return Crypt::decryptString($value);
            } catch (DecryptException $e) {
                // Value may be legacy plaintext during transition.
                return $value;
            }
        }

        return $value;
    }

    /**
     * Encrypt sensitive values before they are written to the database.
     */
    public function setValueAttribute(?string $value): void
    {
        if ($value === self::ENCRYPTED_PLACEHOLDER) {
            // Never persist the masked placeholder. Preserve the existing value if available.
            $this->attributes['value'] = $this->attributes['value'] ?? null;

            return;
        }

        if ($this->isSensitive() && $value !== null && $value !== '') {
            $value = Crypt::encryptString($value);
        }

        $this->attributes['value'] = $value;
    }

    /**
     * Return the value cast to its appropriate PHP type.
     */
    public function typedValue(): mixed
    {
        if ($this->type === SettingType::IMAGE) {
            return $this->getValueAttribute($this->attributes['value'] ?? null);
        }

        $value = $this->attributes['value'] ?? null;

        // Decrypt sensitive values before type casting.
        if ($this->isSensitive() && $value !== null && $value !== '') {
            try {
                $value = Crypt::decryptString($value);
            } catch (DecryptException $e) {
                // Fall back to raw value for legacy plaintext.
            }
        }

        return match ($this->type) {
            SettingType::BOOLEAN => $value === '1' || $value === true || $value === 1,
            SettingType::NUMBER => is_numeric($value) ? (float) $value : 0,
            SettingType::JSON => $value ? json_decode($value, true) : [],
            SettingType::SELECT => $value,
            default => $value,
        };
    }

    public function hasOptions(): bool
    {
        return $this->type === SettingType::SELECT && is_array($this->options) && count($this->options) > 0;
    }

    public function selectOptions(): array
    {
        if (! $this->hasOptions()) {
            return [];
        }

        $options = $this->options;

        // Support both associative [value => label] and sequential [{value, label}] formats.
        if (array_is_list($options)) {
            $mapped = [];
            foreach ($options as $option) {
                if (is_array($option) && isset($option['value'])) {
                    $mapped[$option['value']] = $option['label'] ?? $option['value'];
                }
            }

            return $mapped;
        }

        return $options;
    }

    public function scopeByGroup(Builder $query, SettingGroup $group): Builder
    {
        return $query->where('group', $group->value);
    }

    public function scopeByKey(Builder $query, string $key): Builder
    {
        return $query->where('key', $key);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('label');
    }
}

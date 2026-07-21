<?php

namespace App\Models;

use App\Enums\SettingGroup;
use App\Enums\SettingType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Setting extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
        'options',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'type' => SettingType::class,
            'group' => SettingGroup::class,
            'options' => 'array',
            'sort_order' => 'integer',
        ];
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
     */
    public function getValueAttribute(?string $value): mixed
    {
        if ($this->type === SettingType::IMAGE) {
            $media = $this->getFirstMedia('settings');

            return $media?->getUrl();
        }

        return $value;
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

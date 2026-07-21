<?php

namespace App\Repositories;

use App\Enums\SettingGroup;
use App\Models\Setting;
use Illuminate\Support\Collection;

class SettingRepository
{
    public function __construct(private readonly Setting $model) {}

    public function allByGroup(): Collection
    {
        return $this->model->newQuery()
            ->ordered()
            ->get()
            ->groupBy('group');
    }

    public function getPublicSettings(): Collection
    {
        return $this->model->newQuery()
            ->where('is_public', true)
            ->ordered()
            ->get();
    }

    public function findByKey(string $key): ?Setting
    {
        return $this->model->newQuery()
            ->where('key', $key)
            ->first();
    }

    public function findByGroup(SettingGroup $group): Collection
    {
        return $this->model->newQuery()
            ->byGroup($group)
            ->ordered()
            ->get();
    }

    public function updateOrCreate(string $key, array $data): Setting
    {
        return $this->model->updateOrCreate(['key' => $key], $data);
    }

    public function updateValue(Setting $setting, mixed $value): Setting
    {
        $setting->value = $this->normaliseValue($setting, $value);
        $setting->save();

        return $setting;
    }

    public function search(string $term): Collection
    {
        return $this->model->newQuery()
            ->where('key', 'like', "%{$term}%")
            ->orWhere('label', 'like', "%{$term}%")
            ->orWhere('description', 'like', "%{$term}%")
            ->orWhere('group', 'like', "%{$term}%")
            ->ordered()
            ->get();
    }

    private function normaliseValue(Setting $setting, mixed $value): ?string
    {
        return match ($setting->type) {
            \App\Enums\SettingType::BOOLEAN => $value ? '1' : '0',
            \App\Enums\SettingType::JSON => is_string($value) ? $value : json_encode($value),
            default => $value !== null ? (string) $value : null,
        };
    }
}

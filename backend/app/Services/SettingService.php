<?php

namespace App\Services;

use App\Enums\SettingGroup;
use App\Models\Setting;
use App\Repositories\SettingRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    private const CACHE_TTL = 3600;

    public function __construct(private readonly SettingRepository $repository) {}

    public function allGrouped(): Collection
    {
        return Cache::remember('settings.all_grouped', self::CACHE_TTL, function (): Collection {
            return $this->repository->allByGroup();
        });
    }

    public function publicList(): Collection
    {
        return Cache::remember('settings.public_list', self::CACHE_TTL, function (): Collection {
            return $this->repository->getPublicSettings();
        });
    }

    public function group(SettingGroup $group): Collection
    {
        return Cache::remember("settings.group.{$group->value}", self::CACHE_TTL, function () use ($group): Collection {
            return $this->repository->findByGroup($group);
        });
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $setting = Cache::remember("settings.key.{$key}", self::CACHE_TTL, function () use ($key): ?Setting {
            return $this->repository->findByKey($key);
        });

        if (! $setting) {
            return $default;
        }

        return $setting->typedValue() ?? $default;
    }

    public function set(string $key, mixed $value): Setting
    {
        $setting = $this->repository->findByKey($key);

        if (! $setting) {
            throw new \InvalidArgumentException("Setting [{$key}] does not exist.");
        }

        $this->repository->updateValue($setting, $value);
        $this->flushCache();

        return $setting->fresh();
    }

    public function search(string $term): Collection
    {
        return $this->repository->search($term);
    }

    public function flushCache(): void
    {
        Cache::forget('settings.all_grouped');
        Cache::forget('settings.public_list');

        foreach (SettingGroup::cases() as $group) {
            Cache::forget("settings.group.{$group->value}");
        }

        // Also clear individual keys that may have been cached.
        // In production this would ideally use cache tags; here we rely on TTL.
        foreach (Setting::query()->pluck('key') as $key) {
            Cache::forget("settings.key.{$key}");
        }
    }
}

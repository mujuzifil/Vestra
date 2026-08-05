<?php

namespace Database\Factories;

use App\Enums\MediaAssetStatus;
use App\Enums\MediaAssetType;
use App\Models\MediaAsset;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MediaAsset>
 */
class MediaAssetFactory extends Factory
{
    protected $model = MediaAsset::class;

    public function definition(): array
    {
        $name = fake()->unique()->slug(2).'.jpg';

        return [
            'uuid' => (string) Str::uuid(),
            'disk' => 'public',
            'path' => 'media-library/'.$name,
            'file_name' => $name,
            'original_file_name' => $name,
            'mime_type' => 'image/jpeg',
            'media_type' => MediaAssetType::IMAGE->value,
            'size_bytes' => fake()->numberBetween(1024, 500_000),
            'width' => 1200,
            'height' => 630,
            'checksum' => hash('sha256', fake()->uuid()),
            'status' => MediaAssetStatus::ACTIVE->value,
            'tags' => [],
        ];
    }
}

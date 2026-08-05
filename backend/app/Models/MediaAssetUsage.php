<?php

namespace App\Models;

use App\Enums\MediaUsageContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MediaAssetUsage extends Model
{
    protected $fillable = [
        'media_asset_id',
        'usable_type',
        'usable_id',
        'context',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'context' => MediaUsageContext::class,
            'sort_order' => 'integer',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'media_asset_id');
    }

    public function usable(): MorphTo
    {
        return $this->morphTo();
    }
}

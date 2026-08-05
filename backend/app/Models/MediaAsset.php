<?php

namespace App\Models;

use App\Enums\MediaAssetStatus;
use App\Enums\MediaAssetType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'disk',
        'path',
        'file_name',
        'original_file_name',
        'mime_type',
        'media_type',
        'size_bytes',
        'width',
        'height',
        'checksum',
        'alt_text',
        'caption',
        'description',
        'tags',
        'copyright',
        'internal_notes',
        'status',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'media_type' => MediaAssetType::class,
            'status' => MediaAssetStatus::class,
            'size_bytes' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'tags' => 'array',
        ];
    }

    protected static function booting(): void
    {
        static::creating(function (MediaAsset $asset): void {
            if (! filled($asset->uuid)) {
                $asset->uuid = (string) Str::uuid();
            }
        });
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(MediaAssetUsage::class);
    }

    public function productImages(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function featuredBlogPosts(): HasMany
    {
        return $this->hasMany(BlogPost::class, 'featured_media_asset_id');
    }

    public function url(): ?string
    {
        if (! filled($this->path)) {
            return null;
        }

        if (str_starts_with($this->path, 'http://') || str_starts_with($this->path, 'https://')) {
            return $this->path;
        }

        return asset('storage/'.ltrim($this->path, '/'));
    }

    public function publicPath(): string
    {
        return (string) $this->path;
    }

    public function dimensionsLabel(): ?string
    {
        if (! $this->width || ! $this->height) {
            return null;
        }

        return $this->width.'×'.$this->height;
    }

    public function existsOnDisk(): bool
    {
        try {
            return Storage::disk($this->disk ?: 'public')->exists($this->path);
        } catch (\Throwable) {
            return false;
        }
    }

    public function deleteFile(): void
    {
        try {
            $disk = Storage::disk($this->disk ?: 'public');
            if ($disk->exists($this->path)) {
                $disk->delete($this->path);
            }
        } catch (\Throwable) {
            // File already missing or disk unavailable.
        }
    }
}

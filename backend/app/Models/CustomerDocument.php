<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CustomerDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'type',
        'file_path',
        'file_name',
        'mime_type',
        'size',
        'documentable_type',
        'documentable_id',
        'metadata',
        'is_downloadable',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'attachments' => 'array',
            'is_downloadable' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function fileUrl(): string
    {
        return asset('storage/'.$this->file_path);
    }
}

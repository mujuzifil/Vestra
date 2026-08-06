<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiRequestLog extends Model
{
    use HasFactory;

    /** Append-only request log; schema has `created_at` only. */
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'method',
        'path',
        'status_code',
        'duration_ms',
        'ip',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
            'duration_ms' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

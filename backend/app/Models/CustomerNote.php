<?php

namespace App\Models;

use App\Enums\CustomerNoteType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'user_id',
        'content',
        'type',
        'is_pinned',
    ];

    protected $casts = [
        'type' => CustomerNoteType::class,
        'is_pinned' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerPreference extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerPreferenceFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'notification_preferences',
        'account_preferences',
    ];

    protected function casts(): array
    {
        return [
            'notification_preferences' => 'array',
            'account_preferences' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

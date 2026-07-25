<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CustomerTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'color',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'customer_tag', 'customer_tag_id', 'customer_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

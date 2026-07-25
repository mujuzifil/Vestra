<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchAnalytic extends Model
{
    use HasFactory;

    protected $fillable = [
        'term',
        'user_id',
        'session_id',
        'results_count',
        'clicked_product_id',
        'converted',
        'searched_at',
    ];

    protected $casts = [
        'results_count' => 'integer',
        'converted' => 'boolean',
        'searched_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clickedProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'clicked_product_id');
    }
}

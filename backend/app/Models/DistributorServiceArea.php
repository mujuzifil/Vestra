<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistributorServiceArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'distributor_id',
        'branch_id',
        'region',
        'district',
        'status',
    ];

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(Distributor::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(DistributorBranch::class);
    }
}
